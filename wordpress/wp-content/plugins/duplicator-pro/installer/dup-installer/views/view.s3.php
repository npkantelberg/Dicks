<?php
defined("DUPXABSPATH") or die("");

require_once(DUPX_INIT.'/classes/class.s3.func.php');
require_once(DUPX_INIT.'/views/classes/class.view.s3.php');

$paramsManager = DUPX_Paramas_Manager::getInstance();

//-- START OF VIEW STEP 3            
if ($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_TEST_OK) == false) {
    throw new Exception('Database test not passed');
}

?>

<!-- =========================================
VIEW: STEP 3- INPUT -->
<form id='s3-input-form' method="post" class="content-form" autocomplete="off">
	<?php
    DUPX_U_Html::getHeaderMain('Step <span class="step">3</span> of 4: Update Data <div class="sub-header">This step will update the database and config files to match your new sites values.</div>');

    if ($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_ACTION) == 'manual') {
        echo '<div class="dupx-notice s3-manaual-msg">Manual SQL execution is enabled</div>';
    }

    $actionParams = array(
        'ctrl_action' => 'ctrl-step3',
        DUPX_Security::CTRL_TOKEN => DUPX_CSRF::generate('ctrl-step3'),
        'view' => 'step3',
         DUPX_Security::VIEW_TOKEN => DUPX_CSRF::generate('step3')
    );
   	?>

	<!--  POST PARAMS -->
    <div class="dupx-debug">
        <i>Step 3 - Page Load</i>
        <?php
        $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_CTRL_ACTION, $actionParams['ctrl_action']);
        $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_VIEW, $actionParams['view']);
        ?>
        <input type="hidden" name="<?php echo DUPX_Security::CTRL_TOKEN; ?>" value="<?php echo DUPX_U::esc_attr($actionParams[DUPX_Security::CTRL_TOKEN]); ?>">
        <input type="hidden" name="<?php echo DUPX_Security::VIEW_TOKEN; ?>" value="<?php echo DUPX_U::esc_attr($actionParams[DUPX_Security::VIEW_TOKEN]); ?>">
        <input type="hidden" name="json" value="<?php echo DUPX_U::esc_attr($_POST['json']); ?>" />
    </div>

	<?php 
    DUPX_View_S3::newSettings();
    DUPX_View_S3::mappingMode();
    DUPX_View_S3::customSearchAndReaplce();
    DUPX_View_S3::options();
    ?>
    
	<!--  END TABS  -->
	</div>
    <div class="footer-buttons">
        <div class="content-left">
        </div>
        <div class="content-right" >
            <button id="s3-next" type="button"  onclick="DUPX.runUpdate()" class="default-btn"> Next <i class="fa fa-caret-right"></i> </button>
        </div>
    </div>
</form>

<!-- =========================================
VIEW: STEP 3 - AJAX RESULT  -->
<form id='s3-result-form' method="post" class="content-form" style="display:none" autocomplete="off">
    <?php DUPX_U_Html::getHeaderMain('Step <span class="step">3</span> of 4: Update Data <div class="sub-header">This step will update the database and config files to match your new sites values.</div>'); ?>

	<!--  POST PARAMS -->
	<div class="dupx-debug">
		<i>Step 3 - AJAX Response</i>
		<input type="hidden" name="view"  value="step4" />
		<input type="hidden" name="<?php echo DUPX_Security::VIEW_TOKEN; ?>" value="<?php echo DUPX_CSRF::generate('step4'); ?>">
		<input type="hidden" name="json"    id="ajax-json" />
		<input type='submit' value='manual submit'>
	</div>

	<!--  PROGRESS BAR -->
	<div id="progress-area">
		<div style="width:500px; margin:auto">
            <div class="progress-text"><i class="fas fa-circle-notch fa-spin"></i> Processing Data Replacement <span class="progress-perc">0%</span></div>
			<div id="progress-bar"></div>
			<h3> Please Wait...</h3><br/><br/>
			<i>Keep this window open during the replacement process.</i><br/>
			<i>This can take several minutes.</i>
		</div>
	</div>

	<!--  AJAX SYSTEM ERROR -->
	<div id="ajaxerr-area" style="display:none">
		<p>Please try again an issue has occurred.</p>
		<div style="padding: 0px 10px 10px 10px;">
			<div id="ajaxerr-data">
                <div class="content" >
                    An unknown issue has occurred with the update setup step.  Please see the <?php DUPX_View_Funcs::installerLogLink(); ?> file for more details.
                </div>
                <div class="troubleshooting" >
                    <b>Additional Troubleshooting Tips:</b><br/>
                    - Check the <?php DUPX_View_Funcs::installerLogLink(); ?> file for warnings or errors.<br/>
                    - Check the web server and PHP error logs. <br/>
                    - For timeout issues visit the <a href="https://snapcreek.com/duplicator/docs/faqs-tech/#faq-trouble-100-q" target="_blank">Timeout FAQ Section</a>
                </div>
            </div>
			<div style="text-align:center; margin:10px auto 0px auto">
			<?php
			$archive_config = DUPX_ArchiveConfig::getInstance();
			?>
				<input type="button" onclick='<?php 
				if (0 == $archive_config->mu_mode) { ?>
					DUPX.hideErrorResult2();
				<?php } else { ?>
					window.history.back();
				<?php } ?>' value="&laquo; Try Again"  class="default-btn" /><br/><br/>
				<i style='font-size:11px'>See online help for more details at <a href='https://snapcreek.com' target='_blank'>snapcreek.com</a></i>
			</div>
		</div>
	</div>
</form>

<script>
    var wpUserNameInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_WP_ADMIN_NAME)); ?>;
    var wpPwdInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_WP_ADMIN_PASSWORD)); ?>;
    var wpMailInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_WP_ADMIN_MAIL)); ?>;

/** 
* Timeout (10000000 = 166 minutes) */
DUPX.runUpdate = function()
{
	//Validation
	var wp_username = $.trim($("#" + wpUserNameInputId).val()).length || 0;
	var wp_password = $.trim($("#" + wpPwdInputId).val()).length || 0;
    var wp_mail = $.trim($("#" + wpMailInputId).val()).length || 0;

     if (wp_username >= 1) {
        if (wp_username < 4) {
            alert("The New Admin Account 'Username' must be four or more characters");
            return false;
        } else if (wp_password < 6) {
            alert("The New Admin Account 'Password' must be six or more characters");
            return false;
        } else if (wp_mail === 0) {
            alert("The New Admin Account 'mail' is required");
            return false;
        }
    }

	var nonHttp = false;
	var failureText = '';

	/* IMPORTANT - not trimming the value for good - just in the check */
	$('input[name="search[]"]').each(function() {
		var val = $(this).val();

		if(val.trim() != "") {
			if(val.length < 3) {
				failureText = "Custom search fields must be at least three characters.";
			}

			if(val.toLowerCase().indexOf('http') != 0) {
				nonHttp = true;
			}
		}
	});

	$('input[name="replace[]"]').each(function() {
		var val = $(this).val();
		if(val.trim() != "") {
			// Replace fields can be anything
			if(val.toLowerCase().indexOf('http') != 0) {
				nonHttp = true;
			}
		}
	});

	if(failureText != '') {
		alert(failureText);
		return false;
	}

	if(nonHttp) {
		if(confirm('One or more custom search and replace strings are not URLs.  Are you sure you want to continue?') == false) {
			return false;
		}
	}

    if($('input[type=radio][name=replace_mode]:checked').val() == 'mapping'){
        $("#new-url-container").remove();
    }else if($('input[type=radio][name=replace_mode]:checked').val() == 'legacy') {
        $("#subsite-map-container").remove();
    }

    DUPX.ajaxRequest();
};

var lastChunkPosition = null;

DUPX.checkDataResponseData = function(respData, textStatus , xHr) {
    try {
        var data = $.parseJSON(respData);
    } catch(err) {
        console.error(err);
        console.error('JSON parse failed for response data: ' + respData);
        var status  = "<b>Server Code:</b> "	+ xHr.status		+ "<br/>";
        status += "<b>Status:</b> "			+ xHr.statusText	+ "<br/>";
        status += "<b>Response:</b> "		+ xHr.responseText  + "<hr/>";
        status += "Json response not well formatted<br>";
        $('#ajaxerr-data .content').html(status);
        DUPX.hideProgressBar();
        return false;
    }

    if (typeof(data) != 'undefined') {
        if (data.step3.chunk == 1) {
            if (JSON.stringify(lastChunkPosition) !== JSON.stringify(data.step3.chunkPos)) {
                var lastChunkPosition =  data.step3.chunkPos;
                $('.progress-perc').text(data.step3.progress_perc + '%');
                // if chunk recover the request
                DUPX.ajaxRequest(true);
            } else {
                console.error('Chunk is stuck: ' + respData);
                var status  = "<b>Server Code:</b> "	+ xHr.status		+ "<br/>";
                status += "<b>Status:</b> "			+ xHr.statusText	+ "<br/>";
                status += "<b>Response:</b> "		+ xHr.responseText  + "<hr/>";
                status += "Chunking is stuck<br>";
                $('#ajaxerr-data .content').html(status);
                DUPX.hideProgressBar();
                return false;
            }
        } else if (data.step3.pass == 1) {
            $("#ajax-json").val(escape(JSON.stringify(data)));
            <?php if (! $GLOBALS['DUPX_DEBUG']) : ?>
                setTimeout(function(){$('#s3-result-form').submit();}, 1000);
            <?php endif; ?>
            $('#progress-area').fadeOut(1800);
        } else  {
            DUPX.hideProgressBar();
        }
    } else {
        DUPX.hideProgressBar();
    }
};

DUPX.ajaxRequest = function(chunk) {
    chunk = chunk || false;

    if (chunk) {
        var data = <?php echo DupProSnapJsonU::wp_json_encode($actionParams); ?>;
    } else {
        var data = $('#s3-input-form').serialize();
    }

    let params = {
		type: "POST",
		timeout: 10000000,
		url: window.location.href,
		data: data,
		cache: false,
		success: function(respData, textStatus , xhr){
            DUPX.checkDataResponseData(respData, textStatus , xhr);
		},
		error: function(xhr , textStatus, errorThrown) {
            try {
                console.log('xhr', xhr);
                console.log('textStatus',textStatus);
                DUPX.checkDataResponseData(xhr.responseText, textStatus , xhr);
            } catch(err) {
                var status  = "<b>Server Code:</b> "	+ xhr.status		+ "<br/>";
                status += "<b>Status:</b> "			+ xhr.statusText	+ "<br/>";
                status += "<b>Response:</b> "		+ xhr.responseText  + "<hr/>";
                status += "Ajax response error<br>";
                $('#ajaxerr-data .content').html(status);
                DUPX.hideProgressBar();
            }
		}
	};

    if (chunk === false) {
        params.beforeSend = function() {
			DUPX.showProgressBar();
			$('#s3-input-form').hide();
			$('#s3-result-form').show();
		};
    }

    $.ajax(params);
};

var searchReplaceIndex = 1;

/**
 * Adds a search and replace line         */
DUPX.addSearchReplace = function()
{
	$("#search-replace-table").append("<tr valign='top' id='search-" + searchReplaceIndex + "'>" +
		"<td style='width:80px;padding-top:20px'>Search:</td>" +
		"<td style='padding-top:20px'>" +
			"<input class=\"w95\" type='text' name='search[]' style='margin-right:5px' />" +
			"<a href='javascript:DUPX.removeSearchReplace(" + searchReplaceIndex + ")'><i class='fa fa-minus-circle'></i></a>" +
		"</td>" +
	  "</tr>" +
			  "<tr valign='top' id='replace-" + searchReplaceIndex + "'>" +
		"<td>Replace:</td>" +
		"<td>" +
			"<input class=\"w95\" type='text' name='replace[]' />" +
		"</td>" +
	  "</tr> ");

	searchReplaceIndex++;
};

/**
 * Removes a search and replace line      */
DUPX.removeSearchReplace = function(index)
{
	$("#search-" + index).remove();
	$("#replace-" + index).remove();
};

/**
 * Go back on AJAX result view */
DUPX.hideErrorResult2 = function()
{
	$('#s3-result-form').hide();
	$('#s3-input-form').show(200);
};

DUPX.showHideRevisionNo = function() {
	var cont = $('#wp_post_revisions_no_cont');
	if ('true' == $('#wp_post_revisions').val()) {
		cont.show();
	} else {
		cont.hide();
	}
};

//DOCUMENT LOAD
$(document).ready(function() {
	$("#tabs").tabs();
	DUPX.showHideRevisionNo();
	$('#wp_post_revisions').change(DUPX.showHideRevisionNo);
	$("*[data-type='toggle']").click(DUPX.toggleClick);
	$("#" + wpPwdInputId).passStrength({
			shortPass: 		"top_shortPass",
			badPass:		"top_badPass",
			goodPass:		"top_goodPass",
			strongPass:		"top_strongPass",
			baseStyle:		"top_testresult",
			userid:			"#" + wpUserNameInputId,
			messageloc:		1
	});

    $('input[type=radio][name=replace_mode]').change(function() {
        if (this.value == 'mapping') {
            $("#subsite-map-container").show();
            $("#new-url-container").hide();
        }
        else if (this.value == 'legacy') {
            $("#new-url-container").show();
            $("#subsite-map-container").hide();
        }
    });

    // Sync new urls link
    var inputs_new_urls = $(".sync_url_new");
    inputs_new_urls.keyup(function() {
          inputs_new_urls.val($(this).val());
    });
});
</script>
