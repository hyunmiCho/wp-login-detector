<?php
/**
 * The class related to 'trusted devices'
 *
 * @since	1.0.0
 *
 * @package    Guardgiant
 * @subpackage Guardgiant/includes
 */
class Guardgiant_Trusted_Device {
	
	/**
	* Sets the trusted device cookie on the users browser.
	* The cookie itself is actually an array of encrypted usernames that we trust on this device. This is because a user
	* may have more than one account and thus log in with more than one username.
	*
	* @since	1.0.0
	*
	* @param	string	$username
	*
	*/
	public static function set_cookie($username) {

		if ( headers_sent() )
			return false;

		$cookie_name = GUARDGIANT_TRUSTED_DEVICE_COOKIE_NAME;

		if (Guardgiant_Trusted_Device::is_user_trusted($username)) {
			// the cookie has already been set for this username on this device so we dont need to do anything
			return;
		}

		// the cookie is an array of encrypted usernames
		$encrypted_usernames_array = array();
		
		// get the data from the existing cookie (if any)
		if(isset($_COOKIE[$cookie_name])) {
			$unsanitized_cookies = $_COOKIE[$cookie_name];
			foreach ($unsanitized_cookies as $key => $value) {
				$encrypted_usernames_array[$key] = sanitize_text_field($value);
			}
		}

		// work out an expiration date
		$cookie_expiration = time() + (YEAR_IN_SECONDS * 5);

		// get the randomly generated salt
		$salt = Guardgiant::get_salt();

		// encrypt this username and add it to the array
		$encrypted_username = hash_hmac( 'md5', $username, $salt );
		array_push($encrypted_usernames_array,$encrypted_username);

		foreach ($encrypted_usernames_array as $key => $value) {
			// set the cookie
			setcookie( $cookie_name.'['.$key.']', $value, $cookie_expiration, COOKIEPATH, COOKIE_DOMAIN );
		}
	}


	/**
	* get the cookie from the browser
	*
	* @since	1.0.0
	*
	* @return	string|False	the value from the cookie if it exists, otherwise False
	*
	*/
	public static function get_cookie()
	{
		
		$cookie_name = GUARDGIANT_TRUSTED_DEVICE_COOKIE_NAME;
		
		$sanitized_cookie = array();

		if ( (isset($_COOKIE[$cookie_name])) && (is_array($_COOKIE[$cookie_name])) ) {
			foreach($_COOKIE[$cookie_name] as $cookie) {
				array_push($sanitized_cookie,sanitize_text_field($cookie));
			}
			
			return $sanitized_cookie;
		} 
		
		return FALSE;
	}


	/**
	* Determine whether a user should be trusted or not?
	*
	* @since	1.0.0
	*
	* @param	string	$username

	* @return	bool	True if the user is trusted, False otherwise
	*
	*/
	public static function is_user_trusted($username)
	{
		
		// do we have a cookie?
		$cookie = Guardgiant_Trusted_Device::get_cookie();
		
		if (!$cookie)
			return FALSE;
		
		// check if it is authentic...
		$salt = Guardgiant::get_salt();
		$expected_cookie_value = hash_hmac( 'md5', $username, $salt );

		// the cookie contains a list of encrypted usernames 
		foreach($cookie as $encrypted_username)
		{
			if ($expected_cookie_value == $encrypted_username) {
				
				return TRUE;
			}
		}
		
		// if we end up here then this username is not trusted on this device (browser)
		return FALSE;

	}


	/**
	* Send an email notification to the user when there is a new login from an unrecognized device
	*
	* @since	1.0.0
	*
	* @param Guardgiant_Login_Activity_Log	$log_entry
	*
	*/
	public static function notify_email( $log_entry ) {
		
		$settings = get_option( 'guardgiant-settings' );

		// Has the site administrator enabled this function?
		if (!isset($settings['notify_user_of_login_from_new_device']))
			return;

		// they may have logged in with their email address or username
		// we need to find their email email address regardless

		if ( (!empty($log_entry->username)) && ( !strpos( $log_entry->username, '@', 1 ) === false ) ){
			$user_email = $log_entry->username;
		} else {
			$user = get_user_by('login', $log_entry->username);
			if ($user)
				$user_email = $user->user_email;
			else {
				// no email address?
				return;
			}
		}
		
		// create a string to present the date and time according to the users preferences...
		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );
		
		$attempt_date = wp_date($date_format,$log_entry->attempt_time);
        $attempt_time = wp_date($time_format,$log_entry->attempt_time);
		$date_time_string = $attempt_date . ' ' . __('at','guardgiant') . ' ' . $attempt_time;
				
		// get some details that we will need to put in the email
		$site_name = get_bloginfo( 'name' );
		$site_url = get_bloginfo( 'url' );
		
		// get the email template from the file
		ob_start();
		include("partials/email_notify_user_of_new_device_login.html");
		$message = ob_get_contents();
		ob_end_clean();

		// build our message by replacing the placeholder text with real information
		$message = str_replace('__SITE_NAME', $site_name, $message);
		$message = str_replace('__HEADLINE', __('New device sign-in','guardgiant'), $message );

		$intro_text = __('A new device has been used to sign in to your account. Please review the details below to make sure it was you:','guardgiant');
		$message = str_replace('__INTRO_TEXT', $intro_text, $message);

		$message = str_replace('__LOGIN_TIME_TEXT', __('Date &amp; time:','guardgiant'), $message);
		$message = str_replace('__LOGIN_TIME', $date_time_string, $message);

		$message = str_replace('__ACCOUNT_NAME_TEXT', __('Account:','guardgiant'), $message);
		$message = str_replace('__ACCOUNT_NAME', $log_entry->username, $message);

		$message = str_replace('__IP_ADDRESS_TEXT', __('IP address:','guardgiant'), $message);
		$message = str_replace('__IP_ADDRESS', $log_entry->ip_address, $message);

		$message = str_replace('__COUNTRY_TEXT', __('Location:','guardgiant'), $message);
		if ($log_entry->ip_location)
			$message = str_replace('__COUNTRY', $log_entry->ip_location, $message);
		else
			$message = str_replace('__COUNTRY', __('Unknown','guardgiant'), $message);

		$message = str_replace('__DEVICE_TYPE_TEXT', __('Type of device:','guardgiant'), $message);
		if ($log_entry->device_type)
			$message = str_replace('__DEVICE_TYPE', $log_entry->device_type, $message);
		else
			$message = str_replace('__DEVICE_TYPE', __('Unknown','guardgiant'), $message);

		$message = str_replace('__BEFORE_BUTTON_TEXT', __("If this was you then no further action is required. If you don't recognize this sign-in, your account may have been accessed by an unauthorized third party. Please use the button below if you wish to change your password.",'guardgiant'), $message);
		$message = str_replace('__RESET_PASSWORD_URL', wp_lostpassword_URL(), $message);
		$message = str_replace('__RESET_PASSWORD_BUTTON_TEXT', __('Reset Your Password', 'guardgiant'), $message);

		// Use HTML, not plain text
		$headers = array('Content-Type: text/html; charset=UTF-8');

		// Set the email subject line
		$subject = '[' . $site_name . '] ' . __('New Sign-in To Your Account', 'guardgiant');

		// Send the email
		@wp_mail( $user_email, $subject, $message, $headers);
	}

	
}