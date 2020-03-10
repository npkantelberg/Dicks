<?php # -*- coding: utf-8 -*-

add_action( 'init', array ( 'WPrev_Plugin_Action_Google', 'init' ) );

class WPrev_Plugin_Action_Google
{
    /**
     * Creates a new instance.
     *
     * @wp-hook init
     * @see    __construct()
     * @return void
     */
    public static function init()
    {
        new self;
    }

    /**
     * Register the action. May do more magic things.
     */
    public function __construct()
    {
        add_action( 'wprev_pro_plugin_action', array ( $this, 'wprev_slider_action_print' ), 10, 1 );
    }

    /**
     * Prints out reviews
     *
     * Usage:
     *    <code>do_action( 'wprev_plugin_action', 1 );</code>
     *	
     * @wp-hook wprev_plugin_action
     * @param int $templateid
     * @return void
     */
    public function wprev_slider_action_print( $templateid = 0 )
    {
		$a['tid']=$templateid;
		if($templateid>0){
		//ob_start();
		include plugin_dir_path( __FILE__ ) . '/partials/wp-google-reviews-public-display.php';
		//return ob_get_clean();
		}
    }
}