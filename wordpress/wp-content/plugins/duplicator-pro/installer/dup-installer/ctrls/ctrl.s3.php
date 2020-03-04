<?php
/**
 * controller step 3
 * 
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package CTRL
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

//-- START OF ACTION STEP 3: Update the database
require_once(DUPX_INIT.'/classes/config/class.archive.config.php');
require_once(DUPX_INIT.'/lib/config/class.wp.config.tranformer.php');
require_once(DUPX_INIT.'/lib/config/class.wp.config.tranformer.src.php');
require_once(DUPX_INIT.'/classes/utilities/class.u.multisite.php');
require_once(DUPX_INIT.'/classes/utilities/class.u.search.reaplce.manager.php');
require_once(DUPX_INIT.'/classes/Chunk/class.chunk.s3.manager.php');
require_once(DUPX_INIT.'/classes/class.s3.func.php');

/** JSON RESPONSE: Most sites have warnings turned off by default, but if they're turned on the warnings
  cause errors in the JSON data Here we hide the status so warning level is reset at it at the end */
// We have already removing warning from json resp
// It cause 500 internal server error so commenting out
/*
  $ajax3_error_level	 = error_reporting();
  error_reporting(E_ERROR);
 */
try {
    DUPX_Log::setThrowExceptionOnError(true);

    $nManager = DUPX_NOTICE_MANAGER::getInstance();
    $s3Func   = DUPX_S3_Funcs::getInstance();

    switch ($s3Func->getEngineMode()) {
        case DUPX_S3_Funcs::MODE_CHUNK:
            /** START CHUNK MANAGER */
            $maxIteration                  = 50;     // max iteration before stop. If 0 have no limit
            // auto set prevent timeout
            $inimaxExecutionTime           = ini_get('max_execution_time');
            $maxExecutionTime              = (int) (empty($inimaxExecutionTime) ? DUPX_Constants::CHUNK_MAX_TIMEOUT_TIME : $inimaxExecutionTime);
            $timeOut                       = max(5, $maxExecutionTime - 2) * 1000;    // timeout in milliseconds before stop exectution
            $throttling                    = 2;  // sleep in milliseconds every iteration
            $GLOBALS['DATABASE_PAGE_SIZE'] = 1000;   // database pagination size for engine update queries

            /* TEST INIT SINGLE FUNC
              $maxIteration                  = 1;     // max iteration before stop. If 0 have no limit
              $timeOut                       = 0;    // timeout in milliseconds before stop exectution
              $throttling                    = 0;  // sleep in milliseconds every iteration
              $GLOBALS['DATABASE_PAGE_SIZE'] = 1000000;   // database pagination size for engine update queries
             */

            $chunkmManager = new DUPX_chunkS3Manager($maxIteration, $timeOut, $throttling);
            if ($chunkmManager->start() === false) {
                /* Stop executions */
                $s3Func->chunkStop($chunkmManager->getProgressPerc(), $chunkmManager->getLastPosition());
            } else {
                /* step 3 completed */
                $s3Func->complete();
            }
            break;
        case DUPX_S3_Funcs::MODE_SKIP:
            $s3Func->initLog();

            $s3Func->removeMaincenanceMode();

            $s3Func->removeLicenseKey();
            $s3Func->createNewAdminUser();
            $s3Func->configurationFileUpdate();
            $s3Func->htaccessUpdate();
            $s3Func->generalCleanup();

            $s3Func->noticeTest();
            $s3Func->cleanupTmpFiles();
            $s3Func->finalReportNotices();
            $s3Func->complete();
            break;
        case DUPX_S3_Funcs::MODE_NORMAL:
        default:
            $s3Func->initLog();

            $s3Func->runSearchAndReplace();
            $s3Func->removeMaincenanceMode();

            $s3Func->removeLicenseKey();
            $s3Func->createNewAdminUser();
            $s3Func->configurationFileUpdate();
            $s3Func->htaccessUpdate();
            $s3Func->generalUpdate();
            $s3Func->generalCleanup();

            $s3Func->noticeTest();
            $s3Func->cleanupTmpFiles();
            $s3Func->finalReportNotices();
            $s3Func->complete();
    }

    $nManager->saveNotices();
}
catch (Exception $e) {
    $s3Func->error($e->getMessage());
}

$json = $s3Func->getJsonReport();
DUPX_Log::close();
die(DupProSnapJsonU::wp_json_encode($json));
