<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    WP_Google_Reviews
 * @subpackage WP_Google_Reviews/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WP_Google_Reviews
 * @subpackage WP_Google_Reviews/public
 * @author     Your Name <email@example.com>
 */
class WP_Google_Reviews_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugintoken    The ID of this plugin.
	 */
	private $plugintoken;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugintoken       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugintoken, $version ) {

		$this->_token = $plugintoken;
		$this->version = $version;
		
		
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WP_Google_Reviews_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WP_Google_Reviews_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		/*
		wp_register_style( 'wp-review-slider-pro-public_template1', plugin_dir_url( __FILE__ ) . 'css/wprev-public_template1.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'wp-review-slider-pro-public_template1' );

		wp_register_style( 'wprev_w3', plugin_dir_url( __FILE__ ) . 'css/wprev_w3.css', array(), $this->version, 'all' );
		
		//register slider stylesheet
		wp_register_style( 'unslider', plugin_dir_url( __FILE__ ) . 'css/wprs_unslider.css', array(), $this->version, 'all' );
		wp_register_style( 'unslider-dots', plugin_dir_url( __FILE__ ) . 'css/wprs_unslider-dots.css', array(), $this->version, 'all' );
		

		wp_enqueue_style( 'wprev_w3' );
		wp_enqueue_style( 'unslider' );
		wp_enqueue_style( 'unslider-dots' );
		*/
		//combining all stylesheets
		
		wp_register_style( 'wp-review-slider-pro-public_combine', plugin_dir_url( __FILE__ ) . 'css/wprev-public_combine.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'wp-review-slider-pro-public_combine' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WP_Google_Reviews_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WP_Google_Reviews_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		//wp_enqueue_script( $this->_token."_plublic", plugin_dir_url( __FILE__ ) . 'js/wprev-public.js', array( 'jquery' ), $this->version, false );
		//wp_enqueue_script( $this->_token."_unslider-min", plugin_dir_url( __FILE__ ) . 'js/wprs-unslider-min.js', array( 'jquery' ), $this->version, false );
		
		//combine the js files
		wp_enqueue_script( $this->_token."_plublic_comb", plugin_dir_url( __FILE__ ) . 'js/wprev-public-com-min.js', array( 'jquery' ), $this->version, false );

	}
	
	/**
	 * Register the Shortcode for the public-facing side of the site to display the template.
	 *
	 * @since    1.0.0
	 */
	public function shortcode_wprev_usetemplate() {
	
				add_shortcode( 'wprevpro_usetemplate', array($this,'wprev_usetemplate_func') );
	}	 
	public function wprev_usetemplate_func( $atts, $content = null ) {
		//get attributes
		    $a = shortcode_atts( array(
				'tid' => '0',
				'bar' => 'something',
			), $atts );		//$a['tid'] to get id
	
				ob_start();
				include plugin_dir_path( __FILE__ ) . '/partials/wp-google-reviews-public-display.php';
				return ob_get_clean();
	}
}
