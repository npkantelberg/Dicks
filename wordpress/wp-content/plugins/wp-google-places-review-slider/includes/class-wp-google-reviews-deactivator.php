<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    WP_Google_Reviews
 * @subpackage WP_Google_Reviews/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    WP_Google_Reviews
 * @subpackage WP_Google_Reviews/includes
 * @author     Your Name <email@example.com>
 */
class WP_Google_Reviews_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		wp_clear_scheduled_hook( 'wpfbr_cron_google_review' );

		//delete review table in database -----move to unistall---------
		/*
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$table_name = $wpdb->prefix . 'wpfb_reviews';
		
		$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
	
		//drop review template table -------move to unistall------------
		$table_name = $wpdb->prefix . 'wpfb_post_templates';
		
		$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
				
		*/
	
	}

}
