<?php

/**
 * The core functionality of this plugin.
 *
 * @since	1.0.0
 * 
 * @package    Guardgiant
 * @subpackage Guardgiant/public
 */
class Guardgiant_Public {

	private $plugin_name;
	private $version;	// The version of this plugin.

	/**
	 * Initialize the class and set its properties.
	 * 
	 * @since	1.0.0
	 *
	 * @param	string    $plugin_name       The name of the plugin.
	 * @param	string    $version    		The version of this plugin.
	 * 
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since	1.0.0
	 * 
	 * @param		none
	 * 
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/guardgiant-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since	1.0.0
	 * 
	 * @param		none
	 * 
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/guardgiant-public.js', array( 'jquery' ), $this->version, false );

	}
	
	/**
	 * 
	 * The authenticate filter is called before the inbuilt wordpress basic authentication function. 
	 * We perform checks as to whether the remote IP should be blocked, the user account is locked out and 
	 * whether the captcha field is valid
	 *
	 * @since	1.0.0
	 * 
	 * @param	null|WP_User|WP_Error	$user
	 * @param	string					$username
	 * @param 	string					$password
	 *
	 * @return	WP_User|WP_Error
	 * 
	 */
	public function authenticate($user, $username, $password)
	{
		if ((empty($username)) && (empty($password)))
			return $user;

		
		
    	// Check if we have been passed an email address. We work on usernames here.
		if ( (!empty($username)) && ( !strpos( $username, '@', 1 ) === false ) ) {
			$temp = get_user_by('email', $username);
			if ($temp)
				$username = $temp->user_login;
		}
		
		// Get all the options that the user has set on the settings page
		$settings = get_option( 'guardgiant-settings' );
		
		//
		// SECTION 1: This section checks if the IP address is blocked, or the block needs releasing
		//

		// Get the remote ip address of the user
		$remote_ip_address = Guardgiant::get_ip_address();

		// Check if the IP address is blocked, or the block needs releasing:
		if ($remote_ip_address) {

			// Get record of previous failed logins for this IP address
			$ip_failed_logins_record = Guardgiant_IP_Failed_Logins::get_record_for_ip_address($remote_ip_address);

			// Is this IP currently blocked?
			if ( ($ip_failed_logins_record) && ($ip_failed_logins_record->blocked_time) ){

				// Check if this is a trusted user/device. We only act if they are NOT trusted
				if  ( (!isset($settings['never_lockout_trusted_users'])) || (!Guardgiant_Trusted_Device::is_user_trusted($username) ) ) {

					// Check if we have waited long enough so we can release the block on this IP
					if ($ip_failed_logins_record->should_ip_be_blocked() ) {

						// The IP is blocked.

						// increment count for stats
						Guardgiant_Stats::increment_stat_count('blocked_ip_count');

						// Create error message and return
						$blocked_ip_error = new WP_Error();
						$ip_failed_logins_record->create_blocked_ip_error($blocked_ip_error);
						return $blocked_ip_error; 
						
					} else {
						// OK, time to let them have another attempt at logging in
						$ip_failed_logins_record->release_block_on_ip();
					}
				}
			}
		}

		//
		// SECTION 2: This section checks if the user account has been locked out
		//

		// Check if this is a trusted user/device. We only act if they are NOT trusted
		if  ( (!isset($settings['never_lockout_trusted_users'])) || (!Guardgiant_Trusted_Device::is_user_trusted($username) ) ) {
			
			// Get data regarding previous failed login attempts for this particular user
			$user_failed_logins_record = Guardgiant_User_Failed_Logins::get_record_for_user($username);

			// Make sure the user is not in the whitelist 
			if (!Guardgiant::is_item_in_whitelist($username,$settings[ 'whitelist_users' ])) {
			
				// Have they got a record of previous failed attempts?
				if ($user_failed_logins_record) {

					// Is this user currently locked out?
					if ($user_failed_logins_record->locked_out_time) {

						// Yes, but let's check if they still should be? i.e. have they waited long enough so we can release the lockout
						if ($user_failed_logins_record->should_user_be_locked_out()) {

							// increment count for stats
							Guardgiant_Stats::increment_stat_count('user_lockout_count');

							// User is still locked out. Create error message and return
							$locked_out_error = new WP_Error();
							$user_failed_logins_record->create_user_locked_out_error($locked_out_error);
							return $locked_out_error;

						} else {

							// OK, they have served their time. Release the lock so they can have another attempt at logging in
							$user_failed_logins_record->release_lock_out_for_user();
						}
					}
				}
			}
		}

		//
		// SECTION 3: Checking if the captcha has been set and validating if it is correct
		//

		// has the captcha been shown? 
		if ( ($ip_failed_logins_record) && ($ip_failed_logins_record->should_show_captcha_field()) ) {
						
			// get the response to the captcha
			$captcha_submitted = isset($_POST['g-recaptcha-response']) ?  $_POST['g-recaptcha-response'] : '';
			$captcha_submitted = trim(filter_var($captcha_submitted, FILTER_SANITIZE_STRING));

			// Is the captcha correct?
			$capture_error = Guardgiant_Captcha::validate_captcha_code ($captcha_submitted,$remote_ip_address);
			if (is_wp_error($capture_error))
				return $capture_error;
		
		}
	
		// If we get to this point no errors have occured and we can the user object back
		return $user;
	}


	/**
	 * 
	 * The wp_login_failed hook fires after a user login has failed. 
	 * 
	 * @since	1.0.0
	 *
	 * @param	string		$username
	 * @param 	WP_Error	$error
	 *
	 */
	public function wp_login_failed( $username , $error = null ) {
		
		if (!$error) {
			$error = new WP_Error('unknown_error', __('Unknown error','guardgiant'));
			}

		if ($error->get_error_code() == 'expired_session')
			return;		// we dont need to do anything

		// increment count for stats
		Guardgiant_Stats::increment_stat_count('failed_login_count');

		// check if we have been passed an email address rather than username
		if ( (!empty($username)) && ( !strpos( $username, '@', 1 ) === FALSE ) ) {

			// find their username
			$user = get_user_by('email', $username);
			if ($user)
				$username = $user->user_login;
		}

		$settings = get_option( 'guardgiant-settings' );

		// Get the remote IP address of the user
		$remote_ip_address = Guardgiant::get_ip_address();

		// login from a trusted device?
		$trusted_device = Guardgiant_Trusted_Device::is_user_trusted($username);

		// We DONT need to enforce any counter measures if:
		// 1) The user is in the 'user' whitelist OR
		// 2) The ip address is in the 'ip_address' whitelist
		$enforce_counter_measures = TRUE;
		if  ( ($remote_ip_address) && (Guardgiant::is_ip_in_whitelist($remote_ip_address,$settings[ 'whitelist_ip_addresses' ])) )
			$enforce_counter_measures = FALSE;
		if (Guardgiant::is_item_in_whitelist($username,$settings[ 'whitelist_users' ]))
			$enforce_counter_measures = FALSE;
		
		if ($enforce_counter_measures) {
		
			// Get details about consecutive failed login attempts from this particular IP address.
			// This could indicate an attacker trying any number of username/password combinations
			$ip_failed_logins_record = Guardgiant_IP_Failed_Logins::get_record_for_ip_address($remote_ip_address);
			if (!$ip_failed_logins_record) 
				$ip_failed_logins_record = new Guardgiant_IP_Failed_Logins($remote_ip_address);

			// Is this IP already blocked?
			if ($ip_failed_logins_record->blocked_time) {
				$this->cleanup_login_errors_for_display_to_user($error);	// only show this error (remove others)
				return;
			}
			else {
				// Update record
				$ip_failed_logins_record->attempt_count++; 
				$ip_failed_logins_record->username = $username;
				$ip_failed_logins_record->last_attempt_time = time();
				$ip_failed_logins_record->save();
				
				// Check if we should block this IP address
				if ($ip_failed_logins_record->should_ip_be_blocked()) {

					// Yes, block it
					if (!$ip_failed_logins_record->blocked_time) {
						$ip_failed_logins_record->block_ip();
						$ip_failed_logins_record->create_blocked_ip_error($error);

						// remove any other errors
						$this->cleanup_login_errors_for_display_to_user($error);	

						// log the attempt
						$this->add_login_attempt_to_the_activity_log($remote_ip_address, $username, $trusted_device, $error);

						// increment count for stats
						Guardgiant_Stats::increment_stat_count('blocked_ip_count');

						// now is a good time to do some quick housekeeping
						Guardgiant::do_housekeeping();

						return;					
					}
				}
			}
		}
		
		// OK, next lets look at consecutive failed login attempts for this username. 
		// We are trying to mitigate against distributed brute force attacks where an attacker repeatedly attempts
		// to log in using the same username but from many different IP addresses.

		if ($enforce_counter_measures) {

			$user_failed_logins_record = Guardgiant_User_Failed_Logins::get_record_for_user($username);

			if (!$user_failed_logins_record) 
				$user_failed_logins_record = new Guardgiant_User_Failed_Logins($username);

			// update record with this failed attempt to login
			$user_failed_logins_record->attempt_count++; 
			$user_failed_logins_record->user_ip = $remote_ip_address;
			$user_failed_logins_record->last_attempt_time = time();
			$user_failed_logins_record->save();

			// should we lock out them out?
			if ($user_failed_logins_record->should_user_be_locked_out()) {	

				// Check if this is a trusted user/device. We only act if they are NOT trusted
				if ( (!isset($settings['never_lockout_trusted_users'])) || (!Guardgiant_Trusted_Device::is_user_trusted($username) ) ) {

					// lock them out
					if (!$user_failed_logins_record->locked_out_time) {
						$user_failed_logins_record->lock_out_user();

						$user_failed_logins_record->create_user_locked_out_error($error);

						// increment count for stats
						Guardgiant_Stats::increment_stat_count('user_lockout_count');
					}
				}
			}
		}

		// Do we need to obfuscate the error messages that are about to be displayed to the user?
		if (isset($settings['obfuscate_login_errors'])) {
			$this->obfuscate_errors($error);
		}

		// Prepare error messages for display
		$this->cleanup_login_errors_for_display_to_user($error);

		// Add this login attempt to the activity log
		$this->add_login_attempt_to_the_activity_log($remote_ip_address, $username, $trusted_device, $error);
	}


	/**
	 * The wp_login hook fires after a successful user login. 
	 *
	 * @since	1.0.0
	 * 
	 * @param	string		$username
	 *
	 */
	public function wp_login ($username) {

		// increment count for stats
		Guardgiant_Stats::increment_stat_count('success_login_count');

		$settings = get_option( 'guardgiant-settings' );
		$remote_ip_address = Guardgiant::get_ip_address();

		// Reset (delete) any failed logins records		
		Guardgiant_User_Failed_Logins::delete_record_for_user($username);

		if (isset($settings['reset_IP_failed_login_count_after_successful_login']))
			Guardgiant_IP_Failed_Logins::delete_record_for_ip($remote_ip_address);

		

		// is this a login from an unrecognized device?
		$trusted_device = Guardgiant_Trusted_Device::is_user_trusted($username);

		// Log this in the activity log
		$log_entry = $this->add_login_attempt_to_the_activity_log($remote_ip_address, $username, $trusted_device);

		if  (!$trusted_device) {

			// set a cookie to mark this device/browser as trusted
			Guardgiant_Trusted_Device::set_cookie($username);

			// send a security notice to the user that someone has logged in 
			// to their account from a new device
			Guardgiant_Trusted_Device::notify_email( $log_entry );
		}

		
		
	}


	/**
	 * The login_form hook fires following the ‘Password’ field in the login form.
	 * We insert an additional captcha form field if required 
	 *
	 * @since	1.0.0
	 *
	 */
	public function login_form()
	{
		$settings = get_option( 'guardgiant-settings' );

		// Get the remote IP address of the user
		$remote_ip_address = Guardgiant::get_ip_address();

		// Make sure the IP address is not in the whitelist
		if (!Guardgiant::is_item_in_whitelist($remote_ip_address,$settings[ 'whitelist_ip_addresses' ])) {

			// Get details about consecutive failed login attempts from this particular IP address.
			$ip_failed_logins_record = Guardgiant_IP_Failed_Logins::get_record_for_ip_address($remote_ip_address);

			if ($ip_failed_logins_record) {
				// Shall we show the captcha field in the login form?
				if ($ip_failed_logins_record->should_show_captcha_field()) {
					Guardgiant_captcha::show_captcha_field();
				} 
			}
		}
	}

	/**
	 * Reword error messages to stop hackers working out valid usernames
	 * 
	 * @since	1.0.0
	 * 
	 * @param	WP_Error		The instance we are working on (passed by reference)
	 */
	private function obfuscate_errors(&$error)
	{
		
		// Get the list of error codes. we are going to look for things like 'invalid_username' etc
		$error_codes =  $error->get_error_codes();
		foreach ($error_codes as $error_code) {
		
			switch ($error_code) {
				case ('invalid_username'):
				case ('invalid_email'):
				case ('incorrect_password'):
					// We dont want to display the default error message so lets remove it
					$error->remove($error_code);
					// Now add our own error message 
					$disp_msg = '<strong>' . __('Error','guardgiant') . ':</strong> ';
					$disp_msg .= __( 'Incorrect username or password.','guardgiant') . ' <a href="http://wp.localhost/wp-login.php?action=lostpassword">' . __('Lost your password?','guardgiant') . '</a>';
					$error->add('unknown_credentials', $disp_msg );
					break;
				default:
					// Do nothing with other error messages	
						
			}
		}
	}


	/**
	 * Allows only the specified error to be shown. All other errors are removed.
	 * For example, if the IP address is blocked we dont want to confuse the user by showing any other errors.
	 *
	 * @since	1.0.0
	 * 
	 * @param	string			$allowed_error_code
	 * @param	WP_Error		the instance we are working on (passed by reference)
	 *
	 */
	private function restrict_errors_to_specific_error_code($allowed_error_code, &$error)
	{
		$error_codes = $error->get_error_codes();
		
		// removes all errors that dont have the provided error code
		if ( in_array( $allowed_error_code, $error_codes ) )
		{
			foreach ($error_codes as $existing_error_code) {
				if ($existing_error_code != $allowed_error_code)
					$error->remove($existing_error_code);
			}
		}
		
	}

	/**
	 * Make sure we only show one error based on it's priority. 
	 * For example, we dont want to show invalid username error if the IP is blocked
	 *
	 * @since	1.0.0
	 * 
	 * @param	WP_Error		instance passwd by reference
	 *
	 */
	private function cleanup_login_errors_for_display_to_user( &$error)
	{
		$error_code_to_display = null;
		$error_codes = $error->get_error_codes();

		$has_captcha_error = in_array( 'incorrect_captcha', $error_codes );
		$has_user_lockout_error = in_array( 'locked_out', $error_codes );
		$has_ip_blocked_error = in_array( 'ip_blocked', $error_codes );

		// ip blocked has highest prority, always display it
		if ($has_ip_blocked_error) {
			$error_code_to_display	= 'ip_blocked';
		} else {
			// next, user lockout takes priority
			if ($has_user_lockout_error)
				$error_code_to_display	= 'locked_out';
			else {
				if ($has_captcha_error)
					$error_code_to_display	= 'incorrect_captcha';
			}
		}

		if ($error_code_to_display)
			$this->restrict_errors_to_specific_error_code($error_code_to_display, $error);

	}


	/**
	 * Add login attempt to the activity log
	 * 
	 * @since	1.0.0
	 *
	 * @param	string		ip address
	 * @param	string		username 
	 * @param	bool		is device trusted?
	 * @param	WP_Error	only if this was a failed login attempt
	 *
	 */
	public function add_login_attempt_to_the_activity_log($ip_address, $username, $trusted_device = False, $error = NULL ) {

		$settings = get_option( 'guardgiant-settings' );

		// this must be a successful login if no error is passed to us
		if ($error == NULL) {
			$log = new Guardgiant_Login_Activity_Log($ip_address, $username, $trusted_device, 'success', __('Successful login.','guardgiant'));
		}
		else {
			// get the core details of the error 
			$error_code = $error->get_error_code();

			$error_message = $error->get_error_message();
			$error_message = strip_tags($error_message);

			// Log this in the activity log
			$log = new Guardgiant_Login_Activity_Log($ip_address, $username, $trusted_device, $error_code, $error_message);

		}

		// get the IPs location if the site administrator has enabled this feature
		if (isset($settings['use_ip_address_geolocation']))
		@$log->get_ip_location();

		$log->set_device_type_from_user_agent_string();
		$log->save();
		return $log;
	}


	/**
	 *
	 * @since	1.0.0
	 * 
	 * Cron job used for housekeeping the database tables etc.
	 *
	 */
	public function guardgiant_cron_job() {
		Guardgiant::do_housekeeping();
	}


	/**
	 * Disable XMLRPC 
	 * The user can set this functionality in the general settings page
	 *
	 * @since	2.1.2
	 *
	 */
	public function xmlrpc_enabled() {
		$settings = get_option( 'guardgiant-settings' );

		if ( (isset($settings['disable_xmlrpc'])) && ($settings['disable_xmlrpc']) )
			return false;
		else
			return true;

	}


	/**
	 * Require the user to be logged in to list users via API
	 *
	 * @since	2.2.3
	 * 
	 * @param 	WP_Error	
	 *
	 */
	public function rest_authentication_errors( $errors ) {

		$settings = get_option( 'guardgiant-settings' );

		if ( (isset($settings['require_wordpress_api_auth'])) && ($settings['require_wordpress_api_auth']) )
		{
			if ( ( preg_match( '/users/', $_SERVER['REQUEST_URI'] ) !== 0 ) || ( isset( $_REQUEST['rest_route'] ) && ( preg_match( '/users/', $_REQUEST['rest_route'] ) !== 0 ) ) ) {
				if ( ! is_user_logged_in() ) {
					return new WP_Error( 'auth_error', __( 'You must be logged in to use this endpoint.', 'guardgiant' ), array( 'status' => rest_authorization_required_code() ) );
				}
			}
		}
	
		return $errors;
	}

	/**
	 * Handle errors on the lost password form
	 *
	 * @since	2.2.3
	 * 
	 * @param	WP_Error 	A WP_Error object containing any errors generated by using invalid credentials. 
	 *
	 */
	function lost_password($errors) {
		
		// is there an error on the lost password form?
		if( is_wp_error( $errors ) ) {

			// get the type of error
			$error_code = $errors->get_error_code();

			if ( ($error_code == 'invalid_email') || ($error_code == 'invalidcombo') ) {

				// check if we need to obfuscate this error
				$settings = get_option( 'guardgiant-settings' );
				if (isset($settings['obfuscate_login_errors'])) {

					// we need to obfuscate the error so redirect as if all ok
					wp_safe_redirect('wp-login.php?checkemail=confirm');
				}

			}
		
		}
	}
}
