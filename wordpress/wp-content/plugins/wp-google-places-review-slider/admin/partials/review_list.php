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
	$html="";
//db function variables
global $wpdb;
$table_name = $wpdb->prefix . 'wpfb_reviews';
$rowsperpage = 20;

	$dbmsg = "";
	$html="";
	$currentreview= new stdClass();
	$currentreview->id="";
	$currentreview->rating="";
	$currentreview->review_title="";
	$currentreview->review_text="";
	$currentreview->reviewer_name="";
	$currentreview->reviewer_id="";
	$currentreview->company_name="";
	$currentreview->created_time="";
	$currentreview->created_time_stamp="";
	$currentreview->userpic="";
	$currentreview->review_length="";
	$currentreview->type="";
	$currentreview->from_name="";
	$currentreview->from_url="";
	$currentreview->from_logo="";
	$currentreview->consent="";
	$currentreview->hidestars="";
	
		//form updating here---------------------------
	if(isset($_GET['editrev'])){
		$rid = htmlentities($_GET['editrev']);
		$rid = intval($rid);
		//for updating
		if($rid > 0){
			//get form array
			$currentreview = $wpdb->get_row( "SELECT * FROM ".$table_name." WHERE id = ".$rid );
		}
		
	}
	if(isset($_GET['deleterev'])){
		$rid = htmlentities($_GET['deleterev']);
		$rid = intval($rid);
		//for updating
		if($rid > 0){
			$delete = $wpdb->query("DELETE FROM `".$table_name."` WHERE id = ".$rid);
		}
		
	}
	
	//------------------------------------------
	
		//form posting here--------------------------------
	//if template id present then update database if not then insert as new.
	if (isset($_POST['wprevpro_submitreviewbtn'])){
		//verify nonce wp_nonce_field( 'wprevpro_save_review');
		check_admin_referer( 'wprevpro_save_review');
		
		//get form submission values and then save or update
		$r_id = sanitize_text_field($_POST['editrid']);
		//$rating = sanitize_text_field($_POST['wprevpro_nr_rating']);
		//$text = sanitize_textarea_field($_POST['wprevpro_nr_text']);
		//$name = sanitize_text_field($_POST['wprevpro_nr_name']);
		$avatar_url = esc_url_raw($_POST['wprevpro_nr_avatar_url']);
		//$rdate = sanitize_text_field($_POST['wprevpro_nr_date']);
		
		//insert or update
			$data = array( 
				'userpic' => "$avatar_url",
				);
			$format = array( 
					'%s',
				); 

		if($r_id==""){
			//insert
			//$wpdb->insert( $table_name, $data, $format );
		} else {
			//update
			$updatetempquery = $wpdb->update($table_name, $data, array( 'id' => $r_id ), $format, array( '%d' ));
			if($updatetempquery>0){
				$dbmsg = '<div id="setting-error-wprevpro_message" class="updated settings-error notice is-dismissible">'.__('<p><strong>Review Updated!</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>', 'wp-review-slider-pro').'</div>';
			}
		}
	}
?>
<div class="wrap" id="wp_fb-settings">
	<h1><img src="<?php echo plugin_dir_url( __FILE__ ) . 'logo.png'; ?>"></h1>
<?php 
include("tabmenu.php");
?>
<div class="wpfbr_margin10">
	<a id="wpfbr_helpicon" class="wpfbr_btnicononly button dashicons-before dashicons-editor-help"></a>
	<a id="wpfbr_removeallbtn" class="button dashicons-before dashicons-no"><?php _e('Remove All Reviews', 'wp-google-reviews'); ?></a>
<p>
	<?php 
_e('Search reviews, hide certain reviews, manually add reviews, save a CSV file of your reviews to your computer, and more features available in the <a href="?page=wp_google-get_pro">Pro Version</a> of this plugin!', 'wp-google-reviews'); 
?>
</p>
</div>
<div class="wprevpro_margin10" id="wprevpro_new_review" <?php if($currentreview->id<1){echo "style='display:none;'";}?>>
<form name="newreviewform" id="newreviewform" action="?page=wp_google-reviews" method="post" onsubmit="return validateForm()">
	<table class="form-table ">
		<tbody>
			<tr class="wprevpro_row">
				<th scope="row">
					<?php _e('Review Rating (1 - 5):', 'wp-review-slider-pro'); ?>
				</th>
				<td><div id="divtemplatestyles">
				<?php 
				//if this is a not a manual review or new one then disable this
					$tempdisable = 'disabled';
				?>
					<input type="radio" name="wprevpro_nr_rating" id="wprevpro_nr_rating1-radio" value="1" <?php if($currentreview->rating=="1"){echo "checked";} else {echo $tempdisable;}?> <?php echo $tempdisable; ?>>
					<label for="wprevpro_template_type1-radio"><?php _e('1', 'wp-review-slider-pro'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="radio" name="wprevpro_nr_rating" id="wprevpro_nr_rating2-radio" value="2" <?php if($currentreview->rating=="2"){echo "checked";} else {echo $tempdisable;}?> <?php echo $tempdisable; ?>>
					<label for="wprevpro_template_type2-radio"><?php _e('2', 'wp-review-slider-pro'); ?></label>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="radio" name="wprevpro_nr_rating" id="wprevpro_nr_rating3-radio" value="3" <?php if($currentreview->rating=="3"){echo "checked";} else {echo $tempdisable;}?> <?php echo $tempdisable; ?>>
					<label for="wprevpro_template_type2-radio"><?php _e('3', 'wp-review-slider-pro'); ?></label>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="radio" name="wprevpro_nr_rating" id="wprevpro_nr_rating4-radio" value="4" <?php if($currentreview->rating=="4"){echo "checked";} else {echo $tempdisable;}?> <?php echo $tempdisable; ?>>
					<label for="wprevpro_template_type2-radio"><?php _e('4', 'wp-review-slider-pro'); ?></label>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="radio" name="wprevpro_nr_rating" id="wprevpro_nr_rating5-radio" value="5" <?php if($currentreview->rating=="5" || $currentreview->rating==""){echo "checked";} else {echo $tempdisable;}?> >
					<label for="wprevpro_template_type2-radio"><?php _e('5', 'wp-review-slider-pro'); ?></label>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					
					</div>

				</td>
			</tr>
			<tr class="wprevpro_row">
				<th scope="row">
					<?php _e('Review Text:', 'wp-review-slider-pro'); ?>
				</th>
				<td>
				<?php 
				//if this is a not a manual review or new one then disable this
					$tempdisable = 'readonly';
				?>
					<textarea name="wprevpro_nr_text" id="wprevpro_nr_text" cols="50" rows="4" <?php echo $tempdisable; ?>><?php echo $currentreview->review_text; ?></textarea>
				</td>
			</tr>
			<tr class="wprevpro_row">
				<th scope="row">
					<?php _e('Reviewer Name:', 'wp-review-slider-pro'); ?>
				</th>
				<td>
					<input id="wprevpro_nr_name" data-custom="custom" type="text" name="wprevpro_nr_name" placeholder="" value="<?php echo $currentreview->reviewer_name; ?>" required <?php echo $tempdisable; ?>>
					<span class="description">
					<?php _e('Enter the name of the person who wrote this review.', 'wp-review-slider-pro'); ?>		</span>
				</td>
			</tr>
			<tr class="wprevpro_row">
				<th scope="row">
					<?php _e('Reviewer Pic URL:', 'wp-review-slider-pro'); ?>
				</th>
				<td>
					<input id="wprevpro_nr_avatar_url" data-custom="custom" type="text" name="wprevpro_nr_avatar_url" placeholder="" value="<?php if($currentreview->userpic!=""){echo $currentreview->userpic; } else {echo plugin_dir_url( __FILE__ ) . 'google_mystery_man.png';} ?>"> <a id="upload_avatar_button" class="button"><?php _e('Upload', 'wp-review-slider-pro'); ?></a>
					<br><span class="description">
					<?php _e('Avatar for the person who wrote the review. Click the following image to insert generic avatar URL.', 'wp-review-slider-pro'); ?>
					</span>
					<div class="avatar_images_list">
					<img src="<?php echo plugin_dir_url( __FILE__ ); ?>google_mystery_man.png" alt="thumb" class="rlimg default_avatar_img">&nbsp;&nbsp;&nbsp;
					</div>
					</br>
					<img class="" height="100px" id="avatar_preview" src="<?php if($currentreview->userpic!=""){echo $currentreview->userpic; } else {echo plugin_dir_url( __FILE__ ) . 'google_mystery_man.png';} ?>">
				</td>
			</tr>
			<tr class="wprevpro_row">
				<th scope="row">
					<?php _e('Review Date:', 'wp-review-slider-pro'); ?>
				</th>
				<td>
					<input id="wprevpro_nr_date" data-custom="custom" type="text" name="wprevpro_nr_date" placeholder="" value="<?php if($currentreview->created_time!=""){echo $currentreview->created_time; } else {echo date("Y-m-d H:i:s",current_time( 'timestamp' ));} ?>" required readonly>
				</td>
			</tr>
		</tbody>
	</table>
	<?php 
	//security nonce
	wp_nonce_field( 'wprevpro_save_review');
	$customlastsaveoption = get_option('wprevpro_customlastsave');
	?>
	<input type="hidden" name="editrid" id="editrid"  value="<?php echo $currentreview->id; ?>">
	<input type="hidden" name="editrtype" id="editrtype"  value="<?php echo $currentreview->type; ?>">
	<input type="submit" name="wprevpro_submitreviewbtn" id="wprevpro_submitreviewbtn" class="button button-primary" value="<?php _e('Save Review', 'wp-review-slider-pro'); ?>">
	<a id="wprevpro_addnewreview_cancel" class="button button-secondary"><?php _e('Cancel', 'wp-review-slider-pro'); ?></a>
</form>
</br></br>
</div>

<?php 

	//remove all, first make sure they want to remove all
	if(isset($_GET['opt']) && $_GET['opt']=="delall"){
		$delete = $wpdb->query("TRUNCATE TABLE `".$table_name."`");
	}
	
	//pagenumber
	if(isset($_GET['pnum'])){
	$temppagenum = $_GET['pnum'];
	} else {
	$temppagenum ="";
	}
	if ( $temppagenum=="") {
		$pagenum = 1;
	} else if(is_numeric($temppagenum)){
		$pagenum = intval($temppagenum);
	}
	
	if(!isset($_GET['sortdir'])){
		$_GET['sortdir'] = "";
	}
	if ( $_GET['sortdir']=="" || $_GET['sortdir']=="DESC") {
		$sortdirection = "&sortdir=ASC";
	} else {
		$sortdirection = "&sortdir=DESC";
	}
	$currenturl = remove_query_arg( 'sortdir' );
	
	//make sure sortby is valid
	if(!isset($_GET['sortby'])){
		$_GET['sortby'] = "";
	}
	$allowed_keys = array('created_time_stamp', 'reviewer_name', 'rating', 'review_length', 'pagename', 'type' );
	$checkorderby = sanitize_key($_GET['sortby']);
	
		if(in_array($checkorderby, $allowed_keys, true) && $_GET['sortby']!=""){
			$sorttable = $_GET['sortby']. " ";
		} else {
			$sorttable = "created_time_stamp ";
		}
		if($_GET['sortdir']=="ASC" || $_GET['sortdir']=="DESC"){
			$sortdir = $_GET['sortdir'];
		} else {
			$sortdir = "DESC";
		}
		unset($sorticoncolor);
		for ($x = 0; $x <= 10; $x++) {
			$sorticoncolor[$x]="";
		} 
		if($sorttable=="hide "){
			$sorticoncolor[0]="text_green";
		} else if($sorttable=="reviewer_name "){
			$sorticoncolor[1]="text_green";
		} else if($sorttable=="rating "){
			$sorticoncolor[2]="text_green";
		} else if($sorttable=="created_time_stamp "){
			$sorticoncolor[3]="text_green";
		} else if($sorttable=="review_length "){
			$sorticoncolor[4]="text_green";
		} else if($sorttable=="pagename "){
			$sorticoncolor[5]="text_green";
		} else if($sorttable=="type "){
			$sorticoncolor[6]="text_green";	
		}
		
		$html .= '
		<table class="wp-list-table widefat striped posts">
			<thead>
				<tr>
					<th scope="col" width="70px" class="manage-column"><i class="dashicons dashicons-sort '.$sorticoncolor[0].'" aria-hidden="true"></i> '.__('Edit', 'wp-google-reviews').'</th>
					<th scope="col" width="50px" class="manage-column">'.__('Pic', 'wp-google-reviews').'</th>
					<th scope="col" style="min-width:70px" class="manage-column"><a href="'.esc_url( add_query_arg( 'sortby', 'reviewer_name',$currenturl ) ).$sortdirection.'"><i class="dashicons dashicons-sort '.$sorticoncolor[1].'" aria-hidden="true"></i> '.__('Name', 'wp-google-reviews').'</a></th>
					<th scope="col" width="70px" class="manage-column"><a href="'.esc_url( add_query_arg( 'sortby', 'rating',$currenturl ) ).$sortdirection.'"><i class="dashicons dashicons-sort '.$sorticoncolor[2].'" aria-hidden="true"></i> '.__('Rating', 'wp-google-reviews').'</a></th>
					<th scope="col" class="manage-column">'.__('Review Text', 'wp-google-reviews').'</th>
					<th scope="col" width="100px" class="manage-column"><a href="'.esc_url( add_query_arg( 'sortby', 'created_time_stamp',$currenturl ) ).$sortdirection.'"><i class="dashicons dashicons-sort '.$sorticoncolor[3].'" aria-hidden="true"></i> '.__('Date', 'wp-google-reviews').'</a></th>
					<th scope="col" width="70px" class="manage-column"><a href="'.esc_url( add_query_arg( 'sortby', 'review_length',$currenturl ) ).$sortdirection.'"><i class="dashicons dashicons-sort '.$sorticoncolor[4].'" aria-hidden="true"></i> '.__('Length', 'wp-google-reviews').'</a></th>
					<th scope="col" width="100px" class="manage-column"><a href="'.esc_url( add_query_arg( 'sortby', 'pagename',$currenturl ) ).$sortdirection.'"><i class="dashicons dashicons-sort '.$sorticoncolor[5].'" aria-hidden="true"></i> '.__('Page Name', 'wp-google-reviews').'</a></th>
					<th scope="col" width="100px" class="manage-column"><a href="'.esc_url( add_query_arg( 'sortby', 'type',$currenturl ) ).$sortdirection.'"><i class="dashicons dashicons-sort '.$sorticoncolor[6].'" aria-hidden="true"></i> '.__('Type', 'wp-google-reviews').'</a></th>
				</tr>
				</thead>
			<tbody id="review_list">';
		//get reviews from db
		$lowlimit = ($pagenum - 1) * $rowsperpage;
		$tablelimit = $lowlimit.",".$rowsperpage;
		$reviewsrows = $wpdb->get_results(
			$wpdb->prepare("SELECT * FROM ".$table_name."
			WHERE id>%d
			ORDER BY ".$sorttable." ".$sortdir." 
			LIMIT ".$tablelimit." ", "0")
		);
		//total number of rows
		$reviewtotalcount = $wpdb->get_var( 'SELECT COUNT(*) FROM '.$table_name );
		//total pages
		$totalpages = ceil($reviewtotalcount/$rowsperpage);
		
		if($reviewtotalcount>0){
			foreach ( $reviewsrows as $reviewsrow ) 
			{
				$hideicon = '<i class="dashicons dashicons-admin-tools editrev" aria-hidden="true"></i>';
				$deleteicon = '<i class="dashicons dashicons-trash deleterev" aria-hidden="true"></i>';

				//user image
				if($reviewsrow->userpic!=""){
					$userpic = '<img style="-webkit-user-select: none; width:100px;" src="'.$reviewsrow->userpic.'">';
				} else {
				$userpic = '<img style="-webkit-user-select: none; width:100px;" src="https://graph.facebook.com/v2.2/'.$reviewsrow->reviewer_id.'/picture?type=square">';
				}
				//user profile link
				$profilelink = '';
				if($reviewsrow->type=="Facebook"){
					$profilelink = "http://facebook.com/".$reviewsrow->reviewer_id;
				}
				if($profilelink){
					$userpic = '<a href="'.$profilelink.'" target=_blank>'.$userpic.'</a>';
				}
	
				$html .= '<tr id="'.$reviewsrow->id.'">
						<th scope="col" class="manage-column"><a title="edit" alt="edit" href="'.esc_url( add_query_arg( 'editrev', $reviewsrow->id,$currenturl ) ).'">'.$hideicon.'</a><a title="delete" alt="delete" href="'.esc_url( add_query_arg( 'deleterev', $reviewsrow->id,$currenturl ) ).'">'.$deleteicon.'</a></th>
						<th scope="col" class="manage-column">'.$userpic.'</th>
						<th scope="col" class="manage-column">'.$reviewsrow->reviewer_name.'</th>
						<th scope="col" class="manage-column">'.$reviewsrow->rating.'</th>
						<th scope="col" class="manage-column"><span title="'.$reviewsrow->review_text.'">'.substr( $reviewsrow->review_text, 0, 100 ).'&hellip;</span></th>
						<th scope="col" class="manage-column">'.$reviewsrow->created_time.'</th>
						<th scope="col" class="manage-column">'.$reviewsrow->review_length.'</th>
						<th scope="col" class="manage-column">'.$reviewsrow->pagename.'</th>
						<th scope="col" class="manage-column">'.$reviewsrow->type.'</th>
					</tr>';
			}
		} else {
				$html .= '<tr>
						<th colspan="9" scope="col" class="manage-column">'.__('No reviews found. Please visit the <a href="?page=wp_google-googlesettings">Get Google Reviews</a> page to retrieve reviews from Google.', 'wp-google-reviews').'</th>
					</tr>';
		}					
				
				
		$html .= '</tbody>
		</table>';
		
		//pagination bar
		$html .= '<div id="wpfb_review_list_pagination_bar">';
		$currenturl = remove_query_arg( 'pnum' );
		for ($x = 1; $x <= $totalpages; $x++) {
			if($x==$pagenum){$blue_grey = "blue_grey";} else {$blue_grey ="";}
			$html .= '<a href="'.esc_url( add_query_arg( 'pnum', $x,$currenturl ) ).'" class="button '.$blue_grey.'">'.$x.'</a>';
		} 
		
		$html .= '</div>';
				
		$html .= '</div>';		
 
 echo $html;
?>
	<div id="popup_review_list" class="popup-wrapper wpfbr_hide">
	  <div class="popup-content">
		<div class="popup-title">
		  <button type="button" class="popup-close">&times;</button>
		  <h3 id="popup_titletext"></h3>
		</div>
		<div class="popup-body">
		  <div id="popup_bobytext1"></div>
		  <div id="popup_bobytext2"></div>
		</div>
	  </div>
	</div>
	

