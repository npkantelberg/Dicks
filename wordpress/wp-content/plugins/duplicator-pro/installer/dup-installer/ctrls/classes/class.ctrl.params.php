<?php
/**
 * Controller params manager
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\U
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * singleton class
 */
final class DUPX_Ctrl_Params
{

    /**
     *
     * @var bool    // this variable becomes false if there was something wrong with the validation but the basic is true
     */
    private static $paramsValidated = true;

    /**
     * returns false if at least one param has not been validated
     * 
     * @return bool 
     */
    public static function isParamsValidated()
    {
        return self::$paramsValidated;
    }

    /**
     * 
     * @return boolean
     */
    public static function setParamsBase()
    {
        DUPX_LOG::info('CTRL PARAMS BASE', DUPX_Log::LV_DETAILED);
        $paramsManager = DUPX_Paramas_Manager::getInstance();
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_CTRL_ACTION, DUPX_Param_item_form::INPUT_REQUEST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_VIEW, DUPX_Param_item_form::INPUT_REQUEST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_STEP_ACTION, DUPX_Param_item_form::INPUT_POST);

        $paramsManager->save();
        return true;
    }

    /**
     * 
     * @return boolean
     */
    public static function setParamsStep0()
    {
        DUPX_LOG::info('CTRL PARAMS S0', DUPX_Log::LV_DETAILED);
        $paramsManager = DUPX_Paramas_Manager::getInstance();

        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_SECURE_PASS, DUPX_Param_item_form::INPUT_REQUEST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_SECURE_TRY, DUPX_Param_item_form::INPUT_REQUEST);

        if ($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_STEP_ACTION) === 'revalidate') {
            self::setParamsStep1();
        } else {
            DUPX_ArchiveConfig::getInstance()->setNewPathsAndUrlParamsByMainNew();
        }

        DUPX_Custom_Host_Manager::getInstance()->setManagedHostParams();

        $paramsManager->save();
        return self::$paramsValidated;
    }

    /**
     * 
     * @return boolean
     */
    public static function setParamsStep1()
    {
        DUPX_LOG::info('CTRL PARAMS S1', DUPX_Log::LV_DETAILED);
        $archive_config = DUPX_ArchiveConfig::getInstance();
        $paramsManager  = DUPX_Paramas_Manager::getInstance();
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_LOGGING, DUPX_Param_item_form::INPUT_POST);
        DUPX_Log::setLogLevel();

        $readParamsList = array(
            DUPX_Paramas_Manager::PARAM_URL_OLD,
            DUPX_Paramas_Manager::PARAM_URL_NEW,
            DUPX_Paramas_Manager::PARAM_SITE_URL_OLD,
            DUPX_Paramas_Manager::PARAM_SITE_URL,
            DUPX_Paramas_Manager::PARAM_PATH_WP_CORE_OLD,
            DUPX_Paramas_Manager::PARAM_PATH_WP_CORE_NEW,
            DUPX_Paramas_Manager::PARAM_PATH_OLD,
            DUPX_Paramas_Manager::PARAM_PATH_CONTENT_OLD,
            DUPX_Paramas_Manager::PARAM_PATH_CONTENT_NEW,
            DUPX_Paramas_Manager::PARAM_URL_CONTENT_OLD,
            DUPX_Paramas_Manager::PARAM_URL_CONTENT_NEW,
            DUPX_Paramas_Manager::PARAM_PATH_UPLOADS_OLD,
            DUPX_Paramas_Manager::PARAM_PATH_UPLOADS_NEW,
            DUPX_Paramas_Manager::PARAM_URL_UPLOADS_OLD,
            DUPX_Paramas_Manager::PARAM_URL_UPLOADS_NEW,
            DUPX_Paramas_Manager::PARAM_PATH_PLUGINS_OLD,
            DUPX_Paramas_Manager::PARAM_PATH_PLUGINS_NEW,
            DUPX_Paramas_Manager::PARAM_URL_PLUGINS_OLD,
            DUPX_Paramas_Manager::PARAM_URL_PLUGINS_NEW,
            DUPX_Paramas_Manager::PARAM_PATH_MUPLUGINS_OLD,
            DUPX_Paramas_Manager::PARAM_PATH_MUPLUGINS_NEW,
            DUPX_Paramas_Manager::PARAM_URL_MUPLUGINS_OLD,
            DUPX_Paramas_Manager::PARAM_URL_MUPLUGINS_NEW,
            DUPX_Paramas_Manager::PARAM_PATH_NEW,
            DUPX_Paramas_Manager::PARAM_MULTISITE_INST_TYPE,
            DUPX_Paramas_Manager::PARAM_SUBSITE_ID,
            DUPX_Paramas_Manager::PARAM_ARCHIVE_ENGINE,
            DUPX_Paramas_Manager::PARAM_SET_FILE_PERMS,
            DUPX_Paramas_Manager::PARAM_SET_DIR_PERMS,
            DUPX_Paramas_Manager::PARAM_FILE_PERMS_VALUE,
            DUPX_Paramas_Manager::PARAM_DIR_PERMS_VALUE,
            DUPX_Paramas_Manager::PARAM_SAFE_MODE,
            DUPX_Paramas_Manager::PARAM_WP_CONFIG,
            DUPX_Paramas_Manager::PARAM_HTACCESS_CONFIG,
            DUPX_Paramas_Manager::PARAM_OTHER_CONFIG,
            DUPX_Paramas_Manager::PARAM_FILE_TIME,
            DUPX_Paramas_Manager::PARAM_REMOVE_RENDUNDANT,
            DUPX_Paramas_Manager::PARAM_CLIENT_KICKOFF,
            DUPX_Paramas_Manager::PARAM_ACCEPT_TERM_COND
        );

        foreach ($readParamsList as $cParam) {
            if ($paramsManager->setValueFromInput($cParam, DUPX_Param_item_form::INPUT_POST, false, true) === false) {
                self::$paramsValidated = false;
            }
        }
        $paramsManager->setValue(DUPX_Paramas_Manager::PARAM_BLOGNAME, $archive_config->getBlognameFromSelectedSubsiteId());

        if (self::$paramsValidated === false) {
            $paramsManager->setValue(DUPX_Paramas_Manager::PARAM_VIEW, 'step1');
        } else {
            DUPX_Log::info('UPDATE PARAMS FROM SUBSITE ID', DUPX_Log::LV_DEBUG);
            DUPX_Log::info('NETWORK INSTALL: '.DUPX_Log::varToString($archive_config->isNetworkInstall()), DUPX_Log::LV_DEBUG);

            // UPDATE ACTIVE PARAMS BY SUBSITE ID
            $subsiteId = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_SUBSITE_ID);
            DUPX_Log::info('SUBSITE ID: '.DUPX_Log::varToString($subsiteId), DUPX_Log::LV_DEBUG);

            $activePlugins = DUPX_Plugins_Manager::getInstance()->getDefaultActivePluginsList($subsiteId);
            $paramsManager->setValue(DUPX_Paramas_Manager::PARAM_PLUGINS, $activePlugins);

            if ($archive_config->isNetworkInstall()) {
                $paramsManager->setValue(DUPX_Paramas_Manager::PARAM_MU_REPLACE, $archive_config->getNewUrlsArrayIdVal());
            } else {
                $paramsManager->setFormStatus(DUPX_Paramas_Manager::PARAM_MULTISITE_CROSS_SEARCH, DUPX_Param_item_form::STATUS_SKIP);
            }

            // IF SAFE MODE DISABLE ALL PLUGINS
            if ($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_SAFE_MODE) > 0) {
                $forceDisable = DUPX_Plugins_Manager::getInstance()->getAllPluginsSlugs();

                // EXCLUDE DUPLICATOR PRO
                if (($key = array_search(DUPX_Plugins_Manager::SLUG_DUPLICATOR_PRO, $forceDisable)) !== false) {
                    unset($forceDisable[$key]);
                }

                $paramsManager->setValue(DUPX_Paramas_Manager::PARAM_FORCE_DIABLE_PLUGINS, $forceDisable);
            }
        }

        // reload state after new path and new url
        DUPX_InstallerState::getInstance()->checkState(false, false);
        $paramsManager->save();
        return self::$paramsValidated;
    }

    /**
     * 
     * @return boolean
     */
    public static function setParamsStep2()
    {
        DUPX_LOG::info('CTRL PARAMS S2', DUPX_Log::LV_DETAILED);
        $paramsManager = DUPX_Paramas_Manager::getInstance();

        $readParamsList = array(
            DUPX_Paramas_Manager::PARAM_DB_TABLE_PREFIX,
            DUPX_Paramas_Manager::PARAM_DB_CHUNK,
            DUPX_Paramas_Manager::PARAM_DB_COLLATE_FB,
            DUPX_Paramas_Manager::PARAM_DB_SPACING,
            DUPX_Paramas_Manager::PARAM_DB_VIEW_CREATION,
            DUPX_Paramas_Manager::PARAM_DB_PROC_CREATION,
            DUPX_Paramas_Manager::PARAM_DB_CHARSET,
            DUPX_Paramas_Manager::PARAM_DB_COLLATE,
            DUPX_Paramas_Manager::PARAM_DB_CHARSET_FB,
            DUPX_Paramas_Manager::PARAM_DB_CHARSET_FB_VAL,
            DUPX_Paramas_Manager::PARAM_DB_COLLATE_FB,
            DUPX_Paramas_Manager::PARAM_DB_COLLATE_FB_VAL,
            DUPX_Paramas_Manager::PARAM_DB_MYSQL_MODE,
            DUPX_Paramas_Manager::PARAM_DB_MYSQL_MODE_OPTS
        );

        foreach ($readParamsList as $cParam) {
            if ($paramsManager->setValueFromInput($cParam, DUPX_Param_item_form::INPUT_POST, false, true) === false) {
                self::$paramsValidated = false;
            }
        }

        if (self::$paramsValidated === false) {
            $paramsManager->setValue(DUPX_Paramas_Manager::PARAM_VIEW, 'step2');
        } else {
            $tableOptions = DUPX_Paramas_Descriptors::getTablesOptionsWithPrefix($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_TABLE_PREFIX));
            $paramsManager->setOptions(DUPX_Paramas_Manager::PARAM_DB_TABLES, $tableOptions['options']);
            $paramsManager->setValue(DUPX_Paramas_Manager::PARAM_DB_TABLES, $tableOptions['default']);
        }

        $paramsManager->save();
        return self::$paramsValidated;
    }

    /**
     * 
     * @return boolean
     */
    public static function setParamsStep3()
    {
        DUPX_LOG::info('CTRL PARAMS S3', DUPX_Log::LV_DETAILED);
        $paramsManager = DUPX_Paramas_Manager::getInstance();

        $readParamsList = array(
            DUPX_Paramas_Manager::PARAM_BLOGNAME,
            DUPX_Paramas_Manager::PARAM_REPLACE_MODE,
            DUPX_Paramas_Manager::PARAM_REPLACE_ENGINE,
            DUPX_Paramas_Manager::PARAM_MU_REPLACE,
            DUPX_Paramas_Manager::PARAM_EMPTY_SCHEDULE_STORAGE,
            DUPX_Paramas_Manager::PARAM_DB_TABLES,
            DUPX_Paramas_Manager::PARAM_EMAIL_REPLACE,
            DUPX_Paramas_Manager::PARAM_FULL_SEARCH,
            DUPX_Paramas_Manager::PARAM_POSTGUID,
            DUPX_Paramas_Manager::PARAM_MAX_SERIALIZE_CHECK,
            DUPX_Paramas_Manager::PARAM_MULTISITE_CROSS_SEARCH,
            DUPX_Paramas_Manager::PARAM_PLUGINS,
            DUPX_Paramas_Manager::PARAM_WP_ADMIN_NAME,
            DUPX_Paramas_Manager::PARAM_WP_ADMIN_PASSWORD,
            DUPX_Paramas_Manager::PARAM_WP_ADMIN_MAIL,
            DUPX_Paramas_Manager::PARAM_WP_ADMIN_NICKNAME,
            DUPX_Paramas_Manager::PARAM_WP_ADMIN_FIRST_NAME,
            DUPX_Paramas_Manager::PARAM_WP_ADMIN_LAST_NAME,
            DUPX_Paramas_Manager::PARAM_WP_CONF_DISALLOW_FILE_EDIT,
            DUPX_Paramas_Manager::PARAM_WP_CONF_DISALLOW_FILE_EDIT,
            DUPX_Paramas_Manager::PARAM_WP_CONF_AUTOSAVE_INTERVAL,
            DUPX_Paramas_Manager::PARAM_WP_CONF_WP_POST_REVISIONS,
            DUPX_Paramas_Manager::PARAM_WP_CONF_FORCE_SSL_ADMIN,
            DUPX_Paramas_Manager::PARAM_GEN_WP_AUTH_KEY,
            DUPX_Paramas_Manager::PARAM_WP_CONF_WP_AUTO_UPDATE_CORE,
            DUPX_Paramas_Manager::PARAM_WP_CONF_WP_CACHE,
            DUPX_Paramas_Manager::PARAM_WP_CONF_WPCACHEHOME,
            DUPX_Paramas_Manager::PARAM_WP_CONF_WP_DEBUG,
            DUPX_Paramas_Manager::PARAM_WP_CONF_WP_DEBUG_LOG,
            DUPX_Paramas_Manager::PARAM_WP_CONF_WP_DEBUG_DISPLAY,
            DUPX_Paramas_Manager::PARAM_WP_CONF_SCRIPT_DEBUG,
            DUPX_Paramas_Manager::PARAM_WP_CONF_SAVEQUERIES,
            DUPX_Paramas_Manager::PARAM_WP_CONF_COOKIE_DOMAIN,
            DUPX_Paramas_Manager::PARAM_WP_CONF_WP_MEMORY_LIMIT,
            DUPX_Paramas_Manager::PARAM_WP_CONF_WP_MAX_MEMORY_LIMIT,
        );

        foreach ($readParamsList as $cParam) {
            if ($paramsManager->setValueFromInput($cParam, DUPX_Param_item_form::INPUT_POST, false, true) === false) {
                self::$paramsValidated = false;
            }
        }

        if (self::$paramsValidated === false) {
            $paramsManager->setValue(DUPX_Paramas_Manager::PARAM_VIEW, 'step3');
        } else {
            
        }

        $paramsManager->save();
        return self::$paramsValidated;
    }
}