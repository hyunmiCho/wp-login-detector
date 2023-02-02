<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       www.google.com
 * @since      1.0.0
 *
 * @package    Criminalip_test
 * @subpackage Criminalip_test/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->


<div id="wrap">	
	<div class="wrap">
	<!-- <h1>criminalip_block</h1> -->
	
	<?php
		$default_tab = 'api_key';
		if (isset($_GET['active_tab']))
			$active_tab = sanitize_text_field($_GET['active_tab']);
		else
			$active_tab = $default_tab;
		
		settings_errors();
    ?>
	
	<h2 class="nav-tab-wrapper">
		<!-- <a href="?page=criminalip_test&active_tab=api_key" class="nav-tab <?php echo $active_tab == 'api_key' ? 'nav-tab-active' : ''; ?>">General Settings</a>
		<a href="?page=criminalip_test&active_tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">General Settings</a>
		<a href="?page=criminalip_test&active_tab=stats" class="nav-tab <?php echo $active_tab == 'stats' ? 'nav-tab-active' : ''; ?>">Stats</a>
	    <a href="?page=criminalip_test&active_tab=reverse_proxy" class="nav-tab <?php echo $active_tab == 'reverse_proxy' ? 'nav-tab-active' : ''; ?>">Reverse Proxy</a> -->
		<!-- <a href="?page=guardgiant&active_tab=general_settings" class="nav-tab <?php echo $active_tab == 'general_settings' ? 'nav-tab-active' : ''; ?>"> -->
		<a href="#" class="nav-tab">General Settings </a>   

    </h2>

		<?php
		

		if( $active_tab == 'api_key' ) { ?> 
			<form method="post" action="options.php">
			<input type="hidden" name="active_tab" value="<?php echo esc_attr($active_tab) ?>">
			<?php
			settings_fields( 'criminalip_test_options_group' );
			do_settings_sections( 'criminalip_test_api_key_page' );
			submit_button();
		}

		if( $active_tab == 'settings' ) { ?>2
			<form method="post" action="options.php">
			<input type="hidden" name="active_tab" value="<?php echo esc_attr($active_tab) ?>">
			<?php
			settings_fields( 'criminalip_test_options_group' );
			do_settings_sections( 'criminalip_test_settings_page' );
			submit_button();
		}


		if( $active_tab == 'stats' ) { ?>3
			<form method="post" action="options.php">
			<input type="hidden" name="active_tab" value="<?php echo esc_attr($active_tab) ?>">
			<?php
			settings_fields( 'criminalip_test_options_group' );
			do_settings_sections( 'criminalip_test_stats_page' );
			submit_button();

			// if (Criminalip_test_Stats::has_been_setup_correctly())
			// 	Criminalip_test_Stats::show_demo_stats();
		}


		if( $active_tab == 'reverse_proxy' ) { ?>4
			<form method="post" action="options.php">
			<input type="hidden" name="active_tab" value="<?php echo esc_attr($active_tab) ?>">
			<?php
			settings_fields( 'criminalip_test_options_group' );
			do_settings_sections( 'criminalip_test_reverse_proxy_page' );
			submit_button();
		}

		
		if( $active_tab == 'general_settings' ) { ?> 5
			<form method="post" action="options.php">
			<input type="hidden" name="active_tab" value="<?php echo esc_attr($active_tab) ?>">
			<?php
			settings_fields( 'criminalip_test_options_group' );
			do_settings_sections( 'criminalip_test_general_settings_page' );
			submit_button();
		}

		?>
	</form>
	</div>
</div>
