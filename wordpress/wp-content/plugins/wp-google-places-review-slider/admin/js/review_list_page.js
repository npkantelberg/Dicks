(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 * $( document ).ready(function() same as
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	 
	 //document ready
	$(function(){
		
		//help button clicked
		$( "#wpfbr_helpicon" ).click(function() {
		  openpopup("Tips", '<p>- If you\'re using the pro version you can hide certain reviews by clicking the <i class="dashicons dashicons-visibility text_green" aria-hidden="true"></i> in the table below. There are also ways to hide certain types of reviews under the Templates page.</p>	\
		  <p><b>- Remove All Reviews:</b> Allows you to delete all reviews in your Wordpress database and start over. It Does NOT affect your reviews on Facebook.</p> \
		  <p><b>- Manually Add Review:</b> Allows you to manaully insert a review in to your Wordpress database.</p> \
		  <p><b>- Download a CSV File:</b> Save a CSV file to your computer containing all the reviews in this table.</p> \
		  ', "");
		});
		
		$( "#wprevpro_addnewreview_cancel" ).click(function() {
		  jQuery("#wprevpro_new_review").hide("slow");
		  //reload page without taction and tid
		  //setTimeout(function(){ 
			//window.location.href = "?page=wp_pro-reviews"; 
		  //}, 500);
		  
		});
		
		//remove all button
		$( "#wpfbr_removeallbtn" ).click(function() {
		  openpopup("Are you sure?", '<p>This will delete all reviews in your Wordpress database including the ones you manually entered. It Does NOT affect your reviews on Facebook.</p>', '<a class="button dashicons-before dashicons-no" href="?page=wp_google-reviews&opt=delall">Remove</a>');
		});	

		//upgrade to pro
		$( ".wpfbr_upgrade_needed" ).click(function() {
		  openpopup("Upgrade Needed", '<p>Please upgrade to the Pro Version of this Plugin to access this feature.</p>', '<a class="button dashicons-before  dashicons-cart" href="?page=wp_google-get_pro">Upgrade Here</a>');
		});		
		
		//upload avatar button----------------------------------
		$('#upload_avatar_button').click(function() {
			tb_show('Upload Reviewer Avatar', 'media-upload.php?referer=wp_pro-reviews&type=image&TB_iframe=true&post_id=0', false);
			return false;
		});
		window.send_to_editor = function(html) {
			var image_url = jQuery("<div>" + html + "</div>").find('img').attr('src');
			//var image_url = $('img',html).attr('src');
			$('#wprevpro_nr_avatar_url').val(image_url);
			$("#avatar_preview").attr("src",image_url);
			tb_remove();
		}
		//for adding default avater url to input rlimg
		$( ".rlimg" ).click(function() {
			var tempsrc = $(this).attr('src');
			$("#wprevpro_nr_avatar_url").val(tempsrc);
			$("#wprevpro_nr_avatar_url").select();
			$("#avatar_preview").attr("src",tempsrc);
		});

		//launch pop-up windows code--------
		function openpopup(title, body, body2){

			//set text
			jQuery( "#popup_titletext").html(title);
			jQuery( "#popup_bobytext1").html(body);
			jQuery( "#popup_bobytext2").html(body2);
			
			var popup = jQuery('#popup_review_list').popup({
				width: 400,
				offsetX: -100,
				offsetY: 0,
			});
			
			popup.open();
			//set height
			var bodyheight = Number(jQuery( ".popup-content").height()) + 10;
			jQuery( "#popup_review_list").height(bodyheight);

		}
		//--------------------------------
		
	});

})( jQuery );