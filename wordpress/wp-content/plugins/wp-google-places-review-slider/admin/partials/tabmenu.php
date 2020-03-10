<?php
$url = explode( '?', esc_url_raw( add_query_arg( array() ) ) );

$urltrimmedtab = $url[0];

$urlsettings = esc_url( add_query_arg( 'page', 'wp_fb-settings',$urltrimmedtab ) );
$urlwelcome = esc_url( add_query_arg( 'page', 'wp_google-welcome',$urltrimmedtab ) );
$urlgooglesettings = esc_url( add_query_arg( 'page', 'wp_google-googlesettings',$urltrimmedtab ) );
$urlreviewlist = esc_url( add_query_arg( 'page', 'wp_google-reviews',$urltrimmedtab ) );
$urltemplateposts = esc_url( add_query_arg( 'page', 'wp_google-templates_posts',$urltrimmedtab ) );
$urlgetpro = esc_url( add_query_arg( 'page', 'wp_google-get_pro',$urltrimmedtab ) );
?>	
	<h2 class="nav-tab-wrapper">
	<a href="<?php echo $urlwelcome; ?>" class="nav-tab <?php if($_GET['page']=='wp_google-welcome'){echo 'nav-tab-active';} ?>"><?php _e('Welcome', 'wp-google-reviews'); ?></a>
	<a href="<?php echo $urlgooglesettings; ?>" class="nav-tab <?php if($_GET['page']=='wp_google-googlesettings'){echo 'nav-tab-active';} ?>"><?php _e('Get Google Reviews', 'wp-google-reviews'); ?></a>
	<a href="<?php echo $urlreviewlist; ?>" class="nav-tab <?php if($_GET['page']=='wp_google-reviews'){echo 'nav-tab-active';} ?>"><?php _e('Reviews List', 'wp-google-reviews'); ?></a>
	<a href="<?php echo $urltemplateposts; ?>" class="nav-tab <?php if($_GET['page']=='wp_google-templates_posts'){echo 'nav-tab-active';} ?>"><?php _e('Templates', 'wp-google-reviews'); ?></a>
	<a href="<?php echo $urlgetpro; ?>" class="nav-tab <?php if($_GET['page']=='wp_google-get_pro'){echo 'nav-tab-active';} ?>"><?php _e('Get Pro Version', 'wp-google-reviews'); ?></a>
	</h2>