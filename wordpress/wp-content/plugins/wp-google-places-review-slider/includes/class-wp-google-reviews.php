<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    WP_Google_Reviews
 * @subpackage WP_Google_Reviews/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    WP_Google_Reviews
 * @subpackage WP_Google_Reviews/includes
 * @author     Your Name <email@example.com>
 */
class WP_Google_Reviews {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      WP_Google_Reviews_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugintoken    The string used to uniquely identify this plugin.
	 */
	protected $plugintoken;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->_token = 'wp-google-reviews';
		$this->version = '6.6';
		//using this for development
		//$this->version = time();

		$this->load_dependencies();
		$this->set_locale();
		
		if (is_admin()) {
			$this->define_admin_hooks();
			
		}
		$this->define_public_hooks();
		//save version number to db
		$this->_log_version_number();
		

	}

	
	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		$current_version = get_option($this->_token . '_current_db_version', 0);
		if($current_version!=$this->version){
				
			//create table in database
			global $wpdb;
			$table_name = $wpdb->prefix . 'wpfb_reviews';
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				pageid varchar(50) DEFAULT '' NOT NULL,
				pagename tinytext NOT NULL,
				created_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				created_time_stamp int(12) NOT NULL,
				reviewer_name tinytext NOT NULL,
				reviewer_id varchar(50) DEFAULT '' NOT NULL,
				rating int(2) NOT NULL,
				review_text text NOT NULL,
				hide varchar(3) DEFAULT '' NOT NULL,
				review_length int(5) NOT NULL,
				type varchar(12) DEFAULT '' NOT NULL,
				userpic varchar(250) DEFAULT '' NOT NULL,
				from_url varchar(250) DEFAULT '' NOT NULL,
				UNIQUE KEY id (id)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}
		
		update_option( $this->_token . '_current_db_version', $this->version );
		update_option( $this->_token . '_version', $this->version );
	} // End _log_version_number ()
	

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - WP_Google_Reviews_Loader. Orchestrates the hooks of the plugin.
	 * - WP_Google_Reviews_i18n. Defines internationalization functionality.
	 * - WP_Google_Reviews_Admin. Defines all hooks for the admin area.
	 * - WP_Google_Reviews_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-google-reviews-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-google-reviews-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-google-reviews-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-google-reviews-public.php';
		
		/**
		 * The class responsible for the widget admin and public
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-google-reviews-widget.php';
		
		/**
		 * The class responsible for displaying review template via do_action in template file
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-google-reviews-template_action.php';

		//register the loader
		$this->loader = new WP_Google_Reviews_Loader();
		

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the WP_Google_Reviews_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new WP_Google_Reviews_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new WP_Google_Reviews_Admin( $this->get_token(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// register our wpfbr_settings_init to the admin_init action hook, add setting inputs
		$this->loader->add_action('admin_init', $plugin_admin, 'wpfbr_settings_init');
		
		//add menu page
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_menu_pages' );
		
		//add ajax for adding feedback to table
		$this->loader->add_action( 'wp_ajax_wpfb_get_results', $plugin_admin, 'wpfb_process_ajax' ); 

		//add ajax for adding google reviews to table
		$this->loader->add_action( 'wp_ajax_wpfbr_google_reviews', $plugin_admin, 'wpfbr_ajax_google_reviews' ); 
		
		//add ajax for adding google reviews to table
		$this->loader->add_action( 'wp_ajax_wpfbr_testing_api', $plugin_admin, 'wpfbr_ajax_testing_api' ); 
		
		//add select shortcode list to post edit page
		//$this->loader->add_action( 'media_buttons', $plugin_admin, 'add_sc_select',11 ); 
		//$this->loader->add_action( 'admin_head', $plugin_admin, 'button_js' ); 
		
		//for displaying leave review admin notice
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'wprp_admin_notice__success' ); 
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new WP_Google_Reviews_Public( $this->get_token(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_action( 'wpfbr_cron_google_review', $this, 'wpfbr_cron_googlereviews' );

		//add shortcode shortcode_wprev_usetemplate
		$plugin_public->shortcode_wprev_usetemplate();
		
		
	}

	//google cron get reviews, ... wp_cron runs from front end , .... we do need to call admin here, .... !!!
	public function wpfbr_cron_googlereviews()
	{
		$plugin_admin = new WP_Google_Reviews_Admin( $this->get_token(), $this->get_version() );
		$ret = $plugin_admin->wpfbr_cron_googlereviews();
		
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
	
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_token() {
		return $this->_token;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    WP_Google_Reviews_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
