<?php
defined("DUPXABSPATH") or die("");

class DUP_PRO_Extraction
{

    const ENGINE_MANUAL    = 'manual';
    const ENGINE_ZIP       = 'ziparchive';
    const ENGINE_ZIP_CHUNK = 'ziparchivechunking';
    const ENGINE_ZIP_SHELL = 'shellexec_unzip';
    const ENGINE_DUP       = 'duparchive';

    public $set_file_perms;
    public $set_dir_perms;
    public $file_perms_value;
    public $dir_perms_value;
    public $zip_filetime;
    public $archive_engine;
    public $post_log;
    public $ajax1_start;
    public $root_path;
    public $wpconfig_ark_path;
    public $archive_path;
    public $JSON                       = array();
    public $ajax1_error_level;
    public $dawn_status                = null;
    public $archive_offset;
    public $do_chunking;
    public $wpConfigPath;
    public $chunkedExtractionCompleted = false;
    public $num_files                  = 0;
    public $sub_folder_archive         = '';
    public $max_size_extract_at_a_time;

    public function __construct($post)
    {
        $paramsManager = DUPX_Paramas_Manager::getInstance();

        $this->set_file_perms    = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_SET_FILE_PERMS);
        $this->set_dir_perms     = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_SET_DIR_PERMS);
        $this->file_perms_value  = intval(('0'.$paramsManager->getValue(DUPX_Paramas_Manager::PARAM_FILE_PERMS_VALUE)), 8);
        $this->dir_perms_value   = intval(('0'.$paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DIR_PERMS_VALUE)), 8);
        $this->zip_filetime      = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_FILE_TIME);
        $this->archive_engine    = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_ARCHIVE_ENGINE);
        $this->root_path         = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_PATH_NEW);
        $this->wpConfigPath      = DUPX_WPConfig::getWpConfigPath();
        $this->wpconfig_ark_path = DUPX_Package::getWpconfigArkPath();
        $this->archive_path      = DUPX_Security::getInstance()->getArchivePath();

        $this->archive_offset = (isset($post['archive_offset'])) ? intval($post['archive_offset']) : 0;
        if (isset($post['zip_arc_chunks_extract_rates'])) {
            if (is_array($post['zip_arc_chunks_extract_rates'])) {
                $this->zip_arc_chunks_extract_rates = array_map('DUPX_U::sanitize_text_field', $post['zip_arc_chunks_extract_rates']);
            } else {
                $this->zip_arc_chunks_extract_rates = DUPX_U::sanitize_text_field($post['zip_arc_chunks_extract_rates']);
            }
        } else {
            $this->zip_arc_chunks_extract_rates = array();
        }
        $this->zip_arc_chunk_notice_no               = (isset($post['zip_arc_chunk_notice_no'])) ? DUPX_U::sanitize_text_field($post['zip_arc_chunk_notice_no']) : '-1';
        $this->zip_arc_chunk_notice_change_last_time = (isset($post['zip_arc_chunk_notice_change_last_time'])) ? DUPX_U::sanitize_text_field($post['zip_arc_chunk_notice_change_last_time']) : 0;
        $this->num_files                             = (isset($post['$num_files'])) ? intval($post['num_files']) : 0;
        $this->do_chunking                           = (isset($post['pass'])) ? $post['pass'] == -1 : false;

        DUPX_log::info('DUP ARCHIVE EXTRACTOR: engine '.$this->archive_engine, DUPX_log::LV_DETAILED);

        // check dawn_status only if is a duparchive
        if ($this->archive_engine == self::ENGINE_DUP) {
            if (isset($post['dawn_status'])) {
                // extra_data	{"archive_offset":27121850,"archive_size":27121913,"failures":[],"file_index":3461,"is_done":true,"timestamp":1555090042}
                try {
                    $json = json_decode($post['dawn_status'], true);
                    if (!is_array($json)) {
                        throw new Exception('dawn_status isn\'t an array');
                    }

                    $this->dawn_status = (object) filter_var_array($json,
                            array(
                                'archive_offset' => array(
                                    'filter'  => FILTER_VALIDATE_INT,
                                    'flags'   => FILTER_NULL_ON_FAILURE,
                                    'options' => array(
                                        'min_range' => 0
                                    )
                                ),
                                'archive_size'   => array(
                                    'filter'  => FILTER_VALIDATE_INT,
                                    'flags'   => FILTER_NULL_ON_FAILURE,
                                    'options' => array(
                                        'min_range' => 0
                                    )
                                ),
                                // DupArchiveProcessingFailure
                                'failures'       => array(
                                    'flags' => FILTER_REQUIRE_ARRAY | FILTER_NULL_ON_FAILURE,
                                ),
                                'file_index'     => array(
                                    'filter'  => FILTER_VALIDATE_INT,
                                    'flags'   => FILTER_NULL_ON_FAILURE,
                                    'options' => array(
                                        'min_range' => 0
                                    )
                                ),
                                'is_done'        => FILTER_VALIDATE_BOOLEAN,
                                'timestamp'      => array(
                                    'filter'  => FILTER_VALIDATE_INT,
                                    'flags'   => FILTER_NULL_ON_FAILURE,
                                    'options' => array(
                                        'min_range' => 0
                                    )
                                )
                    ));

                    if (is_null($this->dawn_status->failures)) {
                        $this->dawn_status->failures = array();
                    }

                    foreach ($this->dawn_status->failures as $key => $failure) {
                        $this->dawn_status->failures[$key] = (object) filter_var_array($failure,
                                array(
                                    'type'        => array(
                                        'filter'  => FILTER_VALIDATE_INT,
                                        'options' => array(
                                            'default'   => 0,
                                            'min_range' => 0,
                                            'max_range' => 2
                                        )
                                    ),
                                    'description' => array(
                                        'filter'  => FILTER_CALLBACK,
                                        'options' => array('DUPX_U', 'sanitize_text_field')
                                    ),
                                    'subject'     => array(
                                        'filter'  => FILTER_CALLBACK,
                                        'options' => array('DUPX_U', 'sanitize_text_field')
                                    ),
                                    'isCritical'  => FILTER_VALIDATE_BOOLEAN,
                        ));
                    }
                }
                catch (Exception $e) {
                    DUPX_Log::info("EXCEPTION: Dawn status validate error MESSAGE: ".$e->getMessage());
                    $this->dawn_status = null;
                }
            } else {
                DUPX_Log::info("Dawn status no in POST");
                $this->dawn_status = null;
            }
        }

        $this->post_log = $post;

        unset($this->post_log['dbpass']);
        ksort($this->post_log);

        $this->ajax1_start  = (isset($post['ajax1_start'])) ? $post['ajax1_start'] : DUPX_U::getMicrotime();
        $this->JSON['pass'] = 0;

        $this->ajax1_error_level = error_reporting();
        error_reporting(E_ERROR);

        //===============================
        //ARCHIVE ERROR MESSAGES
        //===============================
        ($GLOBALS['LOG_FILE_HANDLE'] != false) or DUPX_Log::error(ERR_MAKELOG);

        $min_chunk_size                   = 2 * DUPLICATOR_PRO_INSTALLER_MB_IN_BYTES;
        $this->max_size_extract_at_a_time = DUPX_U::get_default_chunk_size_in_byte($min_chunk_size);

        if (isset($post['sub_folder_archive'])) {
            $this->sub_folder_archive = trim(DUPX_U::sanitize_text_field($post['sub_folder_archive']));
        } else {
            if (self::ENGINE_DUP == $this->archive_engine || $this->archive_engine == self::ENGINE_MANUAL) {
                $this->sub_folder_archive = '';
            } elseif (($this->sub_folder_archive = DUPX_U::findDupInstallerFolder(DUPX_Security::getInstance()->getArchivePath())) === false) {
                DUPX_Log::info("findDupInstallerFolder error; set no subfolder");
                // if not found set not subfolder
                $this->sub_folder_archive = '';
            }
        }
    }

    public function runExtraction()
    {
        switch ($this->archive_engine) {
            case self::ENGINE_ZIP_CHUNK:
                $this->runChunkExtraction();
                break;
            case self::ENGINE_ZIP:
                $this->log1();
                if (!$GLOBALS['DUPX_AC']->exportOnlyDB) {
                    $this->exportOnlyDB();
                }
                $this->runZipArchive();
                break;
            case self::ENGINE_MANUAL:
                $this->log1();
                if (!$GLOBALS['DUPX_AC']->exportOnlyDB) {
                    $this->exportOnlyDB();
                }
                DUPX_Log::info("\n\nSTART ZIP FILE EXTRACTION MANUAL MODE >>> ");
                DUPX_Log::info("\tDONE");
                break;
            case self::ENGINE_ZIP_SHELL:
                $this->log1();
                if (!$GLOBALS['DUPX_AC']->exportOnlyDB) {
                    $this->exportOnlyDB();
                }
                $this->runShellExec();
                break;
            case self::ENGINE_DUP:
                $this->log1();
                DUPX_Log::info("\tDUP ARCHIVE EXTRACTION DONE");
                $this->log2();
                break;
            default:
                throw new Exception('No valid engine '.$this->archive_engine);
        }
    }

    public function runStandardExtraction()
    {
        if (!$GLOBALS['DUPX_AC']->exportOnlyDB) {
            $this->exportOnlyDB();
        }

        $this->log1();

        if ($this->archive_engine == self::ENGINE_MANUAL) {
            DUPX_Log::info("\n\nSTART ZIP FILE EXTRACTION MANUAL MODE >>> ");
            DUPX_Log::info("\tDONE");
        } else {

            if ($this->archive_engine == self::ENGINE_ZIP_SHELL) {
                $this->runShellExec();
            } else {
                if ($this->archive_engine == self::ENGINE_ZIP) {
                    $this->runZipArchive();
                } else {
                    $this->log2();
                }
            }
        }
    }

    public function runChunkExtraction()
    {
        if ($this->isFirstChunk()) {
            if (!$GLOBALS['DUPX_AC']->exportOnlyDB) {
                $this->exportOnlyDB();
            }

            $this->log1();
            DUPX_Log::info("\n\nSTART ZIP FILE EXTRACTION CHUNKING >>> ");
            if (!empty($this->sub_folder_archive)) {
                DUPX_Log::info("ARCHIVE dup-installer SUBFOLDER:\"".$this->sub_folder_archive."\"");
            } else {
                DUPX_Log::info("ARCHIVE dup-installer SUBFOLDER:\"".$this->sub_folder_archive."\"", 2);
            }
        } else {
            DUPX_Log::info("\n\nCONTINUE ZIP FILE EXTRACTION CHUNKING >>> ");
        }

        $this->runZipArchiveChunking();
    }

    public function runZipArchiveChunking($chunk = true)
    {
        if (!class_exists('ZipArchive')) {
            DUPX_Log::info("ERROR: Stopping install process.  Trying to extract without ZipArchive module installed.  Please use the 'Manual Archive Extraction' mode to extract zip file.");
            DUPX_Log::error(ERR_ZIPARCHIVE);
        }

        $nManager            = DUPX_NOTICE_MANAGER::getInstance();
        $archiveConfig       = DUPX_ArchiveConfig::getInstance();
        $dupInstallerZipPath = ltrim($this->sub_folder_archive.'/dup-installer', '/');

        $zip        = new ZipArchive();
        $start_time = DUPX_U::getMicrotime();
        $time_over  = false;

        DUPX_Log::info("ARCHIVE OFFSET ".DUPX_Log::varToString($this->archive_offset));
        DUPX_Log::info('DUP INSTALLER ARCHIVE PATH:"'.$dupInstallerZipPath.'"', 2);

        if ($zip->open($this->archive_path) == true) {
            $this->num_files   = $zip->numFiles;
            $num_files_minus_1 = $this->num_files - 1;

            $extracted_size = 0;

            DUPX_Handler::setMode(DUPX_Handler::MODE_VAR, false, false);

            // Main chunk
            do {
                $extract_filename = null;

                $no_of_files_in_micro_chunk = 0;
                $size_in_micro_chunk        = 0;
                do {
                    //rsr uncomment if debugging     DUPX_Log::info("c ao " . $this->archive_offset);
                    $stat_data = $zip->statIndex($this->archive_offset);
                    $filename  = $stat_data['name'];
                    $skip      = (strpos($filename, 'dup-installer') === 0);

                    if ($skip) {
                        DUPX_Log::info("FILE EXTRACTION SKIP: ".DUPX_Log::varToString($filename), DUPX_Log::LV_DETAILED);
                    } else {
                        $extract_filename    = $filename;
                        $size_in_micro_chunk += $stat_data['size'];
                        $no_of_files_in_micro_chunk++;
                    }
                    $this->archive_offset++;
                } while (
                $this->archive_offset < $num_files_minus_1 &&
                $no_of_files_in_micro_chunk < 1 &&
                $size_in_micro_chunk < $this->max_size_extract_at_a_time
                );

                if (!empty($extract_filename)) {
                    // skip dup-installer folder. Alrady extracted in bootstrap
                    if (
                        (strpos($extract_filename, $dupInstallerZipPath) === 0) ||
                        (!empty($this->sub_folder_archive) && strpos($extract_filename, $this->sub_folder_archive) !== 0)
                    ) {
                        DUPX_Log::info("SKIPPING NOT IN ZIPATH:\"".DUPX_Log::varToString($extract_filename)."\"", DUPX_Log::LV_DETAILED);
                    } else {
                        $this->extractFile($zip, $extract_filename, $archiveConfig->destFileFromArchiveName($extract_filename));
                    }
                }

                $extracted_size += $size_in_micro_chunk;
                if ($this->archive_offset == $this->num_files - 1) {

                    if (!empty($this->sub_folder_archive)) {
                        DUPX_U::moveUpfromSubFolder($this->root_path.'/'.$this->sub_folder_archive, true);
                    }

                    DUPX_Log::info("Archive just got done processing last file in list of {$this->num_files}");
                    $this->chunkedExtractionCompleted = true;
                    break;
                }

                if (($time_over = $chunk && (DUPX_U::getMicrotime() - $start_time) > DUPX_Constants::CHUNK_EXTRACTION_TIMEOUT_TIME)) {
                    DUPX_Log::info("TIME IS OVER - CHUNK", 2);
                }
            } while ($this->archive_offset < $num_files_minus_1 && !$time_over);

            // set handler as default
            DUPX_Handler::setMode();
            $zip->close();

            $chunk_time = DUPX_U::getMicrotime() - $start_time;

            $chunk_extract_rate                   = $extracted_size / $chunk_time;
            $this->zip_arc_chunks_extract_rates[] = $chunk_extract_rate;
            $zip_arc_chunks_extract_rates         = $this->zip_arc_chunks_extract_rates;
            $average_extract_rate                 = array_sum($zip_arc_chunks_extract_rates) / count($zip_arc_chunks_extract_rates);

            $expected_extract_time = $average_extract_rate > 0 ? DUPX_Conf_Utils::archiveSize() / $average_extract_rate : 0;

            DUPX_Log::info("Expected total archive extract time: {$expected_extract_time}");
            DUPX_Log::info("Total extraction elapsed time until now: {$expected_extract_time}");

            $elapsed_time      = DUPX_U::getMicrotime() - $this->ajax1_start;
            $max_no_of_notices = count($GLOBALS['ZIP_ARC_CHUNK_EXTRACT_NOTICES']) - 1;

            $zip_arc_chunk_extract_disp_notice_after                     = $GLOBALS['ZIP_ARC_CHUNK_EXTRACT_DISP_NOTICE_AFTER'];
            $zip_arc_chunk_extract_disp_notice_min_expected_extract_time = $GLOBALS['ZIP_ARC_CHUNK_EXTRACT_DISP_NOTICE_MIN_EXPECTED_EXTRACT_TIME'];
            $zip_arc_chunk_extract_disp_next_notice_interval             = $GLOBALS['ZIP_ARC_CHUNK_EXTRACT_DISP_NEXT_NOTICE_INTERVAL'];

            if ($this->zip_arc_chunk_notice_no < 0) { // -1
                if (($elapsed_time > $zip_arc_chunk_extract_disp_notice_after && $expected_extract_time > $zip_arc_chunk_extract_disp_notice_min_expected_extract_time) ||
                    $elapsed_time > $zip_arc_chunk_extract_disp_notice_min_expected_extract_time
                ) {
                    $this->zip_arc_chunk_notice_no++;
                    $this->zip_arc_chunk_notice_change_last_time = DUPX_U::getMicrotime();
                }
            } elseif ($this->zip_arc_chunk_notice_no > 0 && $this->zip_arc_chunk_notice_no < $max_no_of_notices) {
                $interval_after_last_notice = DUPX_U::getMicrotime() - $this->zip_arc_chunk_notice_change_last_time;
                DUPX_Log::info("Interval after last notice: {$interval_after_last_notice}");
                if ($interval_after_last_notice > $zip_arc_chunk_extract_disp_next_notice_interval) {
                    $this->zip_arc_chunk_notice_no++;
                    $this->zip_arc_chunk_notice_change_last_time = DUPX_U::getMicrotime();
                }
            }

            $nManager->saveNotices();

            //rsr todo uncomment when debugging      DUPX_Log::info("Zip archive chunk notice no.: {$this->zip_arc_chunk_notice_no}");
        } else {
            $zip_err_msg = ERR_ZIPOPEN;
            $zip_err_msg .= "<br/><br/><b>To resolve error see <a href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-130-q' target='_blank'>https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-130-q</a></b>";
            DUPX_Log::info($zip_err_msg);
            throw new Exception("Couldn't open zip archive.");
        }
    }

    /**
     * 
     * @param ZipArchive $zipObj
     * @param string $zipFilename
     * @param string $newFilePath
     */
    protected function extractFile($zipObj, $zipFilename, $newFilePath)
    {
        try {
            //rsr uncomment if debugging     DUPX_Log::info("Attempting to extract {$zipFilename}. Time:". time());
            $error = false;

            if ($this->root_path.'/'.ltrim($zipFilename, '\\/') === $newFilePath) {
                if (!$zipObj->extractTo($this->root_path, $zipFilename)) {
                    $error = true;
                }
            } else {
                if (DUPX_Log::isLevel(DUPX_Log::LV_DEBUG)) {
                    DUPX_LOG::info('CUSTOM EXTRACT FILE ['.$zipFilename.'] TO ['.$newFilePath.']', DUPX_Log::LV_DEBUG);
                }
                if (substr($zipFilename, -1) === '/') {
                    DupProSnapLibIOU::mkdir_p(dirname($newFilePath));
                } else {
                    if (($destStream = fopen($newFilePath, 'w')) === false) {
                        if (!file_exists(dirname($newFilePath))) {
                            DupProSnapLibIOU::mkdir_p(dirname($newFilePath));
                            if (($destStream = fopen($newFilePath, 'w')) === false) {
                                $error = true;
                            }
                        } else {
                            $error = true;
                        }
                    }

                    if ($error || ($sourceStream = $zipObj->getStream($zipFilename)) === false) {
                        $error = true;
                    } else {
                        while (!feof($sourceStream)) {
                            fwrite($destStream, fread($sourceStream, 1048576)); // 1M
                        }

                        fclose($sourceStream);
                        fclose($destStream);
                    }
                }
            }

            if ($error) {
                //if (!$zip->extractTo($this->root_path, $zipFilename)) {
                if (DUPX_Custom_Host_Manager::getInstance()->skipWarningExtractionForManaged($zipFilename)) {
                    // @todo skip warning for managed hostiong (it's a temp solution)
                } else {
                    $nManager = DUPX_NOTICE_MANAGER::getInstance();
                    if (DupProSnapLibUtilWp::isWpCore($zipFilename, DupProSnapLibUtilWp::PATH_RELATIVE)) {
                        DUPX_Log::info("FILE CORE EXTRACTION ERROR: ".$zipFilename);
                        $shortMsg      = 'Can\'t extract wp core files';
                        $finalShortMsg = 'Wp core files not extracted';
                        $errLevel      = DUPX_NOTICE_ITEM::CRITICAL;
                        $idManager     = 'wp-extract-error-file-core';
                    } else {
                        DUPX_Log::info("FILE EXTRACTION ERROR: ".$zipFilename);
                        $shortMsg      = 'Can\'t extract files';
                        $finalShortMsg = 'Files not extracted';
                        $errLevel      = DUPX_NOTICE_ITEM::SOFT_WARNING;
                        $idManager     = 'wp-extract-error-file-no-core';
                    }

                    $longMsg = 'FILE: <b>'.htmlspecialchars($zipFilename).'</b><br>Message: '.htmlspecialchars(DUPX_Handler::getVarLogClean()).'<br><br>';
                    $nManager->addNextStepNotice(array(
                        'shortMsg'    => $shortMsg,
                        'longMsg'     => $longMsg,
                        'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_HTML,
                        'level'       => $errLevel
                        ), DUPX_NOTICE_MANAGER::ADD_UNIQUE_APPEND, $idManager);
                    $nManager->addFinalReportNotice(array(
                        'shortMsg'    => $finalShortMsg,
                        'longMsg'     => $longMsg,
                        'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_HTML,
                        'level'       => $errLevel,
                        'sections'    => array('files'),
                        ), DUPX_NOTICE_MANAGER::ADD_UNIQUE_APPEND, $idManager);
                }
            } else {
                DUPX_Log::info("FILE EXTRACTION DONE: ".DUPX_Log::varToString($zipFilename), DUPX_Log::LV_HARD_DEBUG);
            }
        }
        catch (Exception $ex) {
            if (DUPX_Custom_Host_Manager::getInstance()->skipWarningExtractionForManaged($zipFilename)) {
                // @todo skip warning for managed hostiong (it's a temp solution)
            } else {
                $nManager = DUPX_NOTICE_MANAGER::getInstance();

                if (DupProSnapLibUtilWp::isWpCore($zipFilename, DupProSnapLibUtilWp::PATH_RELATIVE)) {
                    DUPX_Log::info("FILE CORE EXTRACTION ERROR: {$zipFilename} | MSG:".$ex->getMessage());
                    $shortMsg      = 'Can\'t extract wp core files';
                    $finalShortMsg = 'Wp core files not extracted';
                    $errLevel      = DUPX_NOTICE_ITEM::CRITICAL;
                } else {
                    DUPX_Log::info("FILE EXTRACTION ERROR: {$zipFilename} | MSG:".$ex->getMessage());
                    $shortMsg      = 'Can\'t extract files';
                    $finalShortMsg = 'Files not extracted';
                    $errLevel      = DUPX_NOTICE_ITEM::SOFT_WARNING;
                    $idManager     = 'wp-extract-error-file-no-core';
                }

                $longMsg = 'FILE: <b>'.htmlspecialchars($zipFilename).'</b><br>Message: '.htmlspecialchars($ex->getMessage()).'<br><br>';

                $nManager->addNextStepNotice(array(
                    'shortMsg'    => $shortMsg,
                    'longMsg'     => $longMsg,
                    'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_HTML,
                    'level'       => $errLevel
                    ), DUPX_NOTICE_MANAGER::ADD_UNIQUE_APPEND, $idManager);
                $nManager->addFinalReportNotice(array(
                    'shortMsg'    => $finalShortMsg,
                    'longMsg'     => $longMsg,
                    'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_HTML,
                    'level'       => $errLevel,
                    'sections'    => array('files'),
                    ), DUPX_NOTICE_MANAGER::ADD_UNIQUE_APPEND, $idManager);
            }
        }
    }

    public function exportOnlyDB()
    {
        if ($this->archive_engine == self::ENGINE_MANUAL || $this->archive_engine == self::ENGINE_DUP) {
            $sql_file_path = DUPX_INIT."/dup-database__{$GLOBALS['DUPX_AC']->package_hash}.sql";
            if (!file_exists($this->wpconfig_ark_path) && !file_exists($sql_file_path)) {
                DUPX_Log::error(ERR_ZIPMANUAL);
            }
        } else {
            if (!is_readable("{$this->archive_path}")) {
                DUPX_Log::error("archive file path:<br/>".ERR_ZIPNOTFOUND);
            }
        }
    }

    public function log1()
    {
        $paramsManager = DUPX_Paramas_Manager::getInstance();

        DUPX_Log::info("********************************************************************************");
        DUPX_Log::info('* DUPLICATOR-PRO: Install-Log');
        DUPX_Log::info('* STEP-1 START @ '.@date('h:i:s'));
        DUPX_Log::info('* NOTICE: Do NOT post to public sites or forums!!');
        DUPX_Log::info("********************************************************************************");

        $labelPadSize = 20;
        DUPX_Log::info("USER INPUTS");

        DUPX_Log::info(str_pad('HOME URL OLD', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_URL_OLD)));
        DUPX_Log::info(str_pad('HOME URL NEW', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_URL_NEW)));
        DUPX_Log::info(str_pad('SITE URL OLD', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_SITE_URL_OLD)));
        DUPX_Log::info(str_pad('SITE URL NEW', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_SITE_URL)));
        DUPX_Log::info(str_pad('CONTENT URL OLD', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_URL_CONTENT_OLD)));
        DUPX_Log::info(str_pad('CONTENT URL NEW', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_URL_CONTENT_NEW)));
        DUPX_Log::info(str_pad('UPLOAD URL OLD', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_URL_UPLOADS_OLD)));
        DUPX_Log::info(str_pad('UPLOAD URL NEW', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_URL_UPLOADS_NEW)));
        DUPX_Log::info(str_pad('PLUGINS URL OLD', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_URL_PLUGINS_OLD)));
        DUPX_Log::info(str_pad('PLUGINS URL NEW', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_URL_PLUGINS_NEW)));
        DUPX_Log::info(str_pad('MUPLUGINS URL OLD', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_URL_MUPLUGINS_OLD)));
        DUPX_Log::info(str_pad('MUPLUGINS URL NEW', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_URL_MUPLUGINS_NEW)));

        DUPX_Log::info(str_pad('HOME PATH OLD', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_PATH_OLD)));
        DUPX_Log::info(str_pad('HOME PATH NEW', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_PATH_NEW)));
        DUPX_Log::info(str_pad('SITE PATH OLD', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_PATH_WP_CORE_OLD)));
        DUPX_Log::info(str_pad('SITE PATH NEW', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_PATH_WP_CORE_NEW)));
        DUPX_Log::info(str_pad('CONTENT PATH OLD', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_PATH_CONTENT_OLD)));
        DUPX_Log::info(str_pad('CONTENT PATH NEW', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_PATH_CONTENT_NEW)));
        DUPX_Log::info(str_pad('UPLOAD PATH OLD', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_PATH_UPLOADS_OLD)));
        DUPX_Log::info(str_pad('UPLOAD PATH NEW', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_PATH_UPLOADS_NEW)));
        DUPX_Log::info(str_pad('PLUGINS PATH OLD', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_PATH_PLUGINS_OLD)));
        DUPX_Log::info(str_pad('PLUGINS PATH NEW', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_PATH_PLUGINS_NEW)));
        DUPX_Log::info(str_pad('MUPLUGINS PATH OLD', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_PATH_MUPLUGINS_OLD)));
        DUPX_Log::info(str_pad('MUPLUGINS PATH NEW', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_PATH_MUPLUGINS_NEW)));

        DUPX_Log::info(str_pad('ARCHIVE ENGINE', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($this->archive_engine));
        DUPX_Log::info(str_pad('CLIENT KICKOFF', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_CLIENT_KICKOFF)));
        DUPX_Log::info(str_pad('SET DIR PERMS', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($this->set_dir_perms));
        DUPX_Log::info(str_pad('DIR PERMS VALUE', $labelPadSize, '_', STR_PAD_RIGHT).': '.decoct($this->dir_perms_value));
        DUPX_Log::info(str_pad('SET FILE PERMS', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($this->set_file_perms));
        DUPX_Log::info(str_pad('FILE PERMS VALUE', $labelPadSize, '_', STR_PAD_RIGHT).': '.decoct($this->file_perms_value));
        DUPX_Log::info(str_pad('SAFE MODE', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_SAFE_MODE)));
        DUPX_Log::info(str_pad('LOGGING', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_LOGGING)));
        DUPX_Log::info(str_pad('WP CONFIG', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_WP_CONFIG)));
        DUPX_Log::info(str_pad('HTACCESS CONFIG', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_HTACCESS_CONFIG)));
        DUPX_Log::info(str_pad('OTHER CONFIG', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_OTHER_CONFIG)));
        DUPX_Log::info(str_pad('FILE TIME', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($this->zip_filetime));
        DUPX_Log::info(str_pad('REMOVE RENDUNDANT', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_REMOVE_RENDUNDANT)));
        if (DUPX_Conf_Utils::showMultisite()) {
            DUPX_Log::info("********************************************************************************");
            DUPX_Log::info("MULTISITE INPUTS");
            DUPX_Log::info(str_pad('MULTI SITE INST TYPE', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_MULTISITE_INST_TYPE)));
            DUPX_Log::info(str_pad('SUBSITE ID', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_SUBSITE_ID)));
        }
        DUPX_Log::info("********************************************************************************\n");

        $log = "--------------------------------------\n";
        $log .= "POST DATA\n";
        $log .= "--------------------------------------\n";
        $log .= print_r($this->post_log, true);
        DUPX_Log::info($log, DUPX_Log::LV_DEBUG);
        
        DUPX_Log::info("--------------------------------------\n");
        $pathsMapping = DUPX_ArchiveConfig::getInstance()->getPathsMapping();
        DUPX_Log::info(str_pad('PATHS MAPPING', $labelPadSize, '_', STR_PAD_RIGHT).': '.DUPX_Log::varToString($pathsMapping));
        DUPX_Log::info("--------------------------------------\n");
    }

    public function log2()
    {
        DUPX_Log::info(">>> DupArchive Extraction Complete");

        if ($this->dawn_status != null) {
            //DUPX_LOG::info("\n(TEMP)DAWS STATUS:" . $extraData);
            $log = "\n--------------------------------------\n";
            $log .= "DUPARCHIVE EXTRACTION STATUS\n";
            $log .= "--------------------------------------\n";

            $dawsStatus = $this->dawn_status;

            if ($dawsStatus === null) {
                $log .= "Can't decode the dawsStatus!\n";
                $log .= print_r($extraData, true);
            } else {
                $criticalPresent = false;

                if (count($dawsStatus->failures) > 0) {
                    $log .= "Archive extracted with errors.\n";

                    foreach ($dawsStatus->failures as $failure) {
                        if ($failure->isCritical) {
                            $log             .= '(C) ';
                            $criticalPresent = true;
                        }

                        $log .= "{$failure->description}\n";
                    }
                } else {
                    $log .= "Archive extracted with no errors.\n";
                }

                if ($criticalPresent) {
                    $log .= "\n\nCritical Errors present so stopping install.\n";
                    exit();
                }
            }

            DUPX_Log::info($log);
        } else {
            DUPX_LOG::info("DAWS STATUS: UNKNOWN since extra_data wasn't in post!");
        }
    }

    public function runShellExec()
    {
        DUPX_Log::info("\n\nSTART ZIP FILE EXTRACTION SHELLEXEC >>> ");

        $command = escapeshellcmd(DUPX_Server::get_unzip_filepath())." -o -qq ".escapeshellarg($this->archive_path)." -d ".escapeshellarg($this->root_path)." 2>&1";
        if ($this->zip_filetime == 'original') {
            DUPX_Log::info("\nShell Exec Current does not support orginal file timestamp please use ZipArchive");
        }

        DUPX_Log::info('SHELL COMMAND: '.DUPX_Log::varToString($command));
        $stderr = shell_exec($command);
        if ($stderr != '') {
            $zip_err_msg = ERR_SHELLEXEC_ZIPOPEN.": $stderr";
            $zip_err_msg .= "<br/><br/><b>To resolve error see <a href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-130-q' target='_blank'>https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-130-q</a></b>";
            DUPX_Log::error($zip_err_msg);
        }
        DUPX_Log::info("<<< Shell-Exec Unzip Complete.");
    }

    public function runZipArchive()
    {
        DUPX_Log::info("\n\nSTART ZIP FILE EXTRACTION STANDARD >>> ");
        $this->runZipArchiveChunking(false);
    }

    public function setFilePermission()
    {
        // When archive engine is ziparchivechunking, File permissions should be run at the end of last chunk (means after full extraction)
        if (self::ENGINE_ZIP_CHUNK == $this->archive_engine && !$this->chunkedExtractionCompleted) {
            return;
        }

        if ($this->set_file_perms || $this->set_dir_perms || (($this->archive_engine == self::ENGINE_ZIP_SHELL) && ($this->zip_filetime == 'current'))) {

            DUPX_Log::info("Resetting permissions");
            $set_file_perms = $this->set_file_perms;
            $set_dir_perms  = $this->set_dir_perms;
            $set_file_mtime = ($this->zip_filetime == 'current');
            $objects        = new RecursiveIteratorIterator(new IgnorantRecursiveDirectoryIterator($this->root_path), RecursiveIteratorIterator::SELF_FIRST);

            foreach ($objects as $name => $object) {
                if ('.' == substr($name, -1)) {
                    continue;
                }

                if ($set_file_perms && is_file($name)) {
                    if (!DupProSnapLibIOU::chmod($name, $this->file_perms_value)) {
                        DUPX_Log::info('CHMOD FAIL: '.$name);
                    }
                } else if ($set_dir_perms && is_dir($name)) {
                    if (!DupProSnapLibIOU::chmod($name, $this->dir_perms_value)) {
                        DUPX_Log::info('CHMOD FAIL: '.$name);
                    }
                }

                if ($set_file_mtime) {
                    @touch($name);
                }
            }
        }
    }

    public function finishFullExtraction()
    {
        DUPX_ServerConfig::reset($this->root_path);

        $ajax1_sum = DUPX_U::elapsedTime(DUPX_U::getMicrotime(), $this->ajax1_start);
        DUPX_Log::info("\nSTEP-1 COMPLETE @ ".@date('h:i:s')." - RUNTIME: {$ajax1_sum}");

        $this->JSON['pass'] = 1;
        error_reporting($this->ajax1_error_level);
        DUPX_Log::close();
        die(DupProSnapJsonU::wp_json_encode($this->JSON));
    }

    public function finishChunkExtraction()
    {
        $this->JSON['pass']               = -1;
        $this->JSON['ajax1_start']        = $this->ajax1_start;
        $this->JSON['archive_offset']     = $this->archive_offset;
        $this->JSON['num_files']          = $this->num_files;
        $this->JSON['sub_folder_archive'] = $this->sub_folder_archive;

        // for displaying notice
        if (self::ENGINE_ZIP_CHUNK == $this->archive_engine) {
            $this->JSON['zip_arc_chunks_extract_rates']          = $this->zip_arc_chunks_extract_rates;
            $this->JSON['zip_arc_chunk_notice_no']               = $this->zip_arc_chunk_notice_no;
            $this->JSON['zip_arc_chunk_notice_change_last_time'] = $this->zip_arc_chunk_notice_change_last_time;
            $this->JSON['zip_arc_chunk_notice']                  = ($this->zip_arc_chunk_notice_no > -1) ? $GLOBALS['ZIP_ARC_CHUNK_EXTRACT_NOTICES'][$this->zip_arc_chunk_notice_no] : '';
        }
        DUPX_Log::close();
        die(DupProSnapJsonU::wp_json_encode($this->JSON));
    }

    public function finishExtraction()
    {
        if ($this->archive_engine != self::ENGINE_ZIP_CHUNK || $this->chunkedExtractionCompleted) {
            $this->finishFullExtraction();
        } else {
            $this->finishChunkExtraction();
        }
    }

    public function isFirstChunk()
    {
        return $this->archive_offset == 0 && $this->archive_engine == self::ENGINE_ZIP_CHUNK;
    }
}