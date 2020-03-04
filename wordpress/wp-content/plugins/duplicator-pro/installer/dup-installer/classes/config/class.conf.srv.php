<?php
/**
 * Class used to update and edit web server configuration files
 * for .htaccess, web.config and user.ini
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\ServerConfig
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

class DUPX_ServerConfig
{
    /**
     * Common timestamp of all members of this class
     * 
     * @staticvar type $time
     * @return type
     */
    public static function getFixedTimestamp()
    {
        static $time = null;

        if (is_null($time)) {
            $time = date("ymdHis");
        }

        return $time;
    }

    /**
     * Creates a copy of the original server config file and resets the original to blank
     *
     * @param string $path		The root path to the location of the server config files
     *
     * @return null
     */
    public static function reset($path)
    {
        $paramsManager = DUPX_Paramas_Manager::getInstance();

        if ($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_HTACCESS_CONFIG) == 'new') {
            $time = self::getFixedTimestamp();
            DUPX_Log::info("\nWEB SERVER CONFIGURATION FILE STATUS:");

            //Apache
            $overwrite_htaccess        = "{$path}/.htaccess";
            $backup_overwrite_htaccess = $path.'/.htaccess-'.$GLOBALS['DUPX_AC']->package_hash.'.orig';

            $status = false;
            if (file_exists($backup_overwrite_htaccess)) {
                @unlink($backup_overwrite_htaccess);
            }
            if (file_exists($overwrite_htaccess)) {
                if (copy($overwrite_htaccess, $backup_overwrite_htaccess)) {
                    $status = @unlink($overwrite_htaccess);
                }
            }

            if ($status) {
                DUPX_Log::info("- .htaccess was reset and a backup made to .htaccess-[HASH].orig");
                $htacess_content = "#This file has been reset by Duplicator Pro. See .htaccess-".$GLOBALS['DUPX_AC']->package_hash." for the original file";
                $htacess_content .= self::getOldHtaccessAddhandlerLine($path);
                file_put_contents("{$path}/.htaccess", $htacess_content);
                DupProSnapLibIOU::chmod("{$path}/.htaccess", 0644);
            } else {
                DUPX_Log::info("- .htaccess file was not reset or backed up.");
            }
        }

        if ($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_OTHER_CONFIG) == 'new') {
            //.user.ini - For WordFence
            self::runReset($path, '.user.ini');

            //IIS: This is reset because on some instances of IIS having old values cause issues
            //Recommended fix for users who want it because errors are triggered is to have
            //them check the box for ignoring the web.config files on step 1 of installer
            if (self::runReset($path, 'web.config')) {
                $xml_contents = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
                $xml_contents .= "<!-- Reset by Duplicator Installer.  Original can be found in web.config.{$time}.orig -->\n";
                $xml_contents .= "<configuration></configuration>\n";
                @file_put_contents("{$path}/web.config", $xml_contents);
            }
        }
    }

    /**
     * Get AddHadler line from existing WP .htaccess file
     *
     * @param $path string root path
     * @return string
     */
    private static function getOldHtaccessAddhandlerLine($path)
    {
        $backupHtaccessPath = $path.'/.htaccess-'.$GLOBALS['DUPX_AC']->package_hash.'.orig';
        if (file_exists($backupHtaccessPath)) {
            $htaccessContent = file_get_contents($backupHtaccessPath);
            if (!empty($htaccessContent)) {
                // match and trim non commented line  "AddHandler application/x-httpd-XXXX .php" case insenstive
                $re      = '/^[\s\t]*[^#]?[\s\t]*(AddHandler[\s\t]+.+\.php[ \t]?.*?)[\s\t]*$/mi';
                $matches = array();
                if (preg_match($re, $htaccessContent, $matches)) {
                    return "\n".$matches[1];
                }
            }
        }
        return '';
    }

    /**
     * Copies the code in .htaccess__[HASH] to .htaccess
     *
     * @param $path					The root path to the location of the server config files
     * @param $new_htaccess_name	New name of htaccess (either .htaccess or a backup name)
     *
     * @return bool					Returns true if the .htaccess file was retained successfully
     */
    public static function renameHtaccess($path, $new_htaccess_name)
    {
        $status = false;

        if (!@rename($path.'/.htaccess__'.$GLOBALS['DUPX_AC']->package_hash, $path.'/'.$new_htaccess_name)) {
            $status = true;
        }

        return $status;
    }

    /**
     * Sets up the web config file based on the inputs from the installer forms.
     *
     * @param int $mu_mode		Is this site a specific multi-site mode
     * @param object $dbh		The database connection handle for this request
     * @param string $path		The path to the config file
     *
     * @return null
     */
    public static function setup($mu_mode, $mu_generation, $dbh, $path)
    {
        DUPX_Log::info("\nWEB SERVER CONFIGURATION FILE UPDATED:");

        $paramsManager = DUPX_Paramas_Manager::getInstance();

        // SKIP HTACCESS
        $skipHtaccessConfigVals = array('nothing', 'original');
        if (in_array($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_HTACCESS_CONFIG), $skipHtaccessConfigVals)) {
            DUPX_Log::info("\nNOTICE: Retaining the original .htaccess, .user.ini and web.config files may cause");
            DUPX_Log::info("issues with the initial setup of your site.  If you run into issues with your site or");
            DUPX_Log::info("during the install process please uncheck the 'Config Files' checkbox labeled:");
            DUPX_Log::info("'Retain original .htaccess, .user.ini and web.config' and re-run the installer.");
            return;
        }

        $timestamp    = date("Y-m-d H:i:s");
        $post_url_new = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_URL_NEW);
        $newdata      = parse_url($post_url_new);
        $newpath      = DUPX_U::addSlash(isset($newdata['path']) ? $newdata['path'] : "");
        $update_msg   = "# This file was updated by Duplicator Pro on {$timestamp}.\n";
        $update_msg   .= (file_exists("{$path}/.htaccess")) ? "# See .htaccess__{$GLOBALS['DUPX_AC']->package_hash} for the .htaccess original file." : "";
        $update_msg   .= self::getOldHtaccessAddhandlerLine($path);

        switch ($mu_mode) {
            case DUPX_MultisiteMode::SingleSite:
            case DUPX_MultisiteMode::Standalone:
                $tmp_htaccess = self::htAcccessNoMultisite($update_msg, $newpath, $dbh);
                DUPX_Log::info("- Preparing .htaccess file with basic setup.");
                break;
            case DUPX_MultisiteMode::Subdomain:
                if ($mu_generation == 1) {
                    $tmp_htaccess = self::htAccessSubdomainPre53($update_msg, $newpath);
                } else {
                    $tmp_htaccess = self::htAccessSubdomain($update_msg, $newpath);
                }
                DUPX_Log::info("- Preparing .htaccess file with multisite subdomain setup.");
                break;
            case DUPX_MultisiteMode::Subdirectory:
                if ($mu_generation == 1) {
                    $tmp_htaccess = self::htAccessSubdirectoryPre35($update_msg, $newpath);
                } else {
                    $tmp_htaccess = self::htAccessSubdirectory($update_msg, $newpath);
                }
                DUPX_Log::info("- Preparing .htaccess file with multisite subdirectory setup.");
                break;
            default:
                throw new Exception('Unknown mode');
        }

        if (file_put_contents("{$path}/.htaccess", $tmp_htaccess) === FALSE) {
            DUPX_Log::info("WARNING: Unable to update the .htaccess file! Please check the permission on the root directory and make sure the .htaccess exists.");
        } else {
            DUPX_Log::info("- Successfully updated the .htaccess file setting.");
        }
        DupProSnapLibIOU::chmod("{$path}/.htaccess", 0644);
    }

    private static function htAcccessNoMultisite($update_msg, $newpath, $dbh)
    {
        $result         = '';
        // no multisite
        $empty_htaccess = false;
        $escapedTablePrefix = mysqli_real_escape_string($dbh, DUPX_Paramas_Manager::getInstance()->getValue(DUPX_Paramas_Manager::PARAM_DB_TABLE_PREFIX));

        $query_result   = DUPX_DB::mysqli_query($dbh, "SELECT option_value FROM `".$escapedTablePrefix."options` WHERE option_name = 'permalink_structure' ");
        
        if ($query_result) {
            $row = @mysqli_fetch_array($query_result);
            if ($row != null) {
                $permalink_structure = trim($row[0]);
                $empty_htaccess      = empty($permalink_structure);
            }
        }

        if ($empty_htaccess) {
            $result = '';
        } else {
            $result = <<<HTACCESS
{$update_msg}
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase {$newpath}
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . {$newpath}index.php [L]
</IfModule>
# END WordPress
HTACCESS;
        }

        return $result;
    }

    private static function htAccessSubdomainPre53($update_msg, $newpath)
    {
        // Pre wordpress 3.5
        $result = <<<HTACCESS
{$update_msg}
# BEGIN WordPress (Pre 3.5 Multisite Subdomain)
RewriteEngine On
RewriteBase {$newpath}
RewriteRule ^index\.php$ - [L]

# uploaded files
RewriteRule ^files/(.+) wp-includes/ms-files.php?file=$1 [L]

RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]
RewriteRule . index.php [L]
# END WordPress
HTACCESS;
        return $result;
    }

    private static function htAccessSubdomain($update_msg, $newpath)
    {
        // 3.5+
        $result = <<<HTACCESS
{$update_msg}
# BEGIN WordPress (3.5+ Multisite Subdomain)
RewriteEngine On
RewriteBase {$newpath}
RewriteRule ^index\.php$ - [L]

# add a trailing slash to /wp-admin
RewriteRule ^wp-admin$ wp-admin/ [R=301,L]

RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]
RewriteRule ^(wp-(content|admin|includes).*) $1 [L]
RewriteRule ^(.*\.php)$ $1 [L]
RewriteRule . index.php [L]
# END WordPress
HTACCESS;
        return $result;
    }

    private static function htAccessSubdirectoryPre35($update_msg, $newpath)
    {
        // Pre 3.5
        $result = <<<HTACCESS
{$update_msg}
# BEGIN WordPress (Pre 3.5 Multisite Subdirectory)
RewriteEngine On
RewriteBase {$newpath}
RewriteRule ^index\.php$ - [L]

# uploaded files
RewriteRule ^([_0-9a-zA-Z-]+/)?files/(.+) wp-includes/ms-files.php?file=$2 [L]

# add a trailing slash to /wp-admin
RewriteRule ^([_0-9a-zA-Z-]+/)?wp-admin$ $1wp-admin/ [R=301,L]

RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]
RewriteRule ^[_0-9a-zA-Z-]+/(wp-(content|admin|includes).*) $1 [L]
RewriteRule ^[_0-9a-zA-Z-]+/(.*\.php)$ $1 [L]
RewriteRule . index.php [L]
# END WordPress
HTACCESS;
        return $result;
    }

    private static function htAccessSubdirectory($update_msg, $newpath)
    {
        $result = <<<HTACCESS
{$update_msg}
# BEGIN WordPress (3.5+ Multisite Subdirectory)
RewriteEngine On
RewriteBase {$newpath}
RewriteRule ^index\.php$ - [L]

# add a trailing slash to /wp-admin
RewriteRule ^([_0-9a-zA-Z-]+/)?wp-admin$ $1wp-admin/ [R=301,L]

RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]
RewriteRule ^([_0-9a-zA-Z-]+/)?(wp-(content|admin|includes).*) $2 [L]
RewriteRule ^([_0-9a-zA-Z-]+/)?(.*\.php)$ $2 [L]
RewriteRule . index.php [L]
# END WordPress
HTACCESS;
        return $result;
    }

    /**
     * Creates a copy of the original server config file and resets the original to blank per file
     *
     * @param string $path		The root path to the location of the server config file
     * @param string $file_name	The file name of the config file
     *
     * @return bool		Returns true if the file was backed-up and reset.
     */
    private static function runReset($path, $file_name)
    {
        $status = false;
        $file   = "{$path}/{$file_name}";
        $time   = $time   = self::getFixedTimestamp();

        if (file_exists($file)) {
            if (copy($file, "{$file}-{$time}.orig")) {
                $status = @unlink("{$path}/{$file_name}");
            }
        }

        ($status) ? DUPX_Log::info("- {$file_name} was reset and a backup made to {$file_name}-{$time}.orig.") : DUPX_Log::info("- {$file_name} file was not reset or backed up.");

        return $status;
    }
}
