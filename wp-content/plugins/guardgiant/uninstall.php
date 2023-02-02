<?php

/**
 * Fired when the plugin is uninstalled.
 *
 *
 * @since      1.0.0
 *
 * @package    Guardgiant
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
} 

global $wpdb;
$tablename = $wpdb->prefix."guardgiant_user_failed_logins";
$wpdb->query( "DROP TABLE IF EXISTS `$tablename`" );

$tablename = $wpdb->prefix."guardgiant_ip_failed_logins";
$wpdb->query( "DROP TABLE IF EXISTS `$tablename`" );

$tablename = $wpdb->prefix."guardgiant_login_activity_log";
$wpdb->query( "DROP TABLE IF EXISTS `$tablename`" );

delete_option('guardgiant-settings');
delete_option('guardgiant_salt');
delete_option('guardgiant-install');
delete_option('guardgiant-stats');

