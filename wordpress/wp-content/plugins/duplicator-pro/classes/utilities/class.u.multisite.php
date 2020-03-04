<?php
defined("ABSPATH") or die("");

class DUP_PRO_MU_Generations
{
    const NotMultisite = 0;
    const PreThreeFive = 1;
    const ThreeFivePlus = 2;
}

class DUP_PRO_MU
{

    public static function networkMenuPageUrl($menu_slug, $echo = true)
    {
        global $_parent_pages;

        if (isset($_parent_pages[$menu_slug])) {
            $parent_slug = $_parent_pages[$menu_slug];
            if ($parent_slug && !isset($_parent_pages[$parent_slug])) {
                $url = network_admin_url(add_query_arg('page', $menu_slug, $parent_slug));
            } else {
                $url = network_admin_url('admin.php?page='.$menu_slug);
            }
        } else {
            $url = '';
        }

        $url = esc_url($url);

        if ($echo) {
            echo esc_url($url);
        }

        return $url;
    }

    public static function isMultisite()
    {
        return self::getMode() > 0;
    }

    // 0 = single site; 1 = multisite subdomain; 2 = multisite subdirectory
    public static function getMode()
    {

		if(is_multisite()) {
            if (defined('SUBDOMAIN_INSTALL') && SUBDOMAIN_INSTALL) {
                return 1;
            } else {
                return 2;
            }
        } else {
            return 0;
        }
    }

    /**
     * This function is wrong because it assumes that if the folder sites exist, blogs.dir cannot exist.
     * This is not true because if the network is old but a new site is created after the WordPress update both blogs.dir and sites folders exist.
     * 
     * @deprecated since version 3.8.4
     *
     * @return int
     */
    public static function getGeneration()
    {
        if(self::getMode() == 0)
        {
            return DUP_PRO_MU_Generations::NotMultisite;
        }
        else
        {
			$sitesDir = WP_CONTENT_DIR . '/uploads/sites';

			if(file_exists($sitesDir))
            {
				return DUP_PRO_MU_Generations::ThreeFivePlus;
            }
            else
            {
				return DUP_PRO_MU_Generations::PreThreeFive;
            }
        }
    }

    // Copied from WordPress 3.7.2
    function legacy_wp_get_sites( $args = array() )
    {
        global $wpdb;

        if ( wp_is_large_network() )
            return array();

        $defaults = array(
            'network_id' => $wpdb->siteid,
            'public'     => null,
            'archived'   => null,
            'mature'     => null,
            'spam'       => null,
            'deleted'    => null,
            'limit'      => 2000,
            'offset'     => 0,
        );

        $args = wp_parse_args( $args, $defaults );

        $query = "SELECT * FROM $wpdb->blogs WHERE 1=1 ";

        if ( isset( $args['network_id'] ) && ( is_array( $args['network_id'] ) || is_numeric( $args['network_id'] ) ) ) {
            $network_ids = implode( ',', wp_parse_id_list( $args['network_id'] ) );
            $query .= "AND site_id IN ($network_ids) ";
        }

        if ( isset( $args['public'] ) )
            $query .= $wpdb->prepare( "AND public = %d ", $args['public'] );

        if ( isset( $args['archived'] ) )
            $query .= $wpdb->prepare( "AND archived = %d ", $args['archived'] );

        if ( isset( $args['mature'] ) )
            $query .= $wpdb->prepare( "AND mature = %d ", $args['mature'] );

        if ( isset( $args['spam'] ) )
            $query .= $wpdb->prepare( "AND spam = %d ", $args['spam'] );

        if ( isset( $args['deleted'] ) )
            $query .= $wpdb->prepare( "AND deleted = %d ", $args['deleted'] );

        if ( isset( $args['limit'] ) && $args['limit'] ) {
            if ( isset( $args['offset'] ) && $args['offset'] )
                $query .= $wpdb->prepare( "LIMIT %d , %d ", $args['offset'], $args['limit'] );
            else
                $query .= $wpdb->prepare( "LIMIT %d ", $args['limit'] );
        }

        $site_results = $wpdb->get_results( $query, ARRAY_A );

        return $site_results;
    }

    // Return an array of { id: {subsite id}, name {subdir name} , blogname {site title} )
    public static function getSubsites($filter_sites = array())
    {
        $site_array = array();
        $mu_mode    = DUP_PRO_MU::getMode();
       
        if ($mu_mode !== 0) {
            if (function_exists('get_sites')) {

                $sites = get_sites(array('number' => 2000));

                $home_url_path = parse_url(get_home_url(), PHP_URL_PATH);
                foreach ($sites as $site) {
                    if(empty($filter_sites) || (!empty($filter_sites) && !in_array($site->blog_id,$filter_sites))){
                        if ($mu_mode == 1) {
                            // Subdomain
                            $name = get_home_url($site->blog_id);
                        } else {
                            // Subdirectory
                            if($sites[0]->domain == $site->domain){
                                $name = $site->path;
                            }else{
                                $name = get_home_url($site->blog_id);
                            }

                            if (DUP_PRO_STR::startsWith($name, $home_url_path)) {
                                $name = substr($name, strlen($home_url_path));
                            }
                        }

                        $site_details = get_blog_details($site->blog_id);

                        $site_info           = new stdClass();
                        $site_info->id       = $site->blog_id;
                        $site_info->name     = $name;
                        $site_info->blogname = $site_details->blogname;

                        array_push($site_array, $site_info);
                        DUP_PRO_LOG::trace("Multisite subsite detected. ID={$site_info->id} Name={$site_info->name}");
                    }
                }
            } else {
                if (function_exists('wp_get_sites')) {
                    $wp_sites = wp_get_sites(array('limit' => 2000));
                } else {
                    $wp_sites = self::legacy_wp_get_sites();
                }

                DUP_PRO_LOG::traceObject("####wp sites", $wp_sites);

                foreach ($wp_sites as $wp_site) {
                    if ($mu_mode == 1) {
                        // Subdomain
                        $wp_name = get_home_url($wp_site['blog_id']);
                    } else {
                        // Subdirectory
                        if($wp_sites[0]['domain'] == $wp_site['domain']){
                            $wp_name = $wp_site['path'];
                        }else{
                            $wp_name = get_home_url($wp_site['blog_id']);
                        }
                    }

                    $site_details = get_blog_details($wp_site['blog_id']);

                    $wp_site_info       = new stdClass();
                    $wp_site_info->id   = $wp_site['blog_id'];
                    $wp_site_info->name = $wp_name;
                    $wp_site_info->blogname = $site_details->blogname;

                    array_push($site_array, $wp_site_info);
                }
            }

            foreach ($site_array as $site_index_key=>$site_info) {
                $site_array[$site_index_key]->blog_prefix = $GLOBALS['wpdb']->get_blog_prefix($site_info->id);
            }
        }

        return $site_array;
    }

    /**
     * Returns the main site ID for the network.
     *
     * Copied from the source of the get_main_site_id() except first line in https://developer.wordpress.org/reference/functions/get_main_site_id/
     * get_main_site_id() is introduced in WP 4.9.0. It is for backward compatibility
     * 
     * @param int|null network id
     * @return int The ID of the main site.
     */
    public static function get_main_site_id($network_id = null)
    {
        // For > WP 4.9.0  
        if (function_exists('get_main_site_id')) {
            return get_main_site_id($network_id);
        }

        if (!is_multisite()) {
            return get_current_blog_id();
        }

        $network = function_exists('get_network') ? get_network($network_id) : wp_get_network($network_id);
        if (!$network) {
            return 0;
        }

        return $network->site_id;
    }
}
