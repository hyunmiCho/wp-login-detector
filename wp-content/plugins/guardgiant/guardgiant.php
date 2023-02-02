<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              www.guardgiant.com
 * @since             1.0.0
 * @package           Guardgiant
 *
 * @wordpress-plugin
 * Plugin Name:       GuardGiant Brute Force Protection
 * Plugin URI:        https://www.guardgiant.com/
 * Description:       Security plugin with 100% brute force protection that doesn't lock out genuine users. 
 * Version:           2.2.5
 * Author:            GuardGiant Brute Force Protection
 * Author URI:        https://www.guardgiant.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       guardgiant
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


define( 'GUARDGIANT_VERSION', '2.2.5' );

// default settings
define( 'GUARDGIANT_DEFAULT_ENABLE_LOCKOUT_OF_USERS', '1' );
define( 'GUARDGIANT_DEFAULT_NUM_OF_FAILED_LOGINS_BEFORE_MITIGATION_STARTS', '10' );
define( 'GUARDGIANT_DEFAULT_MINS_TO_LOCKOUT_ACCOUNT', '2' );
define( 'GUARDGIANT_DEFAULT_NEVER_LOCKOUT_TRUSTED_USERS', '1' );
define( 'GUARDGIANT_DEFAULT_NOTIFY_USER_OF_LOGIN_FROM_NEW_DEVICE','1' );

define( 'GUARDGIANT_DEFAULT_ENABLE_BLOCKING_OF_IPS', '1' );
define( 'GUARDGIANT_DEFAULT_NUM_OF_FAILED_LOGINS_BY_IP_BEFORE_MITIGATION_STARTS', '10' );
define( 'GUARDGIANT_DEFAULT_MINS_TO_BLOCK_IP', '2' );
define( 'GUARDGIANT_DEFAULT_BLOCK_IP_ON_EACH_SUBSEQUENT_FAILED_ATTEMPT', '1' );
define( 'GUARDGIANT_DEFAULT_BLOCK_IP_ON_EACH_SUBSEQUENT_FAILED_ATTEMPT_MINS', '1' );
define( 'GUARDGIANT_DEFAULT_EXPIRE_IP_FAILED_LOGINS_RECORD', '1' );
define( 'GUARDGIANT_DEFAULT_EXPIRE_IP_FAILED_LOGINS_RECORD_IN_HOURS', '24');
define( 'GUARDGIANT_DEFAULT_RESET_IP_FAILED_LOGIN_COUNT_AFTER_SUCCESSFUL_LOGIN', '0' );

define( 'GUARDGIANT_DEFAULT_ENABLE_LOGIN_CAPTCHA', '1' );
define( 'GUARDGIANT_DEFAULT_NUM_OF_FAILED_LOGINS_BY_IP_BEFORE_CAPTCHA_SHOWN', '3' );

define( 'GUARDGIANT_DEFAULT_OBFUSCATE_LOGIN_ERRORS', '1' );
define( 'GUARDGIANT_DEFAULT_SHOW_MINS_REMAINING_IN_ERROR_MSG', '1' );
define( 'GUARDGIANT_DEFAULT_USE_IP_ADDRESS_GEOLOCATION', '1' );
define( 'GUARDGIANT_DEFAULT_DISABLE_XMLRPC','0');

define( 'GUARDGIANT_DEFAULT_AUTO_DETECT_REVERSE_PROXY', '1');
define( 'GUARDGIANT_DEFAULT_SITE_USES_REVERSE_PROXY', '0');
define( 'GUARDGIANT_DEFAULT_REVERSE_PROXY_TRUSTED_HEADER', 'X-FORWARDED-FOR');
define( 'GUARDGIANT_AUTO_DETECT_REVERSE_PROXY_SETTINGS','1');
define( 'GUARDGIANT_USE_MANUAL_SETTINGS_FOR_REVERSE_PROXY','2');

define( 'GUARDGIANT_DEFAULT_REQUIRE_WORDPRESS_API_AUTH', '1' );

// other constants
define( 'GUARDGIANT_DELETE_FAILED_IP_RECORDS_FROM_DB_AFTER_DAYS',45);
define( 'GUARDGIANT_DELETE_FAILED_USER_RECORDS_FROM_DB_AFTER_DAYS',45);
define( 'GUARDGIANT_DELETE_LOGIN_ACTIVITY_RECORDS_FROM_DB_AFTER_DAYS',45);

define( 'GUARDGIANT_TRUSTED_DEVICE_COOKIE_NAME','gg_trusted');
define( 'GUARDGIANT_SECONDS_IN_1_DAY',86400);

define( 'GUARDGIANT_DEFAULT_ITEMS_PER_PAGE_ON_ACTIVITY_LOG',10);



/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-guardgiant-activator.php
 */
function activate_guardgiant() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-guardgiant-activator.php';
	Guardgiant_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-guardgiant-deactivator.php
 */
function deactivate_guardgiant() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-guardgiant-deactivator.php';
	Guardgiant_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_guardgiant' );
register_deactivation_hook( __FILE__, 'deactivate_guardgiant' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-guardgiant.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_guardgiant() {
	
	$plugin = new Guardgiant();
	$plugin->run();

}
run_guardgiant();
