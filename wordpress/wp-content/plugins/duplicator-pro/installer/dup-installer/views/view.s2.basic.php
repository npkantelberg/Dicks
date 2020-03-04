<?php
defined("DUPXABSPATH") or die("");
/** IDE HELPERS */
/* @var $GLOBALS['DUPX_AC'] DUPX_ArchiveConfig */
/* @var $state DUPX_InstallerState */

if (DUPX_InstallerState::getInstance()->getMode() === DUPX_InstallerState::MODE_OVR_INSTALL) {
    $ovr_dbhost = DUPX_WPConfig::getValueFromLocalWpConfig('DB_HOST');
    $ovr_dbname = DUPX_WPConfig::getValueFromLocalWpConfig('DB_NAME');
    $ovr_dbuser = DUPX_WPConfig::getValueFromLocalWpConfig('DB_USER');
    $ovr_dbpass = DUPX_WPConfig::getValueFromLocalWpConfig('DB_PASSWORD');
} else {
    $ovr_dbhost = '';
    $ovr_dbname = '';
    $ovr_dbuser = '';
    $ovr_dbpass = '';
}

DUPX_View_S2::basicPanel();
?>
<script>
    /**
     *  Bacic Action Change  */
    var dbActionInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_DB_ACTION)); ?>;
    var dbHostInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_DB_HOST)); ?>;
    var dbNameInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_DB_NAME)); ?>;
    var dbUserInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_DB_USER)); ?>;
    var dbPassInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_DB_PASS)); ?>;

    var dbDbcharsetfbInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_DB_CHARSET_FB)); ?>;
    var dbDbcharsetfbValWrapperId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormWrapperId(DUPX_Paramas_Manager::PARAM_DB_CHARSET_FB_VAL)); ?>;
    var dbDbcharsetfbValInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_DB_CHARSET_FB_VAL)); ?>;
    
    var dbDbcollatefbInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_DB_COLLATE_FB)); ?>;
    var dbDbcollatefbValWrapperId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormWrapperId(DUPX_Paramas_Manager::PARAM_DB_COLLATE_FB_VAL)); ?>;
    var dbDbcollatefbValInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_DB_COLLATE_FB_VAL)); ?>;


    DUPX.basicDBActionChange = function ()
    {
        var action = $('#' + dbActionInputId).val();
        $('.s2-basic-pane .s2-warning-manualdb').hide();
        $('.s2-basic-pane .s2-warning-emptydb').hide();
        $('.s2-basic-pane .s2-warning-renamedb').hide();
        switch (action)
        {
            case 'create'  :
                break;
            case 'empty'   :
                $('.s2-basic-pane .s2-warning-emptydb').show(300);
                break;
            case 'rename'  :
                $('.s2-basic-pane .s2-warning-renamedb').show(300);
                break;
            case 'manual'  :
                $('.s2-basic-pane .s2-warning-manualdb').show(300);
                break;
        }
    };

//DOCUMENT INIT
    $(document).ready(function ()
    {
        $("#" + dbActionInputId).on("change", DUPX.basicDBActionChange);
        DUPX.basicDBActionChange();

        DUPX.checkOverwriteParameters = function (dbhost, dbname, dbuser, dbpass)
        {
            $("#" + dbHostInputId).val(<?php echo DupProSnapJsonU::wp_json_encode($ovr_dbhost); ?>).prop('readonly', true);
            $("#" + dbNameInputId).val(<?php echo DupProSnapJsonU::wp_json_encode($ovr_dbname); ?>).prop('readonly', true);
            $("#" + dbUserInputId).val(<?php echo DupProSnapJsonU::wp_json_encode($ovr_dbuser); ?>).prop('readonly', true);
            $("#" + dbPassInputId).val(<?php echo DupProSnapJsonU::wp_json_encode($ovr_dbpass); ?>).prop('readonly', true);
            $("#s2-db-basic-setup").show();
        };

        DUPX.fillInPlaceHolders = function ()
        {
            $("#" + dbHostInputId).attr('placeholder', <?php echo DupProSnapJsonU::wp_json_encode($ovr_dbhost); ?>).prop('readonly', false);
            $("#" + dbNameInputId).attr('placeholder', <?php echo DupProSnapJsonU::wp_json_encode($ovr_dbname); ?>).prop('readonly', false);
            $("#" + dbUserInputId).attr('placeholder', <?php echo DupProSnapJsonU::wp_json_encode($ovr_dbuser); ?>).prop('readonly', false);
            $("#" + dbPassInputId).attr('placeholder', <?php echo DupProSnapJsonU::wp_json_encode($ovr_dbpass); ?>).prop('readonly', false);
        };

        DUPX.resetParameters = function ()
        {
            $("#" + dbHostInputId).val('').attr('placeholder', '').prop('readonly', false);
            $("#" + dbNameInputId).val('').attr('placeholder', '').prop('readonly', false);
            $("#" + dbUserInputId).val('').attr('placeholder', '').prop('readonly', false);
            $("#" + dbPassInputId).val('').attr('placeholder', '').prop('readonly', false);
        };

<?php if (DUPX_InstallerState::getInstance()->getMode() === DUPX_InstallerState::MODE_OVR_INSTALL) : ?>
            DUPX.fillInPlaceHolders();
<?php endif; ?>
        DUPX.charsetfbCheckChanged = function () {
            var selectionBoxWrapper = $('#' + dbDbcharsetfbValWrapperId);
            if ($("#" + dbDbcharsetfbInputId).is(':checked')) {
                selectionBoxWrapper.slideDown('slow');
            } else {
                selectionBoxWrapper.slideUp('slow');
            }
        }
        
        DUPX.collatefbCheckChanged = function () {
            var selectionBoxWrapper = $('#' + dbDbcollatefbValWrapperId);
            if ($("#" + dbDbcollatefbInputId).is(':checked')) {
                selectionBoxWrapper.slideDown('slow');
            } else {
                selectionBoxWrapper.slideUp('slow');
            }
        }
       
        DUPX.charsetValChanged = function () {
            
            var collateObj = $('#' + dbDbcollatefbValInputId);
            var charsetObj = $('#' + dbDbcharsetfbValInputId);
            if (collateObj.is(":visible")) {                
                collateObj.find('option').hide();
                collateObj.find('option[data-charset="' + charsetObj.val() + '"]').show().first().prop('selected' , true);
            }
        }
        
        $("#" + dbDbcharsetfbInputId).change(DUPX.charsetfbCheckChanged).trigger('change');
        $("#" + dbDbcollatefbInputId).change(DUPX.collatefbCheckChanged).trigger('change');
        $("#" + dbDbcharsetfbValInputId).change(DUPX.charsetValChanged);
    });
</script>
