<?php
/**
 * controller base
 * 
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package CTRL
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/** IDE HELPERS */
/* @var $GLOBALS['DUPX_AC'] DUPX_ArchiveConfig */

/**
 * Base controller class for installer controllers
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\CTRL\Base
 *
 */
//Enum used to define the various test statues 
final class DUPX_CTRL_Status
{

    const FAILED  = 0;
    const SUCCESS = 1;

}

/**
 * A class structer used to report on controller methods
 *
 * @package Dupicator\ctrls\
 */
class DUPX_CTRL_Report
{

    //Properties
    public $runTime;
    public $outputType = 'JSON';
    public $status;

}

/**
 * Base class for all controllers
 * 
 * @package Dupicator\ctrls\
 */
class DUPX_CTRL_Out
{

    public $report  = null;
    public $payload = null;
    private $timeStart;
    private $timeEnd;

    /**
     *  Init this instance of the object
     */
    public function __construct()
    {
        $this->report  = new DUPX_CTRL_Report();
        $this->payload = null;
        $this->startProcessTime();
    }

    public function startProcessTime()
    {
        $this->timeStart = $this->microtimeFloat();
    }

    public function getProcessTime()
    {
        $this->timeEnd         = $this->microtimeFloat();
        $this->report->runTime = $this->timeEnd - $this->timeStart;
        return $this->report->runTime;
    }

    private function microtimeFloat()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float) $usec + (float) $sec);
    }
}

class DUPX_CTRL
{

    const NAME_MAX_SERIALIZE_STRLEN_IN_M = 'mstrlim';

    public static function mainController()
    {
        $paramsManager   = DUPX_Paramas_Manager::getInstance();
        $paramCtrlAction = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_CTRL_ACTION);

        if (!empty($paramCtrlAction)) {
            DUPX_Log::info("\n".'---------------', DUPX_Log::LV_DETAILED);
            DUPX_Log::info('CONTROLLER: '.DUPX_Log::varToString($paramCtrlAction), DUPX_Log::LV_DETAILED);
            DUPX_Log::info('---------------'."\n", DUPX_Log::LV_DETAILED);

            // LOAD PARAMS
            $paramsOk = false;
            switch ($paramCtrlAction) {
                case "ctrl-step0" :
                    $paramsOk = DUPX_Ctrl_Params::setParamsStep0();
                    break;
                case "ctrl-step1" :
                    $paramsOk = DUPX_Ctrl_Params::setParamsStep1();
                    break;
                case "ctrl-step2" :
                    $paramsOk = DUPX_Ctrl_Params::setParamsStep2();
                    break;
                case "ctrl-step3" :
                    $paramsOk = true;
                    break;
                default:
                    DUPX_Log::error('No valid action request '.$paramCtrlAction);
            }
            DUPX_NOTICE_MANAGER::getInstance()->saveNotices();
            DUPX_Log::logTime('CONTROLLER PARAMS READ', DUPX_Log::LV_DETAILED);

            // IF PARAMS AREN'T VALIDATED DON'T EXECUTE CONTROLLERS
            if (!$paramsOk) {
                DUPX_Log::info('PARAMS AREN\'T VALID, GO BACK TO PREVIOUS STEP', DUPX_Log::LV_DETAILED);
                return;
            }

            switch ($paramCtrlAction) {
                case "ctrl-step0" :
                    require_once(DUPX_INIT.'/ctrls/ctrl.s0.php');
                    if ($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_STEP_ACTION) !== 'revalidate' && $GLOBALS['DUPX_AC']->secure_on) {
                        $password = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_SECURE_PASS);
                        if (!DUPX_Security::passwordArciveCheck($password)) {
                            $paramsManager->setValue(DUPX_Paramas_Manager::PARAM_VIEW, 'secure');
                        } else {
                            $paramsManager->setValue(DUPX_Paramas_Manager::PARAM_VIEW, 'step1');
                        }
                    } else {
                        $paramsManager->setValue(DUPX_Paramas_Manager::PARAM_VIEW, 'step1');
                    }
                    break;
                case "ctrl-step1" :
                    require_once(DUPX_INIT.'/ctrls/ctrl.s1.php');
                    break;
                case "ctrl-step2" :
                    require_once(DUPX_INIT.'/ctrls/ctrl.s2.dbinstall.php');
                    require_once(DUPX_INIT.'/ctrls/ctrl.s2.base.php');
                    break;
                case "ctrl-step3" :
                    require_once(DUPX_INIT.'/ctrls/ctrl.s3.php');
                    break;
                default:
                    DUPX_Log::error('No valid action request '.$paramCtrlAction);
            }
        }
    }
}