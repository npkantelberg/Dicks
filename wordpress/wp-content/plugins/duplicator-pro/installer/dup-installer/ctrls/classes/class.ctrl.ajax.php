<?php
/**
 * controller step 0
 * 
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

final class DUPX_Ctrl_ajax
{

    const AJAX_NAME             = 'ajax_request';
    const ACTION_NAME           = 'ajax_action';
    const TOKEN_NAME            = 'ajax_csrf_token';
    // ACCEPTED ACTIONS
    const ACTION_DATABASE_CHECK = 'dbtest';
    const ACTION_DAWN           = 'dawn';
    const ACTION_INITPASS_CHECK = 'initpass';

    public static function controller()
    {
        $action = null;
        if (self::isAjax($action) === false) {
            return false;
        }

        DUPX_Log::info("\n".'-------------------------'."\n".'AJAX ACTION '.$action);
        DUPX_Log::infoObject('POST DATA: ', $_POST, DUPX_Log::LV_DEBUG);

        try {
            switch ($action) {
                case self::ACTION_DATABASE_CHECK:
                    self::dbTest();
                    break;
                case self::ACTION_DAWN:
                    /*                     * ********************
                     * @todo move this partams set after controller/view refactoring
                     * 
                     */
                    if (isset($_REQUEST['action']) && isset($_REQUEST['action']) === 'start_expand') {
                        $paramsOk = DUPX_Ctrl_Params::setParamsStep1();
                        // IF PARAMS AREN'T VALIDATED DON'T EXECUTE CONTROLLERS
                        if (!$paramsOk) {
                            DUPX_Log::info('PARAMS AREN\'T VALID, GO BACK TO PREVIOUS STEP', DUPX_Log::LV_DETAILED);
                            throw new Exception('Params arent valid');
                        }
                    }
                    /*                     * ***************************** */

                    require_once(DUPX_INIT.'/lib/dup_archive/daws/daws.php');
                    break;
            }
        }
        catch (Exception $e) {
            DUPX_Log::logException($e);

            $jsonResult = array(
                'error'        => true,
                'errorMessage' => $e->getMessage()
            );
            echo DupProSnapJsonU::wp_json_encode($jsonResult);
        }

        // if is ajax always die;
        die();
    }

    public static function isAjax(&$action = null)
    {
        static $argsInput = null;
        if (is_null($argsInput)) {
            $argsInput = filter_input_array(INPUT_POST, array(
                self::AJAX_NAME   => array(
                    'filter'  => FILTER_VALIDATE_BOOLEAN,
                    'flags'   => FILTER_REQUIRE_SCALAR,
                    'options' => array('default' => false)
                ),
                self::ACTION_NAME => array(
                    'filter'  => FILTER_SANITIZE_STRING,
                    'flags'   => FILTER_REQUIRE_SCALAR | FILTER_FLAG_STRIP_HIGH,
                    'options' => array('default' => false)
                )
            ));

            if ($argsInput[self::AJAX_NAME] === false || $argsInput[self::ACTION_NAME] === false) {
                $argsInput[self::AJAX_NAME] = false;
            } else {
                $argsInput[self::AJAX_NAME] = in_array($argsInput[self::ACTION_NAME], self::ajaxActions());
            }
        }

        if ($argsInput[self::AJAX_NAME]) {
            $action = $argsInput[self::ACTION_NAME];
        }

        return $argsInput[self::AJAX_NAME];
    }

    protected static function dbTest()
    {
        require_once(DUPX_INIT.'/api/class.cpnl.ctrl.php');
        require_once(DUPX_INIT.'/ctrls/ctrl.s2.dbtest.php');

        $paramsManager = DUPX_Paramas_Manager::getInstance();
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_DB_VIEW_MODE, DUPX_Param_item_form::INPUT_POST);

        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_DB_CHARSET_FB, DUPX_Param_item_form::INPUT_POST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_DB_CHARSET_FB_VAL, DUPX_Param_item_form::INPUT_POST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_DB_COLLATE_FB, DUPX_Param_item_form::INPUT_POST);
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_DB_COLLATE_FB_VAL, DUPX_Param_item_form::INPUT_POST);

        switch ($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_VIEW_MODE)) {
            case 'basic':
                $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_DB_ACTION, DUPX_Param_item_form::INPUT_POST);
                $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_DB_HOST, DUPX_Param_item_form::INPUT_POST);
                $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_DB_NAME, DUPX_Param_item_form::INPUT_POST);
                $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_DB_USER, DUPX_Param_item_form::INPUT_POST);
                $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_DB_PASS, DUPX_Param_item_form::INPUT_POST);
                break;
            case 'cpnl':
                $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_CPNL_HOST, DUPX_Param_item_form::INPUT_POST);
                $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_CPNL_USER, DUPX_Param_item_form::INPUT_POST);
                $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_CPNL_PASS, DUPX_Param_item_form::INPUT_POST);
                $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_CPNL_DB_USER_CHK, DUPX_Param_item_form::INPUT_POST);
                $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_CPNL_PREFIX, DUPX_Param_item_form::INPUT_POST);

                $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_CPNL_DB_ACTION, DUPX_Param_item_form::INPUT_POST);
                $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_CPNL_DB_HOST, DUPX_Param_item_form::INPUT_POST);

                $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_CPNL_DB_NAME_SEL, DUPX_Param_item_form::INPUT_POST);
                $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_CPNL_DB_NAME_TXT, DUPX_Param_item_form::INPUT_POST);

                $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_CPNL_DB_USER_SEL, DUPX_Param_item_form::INPUT_POST);
                $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_CPNL_DB_USER_TXT, DUPX_Param_item_form::INPUT_POST);

                $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_CPNL_DB_PASS, DUPX_Param_item_form::INPUT_POST);

                // NORMALIZE VALUES FOR DB TEST
                $paramsManager->setValue(DUPX_Paramas_Manager::PARAM_DB_ACTION, $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_CPNL_DB_ACTION));
                // DBHOST
                $paramsManager->setValue(DUPX_Paramas_Manager::PARAM_DB_HOST, $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_CPNL_DB_HOST));

                $cpnlPrefix = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_CPNL_PREFIX);

                // DBNAME
                if ($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_CPNL_DB_ACTION) === 'create') {
                    // CREATE NEW DATABASE
                    $dbName = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_CPNL_DB_NAME_TXT);
                } else {
                    // GET EXISTS DATABASE
                    $dbName = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_CPNL_DB_NAME_SEL);
                }

                if (strpos($dbName, $cpnlPrefix) !== 0) {
                    $dbName = $cpnlPrefix.$dbName;
                }
                $paramsManager->setValue(DUPX_Paramas_Manager::PARAM_DB_NAME, $dbName);

                // DB USER
                if ($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_CPNL_DB_USER_CHK)) {
                    // CREATE NEW USER
                    $dbUser = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_CPNL_DB_USER_TXT);
                } else {
                    // GET EXIST USER
                    $dbUser = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_CPNL_DB_USER_SEL);
                }
                if (strpos($dbUser, $cpnlPrefix) !== 0) {
                    $dbUser = $cpnlPrefix.$dbUser;
                }
                $paramsManager->setValue(DUPX_Paramas_Manager::PARAM_DB_USER, $dbUser);

                //DBPASS
                $paramsManager->setValue(DUPX_Paramas_Manager::PARAM_DB_PASS, $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_CPNL_DB_PASS));
                break;
        }
        $paramsManager->setValueFromInput(DUPX_Paramas_Manager::PARAM_DB_COLLATE_FB, DUPX_Param_item_form::INPUT_POST);

        //INPUTS
        // add to param manager and remove from here
        $dbTestIn              = new DUPX_DBTestIn();
        $dbTestIn->mode        = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_VIEW_MODE);
        $dbTestIn->dbaction    = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_ACTION);
        $dbTestIn->dbhost      = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_HOST);
        $dbTestIn->dbname      = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_NAME);
        $dbTestIn->dbuser      = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_USER);
        $dbTestIn->dbpass      = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_PASS);
        $dbTestIn->dbport      = parse_url($dbTestIn->dbhost, PHP_URL_PORT);
        $dbTestIn->dbcharsetfb = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_CHARSET_FB);
        $dbTestIn->dbcollatefb = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_COLLATE_FB);
        $dbTestIn->cpnlHost    = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_CPNL_HOST);
        $dbTestIn->cpnlUser    = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_CPNL_USER);
        $dbTestIn->cpnlPass    = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_CPNL_PASS);
        $dbTestIn->cpnlNewUser = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_CPNL_DB_USER_CHK);

        $dbTest          = new DUPX_DBTest($dbTestIn);
        $dbTest->runMode = 'TEST';

        if ($dbTest->run()) {
            $paramsManager->setValue(DUPX_Paramas_Manager::PARAM_DB_TEST_OK, true);
        } else {
            $paramsManager->setValue(DUPX_Paramas_Manager::PARAM_DB_TEST_OK, false);
        }
        $paramsManager->save();

        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        die(DupProSnapJsonU::wp_json_encode($dbTest->getTestResponse()));
    }

    public static function ajaxActions()
    {
        static $actions = null;
        if (is_null($actions)) {
            $actions = array(
                self::ACTION_DATABASE_CHECK,
                self::ACTION_DAWN
            );
        }
        return $actions;
    }

    public static function getTokenKeyByAction($action)
    {
        return self::ACTION_NAME.$action;
    }

    public static function getTokenFromInput()
    {
        return filter_input(INPUT_POST, self::TOKEN_NAME, FILTER_SANITIZE_STRING, array('default' => false));
    }

    public static function generateToken($action)
    {
        return DUPX_CSRF::generate(self::getTokenKeyByAction($action));
    }
}