<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Guardgiant
 * @subpackage Guardgiant/includes
 */
class Guardgiant_Activator {

	/**
	 *  activate 
	 *
	 * Called when plug-in is first activated.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;

		// Create database table to store details of failed user logins
		$user_failed_logins_tablename = $wpdb->prefix."guardgiant_user_failed_logins";
		if($wpdb->get_var("SHOW TABLES LIKE '$user_failed_logins_tablename'") != $user_failed_logins_tablename ){

			$sql = "CREATE TABLE `$user_failed_logins_tablename`  (
				`id` BIGINT(20) NOT NULL AUTO_INCREMENT,
				`created_at_time` BIGINT UNSIGNED,
				`username` VARCHAR(100),
				`user_id` BIGINT(20),
				`user_ip` VARCHAR(50),
				`attempt_count` INT(11),
				`last_attempt_time` BIGINT UNSIGNED,
				`locked_out_time` BIGINT UNSIGNED,
				PRIMARY KEY  (id)
				);";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		}

		// Create database table to store details of IP addresses that have failed logins
		$ip_failed_logins_tablename = $wpdb->prefix."guardgiant_ip_failed_logins";
		if($wpdb->get_var("SHOW TABLES LIKE '$ip_failed_logins_tablename'") != $ip_failed_logins_tablename ){

			$sql = "CREATE TABLE `$ip_failed_logins_tablename`  (
				`id` BIGINT(20) NOT NULL AUTO_INCREMENT,
				`created_at_time` BIGINT UNSIGNED,
				`ip_address` VARCHAR(50),
				`username` VARCHAR(100),
				`attempt_count` INT(11),
				`last_attempt_time` BIGINT UNSIGNED,
				`blocked_time` BIGINT UNSIGNED,
				`blocked_duration` BIGINT UNSIGNED,
				PRIMARY KEY  (id)
				);";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		}		


		// Create database table to store the login activity log
		$activity_log_tablename = $wpdb->prefix."guardgiant_login_activity_log";
		if($wpdb->get_var("SHOW TABLES LIKE '$activity_log_tablename'") != $activity_log_tablename ){

			$sql = "CREATE TABLE `$activity_log_tablename`  (
				`id` BIGINT(20) NOT NULL AUTO_INCREMENT,
				`attempt_time` BIGINT UNSIGNED,
				`ip_address` VARCHAR(50),
				`ip_location` VARCHAR(255),
				`device_type` VARCHAR(50),
				`trusted_device` BOOLEAN,
				`username` VARCHAR(100),
				`attempt_count` INT(11),
				`result_code` VARCHAR(50),
				`result_description` VARCHAR(255),
				PRIMARY KEY  (id)
				);";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		}	

		// Set up our default settings
		Guardgiant_Admin::apply_default_settings_if_needed();

		
		// set up stats if required
		$guardgiant_stats = get_option('guardgiant-stats');
		if (!$guardgiant_stats) {
			$guardgiant_stats = array();
			$guardgiant_stats['blocked_ip_count'] = 0;
			$guardgiant_stats['user_lockout_count'] = 0;
			add_option('guardgiant-stats',$guardgiant_stats);
		}

		// check if this site is behind a reverse proxy. 
		Guardgiant::detect_reverse_proxy();

		// set up a cron job to delete old records on an twice daily basis

		//Use wp_next_scheduled to check if the event is already scheduled
		$timestamp = wp_next_scheduled( 'guardgiant_housekeeping' );

		//If $timestamp == false schedule daily backups since it hasn't been done previously
		if( $timestamp == false ){
			//Schedule the event for right now, then to repeat twice daily using the hook 'guardgiant_housekeeping'
			wp_schedule_event( time(), 'twicedaily', 'guardgiant_housekeeping' );
		}

		// Add a welcome message
		$msg = '<strong>' . __('Thank you for installing GuardGiant','guardgiant') . '</strong> </p><p>';
		$msg .=  __('To get started, please','guardgiant') . ' <a href="' . admin_url( 'admin.php?page=guardgiant' ) . '">' . __('review your settings here','guardgiant') . '</a>';
		Guardgiant_Admin::add_flash_notice($msg,'success');

	}
}
