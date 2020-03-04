<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * Defines the scope from which a filter item was created/retrieved from
 * @package DupicatorPro\classes
 */
class DUP_PRO_Archive_Filter_Scope_Base
{

    //All internal storage items that we decide to filter
    public $Core     = array();
    //TODO: Enable with Settings UI
    //Global filter items added from settings
    public $Global   = array();
    //Items when creating a package or template
    public $Instance = array();

}

/**
 * Defines the scope from which a filter item was created/retrieved from
 * @package DupicatorPro\classes
 */
class DUP_PRO_Archive_Filter_Scope_Directory extends DUP_PRO_Archive_Filter_Scope_Base
{

    // Items that are not readable
    public $Warning    = array();
    // Items that are not readable
    public $Unreadable = array();
    // Directories containing other WordPress installs
    public $AddonSites = array();
    //Items that are too large
    public $Size       = array();

}

/**
 * Defines the scope from which a filter item was created/retrieved from
 * @package DupicatorPro\classes
 */
class DUP_PRO_Archive_Filter_Scope_File extends DUP_PRO_Archive_Filter_Scope_Base
{

    // Items that are not readable
    public $Warning    = array();
    // Items that are not readable
    public $Unreadable = array();
    //Items that are too large
    public $Size       = array();

}

/**
 * Defines the filtered items that are pulled from there various scopes
 * @package DupicatorPro\classes
 */
class DUP_PRO_Archive_Filter_Info
{

    /**
     * Contains all folder filter info
     * @var DUP_PRO_Archive_Filter_Scope_Directory 
     */
    public $Dirs  = null;

    /**
     * Contains all folder filter info
     * @var DUP_PRO_Archive_Filter_Scope_File 
     */
    public $Files = null;

    /**
     * Contains all folder filter info
     * @var DUP_PRO_Archive_Filter_Scope_Base 
     */
    public $Exts  = null;

    /**
     * tree size structure for client jstree
     * @var DUP_PRO_Tree_files 
     */
    public $TreeSize = null;

    /**
     * tree char warnings structure for client jstree
     * @var DUP_PRO_Tree_files 
     */
    public $TreeWarning = null;

    public function __construct()
    {
        $this->reset(true);
    }

    /**
     * reset and clean all object
     */
    public function reset($initTreeObjs = false)
    {
        $this->Dirs  = new DUP_PRO_Archive_Filter_Scope_Directory();
        $this->Files = new DUP_PRO_Archive_Filter_Scope_File();
        $this->Exts  = new DUP_PRO_Archive_Filter_Scope_Base();

        if ($initTreeObjs) {
            $this->TreeSize    = new DUP_PRO_Tree_files(ABSPATH, false);
            $this->TreeWarning = new DUP_PRO_Tree_files(ABSPATH, false);
        } else {
            $this->TreeSize    = null;
            $this->TreeWarning = null;
        }
    }
}