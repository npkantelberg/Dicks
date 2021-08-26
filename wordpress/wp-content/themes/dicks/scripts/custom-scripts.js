jQuery( document ).ready(function() {
  // Open and closes the application.
  $('.application-button').on('click', function(){
    $('.application-wrapper').toggleClass('open');
  });

  // Menu scrolling scripts
  $(".menu-item a[href='#home']").click(function() {
    $("html").animate({ scrollTop: 0 }, "slow");
    return false;
  });
  $(".menu-item a[href='#menu']").click(function() {    
    $('html,body').animate({
      scrollTop: $(".menu-section").offset().top - $('#header').height()},
      'slow');
  });
  $("a.header-directions-link[href='#about-us']").click(function() {
    console.log('button-clicked');
    $('html,body').animate({
      scrollTop: $(".about-section").offset().top - $('#header').height()},
      'slow');
  });
  $(".menu-item a[href='#about-us']").click(function() {
    console.log('button-clicked');
    $('html,body').animate({
      scrollTop: $(".about-section").offset().top - $('#header').height()},
      'slow');
  });
  $(".menu-item a[href='#employment']").click(function() {
    $('html,body').animate({
      scrollTop: $(".application-section").offset().top - $('#header').height()},
      'slow');
  });
  // $( ".menu-category" ).each(function( index ) {
  //   var 
  // });
  $(".menu-category").click(function(e) {
    e.preventDefault();
    $('html,body').animate({
      scrollTop: $("#" + $(this).attr('href')).offset().top - $('#header').height() - $('#header').height() - $('#header').height()},
      'slow');
  });

  // Opens the menu on click.
  $('.mobile-menu-trigger').on("click", function(){
    $('#menu').toggleClass('open');
  })
});