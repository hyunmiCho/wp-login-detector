<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @since	1.0.0
 *
 * @package    Criminalip_test
 * @subpackage Criminalip_test/admin
 */

ini_set('display_errors', true); 
/**
 * Core class used to implement the Criminalip_admin_Form object.
 *
 * @since	1.0.0
 *
 */
class Criminalip_Admin_Form {

	// private $plugin_name;
	// private $version;	// The current version of this plugin

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 * 
	 */
	// public function __construct( $plugin_name, $version ) {

	// 	$this->plugin_name = $plugin_name;
	// 	$this->version = $version;

	// }

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since	1.0.0
	 * 
	 */
	// public function enqueue_styles() {

	// 	wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/criminalip_test-admin.css', array(), $this->version, 'all' );

	// }

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 1.0.0
	 * 
	 */
	// public function enqueue_scripts() {

	// 	wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/criminalip_test-admin.js', array( 'jquery' ), $this->version, false );

	// }


	/**
	 * Register the settings page for the admin area.
	 *
	 * @since 1.0.0
	 * 
	 */
	public function register_settings_page() {
 
		// // Create our menu page.
		// Criminalip_admin_Form::apply_default_settings_if_needed();

		add_menu_page(
			__( 'Criminalip_test', 'criminalip_test' ),
			'Criminalip_test',
			'manage_options',
			'criminalip_test',
			array( $this, 'display_settings_page' ) ,
			'dashicons-privacy', 70
		);

		$settings_hook = add_submenu_page(
			'criminalip_test',								// Register this submenu under this parent 
			__( 'Settings', 'criminalip_test' ),			// The text to the display in the browser when this menu item is active
			__( 'Settings', 'criminalip_test' ),        			// The text for this menu item
			'manage_options',                        			// Which type of users can see this menu
			'criminalip_test',                            			// The unique ID - the slug - for this menu item
			array( $this, 'display_settings_page' )  			// The function used to render the menu for this page to the screen
		);

		$activity_log_hook = add_submenu_page(
			'criminalip_test',								// Register this submenu under this parent 
			__( 'Activity Log', 'criminalip_test' ),			// The text to the display in the browser when this menu item is active
			__( 'Activity Log', 'criminalip_test' ),        			// The text for this menu item
			'manage_options',                        			// Which type of users can see this menu
			'criminalip_test-login-activity-log',                            			// The unique ID - the slug - for this menu item
			array( $this, 'display_activity_log_page' )  			// The function used to render the menu for this page to the screen
		);

		add_action( "load-".$activity_log_hook, 'Criminalip_admin_Form::add_screen_option' );
		add_action( "load-".$settings_hook, 'Criminalip_admin_Form::add_settings_page_help_tab' );
	}


	

	/**
	 * Add the screen option on the activity log page
	 *
	 * @since 1.0.0
	 * 
	 */
	public static function add_screen_option() {
		global $criminalip_test_activity_log_table;

		$option = 'per_page';
		 
		$args = array(
			'label' => 'Number of items per page',
			'default' => 'default' ,  //CRIMINALIP_TEST_DEFAULT_ITEMS_PER_PAGE_ON_ACTIVITY_LOG,
			'option' => 'criminalip_test_login_entries_per_page'
		);
		 
		add_screen_option( $option, $args );	
		
		// $criminalip_test_activity_log_table = new Criminalip_test_Table_Login_Activity_Log();

		// add a help tab

		// set up the text content
		$overview_content = '<p>' . __("This screen provides visibility to all login attempts on your site. You can customize the display of this screen to suit your needs.",'criminalip_test') . '</p>';
		$screen_content = '<p>' . __("You can customize the display of this screen’s contents in a number of ways:",'criminalip_test') . '</p>';
		$screen_content .= '<ul><li>' . __("You can hide/display columns based on your needs and decide how many login attempts to list per screen using the Screen Options tab.",'criminalip_test') . '</li>';
		$screen_content .= '<li>' . __("You can filter the login attempts by time period using the text links above the table, for example to only show login attempts within the last 7 days. The default view is to show all available data.",'criminalip_test') . '</li>';
		$screen_content .= '<li>' . __("You can search for login attempts by a certain IP address using the search box.",'criminalip_test') . '</li>';
		$screen_content .= '<li>' . __("You can refine the list to show only failed or successful login attempts or from trusted devices by using the dropdown menus above the table. Click the Filter button after making your selection.",'criminalip_test') . '</li></ul>';

		$current_screen = get_current_screen();
		
		// register our help overview tab
		$current_screen->add_help_tab( array(
			'id' => 'gg_activity_help_overview',
			'title' => __('Overview','criminalip_test'),
			'content' => $overview_content
			)
			);

		// register our screen content tab
		$current_screen->add_help_tab( array(
			'id' => 'gg_activity_help_screen_content',
			'title' => __('Screen Content','criminalip_test'),
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

       // require_once plugin_dir_path(PLUGIN_FILE_URL) . 'criminalip_settings.php';
		require_once plugin_dir_path(PLUGIN_FILE_URL) . 'admin/partials/criminalip_test-admin-display.php';

	}


	/**
	 * Display the activity log page content.
	 *
	 * @since 1.0.0
	 * 
	 */
	public function display_activity_log_page() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/criminalip_test-admin-activity-log.php';

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
			'criminalip_test_options_group',							// Option group name
			'criminalip_test-settings',								// Option name
			array( $this, 'sanitize_settings' )					// Sanitize callback
		);


		// Add a section for the trusted devices.
		add_settings_section(
			'criminalip_test_trusted_devices_settings_section',				// ID used to identify this section and with which to register options
			'',									// Title to be displayed on the administration page
			array( $this, 'trusted_devices_settings_section_callback' ),	// Callback used to render the description of the section
			'criminalip_test_api_key_page'								// Page on which to add this section of options
		);
 
 

		add_settings_field( // API KEY  hmcho 
			'enable_lockout_of_users_with_multiple_failed_login_attempts',		// ID used to identify the field
			__('API KEY','criminalip_test'),				// The label to the left of the option interface element
			array( $this, 'settings_field_input_text_callback' ),		// The name of the function responsible for rendering the option interface
			'criminalip_test_api_key_page',									// The page on which this option will be displayed
			'criminalip_test_trusted_devices_settings_section',						// The name of the section to which this field belongs
			array(
				'label_for' => 'enable_lockout_of_users_with_multiple_failed_login_attempts',
				'default'   => '',
				
			)															// The array of arguments to pass to the callback
		 ); 
 
		add_settings_section(
			'criminalip_test_block_ip_settings_section',					// ID used to identify this section and with which to register options
			'',									// Title to be displayed on the administration page
			array( $this, 'block_ip_settings_section_callback' ),	// Callback used to render the description of the section
			'criminalip_test_api_key_page'								// Page on which to add this section of options
		);
 

		add_settings_field(
			'num_of_failed_logins_by_IP_before_mitigation_starts',		// ID used to identify the field
			__('limit standard','criminalip_test'),				// The label to the left of the option interface element
			array( $this, 'settings_field_input_checkbox_and_2numbers_callback' ),		// The name of the function responsible for rendering the option interface
			'criminalip_test_api_key_page',									// The page on which this option will be displayed
			'criminalip_test_block_ip_settings_section',						// The name of the section to which this field belongs
			array(
				'label_for_1' => 'enable_blocking_of_ips_with_multiple_failed_login_attempts',
				'label_for_2' => 'num_of_failed_logins_by_IP_before_mitigation_starts',
				'label_for_3' => 'mins_to_block_ip', 
				'before_text' => __( '접근하는 외부 IP 가 ', 'criminalip_test' ),
				'middle_text' => __( '초(분) 동안', 'criminalip_test' ),
				'after_text' => __( ' 번 이상의 접근을 할 경우.', 'criminalip_test' ),
				'default2' => '1',
				'default3'   => '1',
			)							 
		);  

		add_settings_field(
			'never_lockout_trusted_users',		// ID used to identify the field
			__('time limit','criminalip_test'),				// The label to the left of the option interface element
			array( $this, 'settings_field_input_number_callback' ),		// The name of the function responsible for rendering the option interface
			'criminalip_test_api_key_page',									// The page on which this option will be displayed
			'criminalip_test_block_ip_settings_section',						// The name of the section to which this field belongs
			array(
				'label_for_1' => 'never_lockout_trusted_users',
				'label_for_2' => 'num_of_failed_logins_by_IP_before_captcha_shown',
				'before_text' => __( '', 'criminalip_test' ),
				'middle_text' => __( 'Access is prohibited for', 'criminalip_test' ),
				'after_text' => __( 'hours', 'criminalip_test' ),
				'default2'   => GUARDGIANT_DEFAULT_NUM_OF_FAILED_LOGINS_BY_IP_BEFORE_CAPTCHA_SHOWN,
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

		$settings = get_option( 'criminalip_test-settings' );	

		$new_input = array();

		global $wp_settings_errors;


		// 필드값 추출하는곳  hmcho
		// The settings page has 4 tabs 'api_key', 'settings', 'captcha' and 'general_settings'
		// we define which fields are on each tab 
		$api_key_tab_fields = array('enable_blocking_of_ips_with_multiple_failed_login_attempts', 'num_of_failed_logins_by_IP_before_mitigation_starts', 'mins_to_block_ip', 'block_IP_on_each_subsequent_failed_attempt', 'block_IP_on_each_subsequent_failed_attempt_mins', 'expire_ip_failed_logins_record', 'expire_ip_failed_logins_record_in_hours', 'reset_IP_failed_login_count_after_successful_login', 'enable_lockout_of_users_with_multiple_failed_login_attempts', 'num_of_failed_logins_before_mitigation_starts', 'mins_to_lockout_account', 'never_lockout_trusted_users', 'notify_user_of_login_from_new_device', 'enable_login_captcha', 'num_of_failed_logins_by_IP_before_captcha_shown' );

		$whitelist_tab_fields = array('whitelist_users','whitelist_ip_addresses');

		$captcha_tab_fields = array('recaptcha_site_key','recaptcha_secret_key');

		$reverse_proxy_tab_fields = array('auto_detect_reverse_proxy','site_uses_reverse_proxy','reverse_proxy_trusted_header');

		$general_settings_tab_fields = array('obfuscate_login_errors','show_mins_remaining_in_error_msg','use_ip_address_geolocation','disable_xmlrpc','require_wordpress_api_auth', 'delete_login_activity_records_from_db_after_days');

		// which tab are we currently working on
		if( isset( $_POST[ 'active_tab' ] ) ) {
			$active_tab =  sanitize_text_field($_POST[ 'active_tab' ]);
		} 
		else
			$active_tab = 'api_key';
	
		// we need to pickup the settings from the other tabs
		switch ($active_tab) {
			case 'api_key':
				$fields = array_merge($whitelist_tab_fields,$captcha_tab_fields,$reverse_proxy_tab_fields,$general_settings_tab_fields);
				foreach($fields as $field) {
					if (isset($settings[$field])) 
						$new_input[$field] = $settings[$field];
				} 
				break;
 

			case 'general_settings':
				$fields = array_merge($api_key_tab_fields,$whitelist_tab_fields,$captcha_tab_fields,$reverse_proxy_tab_fields);
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
		echo "<p>" . __("This Plug-in is a modern security plugin that protects your WordPress site from attackers whilst preserving the best possible user experience. ",'criminalip_test') . "</p>" ;
	
		echo '<h2>' . __('CriminalIP API KEY','criminalip_test') . '</h2>';
		echo __("Please enter Criminalip's API KEY (https://www.criminalip.io) ").'<br>';
		return;
	
		

	}


	/**
	 * This function provides content for the 'IP blocking' section. 
	 * 
	 * @since	1.0.0
	 * 
	 */
	public function block_ip_settings_section_callback() {
		
		echo '<h2>' . __('Limit Access Attempts On User IP','criminalip_test') . '</h2>';
		echo '<p>' . __('Please set a limited number of times for illegal access','criminalip_test') . '</p>';	
	
		return;
	}


	/**
	 * This function provides content for the 'Settings' section. 
	 * 
	 * @since	1.0.0
	 * 
	 */
	public function settings_section_callback() {
		echo '<h2>' . __('Settings','criminalip_test') . '</h2>';
		echo '<p>' . __('Whitelisting is a security feature that provides full access to certain users. Criminalip_test offers a User Whitelist for trusted usernames that should never be locked out. The IP Address Whitelist allows you to create a list of trusted IP addresses (e.g. an office IP) which will never be blocked.','criminalip_test') . '</p>';	
		return;

	}


	/**
	 * This function provides content for the 'Captcha' section. 
	 * 
	 * @since	1.0.0
	 * 
	 */
	public function captcha_section_callback() {
		echo '<h2>' . __('Google reCaptcha v2','criminalip_test') . '</h2>';
		echo '<p>' . __('Google reCaptcha (version 2) provides the most robust way of differentiating between genuine users and automated processes (i.e. brute force scripts used by hackers). ','criminalip_test') . '</p>';	
		echo '<p>' . __('Need help with this page? ','criminalip_test') . '<a href="https://www.criminalip_test.com/keys-for-google-recaptcha/">Click here for step-by-step instructions.</a>';

		return;

	}

	/**
	 * This function provides content for the 'Reverse Proxy' section. 
	 * 
	 * @since	1.0.0
	 * 
	 */
	public function reverse_proxy_section_callback() {
		echo '<h2>' . __('Reverse Proxy','criminalip_test') . '</h2>';
		echo '<p>';
		echo __('Load balancers and CDNs (e.g. Cloudflare) are known as reverse proxies. ','criminalip_test');
		echo __('Due to the nature of these services, all visits to your website are logged with the IP address of the proxy rather than the visitor’s actual IP address. ','criminalip_test');  
		echo __("To remedy this, the visitor's IP address is provided in a 'header field' which Criminalip_test can pick up and use. ",'criminalip_test');  
		echo '</p><p>' . __('Criminalip_test can detect the correct settings for you, however if you prefer you can manually set these details in this section. ','criminalip_test');  
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
		echo '<h2>' . __('General Settings','criminalip_test') . '</h2>';
		return;

	}

	/**
	 * This function provides content for the 'Email Notifications' section. 
	 * 
	 * @since	1.0.0
	 * 
	 */
	public function email_notifications_section_callback() {
		echo '<h2>' . __('Email Notifications','criminalip_test') . '</h2>';
		
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

		$options = get_option( 'criminalip_test-settings' );
		$option = 0;

		if ( ! empty( $options[ $field_id ] ) ) 
			$option = $options[ $field_id ];
		

		?>
		<p>
			<label >
				
				<input type="radio" name="<?php echo 'criminalip_test-settings[' . esc_html($field_id) . ']'; ?>" value="<?php echo esc_html($value1);?>" <?php checked(1, $option, true); ?> > 
				<?php if (!empty($field_description1)) echo  esc_html($field_description1) ?>
			</label>
		</p>
		<p>
			<label >
				
				<input type="radio" name="<?php echo 'criminalip_test-settings[' . esc_html($field_id) . ']'; ?>" value="<?php echo esc_html($value2);?>" <?php checked(2, $option, true); ?> > 
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
		$options = get_option( 'criminalip_test-settings' );
		$option = 0;

		if ( ! empty( $options[ $field_id ] ) ) 
			$option = $options[ $field_id ];
		

		?>
			<label for="<?php echo 'criminalip_test-settings[' . esc_html($field_id) . ']'; ?>">
				<input type="checkbox" name="<?php echo 'criminalip_test-settings[' . esc_html($field_id) . ']'; ?>" id="<?php echo 'criminalip_test-settings[' . $field_id . ']'; ?>" <?php checked( $option, true, 1 ); ?> value="1" /><?php if (!empty($field_description)) echo esc_html($field_description) ?>
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

		$options = get_option( 'criminalip_test-settings' );
		$option = $field_default;

		if ( ! empty( $options[ $field_id ] ) ) 
			$option = $options[ $field_id ];
		

		?>	<span class="description"><?php if (!empty($before_text)) echo esc_html($before_text) . '<br/>'; ?> </span>	
			<input type="text" name="<?php echo 'criminalip_test-settings[' . esc_html($field_id) . ']'; ?>" id="<?php echo 'criminalip_test-settings[' . esc_html($field_id) . ']'; ?>" value="<?php echo  $option; ?>" class="regular-text" />
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

		$options = get_option( 'criminalip_test-settings' );
		$option = $field_default;

		if ( ! empty( $options[ $field_id ] ) ) 
			$option = $options[ $field_id ];
		

		if (empty($rows))
			$rows = 4;

		?>		
		<?php if (!empty($before_text)) echo esc_html($before_text) . '<br/>'; ?>
		<textarea type="text" rows="<?php echo esc_html($rows); ?>" cols="50" name="<?php echo 'criminalip_test-settings[' . esc_html($field_id) . ']'; ?>" id="<?php echo 'criminalip_test-settings[' . esc_html($field_id) . ']'; ?>"  class="large-text code" /><?php echo esc_attr( $option ); ?></textarea>
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

		$options = get_option( 'criminalip_test-settings' );
		$option = $field_default;

		if ( ! empty( $options[ $field_id ] ) ) 
			$option = $options[ $field_id ];
		

		?>
			<span class="description"><?php echo esc_html($before_text); ?> </span>
			<input type="number" step="1" min="1" name="<?php echo 'criminalip_test-settings[' . esc_html($field_id) . ']'; ?>" id="<?php echo 'criminalip_test-settings[' . esc_html($field_id) . ']'; ?>" value="<?php echo esc_attr( $option ); ?>" class="small-text" />
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

		$options = get_option( 'criminalip_test-settings' );
		$option1 = $field_default1;
		$option2 = $field_default2;

		if ( ! empty( $options[ $field_id1 ] ) ) 
			$option1 = $options[ $field_id1 ];
		

		if ( ! empty( $options[ $field_id2 ] ) ) 
			$option2 = $options[ $field_id2 ];
		

		?>
			<span class="description"><?php if (!empty($before_text)) echo esc_html($before_text); ?> </span>
			<input type="number" step="1" min="1" name="<?php echo 'criminalip_test-settings[' . esc_html($field_id1) . ']'; ?>" id="<?php echo 'criminalip_test-settings[' . esc_html($field_id1) . ']'; ?>" value="<?php echo esc_attr( $option1 ); ?>" class="small-text" />
			<span class="description"><?php if (!empty($middle_text)) echo esc_html($middle_text); ?> </span>
			<input type="number" step="1" min="1" name="<?php echo 'criminalip_test-settings[' . esc_html($field_id2) . ']'; ?>" id="<?php echo 'criminalip_test-settings[' . esc_html($field_id2) . ']'; ?>" value="<?php echo esc_attr( $option2 ); ?>" class="small-text" />

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

		$options = get_option( 'criminalip_test-settings' );
		$option1 = $field_default1;
		$option2 = $field_default2;
		
		if ( ! empty( $options[ $field_id1 ] ) ) 
			$option1 = $options[ $field_id1 ];
		

		if ( ! empty( $options[ $field_id2 ] ) ) 
			$option2 = $options[ $field_id2 ];
		

		?>
			<label for="<?php echo 'criminalip_test-settings[' . esc_html($field_id1) . ']'; ?>" >
				<input type="checkbox" name="<?php echo 'criminalip_test-settings[' . esc_html($field_id1) . ']'; ?>" id="<?php echo 'criminalip_test-settings[' . esc_html($field_id1) . ']'; ?>" <?php checked( $option1, true, 1 ); ?> value="1" /><?php if (!empty($before_text)) echo esc_html($before_text); ?>
				<?php if (!empty($middle_text)) echo esc_html($middle_text); ?>
			</label>
			<label for="<?php echo 'criminalip_test-settings[' . esc_html($field_id2) . ']'; ?>" >
				<input type="number" step="1" min="1" name="<?php echo 'criminalip_test-settings[' . esc_html($field_id2) . ']'; ?>" id="<?php echo 'criminalip_test-settings[' . esc_html($field_id2) . ']'; ?>" value="<?php echo esc_attr( $option2 ); ?>" class="small-text" />
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

		$options = get_option( 'criminalip_test-settings' );
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
			<label for="<?php echo 'criminalip_test-settings[' . esc_html($field_id1) . ']'; ?>">
				<input type="checkbox" name="<?php echo 'criminalip_test-settings[' . esc_html($field_id1) . ']'; ?>" id="<?php 'criminalip_test-settings[' . esc_html($field_id1) . ']'; ?>" <?php checked( $option1, true, 1 ); ?> value="1" />
				<?php if (!empty($before_text)) echo esc_html($before_text); ?>
			</label>
			<label for="<?php echo 'criminalip_test-settings[' . esc_html($field_id2) . ']'; ?>">
				<input type="number" step="1" min="1" name="<?php echo 'criminalip_test-settings[' . esc_html($field_id2) . ']'; ?>" id="<?php echo 'criminalip_test-settings[' . esc_html($field_id2) . ']'; ?>" value="<?php echo esc_attr( $option2 ); ?>" class="small-text" />
				<?php if (!empty($middle_text)) echo esc_html($middle_text); ?>
			</label>
			<label for="<?php echo 'criminalip_test-settings[' . esc_html($field_id3) . ']'; ?>">
				<input type="number" step="1" min="1" name="<?php echo 'criminalip_test-settings[' . esc_html($field_id3) . ']'; ?>" id="<?php echo 'criminalip_test-settings[' . esc_html($field_id3) . ']'; ?>" value="<?php echo esc_attr( $option3 ); ?>" class="small-text" />
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
		// global $criminalip_test_activity_log_table;

        // $criminalip_test_activity_log_table->prepare_items();
        
		// echo '<h2>' . __('Recent Login Activity','criminalip_test') . '</h2>';
		// $criminalip_test_activity_log_table->views();
		// $criminalip_test_activity_log_table->search_box('Search', 'search-table'); 
		// $criminalip_test_activity_log_table->display();
            
	}


	/**
	 * Adds a 'settings' link where the criminalip_test plugin is listed (on the plugins page of the admin menu)
	 *
	 * @since	1.0.0
	 *  
	 * @param	$links
	 *
	 * @return	mixed
	 */
	public function plugin_action_links( $links ) {
		array_unshift( $links, '<a href="' . admin_url( 'admin.php?page=criminalip_test' ) . '">' . __( 'Settings','criminalip_test') . '</a>' );
		
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
		$notices = get_option( "criminalip_test_flash_notices", array() );
		 
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
			delete_option( "criminalip_test_flash_notices", array() );
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
		$notices = get_option( "criminalip_test_flash_notices", array() );
	 
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
			update_option("criminalip_test_flash_notices", $notices );
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

		if ( (!$active_tab) || ($active_tab=='api_key') )
		{

			$overview_content = '<p>' . __('This screen allows you to configure the plugin to best suit your needs.','criminalip_test') . '</p>';
			$overview_content .= '<p>' . __('You must click the Save Changes button at the bottom of the screen for new settings to take effect.','criminalip_test') . '</p>';

			$limit_logins_content = '<p>' . __('The primary method used to block brute-force attacks is to simply lock out accounts after a defined number of failed attempts.','criminalip_test') . '</p>';
			$limit_logins_content .= '<p>' . __('There are some downsides to this approach. For example, a persistent attacker could effectively disable an account ','criminalip_test');
			$limit_logins_content .= __('by continuously trying different passwords starting a lockout on each attempt. To protect against this, you should enable','criminalip_test');
			$limit_logins_content .= __(' Trusted Device functionality.','criminalip_test') . '</p>';

			$trusted_devices_content = '<p>' . __('Trusted devices are the modern approach to login security, used by most large scale web sites to keep user accounts secure. It is recommended to enable this functionality.','criminalip_test') . '</p>';
			$trusted_devices_content .= '<p>' . __('When a genuine user makes a successful login to their account using their mobile phone, tablet, or computer Criminalip_test starts treating their device as Trusted.','criminalip_test');
			$trusted_devices_content .= __(" Failed login attempts from trusted devices are directed towards 'Lost Password' forms rather than being subject to account lockouts or additional counter measures.",'criminalip_test') . '</p>';

			$trusted_devices_content .= '<p>' . __('An email sent to users when a login has been made from a new unrecognized device is a useful security measure that can alert users if their account has been compromised.','criminalip_test') . '</p>';

			$blocked_ip_content = '<p>' . __('This section deals with repeated failed attempts from the same IP address. For most sites, the optimum configuration ','criminalip_test');
			$blocked_ip_content .= __('is a progressively longer block each time the IP address makes a failed login attempt.','criminalip_test') . '</p>';
			$blocked_ip_content .= '<p>' . __("The 'Reset after hours' field is important as IP addresses are dynamic and the same user may not be using the same IP from day to day. A 24 hour period is sensible for this setting.",'criminalip_test') . '</p>';
			$blocked_ip_content .= '<p>' . __("Reset after successful login should not be enabled if you allow users to create their own accounts. An attacker could create their own account and then log in periodically to clear any blocks.",'criminalip_test') . '</p>';
			

			//register our help tab
			$current_screen->add_help_tab( array(
				'id' => 'gg_help_overview',
				'title' => __('Overview','criminalip_test'),
				'content' => $overview_content
				)
				);
			$current_screen->add_help_tab( array(
				'id' => 'gg_help_limit_login_attempts',
				'title' => __('API KEY','criminalip_test'),
				'content' => $limit_logins_content
				)
				);

			$current_screen->add_help_tab( array(
				'id' => 'gg_help_trusted_devices',
				'title' => __('Trusted Devices','criminalip_test'),
				'content' => $trusted_devices_content
				)
				);	

			$current_screen->add_help_tab( array(
				'id' => 'gg_help_blocked_ip',
				'title' => __('Block IP Address','criminalip_test'),
				'content' => $blocked_ip_content
				)
				);	
		}	

		if ($active_tab=='captcha') 
		{

			$captcha_content = '<p>' . __('Criminalip_test can place a Google ReCaptcha field on the login form, asking the user to click in a box to prove they are not a robot.','criminalip_test') . '</p>';
			$captcha_content .= '<p>' . __('To preserve a good user experience, the captcha can be configured to only be presented where there have been multiple failed','criminalip_test');
			$captcha_content .= __(' login attempts by the same IP address. Only the IP address in question will be challenged by the ReCaptcha.','criminalip_test') . '</p>';

			$current_screen->add_help_tab( array(
				'id' => 'gg_help_captcha',
				'title' => __('Limit Access Attempts','criminalip_test'),
				'content' => $captcha_content
				)
				);
		}


		if ($active_tab == 'reverse_proxy')
		{
			$reverse_proxy_content = '<p>' . __("Selecting Auto Detect will detect your proxy settings when you click the 'save changes' button. ",'criminalip_test') . '</p>';

			$reverse_proxy_content .= '<p>' . __("For security reasons it will not Auto Detect on an on-going basis. If you add or remove a proxy to your site, please visit this page again and update your settings.",'criminalip_test');

			$current_screen->add_help_tab( array(
				'id' => 'gg_help_reverse_proxy',
				'title' => __('Reverse Proxy','criminalip_test'),
				'content' => $reverse_proxy_content
				)
				);
		}

		if ($active_tab=='general_settings')
		{

			$login_errors_content = '<p>' . __("Error messages displayed after a failed login will disclose whether a valid account has been used. For example the message 'incorrect username' is displayed.",'criminalip_test') . '</p>';
			$login_errors_content .= '<p>' . __('Hackers can use this information to harvest a list of usernames that they can then attack. It is good practice to ','criminalip_test');
			$login_errors_content .= __('obfuscate these messages to a simple incorrect username or password message.','criminalip_test') . '</p>';
			$login_errors_content .= '<p>' . __('If an account has been locked out or an IP address blocked, you can select whether to disclose to the user how many minutes they need to wait before retrying.','criminalip_test') . '</p>';
			
			$ip_geo_content = '<p>' . __('Choose whether to lookup the location of IP addresses that are logged in the activity log.','criminalip_test') . '</p>';

			$xmlrpc_content = '<p>' . __('XML-RPC is a feature of WordPress that enables a remote device like the WordPress application on your smartphone to send data to your WordPress website.','criminalip_test') . '</p>';
			$xmlrpc_content .= '<p>' . __('To decide if you need XMLRPC, ask if you need any of the following:','criminalip_test') . '</p>';
			$xmlrpc_content .= '<p><ul><li>' . __('The WordPress app','criminalip_test') . '</li><li>' . __('Trackbacks and pingbacks','criminalip_test') . '</li><li>' . __('JetPack plugin','criminalip_test') . '</li></ul></p>';
			$xmlrpc_content .= '<p>' . __('It is simple to re-enable XMLRPC so if you are unsure, you can disable first to see if any issues occur.','criminalip_test') . '</p>';

			$block_api_content = '<p>' . __('Some API endpoints will list all the users on your website. For security reasons it is best to disable guest access to this feature.') . '</p>';

			$delete_old_log_records = '<p>' . __('Choose how long to keep entries in the login activity log. Older records will be periodically deleted.','criminalip_test') . '</p>';

			$current_screen->add_help_tab( array(
				'id' => 'gg_help_login_errors',
				'title' => __('Login Errors','criminalip_test'),
				'content' => $login_errors_content
				)
				);

			$current_screen->add_help_tab( array(
				'id' => 'gg_help_ip_geolocation',
				'title' => __('IP Address Geolocation','criminalip_test'),
				'content' => $ip_geo_content
				)
				);

			$current_screen->add_help_tab( array(
				'id' => 'gg_help_xmlrpc',
				'title' => __('XMLRPC','criminalip_test'),
				'content' => $xmlrpc_content
				)
				);

			$current_screen->add_help_tab( array(
				'id' => 'gg_help_block_api',
				'title' => __('WordPress API','criminalip_test'),
				'content' => $block_api_content
				)
				);	

			$current_screen->add_help_tab( array(
				'id' => 'delete_old_log_records',
				'title' => __('Activity Log','criminalip_test'),
				'content' => $delete_old_log_records
				)
				);	
				
		}
	}  
} 
