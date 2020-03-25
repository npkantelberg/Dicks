jQuery( document ).ready(function() {
  $('.application-button').on('click', function(){
    $('.application-wrapper').toggleClass('open');
  })
  $(".menu-item a[href='#home']").click(function(e) {
    $('html,body').animate({
      scrollTop: $(".hero-section").offset().top
    },'slow');
  });
  $(".menu-item a[href='#menu']").click(function() {
    $('html,body').animate({
      scrollTop: $(".menu-section").offset().top},
      'slow');
  });
  $(".menu-item a[href='#about-us']").click(function() {
    $('html,body').animate({
      scrollTop: $(".about-section").offset().top},
      'slow');
  });
  $(".menu-item a[href='#employment']").click(function() {
    $('html,body').animate({
      scrollTop: $(".application-section").offset().top},
      'slow');
  });
});