<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://ljapps.com
 * @since      1.0.0
 *
 * @package    WP_Google_Reviews
 * @subpackage WP_Google_Reviews/admin/partials
 */
 
    // check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
 
    // add error/update messages
 
    // check if the user have submitted the settings
    // wordpress will add the "settings-updated" $_GET parameter to the url
    if (isset($_GET['settings-updated'])) {
        // add settings saved message with the class of "updated"
        add_settings_error('wpfbr_messages', 'wpfbr_message', __('Settings Saved', 'wp-google-reviews'), 'updated');
    }
 
    // show error/update messages
    settings_errors('wpfbr_messages');
?>
    
<div class="wrap" id="wp_fb-settings">
	<h1><img src="<?php echo plugin_dir_url( __FILE__ ) . 'logo.png'; ?>"></h1>

<?php 
include("tabmenu.php");
?>	
	
	<p><?php _e('The first thing you need to do is create a Facebook application that this plugin will communicate with to read your Facebook Page reviews. Don\'t worry it isn\'t hard. Use the button below to go to the Facebook developer site and create an application. Click on the instructions link if you need help. Once you have your app id enter it in the box below and click the "Save Settings" button. Make sure your pop-up blocker is not blocking the Facebook window that pops up.', 'wp-google-reviews'); ?> </p>
	<div id="createbtns">
		<button id="fb_create_app" type="button" class="button button-secondary"><?php _e('Create Facebook App Here', 'wp-google-reviews'); ?></button>&nbsp;&nbsp;
		<a href="<?php echo plugin_dir_url( __FILE__ ) . 'WPFPR_Instructions.pdf'; ?>" target="_blank" id="fb_create_app_help" type="button" class=""><?php _e('Instructions', 'wp-google-reviews'); ?></a>
	</div>
	</br>	
	<form action="options.php" method="post">
		<?php
		// output security fields for the registered setting "wp_fb-settings"
		settings_fields('wp_fb-settings');
		// output setting sections and their fields
		// (sections are registered for "wp_fb-settings", each field is registered to a specific section)
		do_settings_sections('wp_fb-settings');
		// output save settings button
		submit_button('Save Settings');
		?>
	</form>
	<div id="pagelist"><h2><?php _e('Download Your Facebook Page Reviews', 'wp-google-reviews'); ?></h2><p><?php _e('Click the button below for the page(s) you would like to display reviews for. Afterwords go to the "Reviews List" Page to see all your reviews.', 'wp-google-reviews'); ?> </p>
	<table class="wp-list-table widefat fixed striped posts">
		<thead>
			<tr>
				<th scope="col" id="wpfbpr_actions" class="manage-column column-categories"><?php _e('Action', 'wp-google-reviews'); ?></th>
				<th scope="col" id="wpfbpr_fbpagename" class="manage-column"><?php _e('Page Name', 'wp-google-reviews'); ?></th>
				<th scope="col" id="wpfbpr_fbpageid" class="manage-column"><?php _e('Page ID', 'wp-google-reviews'); ?></th>
				<th scope="col" id="wpfbpr_category" class="manage-column"><?php _e('Category', 'wp-google-reviews'); ?></th>
			</tr>
		</thead>
		<tbody id="page_list">
		<tr>
		<td colspan="4">
			No pages to list. Make sure you have properly configured your Facebook App above and clicked the Save Settings button.
		</td>
		</tr>
			</tbody>
	</table>
	</div>

	<div id="popup" class="popup-wrapper wpfbr_hide">
	  <div class="popup-content">
		<div class="popup-title">
		  <button type="button" class="popup-close">&times;</button>
		  <h3 id="popup_titletext"></h3>
		</div>
		<div class="popup-body">
		  <div id="popup_bobytext1"></div>
		  <div id="popup_bobytext2"></div>
		</div>
	  </div>
	</div>
</div>




