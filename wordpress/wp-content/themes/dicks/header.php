<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width" />
<link rel="stylesheet" href="<?php bloginfo('template_url'); ?>/styles/compiled/styles.css">
<link href="https://fonts.googleapis.com/css?family=Sen&display=swap" rel="stylesheet"> 
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="<?php bloginfo('template_url'); ?>/scripts/custom-scripts.js"></script> 
<link href="https://fonts.googleapis.com/css?family=Caveat&display=swap" rel="stylesheet"> 
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
  <div id="wrapper" class="hfeed">
    <header id="header">
      <div class="mobile-menu-trigger">
        <div class="delimiter"></div>
        <div class="delimiter"></div>
        <div class="delimiter"></div>
        <div class="delimiter"></div>
      </div>
      <div id="branding" class="logo">
        <img src="<?php bloginfo('template_url'); ?>/images/logo.png" alt="Dicks Drive-In logo">
      </div>
      <a href="#about-us" class="header-directions-link visible-xs">
        <i class="fas fa-map-marker-alt"></i>
      </a>
      <a class="header-phone-link visible-xs" href="tel:9207663511">
        <i class="fas fa-phone"></i>
      </a>
      <nav id="menu">
        <?php wp_nav_menu( array( 'theme_location' => 'main-menu' ) ); ?>
      </nav>
    </header>
    <div id="container">