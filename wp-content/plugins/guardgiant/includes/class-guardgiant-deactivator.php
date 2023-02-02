<?php

/**
 * Fired during plugin deactivation
 *
 * @since      1.0.0
 *
 * @package    Guardgiant
 * @subpackage Guardgiant/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Guardgiant
 * @subpackage Guardgiant/includes
 */
class Guardgiant_Deactivator {

	/**
	 * Called on deactivation
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		// remove our cron job
		wp_clear_scheduled_hook('guardgiant_housekeeping');

		// (tasks such as deleting database tables are performed only when the plugin is uninstalled)
	
	}

}
