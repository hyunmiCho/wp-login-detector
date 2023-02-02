<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @since	1.0.0
 *
 * @package    Guardgiant
 * @subpackage Guardgiant/admin
 */

/**
 * Core class used to implement the Guardgiant_Admin object.
 *
 * @since	1.0.0
 *
 */
class Guardgiant_Admin {

	private $plugin_name;
	private $version;	// The current version of this plugin

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 * 
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since	1.0.0
	 * 
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/guardgiant-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 1.0.0
	 * 
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/guardgiant-admin.js', array( 'jquery' ), $this->version, false );

	}


	/**
	 * Register the settings page for the admin area.
	 *
	 * @since 1.0.0
	 * 
	 */
	public function register_settings_page() {

		// Create our menu page.
		Guardgiant_Admin::apply_default_settings_if_needed();

		add_menu_page(
			__( 'GuardGiant', 'guardgiant' ),
			'GuardGiant',
			'manage_options',
			'guardgiant',
			array( $this, 'display_settings_page' ) ,
			'dashicons-privacy', 70
		);

		$settings_hook = add_submenu_page(
			'guardgiant',								// Register this submenu under this parent 
			__( 'Settings', 'guardgiant' ),			// The text to the display in the browser when this menu item is active
			__( 'Settings', 'guardgiant' ),        			// The text for this menu item
			'manage_options',                        			// Which type of users can see this menu
			'guardgiant',                            			// The unique ID - the slug - for this menu item
			array( $this, 'display_settings_page' )  			// The function used to render the menu for this page to the screen
		);

		$activity_log_hook = add_submenu_page(
			'guardgiant',								// Register this submenu under this parent 
			__( 'Activity Log', 'guardgiant' ),			// The text to the display in the browser when this menu item is active
			__( 'Activity Log', 'guardgiant' ),        			// The text for this menu item
			'manage_options',                        			// Which type of users can see this menu
			'guardgiant-login-activity-log',                            			// The unique ID - the slug - for this menu item
			array( $this, 'display_activity_log_page' )  			// The function used to render the menu for this page to the screen
		);

		add_action( "load-".$activity_log_hook, 'Guardgiant_Admin::add_screen_option' );
		add_action( "load-".$settings_hook, 'Guardgiant_Admin::add_settings_page_help_tab' );
	}


	

	/**
	 * Add the screen option on the activity log page
	 *
	 * @since 1.0.0
	 * 
	 */
	public static function add_screen_option() {
		global $guardgiant_activity_log_table;

		$option = 'per_page';
		 
		$args = array(
			'label' => 'Number of items per page',
			'default' => GUARDGIANT_DEFAULT_ITEMS_PER_PAGE_ON_ACTIVITY_LOG,
			'option' => 'guardgiant_login_entries_per_page'
		);
		 
		add_screen_option( $option, $args );	
		
		$guardgiant_activity_log_table = new Guardgiant_Table_Login_Activity_Log();

		// add a help tab

		// set up the text content
		$overview_content = '<p>' . __("This screen provides visibility to all login attempts on your site. You can customize the display of this screen to suit your needs.",'guardgiant') . '</p>';
		$screen_content = '<p>' . __("You can customize the display of this screen’s contents in a number of ways:",'guardgiant') . '</p>';
		$screen_content .= '<ul><li>' . __("You can hide/display columns based on your needs and decide how many login attempts to list per screen using the Screen Options tab.",'guardgiant') . '</li>';
		$screen_content .= '<li>' . __("You can filter the login attempts by time period using the text links above the table, for example to only show login attempts within the last 7 days. The default view is to show all available data.",'guardgiant') . '</li>';
		$screen_content .= '<li>' . __("You can search for login attempts by a certain IP address using the search box.",'guardgiant') . '</li>';
		$screen_content .= '<li>' . __("You can refine the list to show only failed or successful login attempts or from trusted devices by using the dropdown menus above the table. Click the Filter button after making your selection.",'guardgiant') . '</li></ul>';

		$current_screen = get_current_screen();
		
		// register our help overview tab
		$current_screen->add_help_tab( array(
			'id' => 'gg_activity_help_overview',
			'title' => __('Overview','guardgiant'),
			'content' => $overview_content
			)
			);

		// register our screen content tab
		$current_screen->add_help_tab( array(
			'id' => 'gg_activity_help_screen_content',
			'title' => __('Screen Content','guardgiant'),
			'content' => $screen_content
			)
			);	

	}


	/**
	 * Set the screen option.
	 *
	 * @since 1.0.0
	 * 
	 */
	public function set_screen_option($status, $option, $value) {
			return $value;
	}




	/**
	 * Display the settings page content.
	 *
	 * @since 1.0.0
	 * 
	 */
	public function display_settings_page() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/guardgiant-admin-display.php';

	}


	/**
	 * Display the activity log page content.
	 *
	 * @since 1.0.0
	 * 
	 */
	public function display_activity_log_page() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/guardgiant-admin-activity-log.php';

	}


	/**
	 * Register the settings for our settings page.
	 *
	 * @since	1.0.0
	 * 
	 */
	public function register_settings() {


		 

		// Here we are going to register our setting.
		register_setting(
			'guardgiant_options_group',							// Option group name
			'guardgiant-settings',								// Option name
			array( $this, 'sanitize_settings' )					// Sanitize callback
		);


		// Add a section for the trusted devices.
		add_settings_section(
			'guardgiant_trusted_devices_settings_section',				// ID used to identify this section and with which to register options
			'',									// Title to be displayed on the administration page
			array( $this, 'trusted_devices_settings_section_callback' ),	// Callback used to render the description of the section
			'guardgiant_brute_force_page'								// Page on which to add this section of options
		);


		// Add the individual form fields for the user lockout section

		add_settings_field(
			'enable_lockout_of_users_with_multiple_failed_login_attempts',		// ID used to identify the field
			__('Limit Login Attempts','guardgiant'),				// The label to the left of the option interface element
			array( $this, 'settings_field_input_checkbox_and_2numbers_callback' ),		// The name of the function responsible for rendering the option interface
			'guardgiant_brute_force_page',									// The page on which this option will be displayed
			'guardgiant_trusted_devices_settings_section',						// The name of the section to which this field belongs
			array(
				'label_for_1' => 'enable_lockout_of_users_with_multiple_failed_login_attempts',
				'label_for_2' => 'num_of_failed_logins_before_mitigation_starts',
				'label_for_3' => 'mins_to_lockout_account',
				'before_text' => __( 'After ', 'guardgiant' ),
				'middle_text' => __( 'failed login attempts, lock out the account for ', 'guardgiant' ),
				'after_text' => __( 'minutes.', 'guardgiant' ),
				'default2' => GUARDGIANT_DEFAULT_NUM_OF_FAILED_LOGINS_BEFORE_MITIGATION_STARTS,
				'default3'   => GUARDGIANT_DEFAULT_MINS_TO_LOCKOUT_ACCOUNT,
				
			)															// The array of arguments to pass to the callback
		);

		add_settings_field(
			'never_lockout_trusted_users',									// ID used to identify the field
			__( 'Trusted Devices', 'guardgiant' ),							// The label to the left of the option interface element
			array( $this, 'settings_field_single_checkbox_callback' ),	// The name of the function responsible for rendering the option interface
			'guardgiant_brute_force_page',									// The page on which this option will be displayed
			'guardgiant_trusted_devices_settings_section',						// The name of the section to which this field belongs
			array(
				'label_for' => 'never_lockout_trusted_users',
				'description' => __( 'Never lock out login attempts from trusted devices.', 'guardgiant' ),
			)															// The array of arguments to pass to the callback 
		);

		add_settings_field(
			'notify_user_of_login_from_new_device',									// ID used to identify the field
			'',							// The label to the left of the option interface element
			array( $this, 'settings_field_single_checkbox_callback' ),	// The name of the function responsible for rendering the option interface
			'guardgiant_brute_force_page',									// The page on which this option will be displayed
			'guardgiant_trusted_devices_settings_section',						// The name of the section to which this field belongs
			array(
				'label_for' => 'notify_user_of_login_from_new_device',
				'description' => __( 'Notify users when there is a successful login from a new device.', 'guardgiant' ),
				
			)															// The array of arguments to pass to the callback 
		);

		// Here we are going to add a section for IP blocking section
		add_settings_section(
			'guardgiant_block_ip_settings_section',					// ID used to identify this section and with which to register options
			'',									// Title to be displayed on the administration page
			array( $this, 'block_ip_settings_section_callback' ),	// Callback used to render the description of the section
			'guardgiant_brute_force_page'								// Page on which to add this section of options
		);

		// Add the individual form fields for the IP blocking section
			

		add_settings_field(
			'enable_login_captcha',		// ID used to identify the field
			__('Captcha','guardgiant'),				// The label to the left of the option interface element
			array( $this, 'settings_field_input_checkbox_and_number_callback' ),		// The name of the function responsible for rendering the option interface
			'guardgiant_brute_force_page',									// The page on which this option will be displayed
			'guardgiant_block_ip_settings_section',						// The name of the section to which this field belongs
			array(
				'label_for_1' => 'enable_login_captcha',
				'label_for_2' => 'num_of_failed_logins_by_IP_before_captcha_shown',
				'before_text' => __( '', 'guardgiant' ),
				'middle_text' => __( 'Add a Captcha field to the login form after ', 'guardgiant' ),
				'after_text' => __( ' failed login attempts.', 'guardgiant' ),
				'default2'   => GUARDGIANT_DEFAULT_NUM_OF_FAILED_LOGINS_BY_IP_BEFORE_CAPTCHA_SHOWN,
			)															// The array of arguments to pass to the callback
		);
	

		add_settings_field(
			'num_of_failed_logins_by_IP_before_mitigation_starts',		// ID used to identify the field
			__('Block IP Address','guardgiant'),				// The label to the left of the option interface element
			array( $this, 'settings_field_input_checkbox_and_2numbers_callback' ),		// The name of the function responsible for rendering the option interface
			'guardgiant_brute_force_page',									// The page on which this option will be displayed
			'guardgiant_block_ip_settings_section',						// The name of the section to which this field belongs
			array(
				'label_for_1' => 'enable_blocking_of_ips_with_multiple_failed_login_attempts',
				'label_for_2' => 'num_of_failed_logins_by_IP_before_mitigation_starts',
				'label_for_3' => 'mins_to_block_ip',
				'before_text' => __( 'After ', 'guardgiant' ),
				'middle_text' => __( 'failed login attempts, block the IP address for ', 'guardgiant' ),
				'after_text' => __( ' minutes.', 'guardgiant' ),
				'default2' => GUARDGIANT_DEFAULT_NUM_OF_FAILED_LOGINS_BY_IP_BEFORE_MITIGATION_STARTS,
				'default3'   => GUARDGIANT_DEFAULT_MINS_TO_BLOCK_IP,
			)															// The array of arguments to pass to the callback
		);
		
		
		add_settings_field(
			'block_IP_on_each_subsequent_failed_attempt',									// ID used to identify the field
			'',							// The label to the left of the option interface element
			array( $this, 'settings_field_single_checkbox_callback' ),	// The name of the function responsible for rendering the option interface
			'guardgiant_brute_force_page',									// The page on which this option will be displayed
			'guardgiant_block_ip_settings_section',						// The name of the section to which this field belongs
			array(
				'label_for' => 'block_IP_on_each_subsequent_failed_attempt',
				'description' => __( 'Block again on each subsequent failed login attempt.', 'guardgiant' )
			)															// The array of arguments to pass to the callback 
		);


		add_settings_field(
			'block_IP_on_each_subsequent_failed_attempt',		// ID used to identify the field
			'',				// The label to the left of the option interface element
			array( $this, 'settings_field_input_checkbox_and_number_callback' ),		// The name of the function responsible for rendering the option interface
			'guardgiant_brute_force_page',									// The page on which this option will be displayed
			'guardgiant_block_ip_settings_section',						// The name of the section to which this field belongs
			array(
				'label_for_1' => 'block_IP_on_each_subsequent_failed_attempt',
				'label_for_2' => 'block_IP_on_each_subsequent_failed_attempt_mins',
				'before_text' => __( '', 'guardgiant' ),
				'middle_text' => __( 'Increase the block time by ', 'guardgiant' ),
				'after_text' => __( ' minutes after each subsequent failed login attempt.', 'guardgiant' ),
				'default2'   => GUARDGIANT_DEFAULT_BLOCK_IP_ON_EACH_SUBSEQUENT_FAILED_ATTEMPT_MINS,
			)															// The array of arguments to pass to the callback
		);

		add_settings_field(
			'expire_ip_failed_logins_record',		// ID used to identify the field
			'',				// The label to the left of the option interface element
			array( $this, 'settings_field_input_checkbox_and_number_callback' ),		// The name of the function responsible for rendering the option interface
			'guardgiant_brute_force_page',									// The page on which this option will be displayed
			'guardgiant_block_ip_settings_section',						// The name of the section to which this field belongs
			array(
				'label_for_1' => 'expire_ip_failed_logins_record',
				'label_for_2' => 'expire_ip_failed_logins_record_in_hours',
				'before_text' => __( '', 'guardgiant' ),
				'middle_text' => __( 'Reset after ', 'guardgiant' ),
				'after_text' => __( ' hours.', 'guardgiant' ),
				'default2'   => GUARDGIANT_DEFAULT_EXPIRE_IP_FAILED_LOGINS_RECORD_IN_HOURS,
				
				
			)															// The array of arguments to pass to the callback
		);
	
		add_settings_field(
			'reset_IP_failed_login_count_after_successful_login',									// ID used to identify the field
			'',							// The label to the left of the option interface element
			array( $this, 'settings_field_single_checkbox_callback' ),	// The name of the function responsible for rendering the option interface
			'guardgiant_brute_force_page',									// The page on which this option will be displayed
			'guardgiant_block_ip_settings_section',						// The name of the section to which this field belongs
			array(
				'label_for' => 'reset_IP_failed_login_count_after_successful_login',
				'description' => __( 'Reset after a successful login.', 'guardgiant' ),
				'further_text' => __( 'Do not enable this if an attacker can sign up for an account on your site.','guardgiant')
			)															// The array of arguments to pass to the callback 
		);

		

		


		// Here we are going to add a section for whitelists.
		add_settings_section(
			'guardgiant_whitelists_section',						// ID used to identify this section and with which to register options
			'',										// Title to be displayed on the administration page
			array( $this, 'whitelists_section_callback' ),		// Callback used to render the description of the section
			'guardgiant_whitelists_page'									// Page on which to add this section of options
		);

		add_settings_field(
			'whitelist_users',										// ID used to identify the field
			__( 'User Whitelist', 'guardgiant' ),					// The label to the left of the option interface element
			array( $this, 'settings_field_input_textarea_callback' ),	// The name of the function responsible for rendering the option interface
			'guardgiant_whitelists_page',								// The page on which this option will be displayed
			'guardgiant_whitelists_section',				// The name of the section to which this field belongs
			array(
				'label_for' => 'whitelist_users',
				'default'   => '',
				'rows' => '5',
				'after_text' => __('This is a list of usernames that will never be locked out. Please enter one username per line.','guardgiant')
				
			)														// The array of arguments to pass to the callback
		);

		add_settings_field(
			'whitelist_ip_addresses',									// ID used to identify the field
			__( 'IP Address Whitelist', 'guardgiant' ),					// The label to the left of the option interface element
			array( $this, 'settings_field_input_textarea_callback' ),	// The name of the function responsible for rendering the option interface
			'guardgiant_whitelists_page',									// The page on which this option will be displayed
			'guardgiant_whitelists_section',						// The name of the section to which this field belongs
			array(
				'label_for' => 'whitelist_ip_addresses',
				'default'   => '',
				'rows' => '5',
				'after_text' => __('This is a list of IP addresses that will never be blocked. Please enter one IP address per line.','guardgiant')
				
			)															// The array of arguments to pass to the callback
		);


		// Here we are going to add a section for Captcha.
		add_settings_section(
			'guardgiant_captcha_section',						// ID used to identify this section and with which to register options
			'',										// Title to be displayed on the administration page
			array( $this, 'captcha_section_callback' ),		// Callback used to render the description of the section
			'guardgiant_captcha_page'									// Page on which to add this section of options
		);


		add_settings_field(
			'recaptcha_site_key',									// ID used to identify the field
			__( 'Site Key (reCaptcha v2)', 'guardgiant' ),					// The label to the left of the option interface element
			array( $this, 'settings_field_input_text_callback' ),	// The name of the function responsible for rendering the option interface
			'guardgiant_captcha_page',									// The page on which this option will be displayed
			'guardgiant_captcha_section',						// The name of the section to which this field belongs
			array(
				'label_for' => 'recaptcha_site_key',
				'default'   => '',
				
			)															// The array of arguments to pass to the callback
		);

		add_settings_field(
			'recaptcha_secret_key',									// ID used to identify the field
			__( 'Secret Key (reCaptcha v2)', 'guardgiant' ),					// The label to the left of the option interface element
			array( $this, 'settings_field_input_text_callback' ),	// The name of the function responsible for rendering the option interface
			'guardgiant_captcha_page',									// The page on which this option will be displayed
			'guardgiant_captcha_section',						// The name of the section to which this field belongs
			array(
				'label_for' => 'recaptcha_secret_key',
				'default'   => '',
			)															// The array of arguments to pass to the callback
		);



		// Here we are going to add a section for general settings.
		add_settings_section(
			'guardgiant_general_settings_section',						// ID used to identify this section and with which to register options
			'',										// Title to be displayed on the administration page
			array( $this, 'general_settings_section_callback' ),		// Callback used to render the description of the section
			'guardgiant_general_settings_page'									// Page on which to add this section of options
		);

		// Here we are going to add fields to our general settings section.
		add_settings_field(
			'obfuscate_login_errors',									// ID used to identify the field
			__( 'Login Errors', 'guardgiant' ),							// The label to the left of the option interface element
			array( $this, 'settings_field_single_checkbox_callback' ),	// The name of the function responsible for rendering the option interface
			'guardgiant_general_settings_page',									// The page on which this option will be displayed
			'guardgiant_general_settings_section',						// The name of the section to which this field belongs
			array(
				'label_for' => 'obfuscate_login_errors',
				'description' => __( 'Don’t let WordPress reveal which users are valid in error messages.', 'guardgiant' )
			)															// The array of arguments to pass to the callback 
		);

		add_settings_field(
			'show_mins_remaining_in_error_msg',							// ID used to identify the field
			__( '', 'guardgiant' ),										// The label to the left of the option interface element
			array( $this, 'settings_field_single_checkbox_callback' ),	// The name of the function responsible for rendering the option interface
			'guardgiant_general_settings_page',									// The page on which this option will be displayed
			'guardgiant_general_settings_section',						// The name of the section to which this field belongs
			array(
				'label_for' => 'show_mins_remaining_in_error_msg',
				'description' => __( 'Show lockout minutes remaining in error messages.', 'guardgiant' )
			)															// The array of arguments to pass to the callback 
		);

		add_settings_field(
			'use_ip_address_geolocation',							// ID used to identify the field
			__( 'IP Address Geolocation', 'guardgiant' ),										// The label to the left of the option interface element
			array( $this, 'settings_field_single_checkbox_callback' ),	// The name of the function responsible for rendering the option interface
			'guardgiant_general_settings_page',									// The page on which this option will be displayed
			'guardgiant_general_settings_section',						// The name of the section to which this field belongs
			array(
				'label_for' => 'use_ip_address_geolocation',
				'description' => __( 'Use geolocation service to lookup locations of IP addresses.', 'guardgiant' )
			)															// The array of arguments to pass to the callback 
		);

		add_settings_field(
			'disable_xmlrpc',							// ID used to identify the field
			__( 'XMLRPC', 'guardgiant' ),										// The label to the left of the option interface element
			array( $this, 'settings_field_single_checkbox_callback' ),	// The name of the function responsible for rendering the option interface
			'guardgiant_general_settings_page',									// The page on which this option will be displayed
			'guardgiant_general_settings_section',						// The name of the section to which this field belongs
			array(
				'label_for' => 'disable_xmlrpc',
				'description' => __( 'Disable XMLRPC service.', 'guardgiant' )
			)															// The array of arguments to pass to the callback 
		);

		add_settings_field(
			'require_wordpress_api_auth',							// ID used to identify the field
			__( 'WordPress API', 'guardgiant' ),										// The label to the left of the option interface element
			array( $this, 'settings_field_single_checkbox_callback' ),	// The name of the function responsible for rendering the option interface
			'guardgiant_general_settings_page',									// The page on which this option will be displayed
			'guardgiant_general_settings_section',						// The name of the section to which this field belongs
			array(
				'label_for' => 'require_wordpress_api_auth',
				'description' => __( 'Refuse guest access to certain API calls (stops user enumeration).', 'guardgiant' )
			)															// The array of arguments to pass to the callback 
		);

		add_settings_field(
			'delete_login_activity_records_from_db_after_days',									// ID used to identify the field
			__( 'Activity Log', 'guardgiant' ),					// The label to the left of the option interface element
			array( $this, 'settings_field_input_number_callback' ),	// The name of the function responsible for rendering the option interface
			'guardgiant_general_settings_page',									// The page on which this option will be displayed
			'guardgiant_general_settings_section',						// The name of the section to which this field belongs
			array(
				'label_for' => 'delete_login_activity_records_from_db_after_days',
				'before_text'   => 'Keep records for ',
				'after_text' => 'days'
				
			)															// The array of arguments to pass to the callback
		);

		// Here we are going to add a section for general settings.
		add_settings_section(
			'guardgiant_reverse_proxy_section',						// ID used to identify this section and with which to register options
			'',										// Title to be displayed on the administration page
			array( $this, 'reverse_proxy_section_callback' ),		// Callback used to render the description of the section
			'guardgiant_reverse_proxy_page'									// Page on which to add this section of options
		);

		add_settings_field(
			'auto_detect_reverse_proxy',										// ID used to identify the field
			__( 'Auto Detect', 'guardgiant' ),					// The label to the left of the option interface element
			array( $this, 'settings_field_radio_buttons_callback' ),	// The name of the function responsible for rendering the option interface
			'guardgiant_reverse_proxy_page',									// The page on which this option will be displayed
			'guardgiant_reverse_proxy_section',						// The name of the section to which this field belongs
			array(
				'label_for' => 'auto_detect_reverse_proxy',
				'value1' => GUARDGIANT_AUTO_DETECT_REVERSE_PROXY_SETTINGS,
				'value2' => GUARDGIANT_USE_MANUAL_SETTINGS_FOR_REVERSE_PROXY,
				'description1' => __( 'Auto detect reverse proxy settings.', 'guardgiant' ),
				'description2' => __( 'Use manual settings below:', 'guardgiant' )
			)															// The array of arguments to pass to the callback 
		);	


		add_settings_field(
			'site_uses_reverse_proxy',										// ID used to identify the field
			__( 'Reverse Proxy', 'guardgiant' ),					// The label to the left of the option interface element
			array( $this, 'settings_field_single_checkbox_callback' ),	// The name of the function responsible for rendering the option interface
			'guardgiant_reverse_proxy_page',									// The page on which this option will be displayed
			'guardgiant_reverse_proxy_section',						// The name of the section to which this field belongs
			array(
				'label_for' => 'site_uses_reverse_proxy',
				'description' => __( 'This site uses a reverse proxy/load balancer.', 'guardgiant' )
			)															// The array of arguments to pass to the callback 
		);	

		add_settings_field(
			'reverse_proxy_trusted_header',									// ID used to identify the field
			__( 'Trusted Header Field', 'guardgiant' ),					// The label to the left of the option interface element
			array( $this, 'settings_field_input_text_callback' ),	// The name of the function responsible for rendering the option interface
			'guardgiant_reverse_proxy_page',									// The page on which this option will be displayed
			'guardgiant_reverse_proxy_section',						// The name of the section to which this field belongs
			array(
				'label_for' => 'reverse_proxy_trusted_header',
				'default'   => '',
				'further_text' => __('Your reverse proxy/load balancer will provide a header with the originating IP address.','guardgiant')
				
			)															// The array of arguments to pass to the callback
		);
	}


	/**
	 * Sanitize the input from our form i.e. what the user has enetered
	 *
	 * @since 	1.0.0
	 * 
	 * @param	array	$input
	 * @return	array	$sanitized_input
	 * 
	 */
	public function sanitize_settings( $input ) {

		$settings = get_option( 'guardgiant-settings' );	

		$new_input = array();

		global $wp_settings_errors;


		// The settings page has 4 tabs 'brute_force', 'whitelists', 'captcha' and 'general_settings'
		// we define which fields are on each tab 
		$brute_force_tab_fields = array('enable_blocking_of_ips_with_multiple_failed_login_attempts', 'num_of_failed_logins_by_IP_before_mitigation_starts', 'mins_to_block_ip', 'block_IP_on_each_subsequent_failed_attempt', 'block_IP_on_each_subsequent_failed_attempt_mins', 'expire_ip_failed_logins_record', 'expire_ip_failed_logins_record_in_hours', 'reset_IP_failed_login_count_after_successful_login', 'enable_lockout_of_users_with_multiple_failed_login_attempts', 'num_of_failed_logins_before_mitigation_starts', 'mins_to_lockout_account', 'never_lockout_trusted_users', 'notify_user_of_login_from_new_device', 'enable_login_captcha', 'num_of_failed_logins_by_IP_before_captcha_shown' );

		$whitelist_tab_fields = array('whitelist_users','whitelist_ip_addresses');

		$captcha_tab_fields = array('recaptcha_site_key','recaptcha_secret_key');

		$reverse_proxy_tab_fields = array('auto_detect_reverse_proxy','site_uses_reverse_proxy','reverse_proxy_trusted_header');

		$general_settings_tab_fields = array('obfuscate_login_errors','show_mins_remaining_in_error_msg','use_ip_address_geolocation','disable_xmlrpc','require_wordpress_api_auth', 'delete_login_activity_records_from_db_after_days');

		// which tab are we currently working on
		if( isset( $_POST[ 'active_tab' ] ) ) {
			$active_tab =  sanitize_text_field($_POST[ 'active_tab' ]);
		} 
		else
			$active_tab = 'brute_force';
	
		// we need to pickup the settings from the other tabs
		switch ($active_tab) {
			case 'brute_force':
				$fields = array_merge($whitelist_tab_fields,$captcha_tab_fields,$reverse_proxy_tab_fields,$general_settings_tab_fields);
				foreach($fields as $field) {
					if (isset($settings[$field])) 
						$new_input[$field] = $settings[$field];
				}

				// if the user enables the captcha field, we check its been setup correctly
				if (isset($input['enable_login_captcha'])) {
					if (!Guardgiant_Captcha::has_been_setup_correctly() ) {
						// it's not setup correctly. unset the setting and notify
						unset($input['enable_login_captcha']);
						$message = __('Please configure your Google reCaptcha keys before enabling captchas. Please see the ','guardgiant') .  '<a href="' . admin_url( 'admin.php?page=guardgiant&active_tab=captcha' ) . '">' . __( 'Captcha tab','guardgiant' ) . '</a>  for details.';
						add_settings_error('enable_login_captcha','enable_login_captcha', $message, 'error' );
					}
				}

				break;
			case 'whitelists':
				$fields = array_merge($brute_force_tab_fields,$captcha_tab_fields,$reverse_proxy_tab_fields,$general_settings_tab_fields);
				foreach($fields as $field) {
					if (isset($settings[$field]))
						$new_input[$field] = $settings[$field];
				}
				break;

			case 'captcha':
				$fields = array_merge($brute_force_tab_fields,$whitelist_tab_fields,$reverse_proxy_tab_fields,$general_settings_tab_fields);
				foreach($fields as $field) {
					if (isset($settings[$field]))
						$new_input[$field] = $settings[$field];
				}
				break;

			case 'reverse_proxy':
				$fields = array_merge($brute_force_tab_fields,$whitelist_tab_fields,$captcha_tab_fields,$general_settings_tab_fields);
				foreach($fields as $field) {
					if (isset($settings[$field]))
						$new_input[$field] = $settings[$field];
				}

				if ( $input['auto_detect_reverse_proxy'] == GUARDGIANT_AUTO_DETECT_REVERSE_PROXY_SETTINGS) {
					
					$proxy_settings = Guardgiant::detect_reverse_proxy(FALSE);
					if (isset($proxy_settings['site_uses_reverse_proxy']))
						$input['site_uses_reverse_proxy'] = $proxy_settings['site_uses_reverse_proxy'];
					$input['reverse_proxy_trusted_header'] = $proxy_settings['reverse_proxy_trusted_header'];	
				}
				break;	

			case 'general_settings':
				$fields = array_merge($brute_force_tab_fields,$whitelist_tab_fields,$captcha_tab_fields,$reverse_proxy_tab_fields);
				foreach($fields as $field) {
					if (isset($settings[$field]))
						$new_input[$field] = $settings[$field];
				}
				break;				
		}
		
		if ( isset( $input ) ) {
			// Loop trough each input and sanitize the value
			foreach ( $input as $key => $value ) {
				switch ($key) {
					case 'whitelist_users':
					case 'whitelist_ip_addresses':
						$new_input[ $key ] = sanitize_textarea_field( $value );
						break;
					case 'num_of_failed_logins_by_IP_before_captcha_shown':
					case 'num_of_failed_logins_by_IP_before_mitigation_starts':
					case 'mins_to_block_ip':
					case 'expire_ip_failed_logins_record_in_hours':
					case 'num_of_failed_logins_before_mitigation_starts':
					case 'mins_to_lockout_account':
					case 'delete_login_activity_records_from_db_after_days':
						$sanitized_value = sanitize_text_field( trim($value) );
						if (filter_var($sanitized_value, FILTER_VALIDATE_INT) !== false)
							$new_input[ $key ] = absint($sanitized_value);
						break;
					case 'reverse_proxy_trusted_header':
						$new_input[ $key ] = strip_tags($value);
						break;	
					case 'notification_email_from_email':
						$new_input[ $key ] = sanitize_email( $value );
						break;

					default:
						$new_input[ $key ] = sanitize_text_field( $value );

				}
			}
		}
		
		return $new_input;

	}

	/* ------------------------------------------------------------------------ *
	* Section Callbacks
	* ------------------------------------------------------------------------ */
	
	/**
	 * This function provides content for the 'user lockout' section. 
	 * 
	 * @since	1.0.0
	 * 
	 */
	public function trusted_devices_settings_section_callback() {
		echo "<p>" . __("GuardGiant is a modern security plugin that protects your WordPress site from attackers whilst preserving the best possible user experience. ",'guardgiant') . "</p>" ;
		
		echo '<h2>' . __('Limit Login Attempts On User Accounts','guardgiant') . '</h2>';
		echo __("When a genuine user makes a successful login to their account using their mobile phone, tablet, or computer GuardGiant starts treating that device as Trusted. ",'guardgiant');

		echo __("Failed login attempts from trusted devices are directed towards 'Lost Password' forms rather than being subject to account lockouts or additional counter measures.",'guardgiant') . '</p>';
		
		return;

	}


	/**
	 * This function provides content for the 'IP blocking' section. 
	 * 
	 * @since	1.0.0
	 * 
	 */
	public function block_ip_settings_section_callback() {
		
		echo '<h2>' . __('Block IP Addresses Making Multiple Failed Login Attempts','guardgiant') . '</h2>';
		echo '<p>' . __('A Captcha is a strong counter-measure that is very hard for an automated process to solve. In addition, a progressive time delay (block) after a failed login attempt slows down attacks to the point where they become unviable. ','guardgiant') . '</p>';	
		return;

	}


	/**
	 * This function provides content for the 'Whitelists' section. 
	 * 
	 * @since	1.0.0
	 * 
	 */
	public function whitelists_section_callback() {
		echo '<h2>' . __('Whitelists','guardgiant') . '</h2>';
		echo '<p>' . __('Whitelisting is a security feature that provides full access to certain users. GuardGiant offers a User Whitelist for trusted usernames that should never be locked out. The IP Address Whitelist allows you to create a list of trusted IP addresses (e.g. an office IP) which will never be blocked.','guardgiant') . '</p>';	
		return;

	}


	/**
	 * This function provides content for the 'Captcha' section. 
	 * 
	 * @since	1.0.0
	 * 
	 */
	public function captcha_section_callback() {
		echo '<h2>' . __('Google reCaptcha v2','guardgiant') . '</h2>';
		echo '<p>' . __('Google reCaptcha (version 2) provides the most robust way of differentiating between genuine users and automated processes (i.e. brute force scripts used by hackers). ','guardgiant') . '</p>';	
		echo '<p>' . __('Need help with this page? ','guardgiant') . '<a href="https://www.guardgiant.com/keys-for-google-recaptcha/">Click here for step-by-step instructions.</a>';

		return;

	}

	/**
	 * This function provides content for the 'Reverse Proxy' section. 
	 * 
	 * @since	1.0.0
	 * 
	 */
	public function reverse_proxy_section_callback() {
		echo '<h2>' . __('Reverse Proxy','guardgiant') . '</h2>';
		echo '<p>';
		echo __('Load balancers and CDNs (e.g. Cloudflare) are known as reverse proxies. ','guardgiant');
		echo __('Due to the nature of these services, all visits to your website are logged with the IP address of the proxy rather than the visitor’s actual IP address. ','guardgiant');  
		echo __("To remedy this, the visitor's IP address is provided in a 'header field' which GuardGiant can pick up and use. ",'guardgiant');  
		echo '</p><p>' . __('GuardGiant can detect the correct settings for you, however if you prefer you can manually set these details in this section. ','guardgiant');  
		echo '</p>';
		return;

	}


	/**
	 * This function provides content for the 'general settings' section. 
	 * 
	 * @since	1.0.0
	 * 
	 */
	public function general_settings_section_callback() {
		echo '<h2>' . __('General Settings','guardgiant') . '</h2>';
		return;

	}

	/**
	 * This function provides content for the 'Email Notifications' section. 
	 * 
	 * @since	1.0.0
	 * 
	 */
	public function email_notifications_section_callback() {
		echo '<h2>' . __('Email Notifications','guardgiant') . '</h2>';
		
		return;

	}



	/* ------------------------------------------------------------------------ *
	* Field Callbacks
	* ------------------------------------------------------------------------ */

/**
	 * This function renders the interface elements for a single checkbox
	 *
	 * @since	1.0.0
	 * 
	 * @param	array		$args
	 * 
	 */
	public function settings_field_radio_buttons_callback( $args ) {

		$field_id = isset($args['label_for']) ? $args['label_for'] : null;
		$field_description1 = isset($args['description1']) ? $args['description1'] : null;
		$field_description2 = isset($args['description2']) ? $args['description2'] : null;
		$further_text = isset($args['further_text']) ? $args['further_text'] : null;
		$value1 = isset($args['value1']) ? $args['value1'] : null;
		$value2 = isset($args['value2']) ? $args['value2'] : null;

		$options = get_option( 'guardgiant-settings' );
		$option = 0;

		if ( ! empty( $options[ $field_id ] ) ) 
			$option = $options[ $field_id ];
		

		?>
		<p>
			<label >
				
				<input type="radio" name="<?php echo 'guardgiant-settings[' . esc_html($field_id) . ']'; ?>" value="<?php echo esc_html($value1);?>" <?php checked(1, $option, true); ?> > 
				<?php if (!empty($field_description1)) echo  esc_html($field_description1) ?>
			</label>
		</p>
		<p>
			<label >
				
				<input type="radio" name="<?php echo 'guardgiant-settings[' . esc_html($field_id) . ']'; ?>" value="<?php echo esc_html($value2);?>" <?php checked(2, $option, true); ?> > 
				<?php if (!empty($field_description2)) echo  esc_html($field_description2) ?>
			</label>
		</p>
		<?php if (!empty($further_text)) echo '<p class="description">' . esc_html($further_text) . '</p>'; ?>
		<?php


	}


	/**
	 * This function renders the interface elements for a single checkbox
	 *
	 * @since	1.0.0
	 * 
	 * @param	array		$args
	 * 
	 */
	public function settings_field_single_checkbox_callback( $args ) {

		$field_id = isset($args['label_for']) ? $args['label_for'] : null;
		$field_description = isset($args['description']) ? $args['description'] : null;
		$further_text = isset($args['further_text']) ? $args['further_text'] : null;
		$options = get_option( 'guardgiant-settings' );
		$option = 0;

		if ( ! empty( $options[ $field_id ] ) ) 
			$option = $options[ $field_id ];
		

		?>
			<label for="<?php echo 'guardgiant-settings[' . esc_html($field_id) . ']'; ?>">
				<input type="checkbox" name="<?php echo 'guardgiant-settings[' . esc_html($field_id) . ']'; ?>" id="<?php echo 'guardgiant-settings[' . $field_id . ']'; ?>" <?php checked( $option, true, 1 ); ?> value="1" /><?php if (!empty($field_description)) echo esc_html($field_description) ?>
			</label>	
			<?php if (!empty($further_text)) echo '<p class="description">' . esc_html($further_text) . '</p>'; ?>
			
		<?php
	}


	/**
	 * This function renders the interface elements for a text input field
	 *
	 * @since	1.0.0
	 * 
	 * @param	array		$args
	 * 
	 */
	public function settings_field_input_text_callback( $args ) {

		$field_id = isset($args['label_for']) ? $args['label_for'] : null;
		$field_default = isset($args['default']) ? $args['default'] : null;
		$before_text = isset($args['before_text']) ? $args['before_text'] : null;
		$after_text = isset($args['after_text']) ? $args['after_text'] : null;
		$further_text = isset($args['further_text']) ? $args['further_text'] : null;

		$options = get_option( 'guardgiant-settings' );
		$option = $field_default;

		if ( ! empty( $options[ $field_id ] ) ) 
			$option = $options[ $field_id ];
		

		?>	<span class="description"><?php if (!empty($before_text)) echo esc_html($before_text) . '<br/>'; ?> </span>	
			<input type="text" name="<?php echo 'guardgiant-settings[' . esc_html($field_id) . ']'; ?>" id="<?php echo 'guardgiant-settings[' . esc_html($field_id) . ']'; ?>" value="<?php echo  $option; ?>" class="regular-text" />
			<span class="description"><?php if (!empty($after_text)) echo esc_html($after_text); ?> </span>
			<?php if (!empty($further_text)) echo '<p class="description">' . esc_html($further_text) . '</p>'; ?>

		<?php

	}

	/**
	 * This function renders the interface elements for a text area input field
	 *
	 * @since	1.0.0
	 * 
	 * @param	array		$args
	 *
	 */
	public function settings_field_input_textarea_callback( $args ) {

		$field_id = isset($args['label_for']) ? $args['label_for'] : null;
		$field_default = isset($args['default']) ? $args['default'] : null;
		$rows = isset($args['rows']) ? $args['rows'] : null;
		$before_text = isset($args['before_text']) ? $args['before_text'] : null;
		$after_text = isset($args['after_text']) ? $args['after_text'] : null;
		$further_text = isset($args['further_text']) ? $args['further_text'] : null;

		$options = get_option( 'guardgiant-settings' );
		$option = $field_default;

		if ( ! empty( $options[ $field_id ] ) ) 
			$option = $options[ $field_id ];
		

		if (empty($rows))
			$rows = 4;

		?>		
		<?php if (!empty($before_text)) echo esc_html($before_text) . '<br/>'; ?>
		<textarea type="text" rows="<?php echo esc_html($rows); ?>" cols="50" name="<?php echo 'guardgiant-settings[' . esc_html($field_id) . ']'; ?>" id="<?php echo 'guardgiant-settings[' . esc_html($field_id) . ']'; ?>"  class="large-text code" /><?php echo esc_attr( $option ); ?></textarea>
		<span class="description"><?php if (!empty($after_text)) echo '<br/>' . esc_html($after_text); ?> </span>
		<?php if (!empty($further_text)) echo '<p class="description">' . esc_html($further_text) . '</p>'; ?>
		<?php

	}

	/**
	 * This function renders the interface elements for a number input field
	 *
	 * @since	1.0.0
	 * 
	 * @param	array		$args
	 * 
	 */
	public function settings_field_input_number_callback( $args ) {

		$field_id = isset($args['label_for']) ? $args['label_for'] : null;
		$field_default = isset($args['default']) ? $args['default'] : null;
		$before_text = isset($args['before_text']) ? $args['before_text'] : null;
		$after_text = isset($args['after_text']) ? $args['after_text'] : null;
		$further_text = isset($args['further_text']) ? $args['further_text'] : null;

		$options = get_option( 'guardgiant-settings' );
		$option = $field_default;

		if ( ! empty( $options[ $field_id ] ) ) 
			$option = $options[ $field_id ];
		

		?>
			<span class="description"><?php echo esc_html($before_text); ?> </span>
			<input type="number" step="1" min="1" name="<?php echo 'guardgiant-settings[' . esc_html($field_id) . ']'; ?>" id="<?php echo 'guardgiant-settings[' . esc_html($field_id) . ']'; ?>" value="<?php echo esc_attr( $option ); ?>" class="small-text" />
			<span class="description"><?php if (!empty($after_text)) echo esc_html($after_text); ?> </span>
			<?php if (!empty($further_text)) echo '<p class="description">' . esc_html($further_text) . '</p>'; ?>
		<?php

	}


	/**
	 * This function renders the interface elements for 2 numbers input field
	 *
	 * @since	1.0.0
	 * 
	 * @param	array		$args
	 * 
	 */
	public function settings_field_input_2numbers_callback( $args ) {

		$field_id1 = isset($args['label_for_1']) ? $args['label_for_1'] : null;
		$field_id2 = isset($args['label_for_2']) ? $args['label_for_2'] : null;
		$field_default1 = isset($args['default1']) ? $args['default1'] : null;
		$field_default2 = isset($args['default2']) ? $args['default2'] : null;
		$before_text = isset($args['before_text']) ? $args['before_text'] : null;
		$middle_text = isset($args['middle_text']) ? $args['middle_text'] : null;
		$after_text = isset($args['after_text']) ? $args['after_text'] : null;
		$further_text = isset($args['further_text']) ? $args['further_text'] : null;

		$options = get_option( 'guardgiant-settings' );
		$option1 = $field_default1;
		$option2 = $field_default2;

		if ( ! empty( $options[ $field_id1 ] ) ) 
			$option1 = $options[ $field_id1 ];
		

		if ( ! empty( $options[ $field_id2 ] ) ) 
			$option2 = $options[ $field_id2 ];
		

		?>
			<span class="description"><?php if (!empty($before_text)) echo esc_html($before_text); ?> </span>
			<input type="number" step="1" min="1" name="<?php echo 'guardgiant-settings[' . esc_html($field_id1) . ']'; ?>" id="<?php echo 'guardgiant-settings[' . esc_html($field_id1) . ']'; ?>" value="<?php echo esc_attr( $option1 ); ?>" class="small-text" />
			<span class="description"><?php if (!empty($middle_text)) echo esc_html($middle_text); ?> </span>
			<input type="number" step="1" min="1" name="<?php echo 'guardgiant-settings[' . esc_html($field_id2) . ']'; ?>" id="<?php echo 'guardgiant-settings[' . esc_html($field_id2) . ']'; ?>" value="<?php echo esc_attr( $option2 ); ?>" class="small-text" />

			<span class="description"><?php if (!empty($after_text)) echo esc_html($after_text); ?> </span>
			<?php if (!empty($further_text)) echo '<p class="description">' . esc_html($further_text) . '</p>'; ?>
		<?php

	}


	/**
	 * This function renders the interface elements for a checkbox and a number input field
	 *
	 * @since	1.0.0
	 * 
	 * @param	array		$args
	 * 
	 */
	public function settings_field_input_checkbox_and_number_callback( $args ) {
		$field_id1 = isset($args['label_for_1']) ? $args['label_for_1'] : null;
		$field_id2 = isset($args['label_for_2']) ? $args['label_for_2'] : null;
		
		$field_default1 = isset($args['default1']) ? $args['default1'] : null;
		$field_default2 = isset($args['default2']) ? $args['default2'] : null;
		
		$before_text = isset($args['before_text']) ? $args['before_text'] : null;
		$middle_text = isset($args['middle_text']) ? $args['middle_text'] : null;
		$after_text = isset($args['after_text']) ? $args['after_text'] : null;
		$further_text = isset($args['further_text']) ? $args['further_text'] : null;

		$options = get_option( 'guardgiant-settings' );
		$option1 = $field_default1;
		$option2 = $field_default2;
		
		if ( ! empty( $options[ $field_id1 ] ) ) 
			$option1 = $options[ $field_id1 ];
		

		if ( ! empty( $options[ $field_id2 ] ) ) 
			$option2 = $options[ $field_id2 ];
		

		?>
			<label for="<?php echo 'guardgiant-settings[' . esc_html($field_id1) . ']'; ?>" >
				<input type="checkbox" name="<?php echo 'guardgiant-settings[' . esc_html($field_id1) . ']'; ?>" id="<?php echo 'guardgiant-settings[' . esc_html($field_id1) . ']'; ?>" <?php checked( $option1, true, 1 ); ?> value="1" /><?php if (!empty($before_text)) echo esc_html($before_text); ?>
				<?php if (!empty($middle_text)) echo esc_html($middle_text); ?>
			</label>
			<label for="<?php echo 'guardgiant-settings[' . esc_html($field_id2) . ']'; ?>" >
				<input type="number" step="1" min="1" name="<?php echo 'guardgiant-settings[' . esc_html($field_id2) . ']'; ?>" id="<?php echo 'guardgiant-settings[' . esc_html($field_id2) . ']'; ?>" value="<?php echo esc_attr( $option2 ); ?>" class="small-text" />
				<?php if (!empty($after_text)) echo esc_html($after_text); ?> 
			</label>
			<?php if (!empty($further_text)) echo '<p class="description">' . esc_html($further_text) . '</p>'; ?>

		<?php
	}


	/**
	 * This function renders the interface elements for a checkbox and 2 number input fields
	 *
	 * @since	1.0.0
	 * 
	 * @param	array		$args
	 * 
	 */
	public function settings_field_input_checkbox_and_2numbers_callback( $args ) {

		$field_id1 = isset($args['label_for_1']) ? $args['label_for_1'] : null;
		$field_id2 = isset($args['label_for_2']) ? $args['label_for_2'] : null;
		$field_id3 = isset($args['label_for_3']) ? $args['label_for_3'] : null;

		$field_default1 = isset($args['default1']) ? $args['default1'] : null;
		$field_default2 = isset($args['default2']) ? $args['default2'] : null;
		$field_default3 = isset($args['default3']) ? $args['default3'] : null;
		
		$before_text = isset($args['before_text']) ? $args['before_text'] : null;;
		$middle_text = isset($args['middle_text']) ? $args['middle_text'] : null;;
		$after_text = isset($args['after_text']) ? $args['after_text'] : null;;
		$further_text = isset($args['further_text']) ? $args['further_text'] : null;

		$options = get_option( 'guardgiant-settings' );
		$option1 = $field_default1;
		$option2 = $field_default2;
		$option3 = $field_default3;

		if ( ! empty( $options[ $field_id1 ] ) ) 
			$option1 = $options[ $field_id1 ];
		

		if ( ! empty( $options[ $field_id2 ] ) ) 
			$option2 = $options[ $field_id2 ];
		

		if ( ! empty( $options[ $field_id3 ] ) ) 
			$option3 = $options[ $field_id3 ];


		?>			
			<label for="<?php echo 'guardgiant-settings[' . esc_html($field_id1) . ']'; ?>">
				<input type="checkbox" name="<?php echo 'guardgiant-settings[' . esc_html($field_id1) . ']'; ?>" id="<?php 'guardgiant-settings[' . esc_html($field_id1) . ']'; ?>" <?php checked( $option1, true, 1 ); ?> value="1" />
				<?php if (!empty($before_text)) echo esc_html($before_text); ?>
			</label>
			<label for="<?php echo 'guardgiant-settings[' . esc_html($field_id2) . ']'; ?>">
				<input type="number" step="1" min="1" name="<?php echo 'guardgiant-settings[' . esc_html($field_id2) . ']'; ?>" id="<?php echo 'guardgiant-settings[' . esc_html($field_id2) . ']'; ?>" value="<?php echo esc_attr( $option2 ); ?>" class="small-text" />
				<?php if (!empty($middle_text)) echo esc_html($middle_text); ?>
			</label>
			<label for="<?php echo 'guardgiant-settings[' . esc_html($field_id3) . ']'; ?>">
				<input type="number" step="1" min="1" name="<?php echo 'guardgiant-settings[' . esc_html($field_id3) . ']'; ?>" id="<?php echo 'guardgiant-settings[' . esc_html($field_id3) . ']'; ?>" value="<?php echo esc_attr( $option3 ); ?>" class="small-text" />
				<?php if (!empty($after_text)) echo esc_html($after_text); ?> 
			</label>
			
			<?php if (!empty($further_text)) echo '<p class="description">' . esc_html($further_text) . '</p>'; ?>
		<?php

	}


	/**
	 * This function renders the login activity tab
	 *
	 * @since	1.0.0
	 * 
	 */
	public function show_login_activity_log_page() {
		global $guardgiant_activity_log_table;

        $guardgiant_activity_log_table->prepare_items();
        
		echo '<h2>' . __('Recent Login Activity','guardgiant') . '</h2>';
		$guardgiant_activity_log_table->views();
		$guardgiant_activity_log_table->search_box('Search', 'search-table'); 
		$guardgiant_activity_log_table->display();
            
	}


	/**
	 * Adds a 'settings' link where the guardgiant plugin is listed (on the plugins page of the admin menu)
	 *
	 * @since	1.0.0
	 *  
	 * @param	$links
	 *
	 * @return	mixed
	 */
	public function plugin_action_links( $links ) {
		array_unshift( $links, '<a href="' . admin_url( 'admin.php?page=guardgiant' ) . '">' . __( 'Settings','guardgiant') . '</a>' );
		
		return $links;
	}

	

	/**
	 * Displays any flash messages that have been
	 * (Messages are displayed once only)
	 *
	 * @since    1.0.0
     * 	 
     */
	public function display_flash_notices() {
		$notices = get_option( "guardgiant_flash_notices", array() );
		 
		// Iterate through our notices to be displayed and print them.
		foreach ( $notices as $notice ) {
				printf('<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
					$notice['type'],
					$notice['dismissible'],
					$notice['notice']
				);
			
		}
	 
		// Now we reset our options to prevent notices being displayed forever.
		if( ! empty( $notices ) ) {
			delete_option( "guardgiant_flash_notices", array() );
		}
	}


	/**
	 * Adds a notice that is displayed once on the next admin page
	 *
	 * @since    1.0.0
     * 
     * @param	string  The notice to be displayed
	 * @param	string	the type/class of message
	 * @param	bool	whether the message can be dismissed
     * 	 
     */
	public static function add_flash_notice( $notice = "", $type = "warning", $dismissible = true ) {
		// Here we return the notices saved on our option, if there are not notices, then an empty array is returned
		$notices = get_option( "guardgiant_flash_notices", array() );
	 
		$dismissible_text = ( $dismissible ) ? "is-dismissible" : "";
	 
		$duplicate = FALSE;
		foreach($notices as $existing_notice) {
			if ($existing_notice['notice'] == $notice) 
				$duplicate = TRUE;
		}

		if (!$duplicate) {
			// We add our new notice.
			array_push( $notices, array( 
					"notice" => $notice, 
					"type" => $type, 
					"dismissible" => $dismissible_text
				) );
		
			// Then we update the option with our notices array
			update_option("guardgiant_flash_notices", $notices );
		}
	}

	/**
	 * Adds a help tab that is displayed on the settings pages
	 *
	 * @since    1.0.0
     *  
     */
	public static function add_settings_page_help_tab() {

		$active_tab = false;
		if (isset($_GET['active_tab'])) {
			$active_tab = sanitize_title_with_dashes($_GET['active_tab']);
		}
		
		$current_screen = get_current_screen();

		if ( (!$active_tab) || ($active_tab=='brute_force') )
		{

			$overview_content = '<p>' . __('This screen allows you to configure the plugin to best suit your needs.','guardgiant') . '</p>';
			$overview_content .= '<p>' . __('You must click the Save Changes button at the bottom of the screen for new settings to take effect.','guardgiant') . '</p>';

			$limit_logins_content = '<p>' . __('The primary method used to block brute-force attacks is to simply lock out accounts after a defined number of failed attempts.','guardgiant') . '</p>';
			$limit_logins_content .= '<p>' . __('There are some downsides to this approach. For example, a persistent attacker could effectively disable an account ','guardgiant');
			$limit_logins_content .= __('by continuously trying different passwords starting a lockout on each attempt. To protect against this, you should enable','guardgiant');
			$limit_logins_content .= __(' Trusted Device functionality.','guardgiant') . '</p>';

			$trusted_devices_content = '<p>' . __('Trusted devices are the modern approach to login security, used by most large scale web sites to keep user accounts secure. It is recommended to enable this functionality.','guardgiant') . '</p>';
			$trusted_devices_content .= '<p>' . __('When a genuine user makes a successful login to their account using their mobile phone, tablet, or computer GuardGiant starts treating their device as Trusted.','guardgiant');
			$trusted_devices_content .= __(" Failed login attempts from trusted devices are directed towards 'Lost Password' forms rather than being subject to account lockouts or additional counter measures.",'guardgiant') . '</p>';

			$trusted_devices_content .= '<p>' . __('An email sent to users when a login has been made from a new unrecognized device is a useful security measure that can alert users if their account has been compromised.','guardgiant') . '</p>';

			$blocked_ip_content = '<p>' . __('This section deals with repeated failed attempts from the same IP address. For most sites, the optimum configuration ','guardgiant');
			$blocked_ip_content .= __('is a progressively longer block each time the IP address makes a failed login attempt.','guardgiant') . '</p>';
			$blocked_ip_content .= '<p>' . __("The 'Reset after hours' field is important as IP addresses are dynamic and the same user may not be using the same IP from day to day. A 24 hour period is sensible for this setting.",'guardgiant') . '</p>';
			$blocked_ip_content .= '<p>' . __("Reset after successful login should not be enabled if you allow users to create their own accounts. An attacker could create their own account and then log in periodically to clear any blocks.",'guardgiant') . '</p>';
			

			//register our help tab
			$current_screen->add_help_tab( array(
				'id' => 'gg_help_overview',
				'title' => __('Overview','guardgiant'),
				'content' => $overview_content
				)
				);
			$current_screen->add_help_tab( array(
				'id' => 'gg_help_limit_login_attempts',
				'title' => __('Limit Login Attempts','guardgiant'),
				'content' => $limit_logins_content
				)
				);

			$current_screen->add_help_tab( array(
				'id' => 'gg_help_trusted_devices',
				'title' => __('Trusted Devices','guardgiant'),
				'content' => $trusted_devices_content
				)
				);	

			$current_screen->add_help_tab( array(
				'id' => 'gg_help_blocked_ip',
				'title' => __('Block IP Address','guardgiant'),
				'content' => $blocked_ip_content
				)
				);	
		}	

		if ($active_tab=='captcha') 
		{

			$captcha_content = '<p>' . __('GuardGiant can place a Google ReCaptcha field on the login form, asking the user to click in a box to prove they are not a robot.','guardgiant') . '</p>';
			$captcha_content .= '<p>' . __('To preserve a good user experience, the captcha can be configured to only be presented where there have been multiple failed','guardgiant');
			$captcha_content .= __(' login attempts by the same IP address. Only the IP address in question will be challenged by the ReCaptcha.','guardgiant') . '</p>';

			$current_screen->add_help_tab( array(
				'id' => 'gg_help_captcha',
				'title' => __('Captcha','guardgiant'),
				'content' => $captcha_content
				)
				);
		}


		if ($active_tab == 'reverse_proxy')
		{
			$reverse_proxy_content = '<p>' . __("Selecting Auto Detect will detect your proxy settings when you click the 'save changes' button. ",'guardgiant') . '</p>';

			$reverse_proxy_content .= '<p>' . __("For security reasons it will not Auto Detect on an on-going basis. If you add or remove a proxy to your site, please visit this page again and update your settings.",'guardgiant');

			$current_screen->add_help_tab( array(
				'id' => 'gg_help_reverse_proxy',
				'title' => __('Reverse Proxy','guardgiant'),
				'content' => $reverse_proxy_content
				)
				);
		}

		if ($active_tab=='general_settings')
		{

			$login_errors_content = '<p>' . __("Error messages displayed after a failed login will disclose whether a valid account has been used. For example the message 'incorrect username' is displayed.",'guardgiant') . '</p>';
			$login_errors_content .= '<p>' . __('Hackers can use this information to harvest a list of usernames that they can then attack. It is good practice to ','guardgiant');
			$login_errors_content .= __('obfuscate these messages to a simple incorrect username or password message.','guardgiant') . '</p>';
			$login_errors_content .= '<p>' . __('If an account has been locked out or an IP address blocked, you can select whether to disclose to the user how many minutes they need to wait before retrying.','guardgiant') . '</p>';
			
			$ip_geo_content = '<p>' . __('Choose whether to lookup the location of IP addresses that are logged in the activity log.','guardgiant') . '</p>';

			$xmlrpc_content = '<p>' . __('XML-RPC is a feature of WordPress that enables a remote device like the WordPress application on your smartphone to send data to your WordPress website.','guardgiant') . '</p>';
			$xmlrpc_content .= '<p>' . __('To decide if you need XMLRPC, ask if you need any of the following:','guardgiant') . '</p>';
			$xmlrpc_content .= '<p><ul><li>' . __('The WordPress app','guardgiant') . '</li><li>' . __('Trackbacks and pingbacks','guardgiant') . '</li><li>' . __('JetPack plugin','guardgiant') . '</li></ul></p>';
			$xmlrpc_content .= '<p>' . __('It is simple to re-enable XMLRPC so if you are unsure, you can disable first to see if any issues occur.','guardgiant') . '</p>';

			$block_api_content = '<p>' . __('Some API endpoints will list all the users on your website. For security reasons it is best to disable guest access to this feature.') . '</p>';

			$delete_old_log_records = '<p>' . __('Choose how long to keep entries in the login activity log. Older records will be periodically deleted.','guardgiant') . '</p>';

			$current_screen->add_help_tab( array(
				'id' => 'gg_help_login_errors',
				'title' => __('Login Errors','guardgiant'),
				'content' => $login_errors_content
				)
				);

			$current_screen->add_help_tab( array(
				'id' => 'gg_help_ip_geolocation',
				'title' => __('IP Address Geolocation','guardgiant'),
				'content' => $ip_geo_content
				)
				);

			$current_screen->add_help_tab( array(
				'id' => 'gg_help_xmlrpc',
				'title' => __('XMLRPC','guardgiant'),
				'content' => $xmlrpc_content
				)
				);

			$current_screen->add_help_tab( array(
				'id' => 'gg_help_block_api',
				'title' => __('WordPress API','guardgiant'),
				'content' => $block_api_content
				)
				);	

			$current_screen->add_help_tab( array(
				'id' => 'delete_old_log_records',
				'title' => __('Activity Log','guardgiant'),
				'content' => $delete_old_log_records
				)
				);	
				
		}
	}



	/**
	 * Checks that default settings have been set
	 *
	 * @since    1.0.0
     * 
     * 	 
     */
	public static function apply_default_settings_if_needed() {
		
		// if this is a new installation then we record install date etc
		$install_settings = get_option('guardgiant-install');
		if (!$install_settings) {
			$install_settings = array();
			$install_settings['orig_install_date'] = time();
			$install_settings['current_version'] = GUARDGIANT_VERSION;
			add_option('guardgiant-install',$install_settings);
			$prev_installed_version = 'none';
		} else {
			
			// make a note of previous installed version
			$prev_installed_version = $install_settings['current_version'];
			$install_settings['current_version'] = GUARDGIANT_VERSION;
			update_option('guardgiant-install',$install_settings);
			
		}

		// if this is a new installation then we need to put in some default settings
		$default_settings = get_option('guardgiant-settings');
		if (!$default_settings) {
			$prev_installed_version = 'none';
			$default_settings = array();
			add_option('guardgiant-settings',$default_settings);
		}

		switch ($prev_installed_version) {
			case 'none':
				$default_settings['enable_blocking_of_ips_with_multiple_failed_login_attempts'] = GUARDGIANT_DEFAULT_ENABLE_BLOCKING_OF_IPS;
				$default_settings['num_of_failed_logins_by_IP_before_mitigation_starts'] = GUARDGIANT_DEFAULT_NUM_OF_FAILED_LOGINS_BY_IP_BEFORE_MITIGATION_STARTS;
				$default_settings['mins_to_block_ip'] = GUARDGIANT_DEFAULT_MINS_TO_BLOCK_IP;
				$default_settings['block_IP_on_each_subsequent_failed_attempt'] = GUARDGIANT_DEFAULT_BLOCK_IP_ON_EACH_SUBSEQUENT_FAILED_ATTEMPT;
				$default_settings['block_IP_on_each_subsequent_failed_attempt_mins'] = GUARDGIANT_DEFAULT_BLOCK_IP_ON_EACH_SUBSEQUENT_FAILED_ATTEMPT_MINS;
				$default_settings['expire_ip_failed_logins_record'] = GUARDGIANT_DEFAULT_EXPIRE_IP_FAILED_LOGINS_RECORD;
				$default_settings['expire_ip_failed_logins_record_in_hours'] = GUARDGIANT_DEFAULT_EXPIRE_IP_FAILED_LOGINS_RECORD_IN_HOURS;
				$default_settings['reset_IP_failed_login_count_after_successful_login'] = GUARDGIANT_DEFAULT_RESET_IP_FAILED_LOGIN_COUNT_AFTER_SUCCESSFUL_LOGIN;
		
				$default_settings['enable_lockout_of_users_with_multiple_failed_login_attempts'] = GUARDGIANT_DEFAULT_ENABLE_LOCKOUT_OF_USERS;
				$default_settings['num_of_failed_logins_before_mitigation_starts'] = GUARDGIANT_DEFAULT_NUM_OF_FAILED_LOGINS_BEFORE_MITIGATION_STARTS;
				$default_settings['mins_to_lockout_account'] = GUARDGIANT_DEFAULT_MINS_TO_LOCKOUT_ACCOUNT;
		
				$default_settings['never_lockout_trusted_users'] = GUARDGIANT_DEFAULT_NEVER_LOCKOUT_TRUSTED_USERS;
				$default_settings['notify_user_of_login_from_new_device'] = GUARDGIANT_DEFAULT_NOTIFY_USER_OF_LOGIN_FROM_NEW_DEVICE;
		
				$default_settings['enable_login_captcha'] = GUARDGIANT_DEFAULT_ENABLE_LOGIN_CAPTCHA;
				$default_settings['num_of_failed_logins_by_IP_before_captcha_shown'] = GUARDGIANT_DEFAULT_NUM_OF_FAILED_LOGINS_BY_IP_BEFORE_CAPTCHA_SHOWN;
		
				$default_settings['whitelist_users'] = '';
				$default_settings['whitelist_ip_addresses'] = '';
				$default_settings['obfuscate_login_errors'] = GUARDGIANT_DEFAULT_OBFUSCATE_LOGIN_ERRORS;
				$default_settings['show_mins_remaining_in_error_msg'] = GUARDGIANT_DEFAULT_SHOW_MINS_REMAINING_IN_ERROR_MSG;
				$default_settings['use_ip_address_geolocation'] = GUARDGIANT_DEFAULT_USE_IP_ADDRESS_GEOLOCATION;
				$default_settings['disable_xmlrpc'] = GUARDGIANT_DEFAULT_DISABLE_XMLRPC;
				
				$default_settings['auto_detect_reverse_proxy'] = GUARDGIANT_AUTO_DETECT_REVERSE_PROXY_SETTINGS;
				
				$default_settings['reverse_proxy_trusted_header'] = GUARDGIANT_DEFAULT_REVERSE_PROXY_TRUSTED_HEADER;
			case '2.1.0':
			case '2.1.1':
			case '2.2.0':
			case '2.2.1':
			case '2.2.2':
				$default_settings['require_wordpress_api_auth'] = GUARDGIANT_DEFAULT_REQUIRE_WORDPRESS_API_AUTH;
			case '2.2.3':
			case '2.2.4':
				$default_settings['delete_login_activity_records_from_db_after_days'] = GUARDGIANT_DELETE_LOGIN_ACTIVITY_RECORDS_FROM_DB_AFTER_DAYS;


		}
		update_option('guardgiant-settings',$default_settings);

	}

}

