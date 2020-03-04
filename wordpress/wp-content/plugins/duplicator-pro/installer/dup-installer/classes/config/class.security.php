<?php
/**
 * Security class
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\Constants
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

require_once(DUPX_INIT.'/classes/class.csrf.php');

/**
 * singleton class 
 * 
 * 
 * In this class all installer security checks are performed. If the security checks are not passed, an exception is thrown and the installer is stopped.
 * This happens before anything else so the class must work without the initialization of all global duplicator variables.
 */
class DUPX_Security
{

    const VIEW_TOKEN   = 'view_csrf_token';
    const CTRL_TOKEN   = 'ctrl_csrf_token';
    const DAWN_TOKEN   = 'daws_csrf_token';
    const ROUTER_TOKEN = 'router_csrf_token';

    /**
     *
     * @var self
     */
    private static $instance = null;

    /**
     * archive path read from  csrf file
     * @var string 
     */
    private $archivePath = null;

    /**
     *
     * @var bootloader read from csrf file
     */
    private $bootloader = null;

    /**
     * package hash read from csrf file
     * @var string 
     */
    private $packageHash = null;

    /**
     *
     * @return self
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
        DUPX_CSRF::init(DUPX_INIT, DUPX_Boot::getPackageHash());
        
        if (!file_exists(DUPX_CSRF::getFilePath())) {
            throw new Exception("CSRF FILE NOT FOUND\n"
                . "Please, check webroot file permsission and dup-installer folder permission");
        }

        $this->bootloader  = DUPX_CSRF::getVal('bootloader');
        $this->archivePath = DUPX_CSRF::getVal('archive');
        $this->packageHash = DUPX_CSRF::getVal('package_hash');
    }

    /**
     * archive path read from intaller.php passed by DUPX_CSFR
     * 
     * @return string
     */
    public function getArchivePath()
    {
        return $this->archivePath;
    }

    /**
     * bootloader path read from intaller.php passed by DUPX_CSFR
     * 
     * @return string
     */
    public function getBootloader()
    {
        return $this->bootloader;
    }

    /**
     * package hash read from intaller.php passed by DUPX_CSFR
     * 
     * @return string  
     */
    public function getPackageHash()
    {
        return $this->packageHash;
    }

    /**
     * 
     * @return boolean
     * @throws Exception    // if fail throw exception of return true
     */
    public function check()
    {
        try {
            // check if current package hash is equal at bootloader package hash
            if ($this->packageHash !== DUPX_Boot::getPackageHash()) {
                throw new Exception('Incorrect hash package');
            }

            $token_tested = false;
            // @todo connect with global debug
            $debug        = false;

            $action = null;
            if (DUPX_Ctrl_ajax::isAjax($action) == true) {
                if (($token = self::getTokenFromInput(DUPX_Ctrl_ajax::TOKEN_NAME)) === false) {
                    $msg = 'Security issue'.($debug ? ' LINE: '.__LINE__.' TOKEN: '.$token.' KEY NAME: '.DUPX_Ctrl_ajax::TOKEN_NAME : '');
                    throw new Exception($msg);
                }
                if (!DUPX_CSRF::check(self::getTokenFromInput(DUPX_Ctrl_ajax::TOKEN_NAME), DUPX_Ctrl_ajax::getTokenKeyByAction($action))) {
                    $msg = 'Security issue'.($debug ? ' LINE: '.__LINE__.' TOKEN: '.$token.' KEY NAME: '.DUPX_Ctrl_ajax::getTokenKeyByAction($action).' KEY VALUE '.DUPX_Ctrl_ajax::getTokenKeyByAction($action) : '');
                    throw new Exception($msg);
                }
                $token_tested = true;
            }

            if (($token = self::getTokenFromInput(self::VIEW_TOKEN)) !== false) {
                if (!isset($_REQUEST[DUPX_Paramas_Manager::PARAM_VIEW])) {
                    $msg = 'Security issue'.($debug ? ' LINE: '.__LINE__.' TOKEN: '.$token.' KEY NAME: '.DUPX_Paramas_Manager::PARAM_VIEW : '');
                    throw new Exception($msg);
                }
                if (!DUPX_CSRF::check($token, $_REQUEST[DUPX_Paramas_Manager::PARAM_VIEW])) {
                    $msg = 'Security issue'.($debug ? ' LINE: '.__LINE__.' TOKEN: '.$token.' KEY NAME: '.DUPX_Paramas_Manager::PARAM_VIEW.' KEY VALUE '.$_REQUEST[DUPX_Paramas_Manager::PARAM_VIEW] : '');
                    throw new Exception($msg);
                }
                $token_tested = true;
            }

            if (($token = self::getTokenFromInput(self::CTRL_TOKEN)) !== false) {
                if (!isset($_REQUEST[DUPX_Paramas_Manager::PARAM_CTRL_ACTION])) {
                    $msg = 'Security issue'.($debug ? ' LINE: '.__LINE__.' TOKEN: '.$token.' KEY NAME: '.DUPX_Paramas_Manager::PARAM_CTRL_ACTION : '');
                    throw new Exception($msg);
                }
                if (!DUPX_CSRF::check($token, $_REQUEST[DUPX_Paramas_Manager::PARAM_CTRL_ACTION])) {
                    $msg = 'Security issue'.($debug ? ' LINE: '.__LINE__.' TOKEN: '.$token.' KEY NAME: '.DUPX_Paramas_Manager::PARAM_CTRL_ACTION.' KEY VALUE '.$_REQUEST[DUPX_Paramas_Manager::PARAM_CTRL_ACTION] : '');
                    throw new Exception($msg);
                }
                $token_tested = true;
            }

            if (($token = self::getTokenFromInput(self::ROUTER_TOKEN)) !== false) {
                if (!isset($_REQUEST[DUPX_Paramas_Manager::PARAM_ROUTER_ACTION])) {
                    $msg = 'Security issue'.($debug ? ' LINE: '.__LINE__.' TOKEN: '.$token.' KEY NAME: '.DUPX_Paramas_Manager::PARAM_ROUTER_ACTION : '');
                    throw new Exception($msg);
                }
                if (!DUPX_CSRF::check($token, $_REQUEST[DUPX_Paramas_Manager::PARAM_ROUTER_ACTION])) {
                    $msg = 'Security issue'.($debug ? ' LINE: '.__LINE__.' TOKEN: '.$token.' KEY NAME: '.DUPX_Paramas_Manager::PARAM_ROUTER_ACTION.' KEY VALUE '.$_REQUEST[DUPX_Paramas_Manager::PARAM_ROUTER_ACTION] : '');
                    throw new Exception($msg);
                }
                $token_tested = true;
            }

            // At least one token must always and in any case be tested
            if (!$token_tested) {
                throw new Exception('Security issue: no token found');
            }
        }
        catch (Exception $e) {
            if (function_exists('error_clear_last')) {
                /**
                 * comment error_clear_last if you want see te exception html on shutdown
                 */
                error_clear_last();
            }

            DUPX_Log::logException($e, DUPX_Log::LV_DEFAULT, 'SECURITY ISSUE: ');
            $this->securityIssueLayout($e->getMessage());
            die();
        }

        /*
          $post_csrf_token = $_REQUEST['csrf_token'];
          if (!DUPX_CSRF::check($post_csrf_token, $paramView)) {
          DUPX_Log::info('SECURITY ISSUE')
          DUPX_Log::error("An invalid request was made to '{$paramView}'.  In order to protect this request from unauthorized access please "
          ."<a href='../".DUPX_Security::getInstance()->getBootloader()."'>restart this install process</a>.");
          } */

        return true;
    }

    /**
     * get sanitized token frominput
     * 
     * @param string $tokenName
     * @return string
     */
    protected static function getTokenFromInput($tokenName)
    {
        // CHECK POST
        $token = filter_input(INPUT_POST, $tokenName, FILTER_SANITIZE_STRING, array('options' => array('default' => false)));
        if ($token === false) {
            // CHECK GET
            $token = filter_input(INPUT_GET, $tokenName, FILTER_SANITIZE_STRING, array('options' => array('default' => false)));
        }
        return $token;
    }

    /**
     * security issue html page
     * 
     * @param string $message
     */
    protected function securityIssueLayout($message)
    {
        ob_start();
        ?>
        <h1>DUPLICATOR PRO: SECURITY ISSUE</h1>
        An invalid request was made.<br>
        Message: <b><?php echo htmlspecialchars($message); ?></b><br>
        <br>
        In order to protect this request from unauthorized access <b>please restart this install process.</b>
        <?php
        $content = ob_get_clean();
        DUPX_Boot::problemLayout($content);
    }

    /**
     * password check, return true if test pass
     * 
     * @param strng $password
     * @return boolean
     */
    public static function passwordArciveCheck($password)
    {
        if ($GLOBALS['DUPX_AC']->secure_on) {
            $pass_hasher = new DUPX_PasswordHash(8, FALSE);
            return $pass_hasher->CheckPassword(base64_encode($password), $GLOBALS['DUPX_AC']->secure_pass);
        } else {
            return true;
        }
    }
}