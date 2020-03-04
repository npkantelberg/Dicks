<?php
/**
 * controller step 1
 * 
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package CTRL
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

DUPX_U::maintenanceMode(true);

$extractor = new DUP_PRO_Extraction($_POST);

$extractor->runExtraction();
$extractor->setFilePermission();
$extractor->finishExtraction();
