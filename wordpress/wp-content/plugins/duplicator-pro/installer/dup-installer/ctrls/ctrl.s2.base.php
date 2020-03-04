<?php
/**
 * controller step 2
 * 
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package CTRL
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

//-- START OF ACTION STEP 2
/** IDE HELPERS */
/* @var $GLOBALS['DUPX_AC'] DUPX_ArchiveConfig */

require_once(DUPX_INIT.'/api/class.cpnl.ctrl.php');

$paramsManager = DUPX_Paramas_Manager::getInstance();

if ($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_TEST_OK) == false) {
    throw new Exception('Database test not passed');
}

$ajax2_start = DUPX_U::getMicrotime();
$JSON        = array(
    'pass' => 0
);

/**
  JSON RESPONSE: Most sites have warnings turned off by default, but if they're turned on the warnings
  cause errors in the JSON data Here we hide the status so warning level is reset at it at the end */
$ajax2_error_level = error_reporting();
error_reporting(E_ERROR);
($GLOBALS['LOG_FILE_HANDLE'] != false) or DUPX_Log::error(ERR_MAKELOG);

$inputValues = array(
    'view_mode'        => $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_VIEW_MODE),
    'dbaction'         => $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_ACTION),
    'dbhost'           => $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_HOST),
    'dbname'           => $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_NAME),
    'dbuser'           => $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_USER),
    'dbpass'           => $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_PASS),
    'dbport'           => parse_url($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_HOST), PHP_URL_PORT),
    'dbchunk'          => $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_CHUNK),
    'dbnbsp'           => $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_SPACING),
    'dbmysqlmode'      => $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_MYSQL_MODE),
    'dbmysqlmode_opts' => $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_MYSQL_MODE_OPTS),
    'dbobj_views'      => $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_VIEW_CREATION),
    'dbobj_procs'      => $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_PROC_CREATION),
    'dbcharset'        => $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_CHARSET),
    'dbcharsetfb'      => $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_CHARSET_FB),
    'dbcharsetfb_val'  => $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_CHARSET_FB_VAL),
    'dbcollate'        => $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_COLLATE),
    'dbcollatefb'      => $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_COLLATE_FB),
    'dbcollatefb_val'  => $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_COLLATE_FB_VAL),
    'cpnl-host'        => $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_CPNL_HOST),
    'cpnl-user'        => $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_CPNL_USER),
    'cpnl-pass'        => $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_CPNL_PASS),
    'cpnl-dbuser-chk'  => $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_CPNL_DB_USER_CHK),
    'pos'              => (int) (isset($_POST['pos']) ? $_POST['pos'] : 0),
    'pass'             => (isset($_POST['pass']) && $_POST['pass']),
    'first_chunk'      => (isset($_POST['first_chunk']) && $_POST['first_chunk']),
    'progress'         => (isset($_POST['progress']) ? $_POST['progress'] : 0),
    'delimiter'        => (isset($_POST['delimiter']) ? $_POST['delimiter'] : ';'),
);

$not_yet_logged = (isset($_POST['first_chunk']) && $_POST['first_chunk']) || (!isset($_POST['continue_chunking']));

if ($not_yet_logged) {
    $labelPadSize = 20;
    DUPX_Log::info("\n\n\n********************************************************************************");
    DUPX_Log::info('* DUPLICATOR PRO INSTALL-LOG');
    DUPX_Log::info('* STEP-2 START @ '.@date('h:i:s'));
    DUPX_Log::info('* NOTICE: Do NOT post to public sites or forums!!');
    DUPX_Log::info("********************************************************************************");
    DUPX_Log::info("USER INPUTS");
    DUPX_Log::info(str_pad('VIEW MODE', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_VIEW_MODE)));
    DUPX_Log::info(str_pad('DB ACTION', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_ACTION)));
    DUPX_Log::info(str_pad('DB HOST', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString('**OBSCURED**'));
    DUPX_Log::info(str_pad('DB NAME', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString('**OBSCURED**'));
    DUPX_Log::info(str_pad('DB PASS', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString('**OBSCURED**'));
    DUPX_Log::info(str_pad('DB PORT', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString('**OBSCURED**'));
    DUPX_Log::info(str_pad('TABLE PREFIX', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_TABLE_PREFIX)));
    DUPX_Log::info(str_pad('MYSQL MODE', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_MYSQL_MODE)));
    DUPX_Log::info(str_pad('MYSQL MODE OPTS', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_MYSQL_MODE_OPTS)));
    DUPX_Log::info(str_pad('NON-BREAKING SPACES', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_SPACING)));
    DUPX_Log::info(str_pad('CHARSET', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_CHARSET)));
    DUPX_Log::info(str_pad('CHARSET FB', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_CHARSET_FB)));
    DUPX_Log::info(str_pad('CHARSET FB VAL', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_CHARSET_FB_VAL)));
    DUPX_Log::info(str_pad('COLLATE', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_COLLATE)));
    DUPX_Log::info(str_pad('COLLATE FB', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_COLLATE_FB)));
    DUPX_Log::info(str_pad('COLLATE FB VAL', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_COLLATE_FB_VAL)));
    DUPX_Log::info(str_pad('CUNKING', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_CHUNK)));
    DUPX_Log::info(str_pad('VIEW CREATION', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_VIEW_CREATION)));
    DUPX_Log::info(str_pad('STORED PROCEDURE', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_PROC_CREATION)));
    DUPX_Log::info("********************************************************************************\n");

    if (!empty($cpnllog)) {
        DUPX_Log::info($cpnllog);
    }

    $POST_LOG = $_POST;
    unset($POST_LOG['dbpass']);
    ksort($POST_LOG);
    $log      = "--------------------------------------\n";
    $log      .= "POST DATA\n";
    $log      .= "--------------------------------------\n";
    $log      .= print_r($POST_LOG, true);
    DUPX_Log::info($log, DUPX_Log::LV_DEBUG);
    DUPX_Log::flush();
}


//===============================================
//DATABASE ROUTINES
//===============================================
$dbinstall = new DUPX_DBInstall($inputValues, $ajax2_start);
if ($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_ACTION) != 'manual') {
    if (!isset($_POST['continue_chunking'])) {
        $dbinstall->prepareCpanel();
        $dbinstall->prepareDB();
    } else if (isset($_POST['first_chunk']) && $_POST['first_chunk'] == 1) {
        $dbchunk_retry = intval($_POST['dbchunk_retry']);
        if ($dbchunk_retry > 0) {
            DUPX_Log::info("## >> Last DB Chunk installation was failed, so retrying from start point. Retrying count: ".$dbchunk_retry);
        }

        if (file_exists($dbinstall->sql_chunk_seek_tell_log)) {
            unlink($dbinstall->sql_chunk_seek_tell_log);
        }

        $dbinstall->prepareCpanel();
        $dbinstall->prepareDB();
    }
}
if ($not_yet_logged) {

    //Fatal Memory errors from file_get_contents is not catchable.
    //Try to warn ahead of time with a check on buffer in memory difference
    $current_php_mem = DUPX_U::returnBytes($GLOBALS['PHP_MEMORY_LIMIT']);
    $current_php_mem = is_numeric($current_php_mem) ? $current_php_mem : null;

    if ($current_php_mem != null && $dbinstall->dbFileSize > $current_php_mem) {
        $readable_size = DUPX_U::readableByteSize($dbinstall->dbFileSize);
        $msg           = "\nWARNING: The database script is '{$readable_size}' in size.  The PHP memory allocation is set\n";
        $msg           .= "at '{$GLOBALS['PHP_MEMORY_LIMIT']}'.  There is a high possibility that the installer script will fail with\n";
        $msg           .= "a memory allocation error when trying to load the database.sql file.  It is\n";
        $msg           .= "recommended to increase the 'memory_limit' setting in the php.ini config file.\n";
        $msg           .= "see: {$faq_url}#faq-trouble-056-q \n";
        DUPX_Log::info($msg);
        unset($msg);
    }

    DUPX_Log::info("--------------------------------------");
    DUPX_Log::info("DATABASE RESULTS");
    DUPX_Log::info("--------------------------------------");
}

if ($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_ACTION) == 'manual') {

    DUPX_Log::info("\n** SQL EXECUTION IS IN MANUAL MODE **");
    DUPX_Log::info("- No SQL script has been executed -");
    $JSON['pass'] = 1;
} elseif (isset($_POST['continue_chunking']) && $_POST['continue_chunking'] === 'true') {
    $ret = $dbinstall->writeInChunks();
    echo json_encode($ret);
    die();
} elseif (isset($_POST['continue_chunking']) && ($_POST['continue_chunking'] === 'false' && $_POST['pass'] == 1)) {
    $rowCountMisMatchTables = $dbinstall->getRowCountMisMatchTables();
    $JSON['pass']           = 1;
    if (!empty($rowCountMisMatchTables)) {
        $nManager = DUPX_NOTICE_MANAGER::getInstance();
        $errMsg   = 'Database Table row count verification was failed for table(s): '
            .implode(', ', $rowCountMisMatchTables).'.';
        DUPX_Log::info($errMsg);
        $nManager->addNextStepNoticeMessage($errMsg, DUPX_NOTICE_ITEM::NOTICE);
        /*
          $nManager->addFinalReportNotice(array(
          'shortMsg' => 'Database Table row count validation failed',
          'level' => DUPX_NOTICE_ITEM::NOTICE,
          'longMsg' => $errMsg,
          'sections' => 'database'
          ));
         */
        $nManager->saveNotices();
    }
} elseif (!isset($_POST['continue_chunking'])) {
    $dbinstall->writeInDB();
    $rowCountMisMatchTables = $dbinstall->getRowCountMisMatchTables();
    $JSON['pass']           = 1;
    if (!empty($rowCountMisMatchTables)) {
        $nManager = DUPX_NOTICE_MANAGER::getInstance();
        $errMsg   = 'Database Table row count verification was failed for table(s): '
            .implode(', ', $rowCountMisMatchTables).'.';
        DUPX_Log::info($errMsg);
        $nManager->addNextStepNoticeMessage($errMsg, DUPX_NOTICE_ITEM::NOTICE);
        /*
          $nManager->addFinalReportNotice(array(
          'shortMsg' => 'Database Table row count was validation failed',
          'level' => DUPX_NOTICE_ITEM::NOTICE,
          'longMsg' => $errMsg,
          'sections' => 'database'
          ));
         */
        $nManager->saveNotices();
    }
}

$dbinstall->runCleanupRotines();

$dbinstall->profile_end = DUPX_U::getMicrotime();
$dbinstall->writeLog();
$JSON                   = $dbinstall->getJSON($JSON);

DUPX_Plugins_Manager::getInstance()->preViewChecks();

//FINAL RESULTS
$ajax1_sum = DUPX_U::elapsedTime(DUPX_U::getMicrotime(), $dbinstall->start_microtime);
DUPX_Log::info("\nINSERT DATA RUNTIME: ".DUPX_U::elapsedTime($dbinstall->profile_end, $dbinstall->profile_start));
DUPX_Log::info('STEP-2 COMPLETE @ '.@date('h:i:s')." - RUNTIME: {$ajax1_sum}");



error_reporting($ajax2_error_level);
DUPX_Log::close();
die(DupProSnapJsonU::wp_json_encode($JSON));
