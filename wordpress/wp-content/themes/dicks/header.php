<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width" />
<link rel="stylesheet" href="<?php bloginfo('template_url'); ?>/styles/compiled/styles.css">
<link href="https://fonts.googleapis.com/css?family=Sen&display=swap" rel="stylesheet"> 
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="<?php bloginfo('template_url'); ?>/scripts/custom-scripts.js"></script> 
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
  <div id="wrapper" class="hfeed">
    <header id="header">
      <div id="branding" class="logo">
        <img src="<?php bloginfo('template_url'); ?>/images/logo.png" alt="Dicks Drive-In logo">
      </div>
      <nav id="menu">
        <?php wp_nav_menu( array( 'theme_location' => 'main-menu' ) ); ?>
      </nav>
    </header>
    <div id="container">