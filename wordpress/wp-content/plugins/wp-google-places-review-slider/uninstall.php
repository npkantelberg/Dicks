<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    WP_Google_Reviews
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

		// Leave no trail
		$option1 = 'widget_wprev_widget';
		$option2 = 'wp-google-reviews_version';
		$option3 = 'wpfbr_options';
		$option4 = 'wpfbr_fb_app_id';
		$option5 = 'wprev_notice_hide_google';
		$option6 = 'wprev_activated_time_google';
		
		
	//================
	//check for pro version, if yes then do not delete this stuff
$filename = plugin_dir_path( __DIR__ ).'/wp-review-slider-pro-premium/wp-review-slider-pro.php';

	if ( is_plugin_active( 'wp-review-slider-pro-premium/wp-review-slider-pro.php' ) || file_exists($filename)) {
		//pro version is installed and activated do not delete tables
		
	} else {
	
		//pro version not installed, okay to delete tables
		if ( !is_multisite() ) 
		{
			delete_option( $option1 );
			delete_option( $option2 );
			delete_option( $option3 );
			delete_option( $option4 );
			delete_option( $option5);
			delete_option( $option6 );
			
			//delete review table in database
			global $wpdb;

			$table_name = $wpdb->prefix . 'wpfb_reviews';
			
			$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
		
			//drop review template table 
			$table_name = $wpdb->prefix . 'wpfb_post_templates';
			
			$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
		} 
		else 
		{
			global $wpdb;
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
			$original_blog_id = get_current_blog_id();

			foreach ( $blog_ids as $blog_id ) 
			{
				switch_to_blog( $blog_id );
				delete_option( $option1 );
				delete_option( $option2 );
				delete_option( $option3 );
				delete_option( $option4 );
				delete_option( $option5);
			delete_option( $option6 );
			
				$table_name = $wpdb->prefix . 'wpfb_reviews';
			
				$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
			
				//drop review template table 
				$table_name = $wpdb->prefix . 'wpfb_post_templates';
				
				$wpdb->query( "DROP TABLE IF EXISTS $table_name" );

				// OR
				// delete_site_option( $option_name );  
			}

			switch_to_blog( $original_blog_id );
		}
	}
	//==================
	




