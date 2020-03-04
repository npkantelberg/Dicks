<?php
/**
 * 
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * View s3 functions
 */
class DUPX_View_S3
{

    public static function newSettings()
    {
        $paramsManager  = DUPX_Paramas_Manager::getInstance();
        $archive_config = DUPX_ArchiveConfig::getInstance();
        ?>
        <div class="hdr-sub1 toggle-hdr close" data-type="toggle" data-target="#s3-new-settings">
            <a href="javascript:void(0)"><i class="fa fa-minus-square"></i>Setup</a>
        </div>
        <div id="s3-new-settings" class="hdr-sub1-area">
            <div class="dupx-opts s3-opts">
                <?php
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_BLOGNAME);

                $paramsManager->setFormStatus(DUPX_Paramas_Manager::PARAM_URL_OLD, DUPX_Param_item_form::STATUS_INFO_ONLY);
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_URL_OLD);
                
                $paramsManager->setFormStatus(DUPX_Paramas_Manager::PARAM_URL_NEW, DUPX_Param_item_form::STATUS_INFO_ONLY);
                //$paramsManager->setFormNote(DUPX_Paramas_Manager::PARAM_URL_NEW, 'the new url can be changed on step 1');
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_URL_NEW);
                
                $paramsManager->setFormStatus(DUPX_Paramas_Manager::PARAM_PATH_OLD, DUPX_Param_item_form::STATUS_INFO_ONLY);
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_PATH_OLD);
                
                $paramsManager->setFormStatus(DUPX_Paramas_Manager::PARAM_PATH_NEW, DUPX_Param_item_form::STATUS_INFO_ONLY);
                $paramsManager->setFormNote(DUPX_Paramas_Manager::PARAM_PATH_NEW, "The 'New Site URL' and 'New Path' can be updated in step 1.");
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_PATH_NEW);

                if ($archive_config->isNetworkInstall()) {
                    $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_REPLACE_MODE);
                }
                ?>
            </div>
        </div>
        <?php
    }

    public static function mappingMode()
    {
        $archive_config = DUPX_ArchiveConfig::getInstance();
        $paramsManager  = DUPX_Paramas_Manager::getInstance();
        if (!$archive_config->isNetworkInstall()) {
            return;
        }
        ?>
        <div id="subsite-map-container" class="<?php echo $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_REPLACE_MODE) == 'mapping' ? '' : 'no-display'; ?>">
            <div class="hdr-sub1 toggle-hdr close" data-type="toggle" data-target="#s3-subsite-mapping">
                <a href="javascript:void(0)"><i class="fa fa-minus-square"></i>Subsite Mapping</a>
            </div>
            <div id="s3-subsite-mapping" class="hdr-sub1-area">
                <div class="url-mapping-header" >
                    <span class="left" >Old urls</span>
                    <span class="right" >New urls</span>
                </div>
                <?php
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_MU_REPLACE);
                ?>
            </div>
        </div>
        <?php
    }

    public static function customSearchAndReaplce()
    {
        $paramsManager = DUPX_Paramas_Manager::getInstance();
        if ($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_REPLACE_ENGINE) === DUPX_S3_Funcs::MODE_SKIP && !$paramsManager->isHtmlInput(DUPX_Paramas_Manager::PARAM_REPLACE_ENGINE)) {
            // IF IS FORCED MODE_SKIP the custom search and reaplace section is useless
            return;
        }
        ?>
        <!-- =========================
        SEARCH AND REPLACE -->
        <div class="hdr-sub1 toggle-hdr open" data-type="toggle" data-target="#s3-custom-replace">
            <a href="javascript:void(0)"><i class="fa fa-plus-square"></i>Replace</a>
        </div>

        <div id="s3-custom-replace" class="hdr-sub1-area no-display" >
            <div class="help-target">
                <?php DUPX_View_Funcs::helpIconLink('step3'); ?>
            </div>

            <table class="s3-opts" id="search-replace-table">
                <tr valign="top" id="search-0">
                    <td>Search:</td>
                    <td><input class="w95" type="text" name="search[]" style="margin-right:5px"></td>
                </tr>
                <tr valign="top" id="replace-0"><td>Replace:</td><td><input class="w95" type="text" name="replace[]"></td></tr>
            </table>
            <button type="button" onclick="DUPX.addSearchReplace();return false;" style="font-size:12px;display: block; margin: 10px 0 0 0; " class="default-btn">Add More</button>
        </div>
        <?php
    }

    public static function options()
    {
        $paramsManager = DUPX_Paramas_Manager::getInstance();
        $wpConfig      = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_WP_CONFIG);
        $skipWpConfig  = ($wpConfig == 'nothing' || $wpConfig == 'original');
        ?>
        <!-- ==========================
        OPTIONS -->
        <div class="hdr-sub1 toggle-hdr open" data-type="toggle" data-target="#s3-adv-opts">
            <a href="javascript:void(0)"><i class="fa fa-plus-square"></i>Options</a>
        </div>
        <!-- START TABS -->
        <div id="s3-adv-opts" class="hdr-sub1-area tabs-area no-display">
            <div id="tabs">
                <ul>
                    <li><a href="#tabs-admin-account">Admin Account</a></li>
                    <li><a href="#tabs-scan-options">Scan Options</a></li>
                    <li><a href="#tabs-plugins">Plugins</a></li>
                    <?php if (!$skipWpConfig) { ?>
                        <li><a href="#tabs-wp-config-file">WP-Config File</a></li>
                    <?php } ?>
                </ul>

                <!-- =====================
                ADMIN TAB -->
                <div id="tabs-admin-account">
                    <?php self::tabNewAdmin(); ?>
                </div>

                <!-- =====================
                SCAN TAB -->
                <div id="tabs-scan-options">
                    <?php self::tabScanOptions(); ?>
                </div>

                <!-- =====================
                PLUGINS  TAB -->
                <div id="tabs-plugins">
                    <?php self::tabPluginsContent(); ?>
                </div>
                <?php if (!$skipWpConfig) { ?>
                    <!-- =====================
                    WP-CONFIG TAB -->
                    <div id="tabs-wp-config-file">
                        <?php self::tabWpConfig(); ?>
                    </div>
                <?php } ?>
            </div>
            <?php
        }

        public static function tabNewAdmin()
        {
            $paramsManager  = DUPX_Paramas_Manager::getInstance();
            $archive_config = DUPX_ArchiveConfig::getInstance();
            ?>
            <div class="help-target">
                <?php DUPX_View_Funcs::helpIconLink('step3'); ?>
            </div>

            <!-- NEW ADMIN ACCOUNT -->
            <div class="hdr-sub3">New Admin Account</div>
            <div style="text-align: center">
                <i style="color:gray;font-size: 11px">This feature is optional.  If the username already exists the account will NOT be created or updated.</i>
                <?php if ($archive_config->isNetworkInstall()) { ?>
                    <br>
                    <i style="color:gray;font-size: 11px">
                        You will create Network Administrator account
                    </i>
                    <?php
                }
                ?>
            </div>
            <div class="dupx-opts s3-opts">
                <?php
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_WP_ADMIN_NAME);
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_WP_ADMIN_PASSWORD);
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_WP_ADMIN_MAIL);
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_WP_ADMIN_NICKNAME);
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_WP_ADMIN_FIRST_NAME);
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_WP_ADMIN_LAST_NAME);
                ?>
            </div>
            <?php
        }

        public static function tabScanOptions()
        {
            $paramsManager = DUPX_Paramas_Manager::getInstance();
            ?>
            <div class="help-target">
                <?php DUPX_View_Funcs::helpIconLink('step3'); ?>
            </div>
            <div class="hdr-sub3">Database Scan Options</div>
            <div  class="dupx-opts s3-opts">
                <?php
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_REPLACE_ENGINE);
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_EMPTY_SCHEDULE_STORAGE);

                if ($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_REPLACE_ENGINE) === DUPX_S3_Funcs::MODE_SKIP && !$paramsManager->isHtmlInput(DUPX_Paramas_Manager::PARAM_REPLACE_ENGINE)) {
                    ?>
                    <p><small>This is a backup mode so the search and replace option are disabled.</small></p>
                    <?php
                } else {
                    /** THIS IT A TEMP HACK -- REMOVE THIS AFTER MVC INTEGRATION **/
                    $tableOptions = DUPX_Paramas_Descriptors::getTablesOptionsWithPrefix($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_TABLE_PREFIX));
                    $paramsManager->setOptions(DUPX_Paramas_Manager::PARAM_DB_TABLES, $tableOptions['options']);
                    $paramsManager->setValue(DUPX_Paramas_Manager::PARAM_DB_TABLES, $tableOptions['default']);
            
                    $tableSelectId = $paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_DB_TABLES);
                    ?>
                    <div class="param-wrapper" >
                        <label for="<?php echo $paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_DB_TABLES); ?>" >
                            <b>Scan Tables:</b>
                        </label>
                        <div class="s3-allnonelinks">
                            <a href="javascript:void(0)" onclick="$('#<?php echo $tableSelectId; ?> option').prop('selected', true);">[All]</a>
                            <a href="javascript:void(0)" onclick="$('#<?php echo $tableSelectId; ?> option').prop('selected', false);">[None]</a>
                        </div><br style="clear:both" />
                        <?php $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_DB_TABLES); ?>
                    </div>
                    <?php
                    $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_EMAIL_REPLACE);
                    $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_FULL_SEARCH);
                    $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_MULTISITE_CROSS_SEARCH);
                    $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_POSTGUID);
                    $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_MAX_SERIALIZE_CHECK);
                }
                ?>
            </div>
            <?php
        }

        public static function tabPluginsContent()
        {
            $paramsManager = DUPX_Paramas_Manager::getInstance();
            ?>
            <div class="help-target">
                <?php DUPX_View_Funcs::helpIconLink('step3'); ?>
            </div>
            <?php
            $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_PLUGINS);
        }

        public static function tabWpConfig()
        {
            $paramsManager = DUPX_Paramas_Manager::getInstance();
            ?>
            <div class="help-target">
                <?php DUPX_View_Funcs::helpIconLink('step3'); ?>
            </div>
            <p>
                See the <a href="https://wordpress.org/support/article/editing-wp-config-php/" target="_blank">WordPress documentation for more information</a>.
            </p>
            <div  class="dupx-opts s3-opts">
                <div class="hdr-sub3">Posts/Pages</div>
                <?php
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_WP_CONF_DISALLOW_FILE_EDIT);
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_WP_CONF_AUTOSAVE_INTERVAL);
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_WP_CONF_WP_POST_REVISIONS);
                ?>
                <div class="hdr-sub3 margin-top">Security</div>
                <?php
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_WP_CONF_FORCE_SSL_ADMIN);
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_WP_CONF_WP_AUTO_UPDATE_CORE);
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_GEN_WP_AUTH_KEY);
                ?>
                <div class="hdr-sub3 margin-top">System/General</div>
                <?php
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_WP_CONF_WP_CACHE);
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_WP_CONF_WP_DEBUG);
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_WP_CONF_WP_DEBUG_LOG);
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_WP_CONF_WP_DEBUG_DISPLAY);
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_WP_CONF_SCRIPT_DEBUG);
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_WP_CONF_SAVEQUERIES);
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_WP_CONF_COOKIE_DOMAIN);
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_WP_CONF_WP_MEMORY_LIMIT);
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_WP_CONF_WP_MAX_MEMORY_LIMIT);
                ?>
                <div class="hdr-sub3 margin-top">Other Settings</div>
                <?php
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_WP_CONF_WPCACHEHOME);
                ?>
            </div>
            <?php
        }
    }    