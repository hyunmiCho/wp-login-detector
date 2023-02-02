<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @since	1.0.0
 * 
 * @package    Guardgiant
 * @subpackage Guardgiant/includes
 */


class Guardgiant {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @access   protected
	 * @var      Guardgiant_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 */
	public function __construct() {
		if ( defined( 'GUARDGIANT_VERSION' ) ) {
			$this->version = GUARDGIANT_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'guardgiant';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Guardgiant_Loader. Orchestrates the hooks of the plugin.
	 * - Guardgiant_i18n. Defines internationalization functionality.
	 * - Guardgiant_Admin. Defines all hooks for the admin area.
	 * - Guardgiant_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since 	1.0.0
	 * 
	 * @access	private
	 * 
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-guardgiant-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-guardgiant-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-guardgiant-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-guardgiant-public.php';

		/**
		 * The class responsible for functions related to failed logins by users
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-guardgiant-user-failed-logins.php';

		/**
		 * The class responsible for functions related to tracking the IP address of failed login attempts
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-guardgiant-ip-failed-logins.php';

		/**
		 * The class responsible for functions related to trusted devices
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-guardgiant-trusted-device.php';

		/**
		 * The class responsible for functions related to displaying the Captcha in forms
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-guardgiant-captcha.php';
		
		/**
		 * The class responsible for functions related to the login activity log
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-guardgiant-login-activity-log.php';

		/**
		 * The class responsible for functions related to displaying the table on the activity log page
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-guardgiant-table-login-activity-log.php';

		/**
		 * The class responsible for functions related to stats
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-guardgiant-stats.php';


		$this->loader = new Guardgiant_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Guardgiant_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since 	1.0.0
	 * 
	 * @access	private
	 * 
	 */
	private function set_locale() {

		$plugin_i18n = new Guardgiant_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 * 
	 * @since 	1.0.0
	 *
	 * @access	private
	 * 
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Guardgiant_Admin( $this->get_plugin_name(), $this->get_version() );

		// register our admin pages in the sidebar
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'register_settings_page' );
		
		// Hook our settings
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );

		// $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		// $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// action links in the 'installed plugins' page
		$this->loader->add_filter( 'plugin_action_links_guardgiant/guardgiant.php' , $plugin_admin, 'plugin_action_links' );

		// Screen option for the activity log page
		$this->loader->add_filter( 'set-screen-option' , $plugin_admin, 'set_screen_option', 10, 3 );

		// Admin notices in the admin area
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'display_flash_notices', 12 );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since 	1.0.0
	 * 
	 * @access	private
	 * 
	 */
	private function define_public_hooks() {

		$plugin_public = new Guardgiant_Public( $this->get_plugin_name(), $this->get_version() );

		// $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		// $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		// Hooks for user authentication
		$this->loader->add_filter( 'authenticate', $plugin_public, 'authenticate',99, 3 );	
		$this->loader->add_action( 'wp_login', $plugin_public, 'wp_login' );	// login success
		$this->loader->add_action( 'wp_login_failed', $plugin_public, 'wp_login_failed',9999,2);	// login failed
		
		// Lost password form
		$this->loader->add_action( 'lost_password', $plugin_public, 'lost_password');	
		
		// Hook to display the captcha in the login page
		$this->loader->add_action( 'login_form', $plugin_public, 'login_form', 99 );
		
		// cron job for DB housekeeping
		$this->loader->add_action( 'guardgiant_housekeeping', $plugin_public, 'guardgiant_cron_job' );

		// Disable XMLRPC hook
		$this->loader->add_filter('xmlrpc_enabled', $plugin_public, 'xmlrpc_enabled');

		// REST API hook
		$this->loader->add_action( 'rest_authentication_errors', $plugin_public, 'rest_authentication_errors' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 	1.0.0
	 * 
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since 	1.0.0
	 * 
	 * @return    string    The name of the plugin.
	 * 
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since 	1.0.0
	 * 
	 * @return    Guardgiant_Loader    Orchestrates the hooks of the plugin.
	 * 
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since 	1.0.0
	 * 
	 * @param	none
	 * @return	string    The version number of the plugin.
	 * 
	 */
	public function get_version() {
		return $this->version;
	}


	/**
	 * Get the IP address of the connecting user
	 *
	 * note: REMOTE_ADDR is provided by the server. The others are provided as headers in the request and thus can not be trusted
	 * 
	 * @since 	1.0.0
	 * 
	 * @return	string|NULL    The IP address if found or NULL.
	 * 
	 */
	public static function get_ip_address() {
		$ip = NULL;
		$settings = get_option( 'guardgiant-settings' );
		$site_uses_reverse_proxy = isset($settings['site_uses_reverse_proxy']);
		$reverse_proxy_trusted_header = isset($settings['reverse_proxy_trusted_header']) ? $settings['reverse_proxy_trusted_header'] : '' ;
		

		if ( ($site_uses_reverse_proxy) && (isset( $_SERVER[ $reverse_proxy_trusted_header ])) ) {
			$ip = $_SERVER[ $reverse_proxy_trusted_header ];
		} else {
			if( isset( $_SERVER[ 'REMOTE_ADDR' ] ) ) {
				$ip = $_SERVER[ 'REMOTE_ADDR' ];
			}
		}

		if (!empty($ip)) {
			$ip = trim($ip); // just to be safe
			$ip = filter_var($ip, FILTER_VALIDATE_IP);
			if (!empty($ip)) {
				return $ip;
			}
			
		}
		

		return NULL;
		
	}

	/**
	 * Detect if this site is behind a reverse proxy i.e. CDN / Load balancer
	 *
	 * A reverse proxy will provide a header with the details of the originating IP
	 * 
	 * @since 	1.0.0
	 * 
	 * @param	bool	whether to save the updated settings
	 * @return	array	updated settings 
	 * 
	 */
	public static function detect_reverse_proxy($save_settings = TRUE) {

		$settings = get_option( 'guardgiant-settings' );

		$site_uses_reverse_proxy = FALSE;
		$reverse_proxy_trusted_header = NULL;

		// this list is in order of importance
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED') as $key){
            if (array_key_exists($key, $_SERVER) === TRUE) {
				$reverse_proxy_trusted_header = $key;
                foreach (explode(',', $_SERVER[$key]) as $ip) {
					$ip = trim($ip); // just to be safe
					
					if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== FALSE){
						
						// we have a valid IP address here, so assume this to be a reverse proxy
						$reverse_proxy_trusted_header = $key;
						$site_uses_reverse_proxy = TRUE;
                    }
                }
            }
		}

		if ($site_uses_reverse_proxy)
			$settings['site_uses_reverse_proxy'] = $site_uses_reverse_proxy;
		else
			unset($settings['site_uses_reverse_proxy']);

		$settings['reverse_proxy_trusted_header'] = $reverse_proxy_trusted_header;
		if ($save_settings) {	
			update_option('guardgiant-settings', $settings);
		}

		return $settings;
	}


	/**
	* Checks if a given item is in the whitelist. The whitelist is an array of strings. 
	*
	* @since	1.0.0

	* @param	string				$item
	* @param 	array				$whitelist
	*
	* @return	bool
	*/
	public static function is_item_in_whitelist($item, $whitelist)
	{
		$whitelist_array = explode( '\n', $whitelist );
		$whitelist_array = array_map('trim',$whitelist_array);
		
		if ( in_array( $item,  $whitelist_array ) ) 
			return TRUE;
		else
			return FALSE;
	}


	/**
	* Checks if a IP address is in the whitelist. The whitelist is an array of strings that can be single IPs or IP ranges 
	*
	* @since	1.0.0
	*
	* @param	string				$ip
	* @param 	array				$whitelist
	*
	* @return	bool
	*/
	public static function is_ip_in_whitelist( $ip, $whitelist )
	{
		$whitelist_array = explode( '\n', $whitelist );
		$whitelist_array = array_map('trim',$whitelist_array);

		foreach ( $whitelist_array as $range )
		{
			$range = array_map('trim', explode('-', $range) );
			if ( count( $range ) == 1 )
			{
				if ( (string)$ip === (string)$range[0] )
					return TRUE;
			}
			else
			{
				$low = ip2long( $range[0] );
				$high = ip2long( $range[1] );
				$needle = ip2long( $ip );

				if ( $low === false || $high === false || $needle === false )
					continue;

				$low = (float)sprintf("%u",$low);
				$high = (float)sprintf("%u",$high);
				$needle = (float)sprintf("%u",$needle);

				if ( $needle >= $low && $needle <= $high )
					return TRUE;
			}
		}

		return FALSE;
	}


	/**
	* sets a crypto salt used by guardgiant 
	*
	* @since	1.0.0
	*
	*/
	public static function get_salt()
	{		
		$salt = get_option( 'guardgiant_salt' );
		
		// create the salt if needed
		if ( ! $salt ) {
			$salt = wp_generate_password( 64, true, true );
			add_option( 'guardgiant_salt', $salt );
		}
	}
	

	/**
	 * Get the first sentence of a string.
	 *
	 * If no ending punctuation is found then $text will
	 * be returned as the sentence. If $strict is set
	 * to TRUE then FALSE will be returned instead.
	 *
	 * @since 1.0.0
	 * 
	 * @param  string  $text   Text
	 * @param  boolean $strict Sentences *must* end with one of the $end characters
	 * @param  string  $end    Ending punctuation
	 * @return string|bool     Sentence or FALSE if none was found
	 */
	public static function get_first_sentence($text, $strict = false, $end = '.?!') {
		preg_match("/^[^{$end}]+[{$end}]/", $text, $result);
		if (empty($result)) {
			return ($strict ? false : $text);
		}
		return $result[0];
	}


	/**
	* Do some housekeeping tasks 
	*
	* @since 1.0.0
	*
	*/
	public static function do_housekeeping()
	{
		Guardgiant_IP_Failed_Logins::delete_old_records();
		Guardgiant_User_Failed_Logins::delete_old_records();	
		Guardgiant_Login_Activity_Log::delete_old_records();

	}



}
