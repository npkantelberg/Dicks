<?php
defined("DUPXABSPATH") or die("");

require_once(DUPX_INIT.'/classes/config/class.archive.config.php');
require_once(DUPX_INIT.'/views/classes/class.view.s1.php');

$paramsManager = DUPX_Paramas_Manager::getInstance();
$archiveConfig = DUPX_ArchiveConfig::getInstance();

if (DUPX_Conf_Utils::archiveExists()) {
    $arcCheck = 'Pass';
} else {
    if (DUPX_Conf_Utils::isConfArkPresent()) {
        $arcCheck = 'Warn';
    } else {
        $arcCheck = 'Fail';
    }
}

$ret_is_dir_writable  = DUPX_Server::is_dir_writable($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_PATH_NEW));
$datetime1            = $GLOBALS['DUPX_AC']->created;
$datetime2            = date("Y-m-d H:i:s");
$fulldays             = round(abs(strtotime($datetime1) - strtotime($datetime2)) / 86400);
$max_time_zero        = ($GLOBALS['DUPX_ENFORCE_PHP_INI']) ? false : @set_time_limit(0);
$max_time_size        = 314572800;  //300MB
$max_time_ini         = ini_get('max_execution_time');
$max_time_warn        = (is_numeric($max_time_ini) && $max_time_ini < 31 && $max_time_ini > 0) && DUPX_Conf_Utils::archiveSize() > $max_time_size;
$parent_has_wordfence = file_exists($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_PATH_NEW).'/../wp-content/plugins/wordfence/wordfence.php');

//REQUIRMENTS
$req     = DUPX_View_S1::getReq($ret_is_dir_writable);
$all_req = in_array('Fail', $req) ? 'Fail' : 'Pass';

//NOTICES
$notice     = DUPX_View_S1::getNotices();
$all_notice = in_array('Warn', $notice) ? 'Warn' : 'Good';

//SUMMATION
$req_success = ($all_req == 'Pass');
$req_notice  = ($all_notice == 'Good');
$all_success = ($req_success && $req_notice);
$agree_msg   = "To enable this button the checkbox above under the 'Terms & Notices' must be checked.";

DUPX_Log::logTime('VIEW STEP 1 CHECK END', DUPX_Log::LV_DETAILED);
?>
<form id="s1-input-form" method="post" class="content-form" autocomplete="off" >
    <?php DUPX_U_Html::getHeaderMain('Step <span class="step">1</span> of 4: Deployment <div class="sub-header">This step will extract the archive file contents.</div>'); ?>
    <input type="hidden" id="s1-input-dawn-status" name="dawn_status" />
    <?php
    $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_CTRL_ACTION, 'ctrl-step1');
    $paramsManager->getHtmlFormParam(DUPX_Security::CTRL_TOKEN, DUPX_CSRF::generate('ctrl-step1'));
    $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_VIEW, 'step1');
    $paramsManager->getHtmlFormParam(DUPX_Security::VIEW_TOKEN, DUPX_CSRF::generate('step1'));

    DUPX_View_S1::infoTabs(); 
    
    require('parts/validation.php');
    
    DUPX_View_S1::options();
    ?>
    <div id="reload_action" class="no-display" >
        <div class="footer-buttons" >
            <div class="content-left">
            </div>
            <div class="content-right" >
                <button id="reload-btn" type="submit" onclick="DUPX.processReload()" 
                        name="<?php echo DUPX_Paramas_Manager::PARAM_STEP_ACTION; ?>" 
                        value="revalidate" class="default-btn">
                    Revalidate <i class="fa fa-redo"></i>
                </button>
            </div>
        </div>
    </div>
    <div id="next_action" >
        <?php
        $req_counts               = array_count_values($req);
        $is_only_permission_issue = (isset($req_counts['Fail']) && 1 == $req_counts['Fail'] && 'Fail' == $req[10] && 'Fail' == $all_req && 'Fail' != $arcCheck);

        if (!$req_success || $arcCheck == 'Fail') :
            ?>
            <div class="s1-err-msg" <?php if ($is_only_permission_issue) { ?>style="padding: 0 0 20px 0;"<?php } ?>>
                <i>
                    This installation will not be able to proceed until the archive and validation sections both pass. Please adjust your servers settings or contact your
                    server administrator, hosting provider or visit the resources below for additional help.
                </i>
                <div style="padding:10px">
                    &raquo; <a href="https://snapcreek.com/duplicator/docs/faqs-tech/" target="_blank">Technical FAQs</a> <br/>
                    &raquo; <a href="https://snapcreek.com/support/docs/" target="_blank">Online Documentation</a> <br/>
                </div>
            </div>
            <?php
            $is_next_btn_html = false;
        else :
            $is_next_btn_html = true;
        endif;

        if ($is_only_permission_issue) {
            ?>
            <div class="s1-accept-check">
                <input id="accept-perm-error" name="accept-perm-error" type="checkbox" onclick="DUPX.showHideNextBtn(this)" />
                <label for="accept-perm-error" style="color: #AF0000;">I would like to proceed with my own risk despite the permission error</label><br/>
            </div>
            <?php
        }

        if ($is_next_btn_html || $is_only_permission_issue) {
            ?>
            <div class="footer-buttons" <?php if ($is_only_permission_issue) { ?>style="display: none;"<?php } ?>>
                <div class="content-left">
                    <?php DUPX_View_S1::acceptAndContinue(); ?>
                </div>
                <div class="content-right" >
                    <button id="s1-deploy-btn" type="button" title="<?php echo $agree_msg; ?>" onclick="DUPX.processNext()"  class="default-btn"> Next <i class="fa fa-caret-right"></i> </button>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</form>


<!-- =========================================
VIEW: STEP 1 - AJAX RESULT
Auto Posts to view.step2.php
========================================= -->

<form id='s1-result-form' method="post" class="content-form" style="display:none" autocomplete="off">
    <?php DUPX_U_Html::getHeaderMain('Step <span class="step">1</span> of 4: Deployment <div class="sub-header">This step will extract the archive file contents.</div>'); ?>
    <!--  POST PARAMS -->
    <div class="dupx-debug">
        <i>Step 1 - AJAX Response</i>
        <input type="hidden" name="view" value="step2" />
        <input type="hidden" name="<?php echo DUPX_Security::VIEW_TOKEN; ?>" value="<?php echo DUPX_U::esc_attr(DUPX_CSRF::generate('step2')); ?>">
        <input type="hidden" name="json" id="ajax-json" />
        <textarea id='ajax-json-debug' name='json_debug_view'></textarea>
        <input type='submit' value='manual submit'>
    </div>

    <!--  PROGRESS BAR -->
    <div id="progress-area">
        <div style="width:500px; margin:auto">
            <div class="progress-text"><i class="fas fa-circle-notch fa-spin"></i> Extracting Archive Files<span id="progress-pct"></span></div>
            <div id="secondary-progress-text"></div>
            <div id="progress-notice"></div>
            <div id="progress-bar"></div>
            <h3> Please Wait...</h3><br/><br/>
            <i>Keep this window open during the extraction process.</i><br/>
            <i>This can take several minutes.</i>
        </div>
    </div>

    <!--  AJAX SYSTEM ERROR -->
    <div id="ajaxerr-area" style="display:none">
        <p>Please try again an issue has occurred.</p>
        <div style="padding: 0px 10px 10px 0px;">
            <div id="ajaxerr-data">An unknown issue has occurred with the file and database setup process.  Please see the <?php DUPX_View_Funcs::installerLogLink(); ?> file for more details.</div>
            <div style="text-align:center; margin:10px auto 0px auto">
                <input type="button" class="default-btn" onclick="DUPX.hideErrorResult()" value="&laquo; Try Again" /><br/><br/>
                <i style='font-size:11px'>See online help for more details at <a href='https://snapcreek.com/ticket' target='_blank'>snapcreek.com</a></i>
            </div>
        </div>
    </div>
</form>
<?php DUPX_Log::logTime('VIEW STEP 1 SCRIPTS START', DUPX_Log::LV_DETAILED); ?>
<script>
    var ctrlActionInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_CTRL_ACTION)); ?>;
    var ctrlTokenInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Security::CTRL_TOKEN)); ?>;
   
    var newUrlInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_URL_NEW)); ?>;
    var newPathInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_PATH_NEW)); ?>;
   
    var exeSafeModeInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_SAFE_MODE)); ?>; 
    var htConfigInputId =  <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_HTACCESS_CONFIG)); ?>;
    var htConfigWrapperId =  <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormWrapperId(DUPX_Paramas_Manager::PARAM_HTACCESS_CONFIG)); ?>;
    var otConfigInputId =  <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_OTHER_CONFIG)); ?>;
    var otConfigWrapperId =  <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormWrapperId(DUPX_Paramas_Manager::PARAM_OTHER_CONFIG)); ?>;
    var clientSideKickoffInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_CLIENT_KICKOFF)); ?>; 
    var archiveEngineInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_ARCHIVE_ENGINE)); ?>; 
    var removeRedundantWrapperId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormWrapperId(DUPX_Paramas_Manager::PARAM_REMOVE_RENDUNDANT)); ?>; 
        
    DUPX.toggleSetupType = function ()
    {
        var val = $("input:radio[name='setup_type']:checked").val();
        $('div.s1-setup-type-sub').hide();
        $('#s1-setup-type-sub-' + val).show(200);
    };

    DUPX.getManaualArchiveOpt = function ()
    {
        $("html, body").animate({scrollTop: $(document).height()}, 1500);
        $("div[data-target='#s1-area-adv-opts']").find('i.fa').removeClass('fa-plus-square').addClass('fa-minus-square');
        $('#s1-area-adv-opts').show(1000);
        $('#' + archiveEngineInputId).val('manual').focus();
    };

    DUPX.startExtraction = function()
    {
        var isManualExtraction = ($('#' + archiveEngineInputId).val() == '<?php echo DUP_PRO_Extraction::ENGINE_MANUAL; ?>');
        var zipEnabled = <?php echo DupProSnapLibStringU::boolToString($archiveConfig->isZipArchive()); ?>;
        var chunkingEnabled  = ($('#' + archiveEngineInputId).val() == '<?php echo DUP_PRO_Extraction::ENGINE_ZIP_CHUNK; ?>');

        $("#operation-text").text("Extracting Archive Files");

        if (zipEnabled || isManualExtraction) {
            if(chunkingEnabled){
                DUPX.runChunkedExtraction(undefined);
            } else {
                DUPX.runStandardExtraction();
            }
        } else {
            DUPX.kickOffDupArchiveExtract();
        }
    }
    
    DUPX.processReload = function ()
    {
        $('#' + ctrlActionInputId).val('ctrl-step0');
        $('#' + ctrlTokenInputId).val(<?php echo DupProSnapJsonU::wp_json_encode(DUPX_CSRF::generate('ctrl-step0')); ?>);
        return true;
    };
 
    DUPX.processNext = function ()
    {
        // @todo temporary solution waiting to implement a frontend validation of the parameters 
        var continueExtraction = true;
        var message = '';
        $('.param-form-type-text input').each(function () {
            var inputVal = $(this).val();
            
            if (inputVal.length === 0) {
                var paramName = $(this).closest('.param-wrapper').find('.main-label').text();
                message = message + "The param " + paramName + " can't be empty\n";
                continueExtraction = false;
            }
        });
        if (continueExtraction) {
            DUPX.startExtraction();
        } else {
            alert(message);
            return false;
        }
    };

    DUPX.updateProgressPercent = function (percent)
    {
        var percentString = '';
        if (percent > 0) {
            percentString = ' ' + percent + '%';
        }
        $("#progress-pct").text(percentString);
    };

    DUPX.updateDupArchiveProgress = function(itemIndex, totalItems)
    {
        itemIndex++;
        var itemIndexString		= DUPX.Util.formatBytes(itemIndex);  //itemIndex.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        var totalItemsString	= DUPX.Util.formatBytes(totalItems); //totalItems.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        var s = "Bytes processed: " + itemIndexString + " of " + totalItemsString;
        $("#secondary-progress-text").text(s);
    }

    DUPX.updateZipArchiveProgress = function(itemIndex, totalItems)
    {
        itemIndex++;
        var itemIndexString		= itemIndex.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        var totalItemsString	= totalItems.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        var s =  "Files processed: " + itemIndexString + " of " + totalItemsString;
        $("#secondary-progress-text").text(s);
    }

    DUPX.clearDupArchiveStatusTimer = function ()
    {
        if (DUPX.dupArchiveStatusIntervalID != -1) {
            clearInterval(DUPX.dupArchiveStatusIntervalID);
            DUPX.dupArchiveStatusIntervalID = -1;
        }
    };

    DUPX.getCriticalFailureText = function(failures)
    {
        var retVal = null;

        if((failures !== null) && (typeof failures !== 'undefined')) {
            var len = failures.length;

            for(var j = 0; j < len; j++) {
                failure = failures[j];
                if(failure.isCritical) {
                    retVal = failure.description;
                    break;
                }
            }
        }

        return retVal;
    };

    DUPX.DAWSProcessingFailed = function(errorText)
    {
        DUPX.clearDupArchiveStatusTimer();
        $('#ajaxerr-data').html(errorText);
        DUPX.hideProgressBar();
    };

DUPX.handleDAWSProcessingProblem = function(errorText, pingDAWS)
{
	DUPX.DAWS.FailureCount++;

	if(DUPX.DAWS.FailureCount <= DUPX.DAWS.MaxRetries) {
		var callback = DUPX.pingDAWS;

		if(pingDAWS) {
			console.log('!!!PING FAILURE #' + DUPX.DAWS.FailureCount);
		} else {
			console.log('!!!KICKOFF FAILURE #' + DUPX.DAWS.FailureCount);
			callback = DUPX.kickOffDupArchiveExtract;
		}

		DUPX.throttleDelay = 9;	// Equivalent of 'low' server throttling
		console.log('Relaunching in ' + DUPX.DAWS.RetryDelayInMs);
		setTimeout(callback, DUPX.DAWS.RetryDelayInMs);
	}
	else {
		console.log('Too many failures.');
		DUPX.DAWSProcessingFailed(errorText);
	}
};


DUPX.handleDAWSCommunicationProblem = function(xHr, pingDAWS, textStatus, page)
{
	DUPX.DAWS.FailureCount++;

	if(DUPX.DAWS.FailureCount <= DUPX.DAWS.MaxRetries) {

		var callback = DUPX.pingDAWS;

		if(pingDAWS) {
			console.log('!!!PING FAILURE #' + DUPX.DAWS.FailureCount);
		} else {
			console.log('!!!KICKOFF FAILURE #' + DUPX.DAWS.FailureCount);
			callback = DUPX.kickOffDupArchiveExtract;
		}
		console.log(xHr);
		DUPX.throttleDelay = 9;	// Equivalent of 'low' server throttling
		console.log('Relaunching in ' + DUPX.DAWS.RetryDelayInMs);
		setTimeout(callback, DUPX.DAWS.RetryDelayInMs);
	}
	else {
		console.log('Too many failures.');
		DUPX.ajaxCommunicationFailed(xHr, textStatus, page);
	}
};

// Will either query for status or push it to continue the extraction
DUPX.pingDAWS = function ()
{
	console.log('pingDAWS:start');
	var request = <?php echo DupProSnapJsonU::wp_json_encode_pprint(array(
        DUPX_Ctrl_ajax::AJAX_NAME => true,
        DUPX_Ctrl_ajax::ACTION_NAME => DUPX_Ctrl_ajax::ACTION_DAWN,
        DUPX_Ctrl_ajax::TOKEN_NAME => DUPX_Ctrl_ajax::generateToken(DUPX_Ctrl_ajax::ACTION_DAWN)
    )); ?>;
	var isClientSideKickoff = DUPX.isClientSideKickoff();

	if (isClientSideKickoff) {
		console.log('pingDAWS:client side kickoff');
		request.action = "expand";
		request.client_driven = 1;
		request.throttle_delay = DUPX.throttleDelay;
		request.worker_time = DUPX.DAWS.PingWorkerTimeInSec;
	} else {
		console.log('pingDAWS:not client side kickoff');
		request.action = "get_status";
	}

	console.log("pingDAWS:action=" + request.action);
	console.log("daws url=" + DUPX.DAWS.Url);

	$.ajax({
		type: "POST",
		timeout: DUPX.DAWS.PingWorkerTimeInSec * 2000, // Double worker time and convert to ms
		url: DUPX.DAWS.Url,
		data: request,
		success: function (respData, textStatus, xHr) {
            try {
                var data = DUPX.parseJSON(respData);
            } catch(err) {
                console.error(err);
                console.error('JSON parse failed for response data: ' + respData);
                console.log('AJAX error. textStatus=');
                console.log(textStatus);
                DUPX.handleDAWSCommunicationProblem(xHr, true, textStatus, 'ping');
                return false;
            }

			DUPX.DAWS.FailureCount = 0;
			console.log("pingDAWS:AJAX success. Resetting failure count");

			// DATA FIELDS
			// archive_offset, archive_size, failures, file_index, is_done, timestamp
			if (typeof (data) != 'undefined' && data.pass == 1) {

				console.log("pingDAWS:Passed");

				var status = data.status;
				var percent = Math.round((status.archive_offset * 100.0) / status.archive_size);

				console.log("pingDAWS:updating progress percent");
				DUPX.updateProgressPercent(percent);
                DUPX.updateDupArchiveProgress(status.archive_offset, status.archive_size);

				var criticalFailureText = DUPX.getCriticalFailureText(status.failures);

				if(status.failures.length > 0) {
					console.log("pingDAWS:There are failures present. (" + status.failures.length) + ")";
				}

				if (criticalFailureText === null) {
					console.log("pingDAWS:No critical failures");
					if (status.is_done) {

						console.log("pingDAWS:archive has completed");
						if(status.failures.length > 0) {

							console.log(status.failures);
							var errorMessage = "pingDAWS:Problems during extract. These may be non-critical so continue with install.\n------\n";
							var len = status.failures.length;

							for(var j = 0; j < len; j++) {
								failure = status.failures[j];
								errorMessage += failure.subject + ":" + failure.description + "\n";
							}

                            <?php 
                            // @todo temp hack for manage hosting
                            if (!DUPX_Custom_Host_Manager::getInstance()->isManaged()) { ?>
							alert(errorMessage);
                            <?php }
                            ?>
						}

						DUPX.clearDupArchiveStatusTimer();
						console.log("pingDAWS:calling finalizeDupArchiveExtraction");
						DUPX.finalizeDupArchiveExtraction(status);
						console.log("pingDAWS:after finalizeDupArchiveExtraction");

						var dataJSON = JSON.stringify(data);

						// Don't stop for non-critical failures - just display those at the end
						$("#ajax-json").val(escape(dataJSON));

						<?php if (!$GLOBALS['DUPX_DEBUG']) : ?>
						setTimeout(function () {
							$('#s1-result-form').submit();
						}, 500);
						<?php endif; ?>
						$('#progress-area').fadeOut(1000);
						//Failures aren't necessarily fatal - just record them for later display

						$("#ajax-json-debug").val(dataJSON);
					} else if (isClientSideKickoff) {
						console.log('pingDAWS:Archive not completed so continue ping DAWS in 500');
						setTimeout(DUPX.pingDAWS, 500);
					}
				}
				else {
					// If we get a critical failure it means it's something we can't recover from so no purpose in retrying, just fail immediately.
                    console.log("pingDAWS:critical failures present, data:" , data);
					var errorString = 'Error Processing Step 1<br/>';
					errorString += criticalFailureText;
					DUPX.DAWSProcessingFailed(errorString);
				}
			} else {
				var errorString = 'Error Processing Step 1<br/>';
                console.log("pingDAWS: success but data problem, data:" , data);
				errorString += data.error;
				DUPX.handleDAWSProcessingProblem(errorString, true);
			}
		},
		error: function (xHr, textStatus) {
			console.log('AJAX error. textStatus=');
			console.log(textStatus);
			DUPX.handleDAWSCommunicationProblem(xHr, true, textStatus, 'ping');
		}
	});
};


DUPX.isClientSideKickoff = function()
{
	return $('#' + clientSideKickoffInputId).is(':checked');
};

DUPX.kickOffDupArchiveExtract = function ()
{
	console.log('kickOffDupArchiveExtract:start');
	var formObj = $('#s1-input-form');
	var isClientSideKickoff = DUPX.isClientSideKickoff();

	var request = <?php echo DupProSnapJsonU::wp_json_encode_pprint(array(
        DUPX_Ctrl_ajax::AJAX_NAME => true,
        DUPX_Ctrl_ajax::ACTION_NAME => DUPX_Ctrl_ajax::ACTION_DAWN,
        DUPX_Ctrl_ajax::TOKEN_NAME => DUPX_Ctrl_ajax::generateToken(DUPX_Ctrl_ajax::ACTION_DAWN)
    )); ?>;
            
	request.action = "start_expand";
	request.archive_filepath = '<?php echo DUPX_Security::getInstance()->getArchivePath(); ?>';
	request.restore_directory = '<?php echo $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_PATH_NEW); ?>';
	request.worker_time = DUPX.DAWS.KickoffWorkerTimeInSec;
	request.client_driven = isClientSideKickoff ? 1 : 0;
	request.throttle_delay = DUPX.throttleDelay;
	request.filtered_directories = <?php echo DupProSnapJsonU::wp_json_encode(array(basename(DUPX_INIT))); ?>;
    
    var formData = DUPX.getFormDataObject(formObj);
    console.log('form data', formData);
    var ajaxParams = $.extend({}, request, formData);

	if (!isClientSideKickoff) {
		console.log('kickOffDupArchiveExtract:Setting timer');
		// If server is driving things we need to poll the status
		DUPX.dupArchiveStatusIntervalID = setInterval(DUPX.pingDAWS, DUPX.DAWS.StatusPeriodInMS);
	}
	else {
		console.log('kickOffDupArchiveExtract:client side kickoff');
	}

	console.log("daws url=" + DUPX.DAWS.Url);

	$.ajax({
		type: "POST",
		timeout: DUPX.DAWS.KickoffWorkerTimeInSec * 2000,  // Double worker time and convert to ms
		url: DUPX.DAWS.Url,
		data: ajaxParams,
		beforeSend: function () {
			DUPX.showProgressBar();
			formObj.hide();
			$('#s1-result-form').show();
			DUPX.updateProgressPercent(0);
		},
		success: function (respData, textStatus, xHr) {
            try {
                var data = DUPX.parseJSON(respData);
            } catch(err) {
                console.error(err);
                console.error('JSON parse failed for response data: ' + respData);
                console.log('kickOffDupArchiveExtract:AJAX error. textStatus=', textStatus);
			    DUPX.handleDAWSCommunicationProblem(xHr, false, textStatus);
                return false;
            }

			console.log('kickOffDupArchiveExtract:success');
			if (typeof (data) != 'undefined' && data.pass == 1) {

				var criticalFailureText = DUPX.getCriticalFailureText(status.failures);

				if (criticalFailureText === null) {

					var dataJSON = JSON.stringify(data);

					//RSR TODO:Need to check only for FATAL errors right now - have similar failure check as in pingdaws
					DUPX.DAWS.FailureCount = 0;
					console.log("kickOffDupArchiveExtract:Resetting failure count");

					$("#ajax-json-debug").val(dataJSON);
					if (typeof (data) != 'undefined' && data.pass == 1) {

						if (isClientSideKickoff) {
							console.log('kickOffDupArchiveExtract:Initial ping DAWS in 500');
							setTimeout(DUPX.pingDAWS, 500);
						}

					} else {
                        console.log("kickOffDupArchiveExtract: success but data problem, data:" , data);
						$('#ajaxerr-data').html('Error Processing Step 1');
						DUPX.hideProgressBar();
					}
				} else {
					// If we get a critical failure it means it's something we can't recover from so no purpose in retrying, just fail immediately.
                    console.log("kickOffDupArchiveExtract: success but data problem, data:" , data);
					var errorString = 'kickOffDupArchiveExtract:Error Processing Step 1<br/>';
					errorString += criticalFailureText;
					DUPX.DAWSProcessingFailed(errorString);
				}
			} else {
                console.log("kickOffDupArchiveExtract: success but data problem, data:" , data);
				var errorString = 'kickOffDupArchiveExtract:Error Processing Step 1<br/>';
				errorString += data.error;
				DUPX.handleDAWSProcessingProblem(errorString, false);
			}
		},
		error: function (xHr, textStatus) {

			console.log('kickOffDupArchiveExtract:AJAX error. textStatus=', textStatus);
			DUPX.handleDAWSCommunicationProblem(xHr, false, textStatus);
		}
	});
};

DUPX.finalizeDupArchiveExtraction = function(dawsStatus)
{
	console.log("finalizeDupArchiveExtraction:start");
	var formObj = $('#s1-input-form');
	$("#s1-input-dawn-status").val(JSON.stringify(dawsStatus));
	console.log("finalizeDupArchiveExtraction:after stringify dawsstatus");
	var formData = formObj.serialize();

	$.ajax({
		type: "POST",
		timeout: 30000,
		url: window.location.href,
		data: formData,
		beforeSend: function () {

		},
		success: function (respData, textStatus, xHr) {
            try {
                var data = DUPX.parseJSON(respData);
            } catch(err) {
                console.error(err);
                console.error('JSON parse failed for response data: ' + respData);
                console.log("finalizeDupArchiveExtraction:error");
                console.log(xHr.statusText);
                console.log(xHr.getAllResponseHeaders());
                console.log(xHr.responseText);
                return false;
            }
			console.log("finalizeDupArchiveExtraction:success");
		},
		error: function (xHr) {
			console.log("finalizeDupArchiveExtraction:error");
			console.log(xHr.statusText);
			console.log(xHr.getAllResponseHeaders());
			console.log(xHr.responseText);
		}
	});
};

/**
 * Performs Ajax post to either do a zip or manual extract and then create db
 */
DUPX.runStandardExtraction = function ()
{
	var formObj = $('#s1-input-form');

	//1800000 = 30 minutes
	//If the extraction takes longer than 30 minutes then user
	//will probably want to do a manual extraction or even FTP
	$.ajax({
		type: "POST",
		timeout: 1800000,
		url: window.location.href,
		data: formObj.serialize(),
		beforeSend: function () {
			DUPX.showProgressBar();
			formObj.hide();
			$('#s1-result-form').show();
		},
		success: function (respData, textStatus, xHr) {
            $("#ajax-json-debug").val(respData);
            var dataJSON = respData;
            try {
                var data = DUPX.parseJSON(respData);
            } catch(err) {
                console.error(err);
                console.error('JSON parse failed for response data: ' + respData);
                DUPX.ajaxCommunicationFailed(xHr, textStatus, 'extract');
                return false;
            }
			if (typeof (data) != 'undefined' && data.pass == 1) {
                $("#ajax-json").val(escape(dataJSON));
                
				<?php if (!$GLOBALS['DUPX_DEBUG']) : ?>
					setTimeout(function () {$('#s1-result-form').submit();}, 500);
				<?php endif; ?>
				$('#progress-area').fadeOut(1000);
			} else {
                console.log('runStandardExtraction: success but data return problem', data);
				$('#ajaxerr-data').html('Error Processing Step 1');
				DUPX.hideProgressBar();
			}
		},
		error: function (xHr, textStatus) {
			DUPX.ajaxCommunicationFailed(xHr, textStatus, 'extract');
		}
	});
};

DUPX.runChunkedExtraction = function (data)
{
    var formObj = $('#s1-input-form');
    var dataToSend;
    var chunkData;

    console.log('runChunkedExtraction called.');

    if(typeof (data) == 'undefined'){
        $("#progress-pct").text("");
        $("#secondary-progress-text").text("");
        chunkData = {
            archive_offset: 0,
            pass: -1
        };
    }else{
        chunkData = data;
    }

    dataToSend = formObj.serialize()+'&'+$.param(chunkData);

    $.ajax({
        type: "POST",
        timeout: 1800000,
        url: window.location.href,
        data: dataToSend,
        beforeSend: function () {
            if(typeof (data) == 'undefined'){
                DUPX.showProgressBar();
                formObj.hide();
                $('#s1-result-form').show();
                DUPX.updateProgressPercent(0);
            }
        },
        success: function (respData, textStatus, xHr) {
            if(typeof (respData) != 'undefined'){
                var dataJSON = respData;
                $("#ajax-json-debug").val(respData);
                try {
                    var data = DUPX.parseJSON(respData);
                } catch(err) {
                    console.error(err);
                    console.error('JSON parse failed for response data: ' + respData);
                    DUPX.ajaxCommunicationFailed(xHr, textStatus, 'extract');
                    return false;
                }
                if (data.pass == 1) {
                    $("#ajax-json").val(escape(dataJSON));
                    <?php if (!$GLOBALS['DUPX_DEBUG']) : ?>
                    setTimeout(function () {
                        $('#s1-result-form').submit();
                    }, 500);
                    <?php endif; ?>
                    $('#progress-area').fadeOut(1000);
                } else if(data.pass == -1){
                    var percent = Math.round((data.archive_offset * 100.0) / data.num_files);
                    $("#progress-notice").html(data.zip_arc_chunk_notice);
                    
                    DUPX.updateProgressPercent(percent);
                    DUPX.updateZipArchiveProgress(data.archive_offset, data.num_files);
                    DUPX.runChunkedExtraction(data);
                } else {
                    console.log('runChunkedExtraction: success but data return problem', data);
                    $('#ajaxerr-data').html('Error Processing Step 1');
                    DUPX.hideProgressBar();
                }
            }
        },
        error: function (xHr, textStatus) {
            DUPX.ajaxCommunicationFailed(xHr, textStatus, 'extract');
        }
    });
};


DUPX.ajaxCommunicationFailed = function (xhr, textStatus, page)
{
	var status = "<b>Server Code:</b> " + xhr.status + "<br/>";
	status += "<b>Status:</b> " + xhr.statusText + "<br/>";
	status += "<b>Response:</b> " + xhr.responseText + "<hr/>";

	if(textStatus && textStatus.toLowerCase() == "timeout" || textStatus.toLowerCase() == "service unavailable") {

		var default_timeout_message = "<b>Recommendation:</b><br/>";
			default_timeout_message += "See <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/?180116102141#faq-trouble-100-q'>this FAQ item</a> for possible resolutions.";
			default_timeout_message += "<hr>";
			default_timeout_message += "<b>Additional Resources...</b><br/>";
			default_timeout_message += "With thousands of different permutations it's difficult to try and debug/diagnose a server. If you're running into timeout issues and need help we suggest you follow these steps:<br/><br/>";
			default_timeout_message += "<ol>";
				default_timeout_message += "<li><strong>Contact Host:</strong> Tell your host that you're running into PHP/Web Server timeout issues and ask them if they have any recommendations</li>";
				default_timeout_message += "<li><strong>Dedicated Help:</strong> If you're in a time-crunch we suggest that you contact <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/?180116150030#faq-resource-030-q'>professional server administrator</a>. A dedicated resource like this will be able to work with you around the clock to the solve the issue much faster than we can in most cases.</li>";
				default_timeout_message += "<li><strong>Consider Upgrading:</strong> If you're on a budget host then you may run into constraints. If you're running a larger or more complex site it might be worth upgrading to a <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/?180116150030#faq-resource-040-q'>managed VPS server</a>. These systems will pretty much give you full control to use the software without constraints and come with excellent support from the hosting company.</li>";
				default_timeout_message += "<li><strong>Contact SnapCreek:</strong> We will try our best to help configure and point users in the right direction, however these types of issues can be time-consuming and can take time from our support staff.</li>";
			default_timeout_message += "</ol>";

		if(page)
		{
			switch(page)
			{
				default:
					status += default_timeout_message;
					break;
				case 'extract':
					status += "<b>Recommendation:</b><br/>";
					status += "See <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-015-q'>this FAQ item</a> for possible resolutions.<br/><br/>";
					break;
				case 'ping':
					status += "<b>Recommendation:</b><br/>";
					status += "See <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/?180116152758#faq-trouble-030-q'>this FAQ item</a> for possible resolutions.<br/><br/>";
					break;
                case 'delete-site':
                    status += "<b>Recommendation:</b><br/>";
					status += "See <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/?180116153643#faq-installer-120-q'>this FAQ item</a> for possible resolutions.<br/><br/>";
					break;
			}
		}
		else
		{
			status += default_timeout_message;
		}

	}
	else if ((xhr.status == 403) || (xhr.status == 500)) {
		status += "<b>Recommendation:</b><br/>";
		status += "See <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-120-q'>this FAQ item</a> for possible resolutions.<br/><br/>"
	} else if ((xhr.status == 0) || (xhr.status == 200)) {
		status += "<b>Recommendation:</b><br/>";
		status += "Possible server timeout! Performing a 'Manual Extraction' can avoid timeouts.";
		status += "See <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-015-q'>this FAQ item</a> for a complete overview.<br/><br/>"
	} else {
		status += "<b>Additional Resources:</b><br/> ";
		status += "&raquo; <a target='_blank' href='https://snapcreek.com/duplicator/docs/'>Help Resources</a><br/>";
		status += "&raquo; <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/'>Technical FAQ</a>";
	}

	$('#ajaxerr-data').html(status);
	DUPX.hideProgressBar();
};

/** Go back on AJAX result view */
DUPX.hideErrorResult = function ()
{
	$('#s1-result-form').hide();
	$('#s1-input-form').show(200);
}

/**
 * show next button */
DUPX.showHideNextBtn = function (evtSrc)
{
    var target = $(".footer-buttons");
    if (evtSrc.checked) {
        target.slideDown();
    } else {
        target.slideUp();
    }
};

DUPX.revalidateOnNewPathUrlChanged = function () {
    $('input.revalidate').each(function () {
        var oldValue = $(this).val();
        $(this).bind("keyup change", function () {
            if ($(this).val() !== oldValue) {
                oldValue = $(this).val();
                $('#next_action').addClass('no-display');
                $('#reload_action').removeClass('no-display'); 
            }
        });
    });
};

/**
 * Accetps Usage Warning */
DUPX.acceptWarning = function ()
{
	if ($("#<?php echo $paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_ACCEPT_TERM_COND); ?>").is(':checked')) {
		$("#s1-deploy-btn").removeAttr("disabled");
		$("#s1-deploy-btn").removeAttr("title");
	} else {
		$("#s1-deploy-btn").attr("disabled", "true");
		$("#s1-deploy-btn").attr("title", "<?php echo $agree_msg; ?>");
	}
};

DUPX.onSafeModeSwitch = function ()
{
    var safeObj = $('#' + exeSafeModeInputId)
    var mode = safeObj ? parseInt(safeObj.val()) : 0;
    var htWr = $('#' + htConfigWrapperId);
    var otWr = $('#' + otConfigWrapperId);

    switch (mode) {
        case 1:
        case 2:
            htWr.find('#' + htConfigInputId + '_0').prop("checked", true);
            htWr.find('input').prop("disabled", true);
            otWr.find('#' + otConfigInputId + '_0').prop("checked", true);
            otWr.find('input').prop("disabled", true);
            break;
        case 0:
        default:
            htWr.find('input').prop("disabled", false);
            otWr.find('input').prop("disabled", false);
            break;
    }
    console.log("mode set to"+mode);
};
//DOCUMENT LOAD
$(document).ready(function ()
{
	DUPX.DAWS = new Object();
	DUPX.DAWS.Url = window.location.href; // + '?is_daws=1&<?php echo DUPX_Security::DAWN_TOKEN; ?>=<?php echo urlencode(DUPX_CSRF::generate('daws'));?>';
	DUPX.DAWS.StatusPeriodInMS = 10000;
	DUPX.DAWS.PingWorkerTimeInSec = 9;
	DUPX.DAWS.KickoffWorkerTimeInSec = 6; // Want the initial progress % to come back quicker

    DUPX.DAWS.MaxRetries = 10;
	DUPX.DAWS.RetryDelayInMs = 8000;

	DUPX.dupArchiveStatusIntervalID = -1;
	DUPX.DAWS.FailureCount = 0;
	DUPX.throttleDelay = 0;

	//INIT Routines
	$("*[data-type='toggle']").click(DUPX.toggleClick);
	$(".tabs").tabs();
	DUPX.acceptWarning();
	DUPX.toggleSetupType();
    
    DUPX.revalidateOnNewPathUrlChanged();

	<?php echo ($arcCheck == 'Fail') ? "$('#s1-area-archive-file-link').trigger('click');" : ""; ?>
	<?php echo (!$all_success) ? "$('#s1-area-sys-setup-link').trigger('click');" : ""; ?>
});
</script>
