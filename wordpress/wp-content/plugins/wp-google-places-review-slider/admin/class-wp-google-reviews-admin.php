<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    WP_Google_Reviews
 * @subpackage WP_Google_Reviews/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WP_Google_Reviews
 * @subpackage WP_Google_Reviews/admin
 * @author     Your Name <email@example.com>
 */
class WP_Google_Reviews_Admin {

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
	 * @param      string    $plugintoken       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugintoken, $version ) {

		$this->_token = $plugintoken;
		$this->_default_api_token = "AIzaSyABvcVvPnSZwfLgSxAoRvuUqUS1TNidFIM";
		//$this->version = $version;
		//for testing==============
		$this->version = time();
		//===================
	}

	/**
	 * Register the stylesheets for the admin area.
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
		//only load for this plugin
		if(isset($_GET['page'])){
			if( strpos( $_GET['page'], "wp_google-" ) !== false || $_GET['page']=="wp_fb-welcome"){
				wp_enqueue_style( $this->_token, plugin_dir_url( __FILE__ ) . 'css/wprev_admin.css', array(), $this->version, 'all' );
				wp_enqueue_style( $this->_token."_wprev_w3", plugin_dir_url( __FILE__ ) . 'css/wprev_w3.css', array(), $this->version, 'all' );
			}
			
			//load template styles for wp_pro-templates_posts page
			if($_GET['page']=="wp_google-templates_posts" || $_GET['page']=="wp_google-get_pro"){
				//enque template styles for preview
				wp_enqueue_style( $this->_token."_style1", plugin_dir_url(dirname(__FILE__)) . 'public/css/wprev-public_template1.css', array(), $this->version, 'all' );

			}
		}

	}

	/**
	 * Register the JavaScript for the admin area.
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
		

		//scripts for all pages in this plugin
		if(isset($_GET['page'])){
			if( strpos( $_GET['page'], "wp_google-" ) !== false ){
				//pop-up script
				wp_register_script( 'simple-popup-js',  plugin_dir_url( __FILE__ ) . 'js/wprev_simple-popup.min.js' , '', $this->version, false );
				wp_enqueue_script( 'simple-popup-js' );
			}
		}

		//scripts for get google reviews page
		if(isset($_GET['page'])){
			if($_GET['page']=="wp_google-googlesettings" ){
				$options = get_option('wpfbr_google_options');
				$default_key = "AIzaSyABvcVvPnSZwfLgSxAoRvuUqUS1TNidFIM";
				
				
				if(empty( $options['google_api_key'] )){
					$google_api_key = $default_key;
				} 
				if ($options['select_google_api']=="mine"){
					$google_api_key =$options['google_api_key'];
				} else {
					$google_api_key = $default_key;
				}
				
				
				if( ! empty($google_api_key ) )
				{
				wp_register_script( 'wpfbr_google_places_gmaps', 'https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places&key=' . esc_attr( $google_api_key ), array( 'jquery' ) );
				wp_enqueue_script( 'wpfbr_google_places_gmaps' );
				}

				//admin js
				wp_enqueue_script( $this->_token, plugin_dir_url( __FILE__ ) . 'js/wprev_getgoogle.js', array( 'jquery' ), $this->version, false );
				//used for ajax
				wp_localize_script($this->_token, 'adminjs_script_vars', 
					array(
						'wpfb_nonce'=> wp_create_nonce('randomnoncestring'),
						'ajax_url' => admin_url( 'admin-ajax.php' ),
						'i18n'     => array( 'google_auth_error' => 
						sprintf( 
						__( '%1$sGoogle API Error:%2$s Wrong Key or Maps API not added. Due to recent changes by Google you must now add the Maps API to your existing API key in order to use the Location Lookup feature of the Google Places Widget. %3$sView documentation here%4$s', 'wp_fb-settings' ), 
						'<strong>', 
						'</strong>', 
						'<br><a href="https://ljapps.com/google-places-api-key/" target="_blank" class="new-window">', 
						'</a>' 
						) ) 
					)
				);
				
			}
		}
		
		//scripts for get fb reviews page
		/*
		if(isset($_GET['page'])){
			if($_GET['page']=="wp_fb-settings"){
				//admin js
				wp_enqueue_script( $this->_token, plugin_dir_url( __FILE__ ) . 'js/wprev_admin.js', array( 'jquery' ), $this->version, false );
				//used for ajax
				wp_localize_script($this->_token, 'adminjs_script_vars', 
					array(
					'wpfb_nonce'=> wp_create_nonce('randomnoncestring')
					)
				);
			}
		}
		*/
		
		//scripts for review list page
		if(isset($_GET['page'])){
			if($_GET['page']=="wp_google-reviews"){
				//admin js
				wp_enqueue_script('review_list_page-js', plugin_dir_url( __FILE__ ) . 'js/review_list_page.js', array( 'jquery' ), $this->version, false );
				
				wp_enqueue_script('thickbox');
				wp_enqueue_style('thickbox');
		 
				wp_enqueue_script('media-upload');
				wp_enqueue_script('wptuts-upload');
			}
			
			//scripts for templates posts page
			if($_GET['page']=="wp_google-templates_posts"){
				//admin js
				wp_enqueue_script('templates_posts_page-js', plugin_dir_url( __FILE__ ) . 'js/templates_posts_page.js', array( 'jquery' ), $this->version, false );
				wp_localize_script('templates_posts_page-js', 'adminjs_script_vars', 
					array(
					'wpfb_nonce'=> wp_create_nonce('randomnoncestring'),
					'pluginsUrl' => wpgooglerev_plugin_url
					)
				);
				//add color picker here
				wp_enqueue_style( 'wp-color-picker' );
				//enque alpha color add-on wprevpro-wp-color-picker-alpha.js
				wp_enqueue_script( 'wp-color-picker-alpha', plugin_dir_url( __FILE__ ) . 'js/wprevpro-wp-color-picker-alpha.js', array( 'wp-color-picker' ), '2.1.2', false );
				
			}
		}
		
	}
	
	public function add_menu_pages() {

		/**
		 * adds the menu pages to wordpress
		 */

		$page_title = 'WP Google Reviews: Welcome';
		$menu_title = 'WP Google Reviews';
		$capability = 'manage_options';
		$menu_slug = 'wp_google-welcome';
		
		add_menu_page($page_title, $menu_title, $capability, $menu_slug, array($this,'wp_fb_welcome'),'dashicons-star-half');
		
		// We add this submenu page with the same slug as the parent to ensure we don't get duplicates
		$sub_menu_title = 'Welcome';
		add_submenu_page($menu_slug, $page_title, $sub_menu_title, $capability, $menu_slug, array($this,'wp_fb_welcome'));		
		
		// Now add the submenu page
		$submenu_page_title = 'WP Google Reviews: Get Google Reviews';
		$submenu_title = 'Get Google Reviews';
		$submenu_slug = 'wp_google-googlesettings';
		add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, array($this,'wp_fb_googlesettings'));
		
		// Now add the submenu page for the actual reviews list
		$submenu_page_title = 'WP Google Reviews: Reviews List';
		$submenu_title = 'Reviews List';
		$submenu_slug = 'wp_google-reviews';
		add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, array($this,'wp_fb_reviews'));
		
		// Now add the submenu page for the reviews templates
		$submenu_page_title = 'WP Google Reviews: Templates';
		$submenu_title = 'Templates';
		$submenu_slug = 'wp_google-templates_posts';
		add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, array($this,'wp_fb_templates_posts'));
		
		// Now add the submenu page for the reviews templates
		$submenu_page_title = 'WP Google Reviews: Upgrade';
		$submenu_title = 'Get Pro';
		$submenu_slug = 'wp_google-get_pro';
		add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, array($this,'wp_fb_getpro'));
	}
	
	public function wp_fb_settings() {
		require_once plugin_dir_path( __FILE__ ) . '/partials/settings.php';
	}

	public function wp_fb_reviews() {
		require_once plugin_dir_path( __FILE__ ) . '/partials/review_list.php';
	}
	public function wp_fb_googlesettings() {
		require_once plugin_dir_path( __FILE__ ) . '/partials/googlesettings.php';
	}
	public function wp_fb_welcome() {
		require_once plugin_dir_path( __FILE__ ) . '/partials/welcome.php';
	}

	public function wp_fb_templates_posts() {
		require_once plugin_dir_path( __FILE__ ) . '/partials/templates_posts.php';
	}
	public function wp_fb_getpro() {
		require_once plugin_dir_path( __FILE__ ) . '/partials/get_pro.php';
	}
	
	/**
	 * custom option and settings on settings page
	 */
	public function wpfbr_settings_init()
	{

		//--======================= GOOGLE =======================--//

		// register a new setting for "wp_fb-google_settings" page
		register_setting('wp_fb-google_settings', 'wpfbr_google_options', array( &$this, 'wpfbr_schedule_cron' ) );

		// register a new section in the "wp_fb-google_settings" page
		add_settings_section(
			'wpfbr_section_developers_google',
			'',
			array($this,'wpfbr_section_developers_google_cb'),
			'wp_fb-google_settings'
		);
	 
		//register Google API key input field
		add_settings_field(
			'google_api_key', 
			'Google API Key',
			array($this,'wpfbr_field_google_api_key_cb'),
			'wp_fb-google_settings',
			'wpfbr_section_developers_google',
			array(
				'label_for'         => 'google_api_key',
				'class'             => 'wpfbr_row'
			)
		);
		//register location type
		add_settings_field(
			'google_location_type', 
			'Location Type',
			array($this,'wpfbr_location_type_cb'),
			'wp_fb-google_settings',
			'wpfbr_section_developers_google',
			array(
				'label_for'         => 'google_location_type',
				'class'             => 'wpfbr_row wpfbr_hide2'
			)
		);
		//register location search field
		add_settings_field(
			'google_location_txt', 
			'Location Search',
			array($this,'wpfbr_location_cb'),
			'wp_fb-google_settings',
			'wpfbr_section_developers_google',
			array(
				'label_for'         => 'google_location_txt',
				'class'             => 'wpfbr_row wpfbr_hide2'
			)
		);
		//register location field after autocomplete
		
		add_settings_field(
			'google_location_set', 
			'Location',
			array($this,'wpfbr_location_set_cb'),
			'wp_fb-google_settings',
			'wpfbr_section_developers_google',
			array(
				'label_for'         => 'google_location_set', 
				'class'             => 'wpfbr_row wpfbr_hide2',
				'tarray'			=> array( 'google_location_set' => array( 'location'=>'', 'place_id'=>'' ) ),
			)
		);
		
		//fetch google reviews with a minimum of X rating
		add_settings_field(
			'google_location_minrating', 
			'Minimum Rating',
			array($this,'wpfbr_location_minrating_cb'),
			'wp_fb-google_settings',
			'wpfbr_section_developers_google',
			array(
				'label_for'         => 'google_location_minrating',
				'class'             => 'wpfbr_row wpfbr_hide2'
			)
		);
		//run cron everyday to get 5 more reviews; google places API only gives 5 reviews;
		add_settings_field(
			'google_review_cron',
			'Auto Fetch Reviews', 
			array($this,'wpfbr_location_review_cron_cb'),
			'wp_fb-google_settings',
			'wpfbr_section_developers_google',
			array(
				'label_for'         => 'google_review_cron',
				'class'             => 'wpfbr_row wpfbr_hide2'
			)
		);
		
		//register language field
		add_settings_field(
			'google_language_option', // as of WP 4.6 this value is used only internally
			'Language Code',
			array($this,'wpfbr_location_language'),
			'wp_fb-google_settings',
			'wpfbr_section_developers_google',
			[
				'label_for'         => 'google_language_option',
				'class'             => ''
			]
		);
		//--======================= /GOOGLE =======================--//
	}
	/**
	 * custom option and settings:
	 * callback functions
	 */
	 
	//==== developers section cb ====
	// section callbacks can accept an $args parameter, which is an array.
	// $args have the following keys defined: title, id, callback.
	// the values are defined at the add_settings_section() function.
	public function wpfbr_section_developers_cb($args)
	{
		//echos out at top of section
	}


	//--======================= GOOGLE =======================--//
	//callback function for register_settings::line 295; 
	public function wpfbr_schedule_cron( $input )
	{
		if(isset($input['google_review_cron'])){
			if( $input['google_review_cron'] == 1 )
			{
				if( ! wp_next_scheduled( 'wpfbr_cron_google_review' )) 
				{
					wp_schedule_event(time(), 'daily', 'wpfbr_cron_google_review');
				}
			}
			else
				wp_clear_scheduled_hook( 'wpfbr_cron_google_review' );
		}
		return $input;
	}
	public function wpfbr_cron_googlereviews()
	{
		$options = get_option('wpfbr_google_options');
		$ret = $this->get_google_reviews( $options );
		return $ret;
	}

	// the values are defined at the add_settings_section() function.
	public function wpfbr_section_developers_google_cb($args)
	{
		//echos out at top of section
	}
	
	//==== field cb =====
	// field callbacks can accept an $args parameter, which is an array.
	// $args is defined at the add_settings_field() function.
	// wordpress has magic interaction with the following keys: label_for, class.
	// the "label_for" key value is used for the "for" attribute of the <label>.
	// the "class" key value is used for the "class" attribute of the <tr> containing the field.
	// you can add custom key value pairs to be used inside your callbacks.
	public function wpfbr_field_google_api_key_cb($args)
	{
		// get the value of the setting we've registered with register_setting()
		$options = get_option('wpfbr_google_options');
		//print_r($options);
		if(!isset($options['select_google_api'])){
			$options['select_google_api']="default";
		}

		// output the field
		?>
		<select class="select_google_api" id="select_google_api" name="wpfbr_google_options[select_google_api]">
			<option value="default" <?php if(esc_attr($options['select_google_api'])=='default'){echo 'selected="selected"';} ?>>Use Default API Key</option>
			<option value="mine" <?php if(esc_attr($options['select_google_api'])=='mine'){echo 'selected="selected"';} ?>>Use My API Key</option>
		</select> <button id="wpfbr_testgooglekey" type="button" class="button">Test API Key</button>
		<p class="usedefaultkey"><?php _e('You can either use the default community API Key or create your own. It is more reliable to create and use your own key since the default key may exceed the daily call limit.', 'wp-google-reviews'); ?></p>
		<div class="showapikey">
			<input class="regular-text showapikey" id="<?php echo esc_attr($args['label_for']); ?>" type="text" name="wpfbr_google_options[<?php echo esc_attr($args['label_for']); ?>]" placeholder="Google API Key" value="<?php echo esc_attr( $options[$args['label_for']] ); ?>">
			<p class="showapikey"><?php _e('Once you have your Google API Key paste it in the box and click the "Save Settings" button.', 'wp-google-reviews'); ?> </p>
			<div class="showapikey" id="createbtns">
				<a href="http://ljapps.com/google-places-api-key/" target="_blank" id="fb_create_google_app_help" type="button" class=""><?php _e('How To Get API Key', 'wp-google-reviews'); ?></a>
			</div>
			<p class="showapikey"><?php _e('Note: Google now requires an active billing account associated with your API Key.', 'wp-google-reviews'); ?> </p>
		</div>
		<?php
	}
	public function wpfbr_location_cb($args)
	{
		$options = get_option('wpfbr_google_options');
		if(!isset($options[$args['label_for']])){
			$options[$args['label_for']]="";
		}
		?>
		<input class="regular-text" id="<?php echo esc_attr($args['label_for']); ?>" type="text" name="wpfbr_google_options[<?php echo esc_attr($args['label_for']); ?>]" 
			placeholder="Enter a location" autocomplete="off" value="<?php echo esc_attr( $options[$args['label_for']] ); ?>">
			<span id="autocomplete_error"></span>
		<p class="description">
			<?php echo  esc_html__('Start typing to search for your Location. If your API Key is setup correctly, then you should get a drop down list of places to click when you start typing.', 'wp_fb-settings'); ?>
		</p>
		<p class="description">
			<?php echo  esc_html__('If you can\'t find your location with the search box then manually input the values below. Look them up and copy them from this ', 'wp_fb-settings'); ?>
			<a href="https://developers.google.com/maps/documentation/javascript/examples/places-placeid-finder"  target="_blank"><?php echo  esc_html__('page', 'wp_fb-settings'); ?></a>.
		</p>
		<div id="wpfbr_result"></div>
		<?php
	}
	public function wpfbr_location_type_cb($args)
	{
		// get the value of the setting we've registered with register_setting()
		$options = get_option('wpfbr_google_options');

		$location_type = array( "all" => __('All','wp_fb-settings'), "address"=>__('Address','wp_fb-settings'), "establishment" => __('Establishment','wp_fb-settings'), "(regions)" => __('Regions','wp_fb-settings') );
		// output the field
		?>
		<select id="<?php echo  esc_attr($args['label_for']); ?>" name="wpfbr_google_options[<?php echo  esc_attr($args['label_for']); ?>]">
			<?php foreach( $location_type as $location_i => $location_v ) { ?>
			<option value="<?php echo esc_attr( $location_i ); ?>" <?php selected( $options[$args['label_for']], $location_i ); ?>><?php echo esc_attr( $location_v ); ?></option>
			<?php } ?>
		</select>		
		<p class="description">
			<?php echo  esc_html__('Enter Location Type.', 'wp_fb-settings'); ?>
		</p>
		<?php
	}

	public function wpfbr_location_set_cb( $args )
	{
		
		$options = get_option('wpfbr_google_options');

		//foreach( $args['label_for'] as $label=>$val ){
		//	foreach( $val as $labeli=>$valv ){
				
		foreach( $args['tarray'] as $label=>$val ){
			foreach( $val as $labeli=>$valv ){
			if(!isset($options[$label][$labeli])){
				$options[$label][$labeli]="";
			}	
				
		?>
		<label>
		<input class="regular-text" id="wpfbr_<?php echo esc_attr($labeli); ?>" type="text"  
				name="wpfbr_google_options[<?php echo esc_attr($label); ?>][<?php echo esc_attr($labeli); ?>]" 
				placeholder="<?php _e('No Location set','wp_fb-settings');?>" value="<?php echo esc_attr($options[$label][$labeli]); ?>"> <?php echo ucfirst( $labeli ); ?></label><br/>
		<?php
		}}
		?>
		<?php
		
	}
	public function wpfbr_location_minrating_cb($args)
	{
		$options = get_option('wpfbr_google_options');
		?>
		<select id="<?php echo  esc_attr($args['label_for']); ?>" name="wpfbr_google_options[<?php echo  esc_attr($args['label_for']); ?>]">
			<?php foreach( range( 1, 5 ) as $minr ) { ?>
			<option value="<?php echo esc_attr( $minr ); ?>" <?php selected( $options[$args['label_for']], $minr ); ?>><?php echo esc_attr( $minr ); ?></option>
			<?php } ?>
		</select>		
		<p class="description">
			<?php echo  esc_html__('Only import reviews with a minimum rating of X.', 'wp_fb-settings'); ?>
		</p>
		<?php
	}

	public function wpfbr_location_review_cron_cb($args)
	{
		$options = get_option('wpfbr_google_options');
		$temparg = esc_attr($args['label_for']);
		if(!isset($options[$temparg])){
			$options[$temparg]='';
		}
		?>
		<input type="checkbox" id="<?php echo  esc_attr($args['label_for']); ?>" name="wpfbr_google_options[<?php echo esc_attr($args['label_for']); ?>]" value="1" <?php checked( $options[$args['label_for']], "1" ); ?>/>
		<p class="description">
		<?php echo  esc_html__('Run a wp-cron to import your 5 most helpful reviews everyday. Google limits the amount of reviews you can download using their Google Places API to the 5 most helpful, this option allows you to get new reviews as they are added. The Pro version allows you to manually add reviews.', 'wp_fb-settings'); ?>
		</p>
		<?php
	}
	
	public function wpfbr_location_language($args)
	{
		// get the value of the setting we've registered with register_setting()
		$options = get_option('wpfbr_google_options');
		if(!isset($options[$args['label_for']])){
			$options[$args['label_for']]='';
		}
		//print_r($options);
		// output the field
		?>
		<input class="regular-text" id="<?php echo esc_attr($args['label_for']); ?>" type="text" name="wpfbr_google_options[<?php echo esc_attr($args['label_for']); ?>]" value="<?php echo esc_attr( $options[$args['label_for']] ); ?>">
				<p class="description">
			<?php echo  esc_html__('Optional: The language code, indicating in which language the results should be returned, if possible. Click ', 'wp_pro-settings'); ?><a href="https://developers.google.com/maps/faq#languagesupport" target='_blank'><?php echo  esc_html__('here', 'wp_pro-settings'); ?></a>
			<?php echo  esc_html__(' for the language codes.', 'wp_pro-settings'); ?>
		</p>
		<?php
	}

	public function wpfbr_ajax_google_reviews()
	{
		global $wpdb, $current_user;
		get_currentuserinfo();

		if(!is_user_logged_in())  
		{
			$out = __('User not logged in','wpcvt_lang');
			//header( "Content-Type: application/json" );
			echo $out;
			die();
		}
		check_ajax_referer('randomnoncestring', 'wpfb_nonce');
		
		if(!defined('DOING_AJAX')) define('DOING_AJAX', 1);
		
		if (strpos(@ini_get('disable_functions'), 'set_time_limit') === false) {
			@set_time_limit(3600);
		}

		$options = get_option('wpfbr_google_options');

		//if( empty( $options['google_api_key'] ) && empty( $options['google_api_key_default'] )){
		//	_e('Google Places API Key not found.');
		//	die();
		//}

		if( empty( $options['google_location_set']['place_id'] )){
			_e('There is no location set. Please search and select location to get reviews.');
			die();
		}

		echo $this->get_google_reviews( $options );
		die();
	}

	public function get_google_reviews( $options = array() )
	{
		global $wpdb;

		if( empty( $options ) )
			$options = get_option('wpfbr_google_options');

		
		if( empty( $options['google_api_key'] ) ){
			$google_api_key = $this->_default_api_token;
		} else {
			$google_api_key = $options['google_api_key'];
		}
		if(isset($options['select_google_api'])){
			if($options['select_google_api']=='default'){
				$google_api_key = $this->_default_api_token;
			} else if ($options['select_google_api']=='mine'){
				$google_api_key = $options['google_api_key'];
			}
		}
		
		$google_places_url = add_query_arg(
			array(
				'placeid' => trim($options['google_location_set']['place_id']),
				'key'     => trim($google_api_key),
				'language' => trim($options['google_language_option']),
			),
			'https://maps.googleapis.com/maps/api/place/details/json'
		);
		$response = $this->get_reviews( $google_places_url );

		//Error message from google;
		//if ( ! empty( $response->error_message ) ) 
		//{
		//	return '<strong>'.$response->status.'</strong>: '.$response->error_message;
		//} 
		//Error message from google;
		

		if ( ! empty( $response['error_message'] ) ) 
		{
			//print_r($response);
			//return '<strong>'.$response->status.'</strong>: '.$response->error_message;
			$error= sprintf( __('Google Error: %s, %s, <a href="%s" target="_blank">more info</a> </br>', 'wp_fb-settings' ), $response['status'], $response['error_message'], $google_places_url );
			return $error;
		} 
				
		//Error essage from wordpress//
		elseif ( isset( $response['error_message'] ) && ! empty( $response['error_message'] ) ) 
		{
			return '<strong>' . $response['status'] . '</strong>: ' . $response['error_message'];
		}
		//-----------------------------------------------

		$stats = array();
		$table_name = $wpdb->prefix . 'wpfb_reviews';

		$numreturned = count($response['result']['reviews']);
		
		//foreach element in $arr
		foreach( $response['result']['reviews'] as $item) 
		{ 
			//only enter reviews with ratings more than X;
			if( ! empty( $options['google_location_minrating'] )&& ! empty($item['rating'])&& (int)$options['google_location_minrating'] > (int)$item['rating'])
				continue;

			//get reviewer id from author url so we can display on front end
			$intreviewer_id = filter_var($item['author_url'], FILTER_SANITIZE_NUMBER_INT);
			
			$reviewlength = mb_substr_count($item['text'], ' ');
						
			//check to see if row is in db already
			$checkrow = $wpdb->get_var( "SELECT id FROM ".$table_name." WHERE created_time_stamp = '".$item['time']."' " );
			if( empty( $checkrow ) )
			{
				$stats[] =array( 
					'pageid' 			=> $response['result']['place_id'], 
					'pagename' 			=> $response['result']['name'], 
					'created_time' 		=> date( "Y-m-d H:i:s", $item['time'] ),
					'created_time_stamp' 	=> $item['time'],
					'reviewer_name' 		=> $item['author_name'],
					'reviewer_id' 		=> $intreviewer_id,
					'rating' 			=> $item['rating'],
					'review_text' 		=> $item['text'],
					'hide' 			=> '',
					'review_length' 		=> $reviewlength,
					'type' 			=> 'Google',
					'userpic'			=> $item['profile_photo_url'],
					'from_url' =>$response['result']['url']
				);
			}
		}
		$i = 0;
		$insertnum = 0;
		foreach ( $stats as $stat ){
			$insertnum = $wpdb->insert( $table_name, $stat );
			$i=$i + 1;
		}
		echo sprintf( __('%d Reviews returned from API. %d New Reviews downloaded.', 'wp_fb-settings' ), $numreturned,$i );
		
		echo "<br><a href='".$google_places_url."' target='_blank'>Google Result</a> ";
		
		return;
	}

	/**
	 * cURL (wp_remote_get) the Google Places API
	 *
	 * @description: CURLs the Google Places API with our url parameters and returns JSON response
	 *
	 * @param $url
	 *
	 * @return array|mixed
	 */
	function get_reviews( $url ) 
	{
		//Sanitize the URL
		$url = esc_url_raw( $url );

		// Send API Call using WP's HTTP API
		$data = wp_remote_get( $url );

		if ( is_wp_error( $data ) ) 
		{
			$response['error_message'] 	= $data->get_error_message();
			$reponse['status'] 		= $data->get_error_code();
			return $response;
		}
		$response = json_decode( $data['body'], true );

		if( ! ( isset( $response['result']['reviews'] ) && ! empty( $response['result']['reviews'] ) ) )
		{
			$response['error_message'] 	= __('No Google Reviews Found.','wp_fb-settings');
			$reponse['status'] 		= __LINE__;
			return $response;
		}

		//Get Reviewers Avatars
		$response = $this->get_reviewers_avatars( $response );

		//Google response data
		return $response;
	}

	/**
	 * Get Reviewers Avatars
	 *
	 * Get avatar from Places API response or provide placeholder.
	 *
	 * @return array
	 */
	function get_reviewers_avatars( $response ) 
	{
		// Includes Avatar image from user.
		if ( isset( $response['result']['reviews'] ) && ! empty( $response['result']['reviews'] ) ) 
		{
			// Loop Google Places reviews.
			foreach( $response['result']['reviews'] as $i => $review ) {
				// Check to see if image is empty (no broken images).
				if ( ! empty( $review['profile_photo_url'] ) ) {
					$avatar_img = $review['profile_photo_url'] . '?sz=100';
				} else {
					$avatar_img = wpgooglerev_plugin_url . '/public/css/imgs/mystery-man.png';
				}
				$response['result']['reviews'][$i]['profile_photo_url'] = $avatar_img;
			}
		}
		return $response;
	}

	/**
	 * Store reviews in table, called from javascript file admin.js
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	 /*
	public function wpfb_process_ajax(){
	//ini_set('display_errors',1);  
	//error_reporting(E_ALL);
		
		check_ajax_referer('randomnoncestring', 'wpfb_nonce');
		
		$postreviewarray = $_POST['postreviewarray'];
		
		//var_dump($postreviewarray);

		//loop through each one and insert in to db
		global $wpdb;
		$table_name = $wpdb->prefix . 'wpfb_reviews';
		
		$stats = array();
		
		foreach($postreviewarray as $item) { //foreach element in $arr
			$pageid = $item['pageid'];
			$pagename = $item['pagename'];
			$created_time = $item['created_time'];
			$created_time_stamp = strtotime($created_time);
			$reviewer_name = $item['reviewer_name'];
			$reviewer_id = $item['reviewer_id'];
			$rating = $item['rating'];
			$review_text = $item['review_text'];
			$review_length = str_word_count($review_text);
			$rtype = $item['type'];
			
			//check to see if row is in db already
			$checkrow = $wpdb->get_row( "SELECT id FROM ".$table_name." WHERE created_time = '$created_time'" );
			if ( null === $checkrow ) {
				$stats[] =array( 
						'pageid' => $pageid, 
						'pagename' => $pagename, 
						'created_time' => $created_time,
						'created_time_stamp' => strtotime($created_time),
						'reviewer_name' => $reviewer_name,
						'reviewer_id' => $reviewer_id,
						'rating' => $rating,
						'review_text' => $review_text,
						'hide' => '',
						'review_length' => $review_length,
						'type' => $rtype
					);
			}
		}
		$i = 0;
		$insertnum = 0;
		foreach ( $stats as $stat ){
			$insertnum = $wpdb->insert( $table_name, $stat );
			$i=$i + 1;
		}
	
		$insertid = $wpdb->insert_id;

		//header('Content-Type: application/json');
		echo $insertnum."-".$insertid."-".$i;

		die();
	}
*/
	/**
	 * adds drop down menu of templates on post edit screen
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */	
	//add_action('media_buttons','add_sc_select',11);
	/*
	public function add_sc_select(){
		//get id's and names of templates that are post type 
		global $wpdb;
		$table_name = $wpdb->prefix . 'wpfb_post_templates';
		$currentforms = $wpdb->get_results("SELECT id, title, template_type FROM $table_name WHERE template_type = 'post'");
		if(count($currentforms)>0){
		echo '&nbsp;<select id="wprs_sc_select"><option value="select">Review Template</option>';
		foreach ( $currentforms as $currentform ){
			$shortcodes_list .= '<option value="[wprevpro_usetemplate tid=\''.$currentform->id.'\']">'.$currentform->title.'</option>';
		}
		 echo $shortcodes_list;
		 echo '</select>';
		}
	}
	
	
	//add_action('admin_head', 'button_js');
	public function button_js() {
			echo '<script type="text/javascript">
			jQuery(document).ready(function(){
			   jQuery("#wprs_sc_select").change(function() {
							if(jQuery("#wprs_sc_select :selected").val()!="select"){
							  send_to_editor(jQuery("#wprs_sc_select :selected").val());
							}
							  return false;
					});
			});
			</script>';
	}
	*/
	
	//ajax for testing the api key
	public function wpfbr_ajax_testing_api(){
		//echo "here";
		check_ajax_referer('randomnoncestring', 'wpfb_nonce');
		
		$apikey = $_POST['apikey'];
		
		$goodkey = false;
		
		//remote get the autocomplete first
		//https://maps.googleapis.com/maps/api/place/autocomplete/json?input=1600+Amphitheatre&key=<API_KEY>		
		$url = "https://maps.googleapis.com/maps/api/place/autocomplete/json?input=1600+Amphitheatre&key=".$apikey;
		$data = wp_remote_get( $url );

		if ( is_wp_error( $data ) ) 
		{
			$response['error_message'] 	= $data->get_error_message();
			$reponse['status'] 		= $data->get_error_code();
			print_r($response);
		}
		$response = json_decode( $data['body'], true );
		
		if(isset($response['predictions'][0]['id'])){
			//autocomplete is working
			echo "- Autocomplete is working.<br>";
			$goodkey = true;
		} else {
			//key not good
			echo "- Something is wrong with this Google API Key. Error from Google...<br><br>";
			print_r($response);
		}
		
		if($goodkey){
				//remote get place if passed outcomplete
				$url = "https://maps.googleapis.com/maps/api/place/details/json?placeid=ChIJC8DB3J5sYogRV8b_lTk20U4&key=".$apikey;
				$data = wp_remote_get( $url );

				if ( is_wp_error( $data ) ) 
				{
					$response['error_message'] 	= $data->get_error_message();
					$reponse['status'] 		= $data->get_error_code();
					print_r($response);
				}
				$response = json_decode( $data['body'], true );
				
				if(isset($response['result']['name'])){
					//place lookup is working
					echo "- Place Look-up is working.<br><br>";
					echo "- This key should be good to go. Make sure to click Save Settings at the bottom.<br><br>";
				} else {
					echo "- Something is wrong with this Google API Key. Error from Google...<br><br>";
					print_r($response);
				}
		}
		die();
				
	}
	
		/**
	 * displays message in admin if it's been longer than 30 days.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function wprp_admin_notice__success () {

		$activatedtime = get_option('wprev_activated_time_google');
		//if this is an old install then use 23 days ago
		if($activatedtime==''){
			$activatedtime= time() - (86400*23);
			update_option( 'wprev_activated_time_google', $activatedtime );
		}
		$thirtydaysago = time() - (86400*30);
		
		//check if an option was clicked on
		if (isset($_GET['wprevpronotice'])) {
		  $wprevpronotice = $_GET['wprevpronotice'];
		} else {
		  //Handle the case where there is no parameter
		   $wprevpronotice = '';
		}
		if($wprevpronotice=='mlater_google'){		//hide the notice for another 30 days
			update_option( 'wprev_notice_hide_google', 'later' );
			$newtime = time() - (86400*21);
			update_option( 'wprev_activated_time_google', $newtime );
			$activatedtime = $newtime;
			
		} else if($wprevpronotice=='notagain_google'){		//hide the notice forever
			update_option( 'wprev_notice_hide_google', 'never' );
		}
		
		$wprev_notice_hide = get_option('wprev_notice_hide_google');

		if($activatedtime<$thirtydaysago && $wprev_notice_hide!='never'){
		
			$urltrimmedtab = remove_query_arg( array('taction', 'tid', 'sortby', 'sortdir', 'opt') );
			$urlmayberlater = esc_url( add_query_arg( 'wprevpronotice', 'mlater_google',$urltrimmedtab ) );
			$urlnotagain = esc_url( add_query_arg( 'wprevpronotice', 'notagain_google',$urltrimmedtab ) );
			
			$temphtml = '<p>Hey, I noticed you\'ve been using my <b>WP Google Review Slider</b> plugin for a while now – that’s awesome! Could you please do me a BIG favor and give it a 5-star rating on WordPress? <br>
			Thanks!<br>
			~ Josh W.<br></p>
			<ul>
			<li><a href="https://wordpress.org/support/plugin/wp-google-places-review-slider/reviews/#new-post" target="_blank">Ok, you deserve it</a></li>
			<li><a href="'.$urlmayberlater.'">Not right now, maybe later</a></li>
			<li><a href="'.$urlnotagain.'">Don\'t remind me again</a></li>
			</ul>
			<p>P.S. If you\'ve been thinking about upgrading to the <a href="https://ljapps.com/wp-review-slider-pro/" target="_blank">Pro</a> version, here\'s a 10% off coupon code you can use! ->  <b>wprevpro10off</b></p>';
			
			?>
			<div class="notice notice-info">
				<div class="wprevpro_admin_notice" style="color: #007500;">
				<?php _e( $temphtml, $this->_token ); ?>
				</div>
			</div>
			<?php
		}

	}
	
	

}
