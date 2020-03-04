<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/** IDE HELPERS */
/* @var $GLOBALS['DUPX_AC'] DUPX_ArchiveConfig */

$paramsManager = DUPX_Paramas_Manager::getInstance();
?>

<!-- =========================================
VIEW: STEP 0 - PASSWORD -->
<form method="post" id="i1-pass-form" class="content-form"  data-parsley-validate="" autocomplete="off" >
    <?php
    $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_CTRL_ACTION, 'ctrl-step0');
    $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_SECURE_TRY, true);
    ?>
    <input type="hidden" name="<?php echo DUPX_Security::CTRL_TOKEN; ?>" value="<?php echo DUPX_CSRF::generate('ctrl-step0'); ?>">

    <div class="hdr-main">
        Installer Password
    </div>

    <?php if ($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_SECURE_TRY)) : ?>
        <div class="error-pane">
            <p>Invalid Password! Please try again...</p>
        </div>
    <?php endif; ?>

    <div style="text-align: center">
        This file was password protected when it was created.   If you do not remember the password	check the details of the package on	the site where it was created or visit
        the online FAQ for <a href="https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-030-q" target="_blank">more details</a>.
        <br/><br/><br/>
    </div>
    <div class="i1-pass-data">
        <?php
        $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_SECURE_PASS);
        ?>
        <div class="footer-buttons" >
            <div class="content-left">
            </div>
            <div class="content-right" >
                <button type="button" name="secure-btn" id="secure-btn" class="default-btn" onclick="DUPX.checkPassword()">Submit</button>
            </div>
        </div>
    </div>

</form>

<script>
    /**
     * Submits the password for validation
     */
    DUPX.checkPassword = function ()
    {
        var $form = $('#i1-pass-form');
        $form.parsley().validate();
        if (!$form.parsley().isValid()) {
            return;
        }
        $form.submit();
    }
</script>
<!-- END OF VIEW INIT 1 -->