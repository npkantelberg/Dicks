<?php
defined("DUPXABSPATH") or die("");

class DUPX_InstallerState
{

    const MODE_UNKNOWN     = -1;
    const MODE_STD_INSTALL = 0;
    const MODE_OVR_INSTALL = 1;
    const MODE_BK_RESTORE  = 2;

    /**
     *
     * @var int
     */
    protected $mode = self::MODE_UNKNOWN;

    /**
     *
     * @var string 
     */
    protected $ovr_wp_content_dir = '';

    /**
     *
     * @var self
     */
    private static $instance = null;

    /**
     *
     * @return self
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        
    }

    /**
     * return installer mode
     * 
     * @return int 
     */
    public function getMode()
    {
        return DUPX_Paramas_Manager::getInstance()->getValue(DUPX_Paramas_Manager::PARAM_INSTALLER_MODE);
    }

    /**
     * return overwrite content dir 
     * 
     * @return string
     */
    public function getOvrWpContent()
    {
        return DUPX_Paramas_Manager::getInstance()->getValue(DUPX_Paramas_Manager::PARAM_INSTALLER_OVR_DIR);
    }

    /**
     * check current installer mode 
     * 
     * @param bool $onlyIfUnknown // check se state only if is unknow state
     * @param bool $saveParams // if true update params
     * @return boolean
     */
    public function checkState($onlyIfUnknown = true, $saveParams = true)
    {
        $paramsManager = DUPX_Paramas_Manager::getInstance();
        $wpConfigPath  = DUPX_WPConfig::getWpConfigPath();

        if ($onlyIfUnknown && $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_INSTALLER_MODE) !== self::MODE_UNKNOWN) {
            return true;
        }

        DUPX_Log::info('CHECK STATE INSTALLER WP CONFIG PATH: '.DUPX_Log::varToString($wpConfigPath), DUPX_Log::LV_DETAILED);

        // RSR TODO: Remove for lite then put back in when we do overwrite
        if (file_exists($wpConfigPath)) {
            $config_transformer = new WPConfigTransformer($wpConfigPath);
            if ($config_transformer->exists('constant', 'WP_CONTENT_DIR')) {
                $wp_content_dir_val = $config_transformer->get_value('constant', 'WP_CONTENT_DIR');
            } else {
                $wp_content_dir_val = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_PATH_CONTENT_NEW);
            }

            $paramsManager->setValue(DUPX_Paramas_Manager::PARAM_INSTALLER_MODE, self::MODE_OVR_INSTALL);
            $paramsManager->setValue(DUPX_Paramas_Manager::PARAM_INSTALLER_OVR_DIR, $wp_content_dir_val);
        } else {
            $paramsManager->setValue(DUPX_Paramas_Manager::PARAM_INSTALLER_MODE, self::MODE_STD_INSTALL);
            $paramsManager->setValue(DUPX_Paramas_Manager::PARAM_INSTALLER_OVR_DIR, '');
        }

        DUPX_Log::info('CHECK STATE INSTALLER MODE: '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_INSTALLER_OVR_DIR)), DUPX_Log::LV_DETAILED);

        if ($saveParams) {
            return $this->save();
        } else {
            return true;
        }
    }

    /**
     * 
     * if (DUPX_InstallerState::getInstance()->getMode() === DUPX_InstallerState::MODE_OVR_INSTALL) {
      echo "<span class='dupx-overwrite'>Mode: Overwrite Install {$db_only_txt}</span>";
      } else {
      echo "Mode: Standard Install {$db_only_txt}";
      }
     */
    public function getHtmlModeHeader()
    {
        $php_enforced_txt = ($GLOBALS['DUPX_ENFORCE_PHP_INI']) ? '<i style="color:red"><br/>*PHP ini enforced*</i>' : '';
        $db_only_txt      = ($GLOBALS['DUPX_AC']->exportOnlyDB) ? ' - Database Only' : '';
        $db_only_txt      = $db_only_txt.$php_enforced_txt;

        switch ($this->getMode()) {
            case self::MODE_UNKNOWN:
                $label = 'Unknown';
                $class = 'mode_unknown';
                break;
            case self::MODE_OVR_INSTALL:
                $label = 'Overwrite Install';
                $class = 'dupx-overwrite mode_overwrite';
                break;
            case self::MODE_STD_INSTALL:
                $label = 'Standard Install';
                $class = 'dupx-overwrite mode_standard';
                break;
            case self::MODE_BK_RESTORE:
                $label = 'Restore backup';
                $class = 'mode_restore_bk';
                break;
        }
        return '<span class="'.$class.'">Mode: '.$label.' '.$db_only_txt.'</span>';
    }

    /**
     * reset current mode
     * 
     * @param boolean $saveParams
     * @return boolean
     */
    public function resetState($saveParams = true)
    {
        $paramsManager = DUPX_Paramas_Manager::getInstance();
        $paramsManager->setValue(DUPX_Paramas_Manager::PARAM_INSTALLER_MODE, self::MODE_UNKNOWN);
        $paramsManager->setValue(DUPX_Paramas_Manager::PARAM_INSTALLER_OVR_DIR, '');
        if ($saveParams) {
            return $this->save();
        } else {
            return true;
        }
    }

    /**
     * save current installer state
     * 
     * @return bool
     * @throws Exception if fail
     */
    public function save()
    {
        return DUPX_Paramas_Manager::getInstance()->save();
    }

    /**
     * this function returns true if both the URL and path old and new path are identical
     * 
     * @return bool
     */
    public function isInstallerCreatedInThisLocation()
    {
        $paramsManager = DUPX_Paramas_Manager::getInstance();

        $path_new = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_PATH_NEW);
        $path_old = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_PATH_OLD);
        $url_new  = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_URL_NEW);
        $url_old  = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_URL_OLD);

        return ($path_new === $path_old && $url_new === $url_old);
    }
}