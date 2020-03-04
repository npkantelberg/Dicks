<?php
defined("DUPXABSPATH") or die("");

/** IDE HELPERS */
/* @var $GLOBALS['DUPX_AC'] DUPX_ArchiveConfig */

require_once(DUPX_INIT.'/classes/config/class.archive.config.php');
require_once(DUPX_INIT.'/views/classes/class.view.s2.php');

$paramsManager = DUPX_Paramas_Manager::getInstance();
?>

<form id='s2-input-form' method="post" class="content-form"  autocomplete="off" data-parsley-validate="true" data-parsley-excluded="input[type=hidden], [disabled], :hidden">
    <?php
    DUPX_U_Html::getHeaderMain('Step <span class="step">2</span> of 4: Install Database <div class="sub-header">This step will install the database from the archive.</div>');
    DUPX_View_S2::modeButtons();
    ?>
    <!--  POST PARAMS -->
    <div class="dupx-debug">
        <i>Step 2 - Page Load</i>
        <?php
        $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_CTRL_ACTION, 'ctrl-step2');
        $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_VIEW, 'step2');
        $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_DB_VIEW_MODE);
        ?>
        <input type="hidden" name="<?php echo DUPX_Security::VIEW_TOKEN; ?>" value="<?php echo DUPX_CSRF::generate('step2'); ?>">
        <input type="hidden" name="<?php echo DUPX_Security::CTRL_TOKEN; ?>" value="<?php echo DUPX_CSRF::generate('ctrl-step2'); ?>"> 
        <input type="hidden" name="dbchunk_retry" id="dbchunk_retry" value="0" />
        <textarea name="dbtest-response" id="debug-dbtest-json"></textarea>
    </div>

    <!-- DATABASE CHECKS -->
    <?php require_once('view.s2.dbtest.php'); ?>

    <!-- BASIC TAB -->
    <div class="s2-basic-pane">
        <?php require_once('view.s2.basic.php'); ?>
    </div>


    <?php
    if (!DUPX_View_S2::skipDbTest()) {
        ?>
        <!-- CPANEL TAB -->
        <div class="s2-cpnl-pane">
            <?php require_once('view.s2.cpnl.php'); ?>
        </div>

        <?php
    }
    ?>

    <!-- BASIC VALIDATION -->
    <div class="s2-basic-pane">
        <?php DUPX_View_S2::basicValitadion(); ?>
    </div>

    <?php
    if (!DUPX_View_S2::skipDbTest()) {
        ?>
        <!-- CPANEL TAB -->
        <div class="s2-cpnl-pane">
            <?php DUPX_View_S2::cpanelValidation(); ?>
        </div>
        <?php
    }

    DUPX_View_S2::basicOptions();
    ?>

</form>


<!-- CONFIRM DIALOG -->
<div id="dialog-confirm" title="Install Confirmation" style="display:none">
    <div style="padding: 10px 0 25px 0">
        <b>Run installer with these settings?</b>
    </div>

    <b>Database Settings:</b><br/>
    <table style="margin-left:20px">
        <tr>
            <td><b>Server:</b></td>
            <td><i id="dlg-dbhost"></i></td>
        </tr>
        <tr>
            <td><b>Name:</b></td>
            <td><i id="dlg-dbname"></i></td>
        </tr>
        <tr>
            <td><b>User:</b></td>
            <td><i id="dlg-dbuser"></i></td>
        </tr>
    </table>
    <br/><br/>

    <small><i class="fa fa-exclamation-triangle"></i> WARNING: Be sure these database parameters are correct! Entering the wrong information WILL overwrite an existing database.
        Make sure to have backups of all your data before proceeding.</small><br/>
</div>


<!-- =========================================
VIEW: STEP 2 - AJAX RESULT
Auto Posts to view.step3.php  -->
<form id='s2-result-form' method="post" class="content-form" style="display:none" autocomplete="off">
    <?php DUPX_U_Html::getHeaderMain('Step <span class="step">2</span> of 4: Install Database <div class="sub-header">This step will install the database from the archive.</div>'); ?>
    <!--  POST PARAMS -->
    <div class="dupx-debug">
        <i>Step 2 - AJAX Response</i>
        <?php
        $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_VIEW, 'step3');
        ?>
        <input type="hidden" name="<?php echo DUPX_Security::VIEW_TOKEN; ?>" value="<?php echo DUPX_CSRF::generate('step3'); ?>">
        <input type="hidden" name="json"   id="ajax-json" />
        <input type='submit' value='manual submit'>
    </div>

    <!--  PROGRESS BAR -->
    <div id="progress-area">
        <div style="width:500px; margin:auto">
            <div class="progress-text"><i class="fas fa-circle-notch fa-spin"></i> Installing Database <span id="progress-pct"></span></div>
            <div id="progress-bar"></div>
            <h3> Please Wait...</h3><br/><br/>
            <i>Keep this window open during the creation process.</i><br/>
            <i>This can take several minutes.</i>
        </div>
    </div>

    <!--  AJAX SYSTEM ERROR -->
    <div id="ajaxerr-area" style="display:none">
        <p>Please try again an issue has occurred.</p>
        <div style="padding: 0px 10px 10px 0px;">
            <div id="ajaxerr-data">An unknown issue has occurred with the file and database setup process.  Please see the <?php DUPX_View_Funcs::installerLogLink(); ?> file for more details.</div>
            <div style="text-align:center; margin:10px auto 0px auto">
                <input type="button" onclick="$('#s2-result-form').hide();  $('#s2-input-form').show(200); $('#dbchunk_retry').val(0);" value="&laquo; Try Again" class="default-btn" /><br/><br/>
                <i style='font-size:11px'>See online help for more details at <a href='https://snapcreek.com/' target='_blank'>snapcreek.com</a></i>
            </div>
        </div>
    </div>
</form>

<script>

    var dbViewModeInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_DB_VIEW_MODE)); ?>;
    var dbchunkInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_DB_CHUNK)); ?>;
    var dbHostInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_DB_HOST)); ?>;
    var dbNameInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_DB_NAME)); ?>;
    var dbUserInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_DB_USER)); ?>;
    var dbPassInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_DB_PASS)); ?>;

    var cpnlHostInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_CPNL_HOST)); ?>;
    var cpnlUserInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_CPNL_USER)); ?>;
    var cpnlPassInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_CPNL_PASS)); ?>;
    var cpnlDbHostInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_CPNL_DB_HOST)); ?>;
    var cpnlDbNameInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_CPNL_DB_NAME_TXT)); ?>;
    var cpnlDbUserInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_CPNL_DB_USER_TXT)); ?>;
    var cpnlDbPassInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_CPNL_DB_PASS)); ?>;


    var CPNL_TOKEN;
    var CPNL_DBINFO = null;
    var CPNL_DBUSERS = null;
    var CPNL_CONNECTED = false;
    var CPNL_PREFIX = false;

    /**
     *  Toggles the cpanel Login area  */
    DUPX.togglePanels = function (pane)
    {
        $('.s2-basic-pane, .s2-cpnl-pane').hide();
        $('#s2-basic-btn, #s2-cpnl-btn').removeClass('active in-active');
        var cpnlSupport = true;

        if (pane == 'basic') {
            $('#' + dbViewModeInputId).val('basic');
            $('.s2-basic-pane').show();
            $('#s2-basic-btn').addClass('active');
            $('#s2-cpnl-btn').addClass('in-active');
            if (!cpnlSupport) {
                $('#s2-opts-hdr-basic, div.footer-buttons').show();
            }
        } else {
            $('#' + dbViewModeInputId).val('cpnl');
            $('.s2-cpnl-pane').show();
            $('#s2-cpnl-btn').addClass('active');
            $('#s2-basic-btn').addClass('in-active');
            if (!cpnlSupport) {
                $('#s2-opts-hdr-cpnl, div.footer-buttons').hide();
            }
        }
    }


    /**
     * Open an in-line confirm dialog*/
    DUPX.confirmDeployment = function ()
    {
<?php if ($paramsManager->isHtmlInput(DUPX_Paramas_Manager::PARAM_DB_HOST)) { ?>
            var dbhost = $("#" + dbHostInputId).val();
<?php } else { ?>
            var dbhost = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_HOST)); ?>;
<?php } ?>
<?php if ($paramsManager->isHtmlInput(DUPX_Paramas_Manager::PARAM_DB_NAME)) { ?>
            var dbname = $("#" + dbNameInputId).val();
<?php } else { ?>
            var dbname = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_NAME)); ?>;
<?php } ?>
<?php if ($paramsManager->isHtmlInput(DUPX_Paramas_Manager::PARAM_DB_USER)) { ?>
            var dbuser = $("#" + dbUserInputId).val();
<?php } else { ?>
            var dbuser = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_USER)); ?>;
<?php } ?>

        if ($('#' + dbViewModeInputId).val() == 'cpnl') {
            DUPX.cpnlSetResults();

            dbhost = $("#" + cpnlDbHostInputId).val();
            dbname = $("#cpnl-dbname-result").val();
            dbuser = $("#cpnl-dbuser-result").val();
        }

        var $formInput = $('#s2-input-form');
        $formInput.parsley().validate();
        if (!$formInput.parsley().isValid()) {
            return;
        }

        $("#dialog-confirm").dialog({
            resizable: false,
            height: "auto",
            width: 550,
            modal: true,
            position: {my: 'top', at: 'top+150'},
            buttons: {
                "OK": function () {
                    DUPX.runDeployment();
                    $(this).dialog("close");
                },
                Cancel: function () {
                    $(this).dialog("close");
                }
            }
        });

        $('#dlg-dbhost').html(dbhost);
        $('#dlg-dbname').html(dbname);
        $('#dlg-dbuser').html(dbuser);
    };

    /**
     * Performs Ajax post to extract files and create db
     * Timeout (10000000 = 166 minutes) */
    DUPX.runDeployment = function (data)
    {
        var $formInput = $('#s2-input-form');
        var $formResult = $('#s2-result-form');
        var local_data = data;

        var dbchunk = $("#" + dbchunkInputId).is(':checked');

        if (local_data === undefined && dbchunk == true) {
            local_data = {
                continue_chunking: dbchunk == true,
                pos: 0,
                pass: 0,
                first_chunk: 1,
                progress: 0,
                delimiter: ';'
            };
        } else if (!dbchunk) {
            local_data = {};
        }

        var new_data = (local_data !== undefined) ? '&' + $.param(local_data) : '';
        var loadProgress = function () {
            DUPX.showProgressBar();
            $formInput.hide();
            $formResult.show();
        };

        $.ajax({
            type: "POST",
            timeout: dbchunk ? 600000 : 10000000, // in milliseconds
            url: window.location.href,
            data: $formInput.serialize() + new_data,
            beforeSend: function () {
                if (!dbchunk) {
                    loadProgress();
                } else if (local_data.first_chunk !== undefined) {
                    loadProgress();
                }
            },
            success: function (respData, textStatus, xhr) {
                try {
                    var data = DUPX.parseJSON(respData);
                } catch (err) {
                    console.error(err);
                    console.error('JSON parse failed for response data: ' + respData);
                    var dbchunk_retry = parseInt($('#dbchunk_retry').val());
                    if (dbchunk && dbchunk_retry < <?php echo $GLOBALS['DB_INSTALL_MULTI_THREADED_MAX_RETRIES']; ?>) {
                        var status = "Server Code: " + xhr.status + "\n";
                        status += "Status: " + xhr.statusText + "\n";
                        status += "Response: " + xhr.responseText;
                        console.error(status);

                        dbchunk_retry = dbchunk_retry + 1;
                        console.log('Incrementing db chunk install retrying to: ' + dbchunk_retry);
                        $('#dbchunk_retry').val(dbchunk_retry);
                        DUPX.runDeployment();
                    } else {
                        var status = "<b>Server Code:</b> " + xhr.status + "<br/>";
                        status += "<b>Status:</b> " + xhr.statusText + "<br/>";
                        status += "<b>Response:</b> " + xhr.responseText + "<hr/>";

                        if (textStatus && textStatus.toLowerCase() == "timeout" || textStatus.toLowerCase() == "service unavailable") {
                            status += "<b>Recommendation:</b><br/>";
                            status += "To resolve this problem please follow the instructions showing <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-100-q'>in the FAQ</a>.<br/><br/>";
                        } else if ((xhr.status == 403) || (xhr.status == 500)) {
                            status += "<b>Recommendation</b><br/>";
                            status += "See <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-120-q'>this section</a> of the Technical FAQ for possible resolutions.<br/><br/>"
                        } else if (xhr.status == 0) {
                            status += "<b>Recommendation</b><br/>";
                            status += "This may be a server timeout and performing a 'Manual Extract' install can avoid timeouts. See <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/?reload=1#faq-installer-015-q'>this section</a> of the FAQ for a description of how to do that.<br/><br/>"
                        } else {
                            status += "<b>Additional Troubleshooting Tips:</b><br/> ";
                            status += "&raquo; <a target='_blank' href='https://snapcreek.com/duplicator/docs/'>Help Resources</a><br/>";
                            status += "&raquo; <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/'>Technical FAQ</a>";
                        }
                        $('#ajaxerr-data').html(status);
                        DUPX.hideProgressBar();
                    }
                    return false;
                }
                if (local_data.continue_chunking) {
                    var is_error = false;
                    if ('undefined' === typeof (data)) {
                        console.error('Undefined AJAX response');
                        is_error = true;
                    } else if (!data) {
                        console.error('Ajax response is false');
                        is_error = true;
                    } else if (data.is_error) {
                        console.error('data.is_error attribute is true');
                        console.error(data.error_msg);
                        is_error = true;
                    }
                    if (is_error) {
                        var dbchunk_retry = parseInt($('#dbchunk_retry').val());
                        if (dbchunk_retry > <?php echo $GLOBALS['DB_INSTALL_MULTI_THREADED_MAX_RETRIES']; ?>) {
                            $('#ajaxerr-data').html('There was an error while installing Database with multi-threaded requests to chunk SQL file');
                            DUPX.hideProgressBar();
                        } else {
                            dbchunk_retry = dbchunk_retry + 1;
                            console.log('Incrementing db chunk install retrying to: ' + dbchunk_retry);
                            $('#dbchunk_retry').val(dbchunk_retry);
                            DUPX.runDeployment();
                        }
                    } else {
                        // console.log(data);
                        DUPX.runDeployment(data);
                        $('#dbchunk_retry').val(0);
                        if (data.hasOwnProperty('progress')) {
                            $('span#progress-pct').html(data.progress + '%');
                        } else {
                            $('#progress-pct').empty();
                        }
                    }

                    return;
                }
                if (typeof (data) != 'undefined' && data.pass == 1) {
                    if ($('#' + dbViewModeInputId).val() == 'basic') {
                    } else {
                        $("#ajax-dbaction").val($("#cpnl-dbaction").val());
                    }

                    //Advanced Opts
                    $("#ajax-json").val(escape(JSON.stringify(data)));

<?php if (!$GLOBALS['DUPX_DEBUG']) : ?>
                        setTimeout(function () {
                            $formResult.submit();
                        }, 1000);
<?php endif; ?>
                    $('#progress-area').fadeOut(700);
                } else {
                    if (data.error_message) {
                        $('#ajaxerr-data').html(data.error_message);
                    }
                    DUPX.hideProgressBar();
                }
            },
            error: function (xhr, textStatus) {
                var dbchunk_retry = parseInt($('#dbchunk_retry').val());
                if (dbchunk && dbchunk_retry < <?php echo $GLOBALS['DB_INSTALL_MULTI_THREADED_MAX_RETRIES']; ?>) {
                    var status = "Server Code: " + xhr.status + "\n";
                    status += "Status: " + xhr.statusText + "\n";
                    status += "Response: " + xhr.responseText;
                    console.error(status);

                    dbchunk_retry = dbchunk_retry + 1;
                    console.log('Incrementing db chunk install retrying to: ' + dbchunk_retry);
                    $('#dbchunk_retry').val(dbchunk_retry);
                    DUPX.runDeployment();
                } else {
                    var status = "<b>Server Code:</b> " + xhr.status + "<br/>";
                    status += "<b>Status:</b> " + xhr.statusText + "<br/>";
                    status += "<b>Response:</b> " + xhr.responseText + "<hr/>";

                    if (textStatus && textStatus.toLowerCase() == "timeout" || textStatus.toLowerCase() == "service unavailable") {
                        status += "<b>Recommendation:</b><br/>";
                        status += "To resolve this problem please follow the instructions showing <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-100-q'>in the FAQ</a>.<br/><br/>";
                    } else if ((xhr.status == 403) || (xhr.status == 500)) {
                        status += "<b>Recommendation</b><br/>";
                        status += "See <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-120-q'>this section</a> of the Technical FAQ for possible resolutions.<br/><br/>"
                    } else if (xhr.status == 0) {
                        status += "<b>Recommendation</b><br/>";
                        status += "This may be a server timeout and performing a 'Manual Extract' install can avoid timeouts. See <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/?reload=1#faq-installer-015-q'>this section</a> of the FAQ for a description of how to do that.<br/><br/>"
                    } else {
                        status += "<b>Additional Troubleshooting Tips:</b><br/> ";
                        status += "&raquo; <a target='_blank' href='https://snapcreek.com/duplicator/docs/'>Help Resources</a><br/>";
                        status += "&raquo; <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/'>Technical FAQ</a>";
                    }
                    $('#ajaxerr-data').html(status);
                    DUPX.hideProgressBar();
                }
            }
        });
    };

    //DOCUMENT LOAD
    $(document).ready(function () {
        //Init
<?php echo ($GLOBALS['DUPX_AC']->cpnl_enable) ? 'DUPX.togglePanels("cpanel");' : 'DUPX.togglePanels("basic");'; ?>
        $("*[data-type='toggle']").click(DUPX.toggleClick);
    });
</script>
