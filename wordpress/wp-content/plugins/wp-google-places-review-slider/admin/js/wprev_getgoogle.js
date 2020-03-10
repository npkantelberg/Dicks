
function getgooglereviewsfunction(pagename) {

	//launch pop-up for progress messages
	openpopup("Downloading Reviews", "Retrieving Google Business reviews for Place ID: <b>"+pagename+"</b> and saving them to your Wordpress database...</br></br>","");

		var data = {
			action		: 'wpfbr_google_reviews',
			_ajax_nonce		: adminjs_script_vars.wpfb_nonce,
		};

		var myajax = jQuery.post(ajaxurl, data, function(response) {
			jQuery("#wpcvt_goals_ajax").hide();
			if( response != '-1' && ! response != '0' )
			{
				jQuery( "#popup_bobytext2").append(response);
			}
			else
			{
				jQuery( "#popup_bobytext2").html(response);
			}
		});
		jQuery(window).unload( function() { myajax.abort(); } );
}

//launch pop-up windows code--------
function openpopup(title, body, body2){

	//set text
	jQuery( "#popup_titletext").html(title);
	jQuery( "#popup_bobytext1").html(body);
	jQuery( "#popup_bobytext2").html(body2);
	
	var popup = jQuery('#popup').popup({
		width: 400,
		height: 300,
		offsetX: -100,
		offsetY: 0,
	});
	
	popup.open();
}
//--------------------------------


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
		 
		 
		//function wpfbr_testapikey(pagename) {
		jQuery("#wpfbr_testgooglekey").click(function(event){
			
			//map
			//var autocomplete = new google.maps.places.Autocomplete(input[index]);
			
			//launch pop-up for progress messages
			openpopup("Testing API Key Against Google Server", "","");
			
			var data = {
			action		: 'wpfbr_testing_api',
			apikey:google_api_key,
			_ajax_nonce		: adminjs_script_vars.wpfb_nonce,
				};

				var myajax = jQuery.post(ajaxurl, data, function(response) {
					if( response != '-1' && ! response != '0' )
					{
						jQuery( "#popup_bobytext2").append(response);
					}
					else
					{
						jQuery( "#popup_bobytext2").html(response);
					}
				});
				jQuery(window).unload( function() { myajax.abort(); } );

		});
	
		
		//------------ google --------------
		jQuery("#fb_create_google_app").click(function(event){
			window.open('https://developers.google.com/places/web-service/');
		});
		
		var default_google_api_key = "AIzaSyABvcVvPnSZwfLgSxAoRvuUqUS1TNidFIM";
		var google_api_key = jQuery("#google_api_key" ).val();
		
		//for selecting which key to use
		jQuery(".select_google_api").change(function(event){
			if(jQuery("#select_google_api").val()=="default"){
				jQuery(".usedefaultkey").show();
				jQuery(".showapikey").hide();
			} else {
				jQuery(".usedefaultkey").hide();
				jQuery(".showapikey").show();
			}
				if(jQuery("#google_api_key" ).val()=='' || jQuery("#select_google_api").val()=="default"){
					google_api_key = default_google_api_key;
				} else {
					google_api_key = jQuery("#google_api_key" ).val();
				}
		
		
		});
		if(jQuery("#select_google_api").val()=="default"){
			jQuery(".usedefaultkey").show();
			jQuery(".showapikey").hide();
		} else {
			jQuery(".usedefaultkey").hide();
			jQuery(".showapikey").show();
		}

		
		//hide stuff if api key is blank, so user will now use default key of mine.
		if(jQuery("#google_api_key" ).val()=='' || jQuery("#select_google_api").val()=="default"){
			google_api_key = default_google_api_key;
		} else {
			google_api_key = jQuery("#google_api_key" ).val();
		}
		//alert(google_api_key);
		
		window.gm_authFailure = function() {
			$('#wpfbr_result').after('<div class="notice wpfbr-notice-error error"><p>' + adminjs_script_vars.i18n.google_auth_error + '</p></div>');
		};

		if(google_api_key!=''){
			$(document).ajaxSuccess(function (e, xhr, settings) {
				wpfbr_initialize_autocomplete();
			});
			$(window).load(function () {
				wpfbr_initialize_autocomplete();
			});
		}

		/**
		 * Initialize Google Places Autocomplete Field
		 */
		function wpfbr_initialize_autocomplete() {
			var input = $('#google_location_txt');
			var types = $('#google_location_type');

			input.each(function (index, value) {
				
				//console.log(autocomplete);
				var autocomplete = new google.maps.places.Autocomplete(input[index]);
				//var autocomplete = new google.maps.places.SearchBox(input[index]);
				//console.log(autocomplete);
				//Handle type select field
				
				$(types).on('change', function () {
					//Set type
					var type = $(this).val();
					autocomplete.setTypes([type]);
					$(input[index]).val('');
					$("#wpfbr_location, #wpfbr_place_id").val('');
				});
				
				add_autocomplete_listener(autocomplete, input[index]);

				//Tame the enter key to not save the widget while using the autocomplete input
				$(input).keydown(function (e) {
					if (e.which == 13) return false;
				});
			});
		}


		/**
		 * Google Maps API Autocomplete Listener
		 *
		 * @param autocomplete
		 * @param input
		 */
		function add_autocomplete_listener(autocomplete, input) {
			google.maps.event.addListener(autocomplete, 'place_changed', function () {
				var place = autocomplete.getPlace();
				//console.log(place);
				if (!place.place_id) {
					//alert('No place reference found for this location.');
					return false;
				}
				//set location and Place ID hidden input value
				$('#wpfbr_location').val(place.name);
				$('#wpfbr_place_id').val(place.place_id);
			});
		}
		
		function gm_authFailure() {
		  console.log("Authentication failed");
		  document.getElementById('autocomplete_error').innerHTML = "Authentication failed";
		}



		
	 });

})( jQuery );
