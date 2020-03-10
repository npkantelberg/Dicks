<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    WP_Google_Reviews
 * @subpackage WP_Google_Reviews/admin/partials
 */
 
     // check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
?>
<div class="wrap" id="wp_fb-settings">
	<h1><img src="<?php echo plugin_dir_url( __FILE__ ) . 'logo.png'; ?>"></h1>
<?php 
include("tabmenu.php");
?>
<div class="wpfbr_margin10">
<a href="https://ljapps.com/wp-review-slider-pro/" class="btn_green dashicons-before dashicons-external"><?php _e('Get Pro Version Here!', 'wp-google-reviews'); ?></a>
<h1>Get the Pro Version of this plugin and unlock these great features!</h1>

<ul style="
    list-style-type: circle;
    margin-left: 30px;
">
<li>Grab all your Google reviews, not just the 5 Most Helpful, using our new Review Funnels feature!</li>
	<li>Customer support via email and a forum.</li>
	<li>Front end submission form.</li>
	<li>Get email notifications when new low reviews are found.</li>
	<li>Create floating Summary Badges and Review Badges, that can display a slide-out of reviews when clicked.</li>
	<li>Built in pagination support so you can load more reviews with a button click.</li>
	<li>Download your reviews from 50+ review sites! Yelp, TripAdvisor, Facebook, Airbnb, WooCommerce, etc... </li>
	<li>Get an email when low reviews are downloaded.</li>
	<li>Hide certain reviews from displaying.</li>
	<li>Manually add reviews to your database.</li>
	<li>Download all your reviews in CSV format to your computer.</li>
	<li>Access more Review Template styles!</li>
	<li>Advanced slider controls like: Autoplay, slide animation direction, hide navigation arrows and dots, adjust slider height for each slide.</li>
	<li>Change the minimum rating of the reviews to display. Allows you to hide low rating reviews.</li>
	<li>Use a minimum and maximum word count so you can hide short or long reviews.</li>
	<li>Only display reviews of a certain type (Facebook, Yelp, TripAdvisor, Google, manually input).</li>
	<li>Specify which Page to display reviews from per a template.</li>
	<li>Individually choose which reviews you want to display per a template.</li>
	<li>Add a Read More link to long reviews!</li>
	<li>Display a summary of your review ratings in a Google Search Result. You can automatically create the correct review snippet markup!</li>
	<li>Access to all new features we add in the future!</li>
</ul>
<h1>Some of Our Happy Pro Customers</h1>
<div class="w3_wprs-row">
							<div class="w3_wprs-col s4">
							  <style>.wprevpro_t1_DIV_2::after{ border-top: 30px solid #fdfdfd; }.wprevpro_t1_DIV_1 {margin: 5px;}a {
    text-decoration: none;
}</style>
							  <div class="w3_wprs-col">
							  <div class="wprevpro_t1_DIV_1">	
							  <div class="wprevpro_t1_DIV_2 wprev_preview_bg1 wprev_preview_bradius" style="border-radius: 0px; background: rgb(253, 253, 253);">										<p class="wprevpro_t1_P_3 wprev_preview_tcolor1" style="color: rgb(85, 85, 85);">											<span class="wprevpro_star_imgs"><img src="https://ljapps.com/wp-content/plugins/wp-review-slider-pro-premium/public/partials/imgs/stars_5_yellow.png" alt="">&nbsp;&nbsp;</span>Great for my site! Good choice of styles & formats, easy to use. Show cases our 5* (others if required) reviews from Facebook & Google+ easy to update. Good all round plugin.</p>									</div><span class="wprevpro_t1_A_8"><img src="https://s3-us-west-2.amazonaws.com/freemius/plugins//reviews/c8174af85095ea546c03cddd103abfd2.jpg" alt="thumb" class="wprevpro_t1_IMG_4"></span> <span class="wprevpro_t1_SPAN_5 wprev_preview_tcolor2" style="color: rgb(85, 85, 85);">Antony Bowers<br>Director, <a href="https://www.sweetfantasies.co.uk" target="_blank">Sweet Fantasies Cakes </a><br><span id="wprev_showdate"></span> </span>								
							  </div>								
							  </div>
							</div>
							<div class="w3_wprs-col s4">
							  <div class="w3_wprs-col">							
							  <div class="wprevpro_t1_DIV_1">									
							  <div class="wprevpro_t1_DIV_2 wprev_preview_bg1 wprev_preview_bradius" style="border-radius: 0px; background: rgb(253, 253, 253);">										<p class="wprevpro_t1_P_3 wprev_preview_tcolor1" style="color: rgb(85, 85, 85);">											<span class="wprevpro_star_imgs"><img src="https://ljapps.com/wp-content/plugins/wp-review-slider-pro-premium/public/partials/imgs/stars_5_yellow.png" alt="">&nbsp;&nbsp;</span>Great product, great support! Love this product and the support received has been amazing and fast.</p>									</div><span class="wprevpro_t1_A_8"><img src="https://s3-media3.fl.yelpcdn.com/photo/9Fs55PxyEqobFBQwzmu_wg/120s.jpg" alt="thumb" class="wprevpro_t1_IMG_4"></span> <span class="wprevpro_t1_SPAN_5 wprev_preview_tcolor2" style="color: rgb(85, 85, 85);">Russ Kemp<br>Owner, <a href="https://www.russkempphotography.com" target="_blank">Russ Kemp Photography </a><br><span id="wprev_showdate"></span> </span>								</div>								
							  </div>
							</div>
						  <div class="w3_wprs-col s4">
							  <div class="w3_wprs-col">							
							  <div class="wprevpro_t1_DIV_1">
							  <div class="wprevpro_t1_DIV_2 wprev_preview_bg1 wprev_preview_bradius" style="border-radius: 0px; background: rgb(253, 253, 253);">										<p class="wprevpro_t1_P_3 wprev_preview_tcolor1" style="color: rgb(85, 85, 85);">											<span class="wprevpro_star_imgs"><img src="https://ljapps.com/wp-content/plugins/wp-review-slider-pro-premium/public/partials/imgs/stars_5_yellow.png" alt="">&nbsp;&nbsp;</span>Exactly what I needed. Excellent support as well.		</p>									</div><span class="wprevpro_t1_A_8"><img src="https://www.gravatar.com/avatar/950af451288353b2525a0064e4e95efa?size=75&default=https%3A%2F%2Fdashboard.freemius.com%2Fassets%2Fimg%2Ffs%2Fprofile-pics%2Fprofile-pic-2.png" alt="thumb" class="wprevpro_t1_IMG_4"></span> <span class="wprevpro_t1_SPAN_5 wprev_preview_tcolor2" style="color: rgb(85, 85, 85);">C.J. Ware<br><span id="wprev_showdate"></span> </span>								</div>								
							  </div>
						  </div>
					</div>
					</br>
	<div>				
	<a href="https://ljapps.com/feedback-for-wp-review-slider-pro/" target="_blank">Read More Feedback</a>
	</div>		
	</br>
	<div>	
	<a href="https://ljapps.com/wp-review-slider-pro/" class="btn_green dashicons-before dashicons-external" target="_blank"><?php _e('Get Pro Version Here!', 'wp-fb-reviews'); ?></a>
	</div>
</div>

</div>

	

