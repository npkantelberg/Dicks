<?php
/**
 * Original installer files manager
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\U
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * Original installer files manager
 * 
 * This class saves a file or folder in the original files folder and saves the original location persistant.
 * By entry we mean a file or a folder but not the files contained within it.
 * In this way it is possible, for example, to move an entire plugin to restore it later.
 * 
 * singleton class
 */
final class DUPX_Orig_File_Manager
{

    const MODE_MOVE             = 'move';
    const MODE_COPY             = 'copy';
    const ORIG_FOLDER_PREFIX    = 'original_files_';
    const PERSISTANCE_FILE_NAME = 'entries_stored.json';

    /**
     *
     * @var DUPX_Orig_File_Manager
     */
    private static $instance = null;

    /**
     *
     * @var string
     */
    private $persistanceFile = null;

    /**
     *
     * @var string
     */
    private $origFilesFolder = null;

    /**
     *
     * @var array 
     */
    private $origFolderEntries = array();

    /**
     *
     * @var string 
     */
    private $rootPath = null;

    /**
     *
     * @return DUPX_Orig_File_Manager
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
        $this->rootPath        = DUPX_INIT;
        $this->origFilesFolder = $this->rootPath.'/'.self::ORIG_FOLDER_PREFIX.$GLOBALS['DUPX_AC']->package_hash;
        $this->persistanceFile = $this->origFilesFolder.'/'.self::PERSISTANCE_FILE_NAME;
    }

    /**
     * create a main folder if don't exist and load the entries
     * 
     * @param boolen $reset
     */
    public function init($reset = false)
    {
        $this->createMainFolder($reset);
        $this->load();
    }

    /**
     * 
     * @param boolean $reset    // if true delete current folder
     * @return boolean          // return true if succeded
     * @throws Exception
     */
    public function createMainFolder($reset = false)
    {
        if ($reset) {
            $this->deleteMainFolder();
        }

        if (!file_exists($this->origFilesFolder)) {
            if (!DupProSnapLibIOU::mkdir($this->origFilesFolder, 'u+rwx')) {
                throw new Exception('Can\'t create the original files folder '.DUPX_Log::varToString($this->origFilesFolder));
            }
        }

        $silentFile = $this->origFilesFolder.'/index.php';
        if (!file_exists($silentFile)) {
            if (!file_put_contents($silentFile, '<?php // Silence is golden.')) {
                throw new Exception('Can\'t create the original files folder silent');
            }
        }

        if (!file_exists($this->persistanceFile)) {
            $this->save();
        }

        return true;
    }

    /**
     * delete origianl files folder
     * 
     * @return boolean
     * @throws Exception
     */
    public function deleteMainFolder()
    {
        if (file_exists($this->origFilesFolder) && !DupProSnapLibIOU::rrmdir($this->origFilesFolder)) {
            throw new Exception('Can\'t delete the original files folder '.DUPX_Log::varToString($this->origFilesFolder));
        }
        $this->origFolderEntries = array();

        return true;
    }

    /**
     * add a entry on original folder.
     * 
     * @param string $identifier    // entry identifier
     * @param string $path          // entry path. can be a file or a folder
     * @param string $mode          // MODE_MOVE move the item in original folder
     *                                 MODE_COPY copy the item in original folder
     * @param bool|string $rename   // if rename is a string the item is renamed in original folder.
     * @return boolean              // true if succeded
     * @throws Exception
     */
    public function addEntry($identifier, $path, $mode = self::MODE_MOVE, $rename = false)
    {
        if (!file_exists($path)) {
            return false;
        }

        $baseName = empty($rename) ? basename($path) : $rename;

        $relativePath = DupProSnapLibIOU::getRelativePath($path, $this->rootPath);
        $parentFolder = dirname($relativePath);
        if (empty($parentFolder) || $parentFolder === '.') {
            $parentFolder = '';
        } else {
            $parentFolder .= '/';
        }
        $targetFolder = $this->origFilesFolder.'/'.$parentFolder;
        if (!file_exists($targetFolder)) {
            DupProSnapLibIOU::mkdir_p($targetFolder);
        }
        $dest = $targetFolder.$baseName;

        switch ($mode) {
            case self::MODE_MOVE:
                if (!DupProSnapLibIOU::rename($path, $dest)) {
                    throw new Exception('Can\'t move the original file  '.DUPX_Log::varToString($path));
                }
                break;
            case self::MODE_COPY:
                if (!DupProSnapLibIOU::rcopy($path, $dest)) {
                    throw new Exception('Can\'t copy the original file  '.DUPX_Log::varToString($path));
                }
                break;
            default:
                throw new Exception('invalid mode addEntry');
        }

        $this->origFolderEntries[$identifier] = array(
            'baseName' => $baseName,
            'source'   => $path,
            'stored'   => $dest,
            'mode'     => $mode
        );

        $this->save();
        return true;
    }

    /**
     * get entry info from itendifier
     * 
     * @param string $identifier
     * @return boolean  // false if entry don't exists
     */
    public function getEntry($identifier)
    {
        if (isset($this->origFolderEntries[$identifier])) {
            return $this->origFolderEntries[$identifier];
        } else {
            return false;
        }
    }

    /**
     * this function restore current entry in original position.
     * If mode is copy it simply delete the entry else move the entry in original position
     * 
     * @param string $identifier    // identified of current entrye
     * @param boolean $save         // update saved entries
     * @return boolean              // true if succeded
     * @throws Exception
     */
    public function restoreEntry($identifier, $save = true)
    {
        if (!isset($this->origFolderEntries[$identifier])) {
            return false;
        }

        $entry = $this->origFolderEntries[$identifier];

        switch ($entry['mode']) {
            case self::MODE_MOVE:
                if (!DupProSnapLibIOU::rename($entry['stored'], $entry['source'])) {
                    throw new Exception('Can\'t move the original file  '.DUPX_Log::varToString($entry['stored']));
                }
                break;
            case self::MODE_COPY:
                if (!DupProSnapLibIOU::rrmdir($entry['stored'])) {
                    throw new Exception('Can\'t delete entry '.DUPX_Log::varToString($entry['stored']));
                }
                break;
            default:
                throw new Exception('invalid mode addEntry');
        }

        unset($this->origFolderEntries[$identifier]);
        if ($save) {
            $this->save();
        }
        return true;
    }

    /**
     * put all entries on original position and empty original folder
     * 
     * @return boolean
     */
    public function restoreAll()
    {
        foreach (array_keys($this->origFolderEntries) as $ident) {
            $this->restoreEntry($ident, false);
        }
        $this->save();
        return true;
    }

    /**
     * save notices from json file
     */
    public function save()
    {
        if (!file_put_contents($this->persistanceFile, DupProSnapJsonU::wp_json_encode_pprint($this->origFolderEntries))) {
            throw new Exception('Can\'t write persistence file');
        }
        return true;
    }

    /**
     * load notice from json file
     */
    private function load()
    {
        if (file_exists($this->persistanceFile)) {
            $json                    = file_get_contents($this->persistanceFile);
            $this->origFolderEntries = json_decode($json, true);
        } else {
            $this->origFolderEntries = array();
        }
        return true;
    }

    private function __clone()
    {
        
    }

    private function __wakeup()
    {
        
    }
}