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
	
	<?php
		$default_tab = 'brute_force';
		if (isset($_GET['active_tab']))
			$active_tab = sanitize_text_field($_GET['active_tab']);
		else
			$active_tab = $default_tab;
		
		settings_errors();
    ?>
	
	<h2 class="nav-tab-wrapper">
		<a href="?page=guardgiant&active_tab=brute_force" class="nav-tab <?php echo $active_tab == 'brute_force' ? 'nav-tab-active' : ''; ?>">Prevent Brute Force Attacks</a>
		<a href="?page=guardgiant&active_tab=whitelists" class="nav-tab <?php echo $active_tab == 'whitelists' ? 'nav-tab-active' : ''; ?>">Whitelists</a>
		<a href="?page=guardgiant&active_tab=captcha" class="nav-tab <?php echo $active_tab == 'captcha' ? 'nav-tab-active' : ''; ?>">Captcha</a>
		<a href="?page=guardgiant&active_tab=reverse_proxy" class="nav-tab <?php echo $active_tab == 'reverse_proxy' ? 'nav-tab-active' : ''; ?>">Reverse Proxy</a>
		<a href="?page=guardgiant&active_tab=general_settings" class="nav-tab <?php echo $active_tab == 'general_settings' ? 'nav-tab-active' : ''; ?>">General Settings</a>

    </h2>

		<?php
		

		if( $active_tab == 'brute_force' ) { ?>
			<form method="post" action="options.php">
			<input type="hidden" name="active_tab" value="<?php echo esc_attr($active_tab) ?>">
			<?php
			settings_fields( 'guardgiant_options_group' );
			do_settings_sections( 'guardgiant_brute_force_page' );
			submit_button();
		}

		if( $active_tab == 'whitelists' ) { ?>
			<form method="post" action="options.php">
			<input type="hidden" name="active_tab" value="<?php echo esc_attr($active_tab) ?>">
			<?php
			settings_fields( 'guardgiant_options_group' );
			do_settings_sections( 'guardgiant_whitelists_page' );
			submit_button();
		}


		if( $active_tab == 'captcha' ) { ?>
			<form method="post" action="options.php">
			<input type="hidden" name="active_tab" value="<?php echo esc_attr($active_tab) ?>">
			<?php
			settings_fields( 'guardgiant_options_group' );
			do_settings_sections( 'guardgiant_captcha_page' );
			submit_button();

			if (Guardgiant_Captcha::has_been_setup_correctly())
				Guardgiant_Captcha::show_demo_captcha();
		}


		if( $active_tab == 'reverse_proxy' ) { ?>
			<form method="post" action="options.php">
			<input type="hidden" name="active_tab" value="<?php echo esc_attr($active_tab) ?>">
			<?php
			settings_fields( 'guardgiant_options_group' );
			do_settings_sections( 'guardgiant_reverse_proxy_page' );
			submit_button();
		}

		
		if( $active_tab == 'general_settings' ) { ?>
			<form method="post" action="options.php">
			<input type="hidden" name="active_tab" value="<?php echo esc_attr($active_tab) ?>">
			<?php
			settings_fields( 'guardgiant_options_group' );
			do_settings_sections( 'guardgiant_general_settings_page' );
			submit_button();
		}

		?>
	</form>
	</div>
</div>
