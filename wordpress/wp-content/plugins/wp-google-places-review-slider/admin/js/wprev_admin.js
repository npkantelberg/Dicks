//------------get the reviews for a page and save to db with ajax--------------------
function getfbreviewsfunction(pageid,pagetoken,pagename) {

	//launch pop-up for progress messages
	openpopup("Downloading Reviews", "Retrieving Facebook reviews from your <b>"+pagename+"</b> page and saving them to your Wordpress database...</br></br>","");

	var reviewarray = new Array();
	var totalinserted = 0;
	var numtodownload = 25;
	var msg = "";
	for ( var i = 0; i < numtodownload; i++ ) {
		reviewarray[i] = []; 
	}
	var aftercode = "";
	getandsavefbreviews(pageid,pagetoken,pagename,reviewarray,totalinserted,numtodownload,aftercode);

}

function getandsavefbreviews(pageid,pagetoken,pagename,reviewarray,totalinserted,numtodownload,aftercode){	
	//start a loop here that loops on success and stops on error or no more entries, try every 25, update progress bar
	var pagingdata = "";
	FB.api(pageid+'/ratings', {
		access_token : pagetoken,
		pretty:0,
		limit:numtodownload,
		after:aftercode
		}, function(response) {
		//console.log(response);
		//console.log(response.data.length);
			if(response.data.length > 0){
				var fbreviewarray = response.data;
				pagingdata = response.paging;
				for (i = 0; i < fbreviewarray.length; i++) {
					if(fbreviewarray[i].reviewer){
					reviewarray[i] = {};
					reviewarray[i]['pageid']=pageid;
					reviewarray[i]['pagename']=pagename;
					reviewarray[i]['created_time']=fbreviewarray[i].created_time;
					reviewarray[i]['reviewer_name']=fbreviewarray[i].reviewer.name;
					reviewarray[i]['reviewer_id']=fbreviewarray[i].reviewer.id;
					reviewarray[i]['rating']=fbreviewarray[i].rating;
					if(fbreviewarray[i].review_text){
						reviewarray[i]['review_text']=fbreviewarray[i].review_text;
					} else {
						reviewarray[i]['review_text']="";
					}
					reviewarray[i]['type']="Facebook";
					}

				}
		// take response and format array based on what we need only
		//send array via ajax to php function to insert to db.
		// use nonce to make sure this is not hijacked
				//post to server
				var stringifyreviews = JSON.stringify(reviewarray);
				senddata = {
					action: 'wpfb_get_results',	//required
					wpfb_nonce: adminjs_script_vars.wpfb_nonce,
					postreviewarray: reviewarray
					};
				//console.log(stringifyreviews);

				jQuery.post(ajaxurl, senddata, function (response){
					var res = response.split("-");
					totalinserted = Number(totalinserted) + Number(res[2]);
					if(totalinserted>0){
						jQuery( "#popup_bobytext2").html("Total Downloaded: " + totalinserted);
					}
					if(response="0-0-0" && totalinserted==0){
						jQuery( "#popup_bobytext2").html("No new reviews found.");
					}
					
					if(!pagingdata.next){
						jQuery( "#popup_bobytext2").append("</br></br>Finished!");
					}
					
					//loop here if paging data next is available
					if(pagingdata.next){
						aftercode = pagingdata.cursors.after;
						getandsavefbreviews(pageid,pagetoken,pagename,reviewarray,totalinserted,numtodownload,aftercode)
					}
					
				});

			} else {
				//alert("Oops, no reviews returned from Facebook for that page. Please try again or contact us for help.");
				msg = "Oops, no reviews returned from Facebook for that page. If the page does in fact have reviews on Facebook, please try again or contact us for help.";
				jQuery( "#popup_bobytext2").html(msg);
			}

	});

	
}


//launch pop-up windows code--------
function openpopup(title, body, body2){

	//set text
	jQuery( "#popup_titletext").html(title);
	jQuery( "#popup_bobytext1").html(body);
	jQuery( "#popup_bobytext2").html(body2);
	
	var popup = jQuery('#popup').popup({
		width: 400,
		height: 200,
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
		//add links to buttons
		jQuery("#fb_create_app").click(function(){
			window.open('https://developers.facebook.com/apps');
		});

		//hide page list pagelist
		//jQuery("#pagelist").hide();
		jQuery("#fb_user_token_btn").closest('.wpfbr_row').hide();
		
	   var tempappid = jQuery("#fb_app_ID" ).val();
	   //hide stuff if app id is not set
		if(tempappid==''){
			//jQuery(".form-table").hide();
			jQuery("#fb_user_token_btn").closest('.wpfbr_row').hide();
			jQuery("#fb_user_token_field_display").closest('.wpfbr_row').hide();
			jQuery("#pagelist").hide();
		} else {
			jQuery("#fb_user_token_field_display").closest('.wpfbr_row').hide();
		}

		jQuery.ajaxSetup({ cache: true });
		jQuery.getScript('//connect.facebook.net/en_US/sdk.js', function(){
			FB.init({
			  appId: tempappid,
			  version: 'v2.7' // or v2.1, v2.2, v2.3, ...
			}); 
			if(tempappid!=''){
				listpages();
			}
		});
		
		jQuery("#fb_user_token_btn" ).click(function() {
			 listpages();
		});
		

		//--------------------------
		function listpages(){
			if(tempappid==''){
				alert('Please enter your Facebook App ID above and click the Save Settings button at the bottom of this page.');
				return false;
			}
			
			//request manage_pages scope
			FB.login(function(){}, {scope: 'manage_pages'});
			
			FB.getLoginStatus(function(response) {
			  if (response.status === 'connected') {
			  var accesstoken = response.authResponse.accessToken;
			  //save access token in hidden field
			  jQuery("#fb_user_token_field_display" ).val(accesstoken);
			  
				//console.log(response.authResponse.accessToken);
				
				FB.api('/me/accounts?limit=100', function(response){
				//console.log(response);
				
					//loop through page access tokens and save and display them in the table.
					if(response.data[0].access_token){
					jQuery("#page_list" ).html("");
						var fbpagearray = response.data;
						var tablerows = "";
						var i = 0;
						var temppagename = "";
						for (i = 0; i < fbpagearray.length; i++) { 
							temppagename = fbpagearray[i].name.replace(/'/g, "%27");
							tablerows = tablerows + '<tr id="" class=""><td><button onclick=\'getfbreviewsfunction("' + fbpagearray[i].id + '", "' + fbpagearray[i].access_token + '", "' + temppagename + '")\' id="getreviews_' + fbpagearray[i].id + '" type="button" class="btn_green">Retrieve Reviews</button></td> \
										<td><strong>' + fbpagearray[i].name + '</strong></td> \
										<td><strong>' + fbpagearray[i].id + '</strong></td> \
										<td><strong>' + fbpagearray[i].category + '</strong></td> \
									</tr>';
						}
						jQuery("#page_list" ).append( tablerows );
						jQuery("#pagelist").show();
					} else {
						alert("Oops, no Facebook pages found. Please try again or contact us for help.");
					}
				
				});
				
			  }
			});
			

			//call the graph api to get a page access token and put it in the text field
		
		}
		
	 });

})( jQuery );
