<?php
/**
 * Class used to update and edit web server configuration files
 * for .htaccess, web.config and user.ini
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\Crypt
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * Package related functions
 *
 */
final class DUPX_Package
{

    /**
     * 
     * @staticvar bool|string $packageHash
     * @return bool|string false if fail
     */
    public static function getPackageHash()
    {
        static $packageHash = null;
        if (is_null($packageHash)) {
            if (($packageHash = DUPX_Boot::getPackageHash()) === false) {
                throw new Exception('PACKAGE ERROR: can\'t find package hash');
            }
        }
        return $packageHash;
    }

    /**
     * 
     * @staticvar string $archivePath
     * @return bool|string false if fail
     */
    public static function getPackageArchivePath()
    {
        static $archivePath = null;
        if (is_null($archivePath)) {
            $path = DUPX_INIT.'/'.DUPX_Boot::ARCHIVE_PREFIX.self::getPackageHash().DUPX_Boot::ARCHIVE_EXTENSION;
            if (!file_exists($path)) {
                throw new Exception('PACKAGE ERROR: can\'t read package path: '.$path);
            } else {
                $archivePath = $path;
            }
        }
        return $archivePath;
    }

    /**
     *
     * @return string
     */
    public static function getWpconfigArkPath()
    {
        return DUPX_Paramas_Manager::getInstance()->getValue(DUPX_Paramas_Manager::PARAM_PATH_NEW).'/dup-wp-config-arc__'.self::getPackageHash().'.txt';
    }

    /**
     * 
     * @staticvar type $path
     * @return string
     */
    public static function getWpconfigSamplePath()
    {
        static $path = null;
        if (is_null($path)) {
            $path = DUPX_INIT.'/assets/wp-config-sample.php';
        }
        return $path;
    }

    /**
     * Get htaccess archive file path
     * 
     * @return string the .htaccess file's path
     */
    public static function getHtaccessArkPath()
    {
        return DUPX_Paramas_Manager::getInstance()->getValue(DUPX_Paramas_Manager::PARAM_PATH_NEW).'/.htaccess__'.$GLOBALS['DUPX_AC']->package_hash;
    }
}