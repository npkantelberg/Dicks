<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://ljapps.com
 * @since             1.0
 * @package           WP_Google_Places_Reviews
 *
 * @wordpress-plugin
 * Plugin Name:       WP Google Review Slider
 * Plugin URI:        http://ljapps.com/wp-review-slider-pro/
 * Description:       Allows you to easily display your Google Places business reviews in your Posts, Pages, and Widget areas!
 * Version:           6.6
 * Author:            LJ Apps
 * Author URI:        http://ljapps.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-google-reviews
 * Domain Path:       /languages
 */

 
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-google-reviews-activator.php
 */
function activate_WP_Google_Reviews($networkwide) {
	//save time activated
	$newtime=time();
	update_option( 'wprev_activated_time_google', $newtime );
	
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-google-reviews-activator.php';
	WP_Google_Reviews_Activator::activate_all($networkwide);
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-google-reviews-deactivator.php
 */
function deactivate_WP_Google_Reviews() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-google-reviews-deactivator.php';
	WP_Google_Reviews_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_WP_Google_Reviews' );
register_deactivation_hook( __FILE__, 'deactivate_WP_Google_Reviews' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-google-reviews.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_WP_Google_Reviews() {
	define( 'wpgooglerev_plugin_dir', plugin_dir_path( __FILE__ ) );
	define( 'wpgooglerev_plugin_url', plugins_url( "",__FILE__) );
	
	$plugin = new WP_Google_Reviews();
	$plugin->run();

}
run_WP_Google_Reviews();