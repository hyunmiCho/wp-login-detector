<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       www.google.com
 * @since      1.0.0
 *
 * @package    Guardgiant
 * @subpackage Guardgiant/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->


<div id="wrap">	
	<div class="wrap">
	<h1>GuardGiant</h1>

	<h2 class="nav-tab-wrapper">
		<a href="?page=guardgiant-login-activity-log" class="nav-tab nav-tab-active">Activity Log</a>
    </h2>			
			<form id="activity-log" method="get">
            <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']) ?>" />
				
				<?php	
			$this->show_login_activity_log_page(); ?>
	</form>
	</div>
</div>