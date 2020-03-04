<?php
/**
 * controller step 2 db install test
 * 
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package CTRL
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

class DUPX_DBInstall
{
    private $dbh = null;
    private $post;
    public $dbaction = null;
    public $sql_result_data;
    public $sql_result_data_length;
    public $dbvar_maxtime;
    public $dbvar_maxpacks;
    public $dbvar_sqlmode;
    public $dbvar_version;
    public $pos_in_sql;
    public $sql_file_path;
    public $php_mem;
    public $php_mem_range;
    public $table_count;
    public $table_rows;
    public $query_errs;
    public $drop_tbl_log;
    public $rename_tbl_log;
    public $dbquery_errs;
    public $dbquery_rows;
    public $dbtable_count;
    public $dbtable_rows;
    public $dbdelete_count;
    public $profile_start;
    public $profile_end;
    public $start_microtime;
    public $dbcharsetfb;
    public $dbcharsetfbval;
    public $dbcollatefb;
    public $dbcollatefbval;
    public $dbobj_views;
    public $dbobj_procs;
    public $dbchunk;
    public $supportedCollateList;
    public $supportedCharSetList;
    public $dbFileSize = 0;
    private $threadTimeOut = 10;

    public function __construct($inputValues, $start_microtime)
    {
        $this->post                    = $inputValues;
        $this->php_mem                 = $GLOBALS['PHP_MEMORY_LIMIT'];
        $this->sql_file_path           = DUPX_INIT."/dup-database__{$GLOBALS['DUPX_AC']->package_hash}.sql";
        $this->sql_chunk_seek_tell_log = DUPX_INIT."/dup-database-seek-tell-log__{$GLOBALS['DUPX_AC']->package_hash}.txt";
        $this->dbFileSize              = @filesize($this->sql_file_path);
        $this->dbFileSize              = ($this->dbFileSize === false) ? 0 : $this->dbFileSize;

        $this->dbaction = $inputValues['dbaction'];
        
        $this->profile_start   = isset($inputValues['profile_start']) ? DUPX_U::sanitize_text_field($inputValues['profile_start']) : DUPX_U::getMicrotime();
        $this->start_microtime = isset($inputValues['start_microtime']) ? DUPX_U::sanitize_text_field($inputValues['start_microtime']) : $start_microtime;
        $this->thread_start_time = microtime(true);
        $this->dbvar_maxtime   = is_null($this->dbvar_maxtime) ? 300 : $this->dbvar_maxtime;
        $this->dbvar_maxpacks  = is_null($this->dbvar_maxpacks) ? 1048576 : $this->dbvar_maxpacks;
        $this->dbvar_sqlmode   = empty($this->dbvar_sqlmode) ? 'NOT_SET' : $this->dbvar_sqlmode;
        $this->dbquery_errs    = isset($inputValues['dbquery_errs']) ? DUPX_U::sanitize_text_field($inputValues['dbquery_errs']) : 0;
        $this->drop_tbl_log    = isset($inputValues['drop_tbl_log']) ? DUPX_U::sanitize_text_field($inputValues['drop_tbl_log']) : 0;
        $this->rename_tbl_log  = isset($inputValues['rename_tbl_log']) ? DUPX_U::sanitize_text_field($inputValues['rename_tbl_log']) : 0;
        $this->dbquery_rows    = isset($inputValues['dbquery_rows']) ? DUPX_U::sanitize_text_field($inputValues['dbquery_rows']) : 0;
        $this->dbdelete_count  = isset($inputValues['dbdelete_count']) ? DUPX_U::sanitize_text_field($inputValues['dbdelete_count']) : 0;
        $this->dbcharset         = $inputValues[DUPX_Paramas_Manager::PARAM_DB_CHARSET];
        $this->dbcollate         = $inputValues[DUPX_Paramas_Manager::PARAM_DB_COLLATE];
        $this->dbcharsetfb       = $inputValues[DUPX_Paramas_Manager::PARAM_DB_CHARSET_FB];
        $this->dbcharsetfbval    = $inputValues[DUPX_Paramas_Manager::PARAM_DB_CHARSET_FB_VAL];
        $this->dbcollatefb       = $inputValues[DUPX_Paramas_Manager::PARAM_DB_COLLATE_FB];
        $this->dbcollatefbval    = $inputValues[DUPX_Paramas_Manager::PARAM_DB_COLLATE_FB_VAL];
        $this->dbobj_views       = $inputValues[DUPX_Paramas_Manager::PARAM_DB_VIEW_CREATION];
        $this->dbobj_procs       = $inputValues[DUPX_Paramas_Manager::PARAM_DB_PROC_CREATION];
        $this->dbchunk           = $inputValues[DUPX_Paramas_Manager::PARAM_DB_CHUNK];
    }

    public function prepareCpanel()
    {
        //===============================================
        //CPANEL LOGIC: From Postback
        //===============================================
        $cpnllog = "";

        if ($this->post['view_mode'] == 'cpnl') {
            try {
                $cpnllog = "--------------------------------------\n";
                $cpnllog .= "CPANEL API\n";
                $cpnllog .= "--------------------------------------\n";

                $CPNL = new DUPX_cPanel_Controller();

                $cpnlToken = $CPNL->create_token($this->post['cpnl-host'], $this->post['cpnl-user'], $this->post['cpnl-pass']);
                $cpnlHost  = $CPNL->connect($cpnlToken);

                //CREATE DB USER: Attempt to create user should happen first in the case that the
                //user passwords requirements are not met.
                if ($this->post['cpnl-dbuser-chk']) {
                    $result = $CPNL->create_db_user($cpnlToken, $this->post['dbuser'], $this->post['dbpass']);
                    if ($result['status'] !== true) {
                        DUPX_Log::info('CPANEL API ERROR: create_db_user '.print_r($result['cpnl_api'], true), 2);
                        DUPX_Log::error(sprintf(ERR_CPNL_API, $result['status']));
                    } else {
                        $cpnllog .= "- A new database user was created\n";
                    }
                }

                //CREATE NEW DB
                if ($this->post['dbaction'] == 'create') {
                    $result = $CPNL->create_db($cpnlToken, $this->post['dbname']);
                    if ($result['status'] !== true) {
                        DUPX_Log::info('CPANEL API ERROR: create_db '.print_r($result['cpnl_api'], true), 2);
                        DUPX_Log::error(sprintf(ERR_CPNL_API, $result['status']));
                    } else {
                        $cpnllog .= "- A new database was created\n";
                    }
                } else {
                    $cpnllog .= "- Used to connect to existing database named [{$post_db_name}]\n";
                }

                //ASSIGN USER TO DB IF NOT ASSIGNED
                $result = $CPNL->is_user_in_db($cpnlToken, $this->post['dbname'], $this->post['dbuser']);
                if (!$result['status']) {
                    $result = $CPNL->assign_db_user($cpnlToken, $this->post['dbname'], $this->post['dbuser']);
                    if ($result['status'] !== true) {
                        DUPX_Log::info('CPANEL API ERROR: assign_db_user '.print_r($result['cpnl_api'], true), 2);
                        DUPX_Log::error(sprintf(ERR_CPNL_API, $result['status']));
                    } else {
                        $cpnllog .= "- Database user was assigned to database";
                    }
                }
            }
            catch (Exception $ex) {
                DUPX_Log::error($ex);
            }
        }
    }
    
    /**
     * execute a connection if db isn't connected
     * 
     * @return resource
     */
    protected function dbConnect()
    {
        if (is_null($this->dbh)) {
            //ESTABLISH CONNECTION
            if (($this->dbh = DUPX_DB::connect($this->post['dbhost'], $this->post['dbuser'], $this->post['dbpass'])) == false) {
                $this->dbh = null;
                DUPX_Log::error(ERR_DBCONNECT.mysqli_connect_error());
            }

            // EXEC ALWAYS A DB SELECT is required when chunking is activated
            if (mysqli_select_db($this->dbh, mysqli_real_escape_string($this->dbh, $this->post['dbname'])) == false) {
                if ($this->post['dbaction'] == 'empty' || $this->post['dbaction'] == 'rename') {
                    DUPX_Log::error(sprintf(ERR_DBCREATE, $this->post['dbname']));
                } else {
                    DUPX_Log::info('CAN\'T SELECT DATABASE '.$this->post['dbname'].' is ok for action '.$this->post['dbaction']);
                }
            }

            DUPX_DB::mysqli_query($this->dbh, "SET wait_timeout = ".mysqli_real_escape_string($this->dbh, $GLOBALS['DB_MAX_TIME']));
            DUPX_DB::mysqli_query($this->dbh, "SET GLOBAL max_allowed_packet = ".mysqli_real_escape_string($this->dbh, $GLOBALS['DB_MAX_PACKETS']), DUPX_Log::LV_DEBUG);
            DUPX_DB::mysqli_query($this->dbh, "SET max_allowed_packet = ".mysqli_real_escape_string($this->dbh, $GLOBALS['DB_MAX_PACKETS']), DUPX_Log::LV_DEBUG);

            $this->dbvar_maxtime  = DUPX_DB::getVariable($this->dbh, 'wait_timeout');
            $this->dbvar_maxpacks = DUPX_DB::getVariable($this->dbh, 'max_allowed_packet');
            $this->dbvar_sqlmode  = DUPX_DB::getVariable($this->dbh, 'sql_mode');
            $this->dbvar_version  = DUPX_DB::getVersion($this->dbh);

            $this->supportedCollateList = DUPX_DB::getSupportedCollateList($this->dbh);
            $this->supportedCharSetList = DUPX_DB::getSupportedCharSetList($this->dbh);
        }
        return $this->dbh;
    }

    public function prepareDB()
    {
        $this->dbConnect();
        
        DUPX_DB::setCharset($this->dbh, $this->dbcharset, $this->dbcollate);
		$this->setSQLSessionMode();

        //Set defaults incase the variable could not be read
        $this->drop_tbl_log   = 0;
        $this->rename_tbl_log = 0;
        $sql_file_size1       = DUPX_U::readableByteSize(@filesize(DUPX_INIT."/dup-database__{$GLOBALS['DUPX_AC']->package_hash}.sql"));
        $collate_fb           = $this->dbcollatefb ? 'On' : 'Off';

        DUPX_Log::info("--------------------------------------");
        DUPX_Log::info('DATABASE-ENVIRONMENT');
        DUPX_Log::info("--------------------------------------");
        DUPX_Log::info("MYSQL VERSION:\tThis Server: {$this->dbvar_version} -- Build Server: {$GLOBALS['DUPX_AC']->version_db}");
        DUPX_Log::info("FILE SIZE:\tdup-database__{$GLOBALS['DUPX_AC']->package_hash}.sql ({$sql_file_size1})");
        DUPX_Log::info("TIMEOUT:\t{$this->dbvar_maxtime}");
        DUPX_Log::info("MAXPACK:\t{$this->dbvar_maxpacks}");
        DUPX_Log::info("SQLMODE-GLOBAL:\t{$this->dbvar_sqlmode}");
		DUPX_Log::info("SQLMODE-SESSION:" . ($this->getSQLSessionMode()));
        DUPX_Log::info("COLLATE FB:\t{$collate_fb}");
		DUPX_Log::info("DB CHUNKING:\t"	  . ($this->dbchunk	    ? 'enabled' : 'disabled'));
		DUPX_Log::info("DB VIEWS:\t"	  . ($this->dbobj_views ? 'enabled' : 'disabled'));
        DUPX_Log::info("DB PROCEDURES:\t" . ($this->dbobj_procs ? 'enabled' : 'disabled'));

        if (version_compare($this->dbvar_version, $GLOBALS['DUPX_AC']->version_db) < 0) {
            DUPX_Log::info("\nNOTICE: This servers version [{$this->dbvar_version}] is less than the build version [{$GLOBALS['DUPX_AC']->version_db}].  \n"
                ."If you find issues after testing your site please referr to this FAQ item.\n"
                ."https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-260-q");
        }

        //CREATE DB
        switch ($this->dbaction) {
            case "create":
                if ($this->post['view_mode'] == 'basic') {
                    DUPX_DB::mysqli_query($this->dbh, "CREATE DATABASE IF NOT EXISTS `".mysqli_real_escape_string($this->dbh, $this->post['dbname'])."`");
                }
                if (mysqli_select_db($this->dbh, mysqli_real_escape_string($this->dbh, $this->post['dbname'])) == false) {
                    DUPX_Log::error(sprintf(ERR_DBCONNECT_CREATE, $this->post['dbname']));
                }
                break;

            //DROP DB TABLES:  DROP TABLE statement does not support views
            case "empty":
                //Drop all tables, views and procs
                $this->dropTables();
                $this->dropViews();
                $this->dropProcs();
                break;

            //RENAME DB TABLES
            case "rename" :
                $sql          = "SHOW TABLES FROM `{$this->post['dbname']}` WHERE  `Tables_in_{$this->post['dbname']}` NOT LIKE '{$GLOBALS['DB_RENAME_PREFIX']}%'";
                $found_tables = null;
                if ($result       = DUPX_DB::mysqli_query($this->dbh, $sql)) {
                    while ($row = mysqli_fetch_row($result)) {
                        $found_tables[] = $row[0];
                    }
                    if (count($found_tables) > 0) {
                        foreach ($found_tables as $table_name) {
                            $sql    = "RENAME TABLE `".mysqli_real_escape_string($this->dbh, $this->post['dbname'])."`.`".mysqli_real_escape_string($this->dbh, $table_name)."` TO  `".mysqli_real_escape_string($this->dbh, $this->post['dbname'])."`.`".mysqli_real_escape_string($this->dbh, $GLOBALS['DB_RENAME_PREFIX']).mysqli_real_escape_string($this->dbh, $table_name)."`";
                            if (!$result = DUPX_DB::mysqli_query($this->dbh, $sql)) {
                                DUPX_Log::error(sprintf(ERR_DBTRYRENAME, "{$this->post['dbname']}.{$table_name}"));
                            }
                        }
                        $this->rename_tbl_log = count($found_tables);
                    }
                }
                break;
        }
    }

    public function writeInChunks() {
        DUPX_Log::info("--------------------------------------");
        DUPX_Log::info("** DATABASE CHUNK install start");
        DUPX_Log::info("--------------------------------------");
        $this->dbConnect();
        
        if (isset($this->post['dbchunk_retry']) && $this->post['dbchunk_retry'] > 0) {
            DUPX_Log::info("DATABASE CHUNK RETRY COUNT: ".DUPX_Log::varToString($this->post['dbchunk_retry']));
        }

        if (!empty($this->post['delimiter'])) {
            $delimiter = $this->post['delimiter'];
        } else {
            $delimiter = ';';
        }

        $handle = fopen($this->sql_file_path, 'rb');
       	if ($handle === false) {
            return false;
        }

        DUPX_Log::info("DATABASE CHUNK SEEK POSITION: ".DUPX_Log::varToString($this->post['pos']));

        if (-1 !== fseek($handle, $this->post['pos'])) {
            DUPX_DB::setCharset($this->dbh, $this->dbcharset, $this->dbcollate);

			$this->setSQLSessionMode();

            $elapsed_time = (microtime(true) - $this->thread_start_time);
            if ($elapsed_time < $this->threadTimeOut) {
                DUPX_Log::info('DATABASE CHUNK START POS:'.DUPX_Log::varToString($this->post['pos']), DUPX_Log::LV_DETAILED);

                if (!mysqli_ping($this->dbh)) {
                    mysqli_close($this->dbh);
                    $this->dbh = DUPX_DB::connect($this->post['dbhost'], $this->post['dbuser'], $this->post['dbpass'], $this->post['dbname']);
                    // Reset session setup
                    DUPX_DB::mysqli_query($this->dbh, "SET wait_timeout = ".mysqli_real_escape_string($this->dbh, $GLOBALS['DB_MAX_TIME']));
                    DUPX_DB::setCharset($this->dbh, $this->dbcharset, $this->dbcollate);
                }
                if (@mysqli_autocommit($this->dbh, false)) {
                    DUPX_Log::info('Auto Commit set to false successfully');
                } else {
                    DUPX_Log::info('Failed to set Auto Commit to false');
                }

                DUPX_Log::info("DATABASE CHUNK: Iterating query loop", DUPX_Log::LV_DEBUG);
                $query = null;
                while (($line  = fgets($handle)) !== false) {
                    if ('DELIMITER ;' == trim($query)) {
                        $delimiter = ';';
                        $query     = null;
                        continue;
                    }
                    $query .= $line;

                    if (preg_match('/'.$delimiter.'\s*$/S', $query)) {
                        // Temp: Uncomment this to randomly kill the php db process to simulate real world hosts and verify system recovers properly
                        /*
                          $rand_no = rand(0, 500);
                          if (0 == $this->post['dbchunk_retry'] && 1 == $rand_no) {
                          DUPX_Log::info("intentionally killing db chunk installation process");
                          error_log('intentionally killing db chunk installation process');
                          exit(1);
                          }
                         */

                        $query = trim($query);
                        if (0 === strpos($query, "DELIMITER")) {
                            // Ending delimiter
                            // control never comes in this if condition, but written
                            if ('DELIMITER ;' == $query) {
                                $delimiter = ';';
                            } else { // starting delimiter
                                $delimiter = substr($query, 10);
                                $delimiter = trim($delimiter);
                            }

                            DUPX_Log::info("Skipping delimiter query");
                            $query = null;
                            continue;
                        }

                        //CHANGE TABLE PREFIX *****
                        $query = $this->updateTableNamesWithNewTablePrefix($query);
                        
                        $this->writeQueryInDB($query);

                        $elapsed_time = (microtime(true) - $this->thread_start_time);
                        if (DUPX_Log::isLevel(DUPX_Log::LV_DEBUG)) {
                            DUPX_Log::info("DATABASE CHUNK: Elapsed time: ".DUPX_Log::varToString($elapsed_time), DUPX_Log::LV_DEBUG);
                            if ($elapsed_time > $this->threadTimeOut) {
                                DUPX_Log::info("DATABASE CHUNK: Breaking query loop.", DUPX_Log::LV_DEBUG);
                            } else {
                                DUPX_Log::info("DATABASE CHUNK: Not Breaking query loop", DUPX_Log::LV_HARD_DEBUG);
                            }
                        }
                        if ($elapsed_time > $this->threadTimeOut) {
                            break;
                        }
                        $query = null;
                    }
                }
                if (@mysqli_autocommit($this->dbh, true)) {
                    DUPX_Log::info('Auto Commit set to true successfully');
                } else {
                    DUPX_Log::info('Failed to set Auto Commit to true');
                }
            } else {
                DUPX_Log::info("DATABASE CHUNK: Skipping query loop because already out of time. Elapsed time: ".DUPX_Log::varToString($elapsed_time), DUPX_Log::LV_DEBUG);
                $query_offset = ftell($handle);
            }

            $query_offset = ftell($handle);
			$progress = ceil($query_offset / $this->dbFileSize * 100);

            $json['profile_start']   = $this->profile_start;
            $json['start_microtime'] = $this->start_microtime;
            $json['delimiter'] = $delimiter;
            $json['dbquery_errs']    = $this->dbquery_errs;
            $json['drop_tbl_log']    = $this->drop_tbl_log;
            $json['dbquery_rows']    = $this->dbquery_rows;
            $json['rename_tbl_log']  = $this->rename_tbl_log;
            $json['dbdelete_count']  = $this->dbdelete_count;
            $json['progress']		 = $progress;
            $json['pos']             = $query_offset;

            $seek_tell_log_line = (
                    file_exists($this->sql_chunk_seek_tell_log)
                    &&
                    filesize($this->sql_chunk_seek_tell_log) > 0
                ) ? ',' : '';
            $seek_tell_log_line .= $this->post['pos'].'-'.$query_offset;
            file_put_contents($this->sql_chunk_seek_tell_log, $seek_tell_log_line, FILE_APPEND);

            if (feof($handle)) {
                // ensure integrity
                $is_okay = true;
                $seek_tell_log = file_get_contents($this->sql_chunk_seek_tell_log);
                $seek_tell_log_explodes = explode(',', $seek_tell_log);
                $is_okay = true;
                $last_start = 0;
                $last_end = 0;
                foreach ($seek_tell_log_explodes as $seek_tell_log_explode) {
                    $temp_arr = explode('-', $seek_tell_log_explode);
                    if (is_array($temp_arr) && 2 == count($temp_arr)) {
                        $start = $temp_arr[0];
                        $end = $temp_arr[1];
                        if ($start != $last_end) {
                            $is_okay = false;
                            break;    
                        }
                        if ($last_start > $end) {
                            $is_okay = false;
                            break;
                        }

                        $last_start = $start;
                        $last_end = $end;
                    } else {
                        $is_okay = false;
                        break;
                    }
                }
                if ($is_okay) {
                    $expected_file_size = $last_end;
                    $actual_file_size = filesize($this->sql_file_path);
                    if ($expected_file_size != $actual_file_size) {
                        $is_okay = false;
                    }
                }

                if ($is_okay) {
                    DUPX_Log::info('DATABASE CHUNK: DB install chunk process integrity check has been just passed successfully.', DUPX_Log::LV_DETAILED);
                    $json['pass']              = 1;
                    $json['continue_chunking'] = false;
                } else {
                    DUPX_Log::info('DB install chunk process integrity check has been just failed.');
                    $json['is_error'] = 1;
                    $json['error_msg'] = 'DB install chunk process integrity check has been just failed.';
                }
            } else {
                $json['pass']              = 0;
                $json['continue_chunking'] = true;
            }
        }
        DUPX_Log::info("DATABASE CHUNK: End Query offset ".DUPX_Log::varToString($query_offset), DUPX_Log::LV_DETAILED);
        
        if ($json['pass']) {
            DUPX_Log::info('DATABASE CHUNK: This is last chunk', DUPX_Log::LV_DETAILED);
        }

        fclose($handle);

        DUPX_Log::info("--------------------------------------");
        DUPX_Log::info("** DATABASE CHUNK install end");
        DUPX_Log::info("--------------------------------------");

        ob_flush();
        flush();

        return $json;
    }
    
    public static function updateTableNamesWithNewTablePrefix($query)
    {
        static $sreachRegEx  = null;
        static $replaceRegEx = null;

        if (is_null($sreachRegEx)) {
            $oldPrefix = DUPX_ArchiveConfig::getInstance()->wp_tableprefix;
            $newPrefix = DUPX_Paramas_Manager::getInstance()->getValue(DUPX_Paramas_Manager::PARAM_DB_TABLE_PREFIX);
            if ($oldPrefix === $newPrefix) {
                DUPX_LOG::info('NEW TABLE PREFIX NOT CHANGED', DUPX_Log::LV_DETAILED);
                $sreachRegEx  = false;
                $replaceRegEx = false;
            } else {
                DUPX_LOG::info('NEW TABLE PREFIX CHANGED', DUPX_Log::LV_DETAILED);
                $tables              = (array) DUPX_ArchiveConfig::getInstance()->dbInfo->tablesList;
                $tablesWithoutPrefix = array();
                $oldPrefixLen        = strlen($oldPrefix);
                foreach ($tables as $table => $tableInfo) {
                    if (strpos($table, $oldPrefix) === 0) {
                        $tablesWithoutPrefix[] = preg_quote(substr($table, $oldPrefixLen), '/');
                    } else {
                        DUPX_LOG::info('The table '.$table.' don\'t have prefix so the prefix can\'t be changed', DUPX_Log::LV_DETAILED);
                        DUPX_NOTICE_MANAGER::getInstance()->addBothNextAndFinalReportNotice(array(
                            'shortMsg' => 'The table '.$table.' don\'t have prefix so the prefix can\'t be changed',
                            'level'    => DUPX_NOTICE_ITEM::CRITICAL,
                            'sections' => 'database'
                        ));
                    }
                }
                $sreachRegEx  = '/'.$oldPrefix.'('.implode('|',$tablesWithoutPrefix).')/m';
                $replaceRegEx = DUPX_Paramas_Manager::getInstance()->getValue(DUPX_Paramas_Manager::PARAM_DB_TABLE_PREFIX).'$1';
                
                DUPX_LOG::info('TABLE PREFIX REGEX SEARCH:'.DUPX_Log::varToString($sreachRegEx), DUPX_Log::LV_DEFAULT);
                DUPX_LOG::info('TABLE PREFIX REGEX REPLACE:'.DUPX_Log::varToString($replaceRegEx), DUPX_Log::LV_DEFAULT);
            }
        }

        if ($sreachRegEx === false) {
            return $query;
        } else {
            return preg_replace($sreachRegEx, $replaceRegEx, $query);
        }
    }

    public function getRowCountMisMatchTables()
    {
        $nManager      = DUPX_NOTICE_MANAGER::getInstance();
        $archiveConfig = DUPX_ArchiveConfig::getInstance();

        $this->dbConnect();

        if (is_null($this->dbh)) {
            $errorMsg = "**ERROR** database DBH is null";
            $this->dbquery_errs++;
            $nManager->addBothNextAndFinalReportNotice(array(
                'shortMsg' => $errorMsg,
                'level'    => DUPX_NOTICE_ITEM::CRITICAL,
                'sections' => 'database'
                ), DUPX_NOTICE_MANAGER::ADD_UNIQUE, 'query-dbh-null');
            DUPX_Log::info($errorMsg);
            $nManager->saveNotices();
            return false;
        }

        $tableWiseRowCounts = $GLOBALS['DUPX_AC']->dbInfo->tableWiseRowCounts;
        $tablePrefix        = DUPX_Paramas_Manager::getInstance()->getValue(DUPX_Paramas_Manager::PARAM_DB_TABLE_PREFIX);
        $skipTables         = array(
            $tablePrefix."duplicator_packages",
            $tablePrefix."options",
            $tablePrefix."duplicator_pro_packages",
            $tablePrefix."duplicator_pro_entities",
        );
        $misMatchTables     = array();
        foreach ($tableWiseRowCounts as $table => $rowCount) {
            $table = $archiveConfig->getTableWithNewPrefix($table);
            if (in_array($table, $skipTables)) {
                continue;
            }
            $sql    = "SELECT count(*) as cnt FROM `".mysqli_real_escape_string($this->dbh, $table)."`";
            $result = DUPX_DB::mysqli_query($this->dbh, $sql);
            if (false !== $result) {
                $row = mysqli_fetch_assoc($result);
                if ($rowCount != ($row['cnt'])) {
                    $errMsg           = 'DATABASE: table '.DUPX_Log::varToString($table).' row count mismatch; expected '.DUPX_Log::varToString($rowCount).' in database'.DUPX_Log::varToString($row['cnt']);
                    DUPX_Log::info($errMsg);
                    $nManager->addBothNextAndFinalReportNotice(array(
                        'shortMsg' => 'Database Table row count validation was failed',
                        'level'    => DUPX_NOTICE_ITEM::NOTICE,
                        'longMsg'  => $errMsg."\n",
                        'sections' => 'database'
                        ), DUPX_NOTICE_MANAGER::ADD_UNIQUE_APPEND, 'row-count-mismatch');
                    $misMatchTables[] = $table;
                }
            }
        }
        return $misMatchTables;
    }

    public function writeInDB()
    {
        //WRITE DATA
        $fcgi_buffer_pool  = 5000;
        $fcgi_buffer_count = 0;
        $counter           = 0;
        
        $this->dbConnect();

        $handle = fopen($this->sql_file_path, 'rb');
        if ($handle === false) {
            return false;
        }
        $paramsManager = DUPX_Paramas_Manager::getInstance();
        $nManager = DUPX_NOTICE_MANAGER::getInstance();
        if (is_null($this->dbh)) {
            $errorMsg = "**ERROR** database DBH is null";
            $this->dbquery_errs++;
            $nManager->addNextStepNoticeMessage($errorMsg , DUPX_NOTICE_ITEM::CRITICAL , DUPX_NOTICE_MANAGER::ADD_UNIQUE , 'query-dbh-null');
            $nManager->addFinalReportNotice(array(
                    'shortMsg' => $errorMsg,
                    'level' => DUPX_NOTICE_ITEM::CRITICAL,
                    'sections' => 'database'
            ), DUPX_NOTICE_MANAGER::ADD_UNIQUE , 'query-dbh-null');
            DUPX_Log::info($errorMsg);
            $nManager->saveNotices();
            return;
        }
        
        @mysqli_autocommit($this->dbh, false);
        
        $query = null;
        $delimiter = ';';
        while (($line = fgets($handle)) !== false) {
            if ('DELIMITER ;' == trim($query)) {
                $delimiter = ';';
                $query = null;
                continue;
            }
            $query .= $line;
            if (preg_match('/'.$delimiter.'\s*$/S', $query)) {
                $query_strlen = strlen(trim($query));
                if ($this->dbvar_maxpacks < $query_strlen) {
                    $errorMsg = "**ERROR** Query size limit [length={$this->dbvar_maxpacks}] [sql=".substr($this->sql_result_data[$counter], 0, 75)."...]";
                    $this->dbquery_errs++;
                    $nManager->addNextStepNoticeMessage('QUERY ERROR: size limit' , DUPX_NOTICE_ITEM::SOFT_WARNING , DUPX_NOTICE_MANAGER::ADD_UNIQUE , 'query-size-limit-msg');
                    $nManager->addFinalReportNotice(array(
                            'shortMsg' => 'QUERY ERROR: size limit',
                            'level' => DUPX_NOTICE_ITEM::SOFT_WARNING,
                            'longMsg' => $errorMsg,
                            'sections' => 'database'
                    ));
                    DUPX_Log::info($errorMsg);

                } elseif ($query_strlen > 0) {
                    $query = $this->nbspFix($query);
                    $query = $this->applyQueryCharsetCollationFallback($query);
                    $query = $this->applyQueryProcUserFix($query);

                    // $query = $this->queryDelimiterFix($query);
                    $query = trim($query);
                    if (0 === strpos($query, "DELIMITER")) {
                        // Ending delimiter
                        // control never comes in this if condition, but written
                        if ('DELIMITER ;' == $query) { 
                            $delimiter = ';';
                        } else { // starting delimiter
                            $delimiter =  substr($query, 10);
                            $delimiter =  trim($delimiter);
                        }

                        DUPX_Log::info("Skipping delimiter query");
                        $query = null;
                        continue;
                    }

                    $tempRes = DUPX_DB::mysqli_query($this->dbh, $query);
                    if (!is_bool($tempRes)) {
                        @mysqli_free_result($tempRes);
                    }
                    $err = mysqli_error($this->dbh);
                    //Check to make sure the connection is alive
                    if (!empty($err)) {
                        if (!mysqli_ping($this->dbh)) {
                            mysqli_close($this->dbh);
                            $this->dbh = DUPX_DB::connect($this->post['dbhost'], $this->post['dbuser'], $this->post['dbpass'], $this->post['dbname']);
                            // Reset session setup
                            DUPX_DB::mysqli_query($this->dbh, "SET wait_timeout = ".mysqli_real_escape_string($this->dbh, $GLOBALS['DB_MAX_TIME']));
                            DUPX_DB::setCharset($this->dbh, $this->dbcharset, $this->dbcollate);
                        }
                        $errMsg = "**ERROR** database error write '{$err}' - [sql=".substr($query, 0, 75)."...]";
                        DUPX_Log::info($errMsg);

                        if (DUPX_U::contains($err, 'Unknown collation')) {
                            $nManager->addNextStepNotice(array(
                                'shortMsg' => 'DATABASE ERROR: database error write',
                                'level' => DUPX_NOTICE_ITEM::HARD_WARNING,
                                'longMsg' => 'Unknown collation<br>RECOMMENDATION: Try resolutions found at https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-110-q',
                                'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_HTML,
                                'faqLink' => array(
                                    'url' => 'https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-110-q',
                                    'label' => 'FAQ Link'
                                )
                            ), DUPX_NOTICE_MANAGER::ADD_UNIQUE , 'query-collation-write-msg');
                            $nManager->addFinalReportNotice(array(
                                'shortMsg' => 'DATABASE ERROR: database error write',
                                'level' => DUPX_NOTICE_ITEM::HARD_WARNING,
                                'longMsg' => 'Unknown collation<br>RECOMMENDATION: Try resolutions found at https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-110-q'.'<br>'.$errMsg,
                                'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_HTML,
                                'sections' => 'database',
                                'faqLink' => array(
                                    'url' => 'https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-110-q',
                                    'label' => 'FAQ Link'
                                )
                            ));
                            DUPX_Log::info('RECOMMENDATION: Try resolutions found at https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-110-q');
                        } else {
                            $nManager->addNextStepNoticeMessage('DATABASE ERROR: database error write' , DUPX_NOTICE_ITEM::SOFT_WARNING , DUPX_NOTICE_MANAGER::ADD_UNIQUE , 'query-write-msg');
                            $nManager->addFinalReportNotice(array(
                                'shortMsg' => 'DATABASE ERROR: database error write',
                                'level' => DUPX_NOTICE_ITEM::SOFT_WARNING,
                                'longMsg' => $errMsg,
                                'sections' => 'database'
                            ));
                        }

                        $this->dbquery_errs++;

                        //Buffer data to browser to keep connection open
                    } else {
                        if ($fcgi_buffer_count++ > $fcgi_buffer_pool) {
                            $fcgi_buffer_count = 0;
                        }
                        $this->dbquery_rows++;
                    }
                }
                $query = null;
                $counter++;
            }
        }
        @mysqli_commit($this->dbh);
        @mysqli_autocommit($this->dbh, true);

        $nManager ->saveNotices();
    }

    public function writeQueryInDB($query) {
        $this->dbConnect();
        
        $query_strlen = strlen(trim($query));
        
        $nManager = DUPX_NOTICE_MANAGER::getInstance();
        $paramsManager = DUPX_Paramas_Manager::getInstance();
        
        @mysqli_autocommit($this->dbh, false);

        if ($this->dbvar_maxpacks < $query_strlen) {

            $errorMsg = "**ERROR** Query size limit [length={$this->dbvar_maxpacks}] [sql=".substr($this->sql_result_data[$counter], 0, 75)."...]";
            $this->dbquery_errs++;
            $nManager->addNextStepNoticeMessage('QUERY ERROR: size limit' , DUPX_NOTICE_ITEM::SOFT_WARNING , DUPX_NOTICE_MANAGER::ADD_UNIQUE , 'query-size-limit-msg');
            $nManager->addFinalReportNotice(array(
                    'shortMsg' => 'QUERY ERROR: size limit',
                    'level' => DUPX_NOTICE_ITEM::SOFT_WARNING,
                    'longMsg' => $errorMsg,
                    'sections' => 'database'
            ));
            DUPX_Log::info($errorMsg);
        } elseif ($query_strlen > 0) {
            $query = $this->nbspFix($query);
            $query = $this->applyQueryCharsetCollationFallback($query);
            $query = $this->applyQueryProcUserFix($query);
            $query = trim($query);
         
            $query_res = DUPX_DB::mysqli_query($this->dbh, $query);
            if (!is_bool($query_res)) {
                @mysqli_free_result($query_res);
            }
            if ($query_res) {
                $err = mysqli_error($this->dbh);
            }
            //Check to make sure the connection is alive
            if (!empty($err)) {
                if (!mysqli_ping($this->dbh)) {
                    mysqli_close($this->dbh);
                    $this->dbh = DUPX_DB::connect($this->post['dbhost'], $this->post['dbuser'], $this->post['dbpass'], $this->post['dbname']);
                    // Reset session setup
                            DUPX_DB::mysqli_query($this->dbh, "SET wait_timeout = ".mysqli_real_escape_string($this->dbh, $GLOBALS['DB_MAX_TIME']));
                            DUPX_DB::setCharset($this->dbh, $this->dbcharset, $this->dbcollate);
                }

                $errMsg = "**ERROR** database error write '{$err}' - [sql=".substr($query, 0, 75)."...]";
                DUPX_Log::info($errMsg);

                if (DUPX_U::contains($err, 'Unknown collation')) {
                    $nManager->addNextStepNotice(array(
                        'shortMsg' => 'DATABASE ERROR: database error write',
                        'level' => DUPX_NOTICE_ITEM::HARD_WARNING,
                        'longMsg' => 'Unknown collation<br>RECOMMENDATION: Try resolutions found at https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-110-q',
                        'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_HTML,
                        'faqLink' => array(
                            'url' => 'https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-110-q',
                            'label' => 'FAQ Link'
                        )
                    ), DUPX_NOTICE_MANAGER::ADD_UNIQUE , 'query-collation-write-msg');
                    $nManager->addFinalReportNotice(array(
                        'shortMsg' => 'DATABASE ERROR: database error write',
                        'level' => DUPX_NOTICE_ITEM::HARD_WARNING,
                        'longMsg' => 'Unknown collation<br>RECOMMENDATION: Try resolutions found at https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-110-q'.'<br>'.$errMsg,
                        'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_HTML,
                        'sections' => 'database',
                        'faqLink' => array(
                            'url' => 'https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-110-q',
                            'label' => 'FAQ Link'
                        )
                    ));
                    DUPX_Log::info('RECOMMENDATION: Try resolutions found at https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-110-q');
                } else {
                    $nManager->addNextStepNoticeMessage('DATABASE ERROR: database error write' , DUPX_NOTICE_ITEM::SOFT_WARNING , DUPX_NOTICE_MANAGER::ADD_UNIQUE , 'query-write-msg');
                    $nManager->addFinalReportNotice(array(
                        'shortMsg' => 'DATABASE ERROR: database error write',
                        'level' => DUPX_NOTICE_ITEM::SOFT_WARNING,
                        'longMsg' => $errMsg,
                        'sections' => 'database'
                    ));
                }

                $this->dbquery_errs++;

                //Buffer data to browser to keep connection open
            } else {
                /*
                if ($fcgi_buffer_count++ > $fcgi_buffer_pool) {
                    $fcgi_buffer_count = 0;
                }
                */
                $this->dbquery_rows++;
            }
        }
        @mysqli_commit($this->dbh);
        @mysqli_autocommit($this->dbh, true);
    }

	public function runCleanupRotines()
    {
        $this->dbConnect();
        //DATA CLEANUP: Perform Transient Cache Cleanup
        //Remove all duplicator entries and record this one since this is a new install.
        $dbdelete_count1 = 0;
        $dbdelete_count2 = 0;
        
        $table_prefix = DUPX_Paramas_Manager::getInstance()->getValue(DUPX_Paramas_Manager::PARAM_DB_TABLE_PREFIX);

        DUPX_DB::mysqli_query($this->dbh, "DELETE FROM `".mysqli_real_escape_string($this->dbh, $table_prefix)."duplicator_pro_packages`");
        $dbdelete_count1 = @mysqli_affected_rows($this->dbh);

        DUPX_DB::mysqli_query($this->dbh,
                "DELETE FROM `".mysqli_real_escape_string($this->dbh, $table_prefix)."options` WHERE `option_name` LIKE ('_transient%') OR `option_name` LIKE ('_site_transient%')");
        $dbdelete_count2 = @mysqli_affected_rows($this->dbh);

        $this->dbdelete_count += (abs($dbdelete_count1) + abs($dbdelete_count2));

        $opts_delete = json_decode($GLOBALS['DUPX_AC']->opts_delete);
        //Reset Duplicator Options
        foreach ($opts_delete as $value) {
            DUPX_DB::mysqli_query($this->dbh, "DELETE FROM `".mysqli_real_escape_string($this->dbh, $table_prefix)."options` WHERE `option_name` = '".mysqli_real_escape_string($this->dbh, $value)."'");
        }

		DUPX_Log::info("Starting Cleanup Routine...");

        //Remove views from DB
        if (!$this->dbobj_views) {
            $this->dropViews();
			DUPX_Log::info("/t - Views Dropped.");
        }

        //Remove procedures from DB
        if (!$this->dbobj_procs) {
            $this->dropProcs();
			DUPX_Log::info("/t - Procs Dropped.");
        }

		DUPX_Log::info("Cleanup Routine Complete");
	}

	private function getSQLSessionMode()
    {
        $this->dbConnect();
		$result = DUPX_DB::mysqli_query($this->dbh, "SELECT @@SESSION.sql_mode;");
		$row = mysqli_fetch_row($result);
		$result->close();
		return is_array($row) ? $row[0] : '';
	}

	/*SQL MODE OVERVIEW:
	 * sql_mode can cause db create issues on some systems because the mode affects how data is inserted.
	 * Right now defaulting to	NO_AUTO_VALUE_ON_ZERO (https://dev.mysql.com/doc/refman/5.5/en/sql-mode.html#sqlmode_no_auto_value_on_zero)
	 * has been the saftest option because the act of seting the sql_mode will nullify the MySQL Engine defaults which can be very problematic
	 * if the default is something such as STRICT_TRANS_TABLES,STRICT_ALL_TABLES,NO_ZERO_DATE.  So the default behavior will be to always
	 * use NO_AUTO_VALUE_ON_ZERO.  If the user insits on using the true system defaults they can use the Custom option.  Note these values can
	 * be overriden by values set in the database.sql script such as:
	 * !40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO'
	*/
	private function setSQLSessionMode()
    {
        $this->dbConnect();
        switch ($this->post['dbmysqlmode']) {
            case 'DEFAULT':
                DUPX_DB::mysqli_query($this->dbh, "SET SESSION sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");
                break;
            case 'DISABLE':
                DUPX_DB::mysqli_query($this->dbh, "SET SESSION sql_mode = ''");
                break;
            case 'CUSTOM':
                $dbmysqlmode_opts = $this->post['dbmysqlmode_opts'];
                $qry_session_custom = DUPX_DB::mysqli_query($this->dbh, "SET SESSION sql_mode = '".mysqli_real_escape_string($dbh, $dbmysqlmode_opts)."'");
                if ($qry_session_custom == false) {
                    $sql_error = mysqli_error($this->dbh);
                    $log       = "WARNING: A custom sql_mode setting issue has been detected:\n{$sql_error}.\n";
                    $log       .= "For more details visit: http://dev.mysql.com/doc/refman/5.7/en/sql-mode.html\n";
					DUPX_Log::info($log);
                }
                break;
        }
	}

    private function dropTables()
    {
        $sql          = "SHOW FULL TABLES WHERE Table_Type != 'VIEW'";
        $found_tables = array();

        if (($result = DUPX_DB::mysqli_query($this->dbh, $sql)) === false) {
            DUPX_Log::error('QUERY '.DUPX_Log::varToString($sql).'ERROR: '.mysqli_error($this->dbh));
        }
        while ($row = mysqli_fetch_row($result)) {
            $found_tables[] = $row[0];
        }
        if (count($found_tables) > 0) {
            $sql = "SET FOREIGN_KEY_CHECKS = 0;";
            DUPX_DB::mysqli_query($this->dbh, $sql);
            foreach ($found_tables as $table_name) {
                $sql    = "DROP TABLE `".mysqli_real_escape_string($this->dbh, $this->post['dbname'])."`.`".mysqli_real_escape_string($this->dbh, $table_name)."`";
                if (!$result = DUPX_DB::mysqli_query($this->dbh, $sql)) {
                    DUPX_Log::error(sprintf(ERR_DBTRYCLEAN, "{$this->post['dbname']}.{$table_name}")."<br/>ERROR MESSAGE:{$err}");
                }
            }
            $sql                = "SET FOREIGN_KEY_CHECKS = 1;";
            DUPX_DB::mysqli_query($this->dbh, $sql);
            $this->drop_tbl_log = count($found_tables);
        }
    }

    private function dropProcs()
    {
        $sql    = "SHOW PROCEDURE STATUS";
        $found  = null;
        if ($result = DUPX_DB::mysqli_query($this->dbh, $sql)) {
            while ($row = mysqli_fetch_row($result)) {
                $found[] = $row[1];
            }
            if (!is_null($found) && count($found) > 0) {
                foreach ($found as $proc_name) {
                    $sql    = "DROP PROCEDURE IF EXISTS `".mysqli_real_escape_string($this->dbh, $this->post['dbname'])."`.`".mysqli_real_escape_string($this->dbh, $proc_name)."`";
                    if (!$result = DUPX_DB::mysqli_query($this->dbh, $sql)) {
                        DUPX_Log::error(sprintf(ERR_DBTRYCLEAN, "{$this->post['dbname']}.{$proc_name}")."<br/>ERROR MESSAGE:{$err}");
                    }
                }
            }
        }
    }

    private function dropViews()
    {
        $sql         = "SHOW FULL TABLES WHERE Table_Type = 'VIEW'";
        $found_views = null;
        if ($result      = DUPX_DB::mysqli_query($this->dbh, $sql)) {
            while ($row = mysqli_fetch_row($result)) {
                $found_views[] = $row[0];
            }
            if (!is_null($found_views) && count($found_views) > 0) {
                foreach ($found_views as $view_name) {
                    $sql    = "DROP VIEW `".mysqli_real_escape_string($this->dbh, $this->post['dbname'])."`.`".mysqli_real_escape_string($this->dbh, $view_name)."`";
                    if (!$result = DUPX_DB::mysqli_query($this->dbh, $sql)) {
                        DUPX_Log::error(sprintf(ERR_DBTRYCLEAN, "{$this->post['dbname']}.{$view_name}")."<br/>ERROR MESSAGE:{$err}");
                    }
                }
            }
        }
    }

    public function writeLog()
    {
        $this->dbConnect();
        $nManager = DUPX_NOTICE_MANAGER::getInstance();
        
        
        DUPX_Log::info("ERRORS FOUND:\t{$this->dbquery_errs}");
        DUPX_Log::info("DROPPED TABLES:\t{$this->drop_tbl_log}");
        DUPX_Log::info("RENAMED TABLES:\t{$this->rename_tbl_log}");
        DUPX_Log::info("QUERIES RAN:\t{$this->dbquery_rows}\n");

        $this->dbtable_rows  = 1;
        $this->dbtable_count = 0;

        DUPX_Log::info("TABLES ROWS\n");
        if (($result = DUPX_DB::mysqli_query($this->dbh, "SHOW TABLES")) != false) {
            while ($row = mysqli_fetch_array($result, MYSQLI_NUM)) {
                $table_rows         = DUPX_DB::countTableRows($this->dbh, $row[0]);
                $this->dbtable_rows += $table_rows;
                DUPX_Log::info('TABLE '.str_pad(DUPX_Log::varToString($row[0]), 50, '_', STR_PAD_RIGHT).'[ROWS:'.str_pad($table_rows, 6, " ", STR_PAD_LEFT).']');
                $this->dbtable_count++;
            }
            @mysqli_free_result($result);
        }

        DUPX_Log::info("\n".'DATABASE CACHE/TRANSITIENT [ROWS:'.str_pad($this->dbdelete_count, 6, " ", STR_PAD_LEFT).']');

        if ($this->dbtable_count == 0) {
            $tablePrefix = DUPX_Paramas_Manager::getInstance()->getValue(DUPX_Paramas_Manager::PARAM_DB_TABLE_PREFIX);
            $longMsg = "You may have to manually run the installer-data.sql to validate data input. ".
                "Also check to make sure your installer file is correct and the table prefix '".$tablePrefix." is correct for this particular version of WordPress.";
            $nManager->addBothNextAndFinalReportNotice(array(
                'shortMsg' => 'No table in database',
                'level' => DUPX_NOTICE_ITEM::NOTICE,
                'longMsg' => $longMsg,
                'sections' => 'database'
            ));
            DUPX_Log::info("NOTICE: ".$longMsg."\n");
        }

        $nManager->saveNotices();
    }

    public function getJSON($json)
    {
        $json['table_count'] = $this->dbtable_count;
        $json['table_rows']  = $this->dbtable_rows;
        $json['query_errs']  = $this->dbquery_errs;

        return $json;
    }

    private function applyQueryCharsetCollationFallback($query)
    {
        if ($this->dbcharsetfb && !empty($this->dbcharsetfbval) && preg_match('/ CHARSET=([^\s;]+)/i', $query, $charsetMatch)) {
            $charset = $charsetMatch[1];
            if (false === array_search($charset, $this->supportedCharSetList)) {
                DUPX_Log::info("LEGACY CHARSET FALLBACK: ".$charset." to ".$this->dbcharsetfbval, DUPX_Log::LV_DEBUG);
                $query = DupProSnapLibStringU::strLastReplace("CHARSET=$charset", "CHARSET=".$this->dbcharsetfbval, $query);
                if (preg_match('/ COLLATE=([^\s;]+)/i', $query, $collateMatch)) {
                    $collate = $collateMatch[1];
                    $query   = DupProSnapLibStringU::strLastReplace(" COLLATE=$collate", "", $query);
                }
                if (preg_match('/ COLLATE ([^\s;]+)/i', $query, $collateMatch)) {
                    $collate = $collateMatch[1];
                    $query   = str_replace(" COLLATE $collate", "", $query);
                }
            }
        }

        if ($this->dbcollatefb && !empty($this->dbcollatefbval)) {
            if (preg_match('/ COLLATE=([^\s]+)/i', $query, $collateMatch)) {
                $collate = $collateMatch[1];
                if (false === array_search($collate, $this->supportedCollateList)) {
                    DUPX_Log::info("LEGACY COLLATION FALLBACK (equal): ".$collate." to ".$this->dbcollatefbval, DUPX_Log::LV_DEBUG);
                    $query = DupProSnapLibStringU::strLastReplace("COLLATE=$collate", "COLLATE=".$this->dbcollatefbval, $query, false);
                }
            }
            if (preg_match('/ COLLATE ([^\s]+)/i', $query, $collateMatch)) {
                $collate = $collateMatch[1];
                if (false === array_search($collate, $this->supportedCollateList)) {
                    DUPX_Log::info("LEGACY COLLATION FALLBACK (space): ".$collate." to ".$this->dbcollatefbval, DUPX_Log::LV_DEBUG);
                    $query = str_replace("COLLATE $collate", "COLLATE ".$this->dbcollatefbval, $query);
                }
            }
        }

        return $query;
    }

    private function applyProcUserFix()
    {
        foreach ($this->sql_result_data as $key => $query) {
            if (preg_match("/DEFINER.*PROCEDURE/", $query) === 1) {
                $query                       = preg_replace("/DEFINER.*PROCEDURE/", "PROCEDURE", $query);
                $query                       = str_replace("BEGIN", "SQL SECURITY INVOKER\nBEGIN", $query);
                $this->sql_result_data[$key] = $query;
            }
        }
    }

    private function applyQueryProcUserFix($query) {
        if (preg_match("/DEFINER.*PROCEDURE/", $query) === 1) {
            $query                       = preg_replace("/DEFINER.*PROCEDURE/", "PROCEDURE", $query);
            $query                       = str_replace("BEGIN", "SQL SECURITY INVOKER\nBEGIN", $query);
        }
        return $query;
    }

    private function delimiterFix($counter)
    {
        $firstQuery = trim(preg_replace('/\s\s+/', ' ', $this->sql_result_data[$counter]));
        $start      = $counter;
        $end        = 0;
        if (strpos($firstQuery, "DELIMITER") === 0) {
            $this->sql_result_data[$start] = "";
            $continueSearch                = true;
            while ($continueSearch) {
                $counter++;
                if (strpos($this->sql_result_data[$counter], 'DELIMITER') === 0) {
                    $continueSearch        = false;
                    unset($this->sql_result_data[$counter]);
                    $this->sql_result_data = array_values($this->sql_result_data);
                } else {
                    $this->sql_result_data[$start] .= $this->sql_result_data[$counter].";\n";
                    unset($this->sql_result_data[$counter]);
                }
            }
        }
    }

    public function nbspFix($sql)
    {
        if ($this->post['dbnbsp']) {
            if ($this->firstOrNotChunking()) {
                DUPX_Log::info("ran fix non-breaking space characters\n");
            }
            $sql = preg_replace('/\xC2\xA0/', ' ', $sql);
        }
        return $sql;
    }

    public function firstOrNotChunking()
    {
        return (!isset($this->post['continue_chunking']) || $this->post['first_chunk']);
    }

    public function disableRSSSL()
    {
        if(!DUPX_U::is_ssl()) {
            if($this->deactivatePlugin("really-simple-ssl/rlrsssl-really-simple-ssl.php")){
                DUPX_Log::info("Deactivated 'Really Simple SSL' plugin\n");
            }
        }
    }

    public function deactivatePlugin($slug)
    {
        $this->dbConnect();
        $excapedTablePrefix = mysqli_real_escape_string($this->dbh, DUPX_Paramas_Manager::getInstance()->getValue(DUPX_Paramas_Manager::PARAM_DB_TABLE_PREFIX));
        $sql = "SELECT * FROM ".$excapedTablePrefix."options WHERE option_name = 'active_plugins'";
        $arr = mysqli_fetch_assoc(DUPX_DB::mysqli_query($this->dbh, $sql));
        $active_plugins_serialized = stripslashes($arr['option_value']);
        $active_plugins = unserialize($active_plugins_serialized);
        foreach ($active_plugins as $key => $active_plugin){
            if($active_plugin == $slug){
                unset($active_plugins[$key]);
                $active_plugins = array_values($active_plugins);
                $active_plugins_serialized = mysqli_real_escape_string($this->dbh,serialize($active_plugins));
                $sql = "UPDATE `".$excapedTablePrefix."options` SET `option_value`='".mysqli_real_escape_string($this->dbh, $active_plugins_serialized)."' WHERE `option_name` = 'active_plugins'";
                $result = DUPX_DB::mysqli_query($this->dbh, $sql);
                return $result;
                break;
            }
        }
    }

    public function __destruct()
    {
        @mysqli_close($this->dbh);
        $this->dbh = null;
    }
}
