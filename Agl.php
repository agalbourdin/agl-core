<?php
namespace Agl;

/**
 * Include the required application class and initialize the Autoload.
 */

require(__DIR__ . DS . 'Autoload.php');
new Autoload();

require(__DIR__ . DS . 'Exception.php');

/**
 * Mother application class.
 *
 * @category Agl
 * @package Agl
 * @version 0.1.0
 */

final class Agl
{
    /**
     * Agl version.
     */
    const AGL_VERSION = '0.1.0';

    /**
     * Available protocols and dirs.
     */
    const AGL_CORE_DIR              = 'Core';
    const AGL_MORE_DIR              = 'More';
    const AGL_LIB_DIR               = 'Lib';

    const AGL_CORE_POOL             = 'core';
    const AGL_MORE_POOL             = 'more';

    const APP_PHP_DIR               = 'app/php';
    const APP_ETC_DIR               = 'app/etc';
    const APP_VAR_DIR               = 'app/var';
    const APP_PUBLIC_DIR            = 'public';

    const PHP_EXT                   = '.php';

    /**
     * Agl Singleton.
     *
     * @var Agl
     */
    private static $_instance = NULL;

    /**
     * Current application absolute path.
     *
     * @var string
     */
    private $_appPath = NULL;

    /**
     * Should AGL cache the configuration parameters?
     */
    private $_cache = false;

    /**
     * Save the database instance.
     *
     * @var Connection
     */
    private $_db = NULL;

    /**
     * Running in debug mode.
     *
     * @var Debug
     */
    private $_debug = false;

    /**
     * Save the config instance.
     *
     * @var Xml
     */
    private $_config = NULL;

    /**
     * Access the class as a Singleton (run() must be called before the first
     * call to app()).
     *
     * @return Agl
     */
    public static function app()
    {
        if (self::$_instance === NULL) {
            throw new Exception("The application must be initialized - app() cannot be triggered before run()");
        }

        return self::$_instance;
    }

    /**
     * Create an instance of Agl and initialize it.
     *
     * @param bool $pCache
     * @param bool $pDebug
     */
    public static function run($pCache = false, $pDebug = false)
    {
        if (self::$_instance === NULL) {
            self::$_instance = new self($pCache, $pDebug);
        } else {
            throw new Exception("The application has already been initialized - use app() to get access to it");
        }
    }

    /**
     * Call the validation class to validate an associative array of types and
     * values. Throw an exception if validation fail.
     *
     * @param array $pParams
     * @return bool
     */
    public static function validateParams(array $pParams)
    {
        if (! \Agl\Core\Data\Validation::validate($pParams)) {
            throw new \Agl\Exception("Validation failed for data '" . json_encode($pParams) . "'");
        }

        return true;
    }

    /**
     * Create and return a new Tree.
     *
     * @param string $pCollection The type of the collection to use to create
     * the tree
     * @return Tree
     */
    public static function getTree(\Agl\Core\Db\Item\Item $pItem)
    {
        $tree = new \Agl\Core\Db\Tree\Tree($pItem->getDbContainer());
        $tree->setMainItem($pItem);
        return $tree;
    }

    /**
     * Create and return a new Collection.
     *
     * @param string $pCollection The type of the collection to create
     * @return Collection
     */
    public static function getCollection($pCollection)
    {
        return new \Agl\Core\Db\Collection\Collection($pCollection);
    }

    /**
     * Create and return a new Conditions instance.
     *
     * @return Conditions
     */
    public static function newConditions()
    {
        return new \Agl\Core\Db\Query\Conditions\Conditions();
    }

    /**
     * Create a new instance of the requested class.
     *
     * @param string $pClass Class path
     * @param array $pArgs Arguments to construct the requested class
     * @return mixed
     */
    public static function getInstance($pClass, array $pArgs = array())
    {
        return \Agl\Core\Loader\Loader::getInstance($pClass, $pArgs);
    }

    /**
     * Create a new instance of the requested class as a singleton.
     *
     * @param string $pClass Class path
     * @param array $pArgs Arguments to construct the requested class
     * @return mixed
     */
    public static function getSingleton($pClass, array $pArgs = array())
    {
        return \Agl\Core\Loader\Loader::getSingleton($pClass, $pArgs);
    }

    /**
     * Return an instance of the requested model.
     *
     * @param string $pModel
     * @param array $pFields Attributes to add to the item
     * @return mixed
     */
    public static function getModel($pModel, array $pFields = array())
    {
        return \Agl\Core\Loader\Loader::getModel($pModel, $pFields);
    }

    /**
     * Create a singleton instance of the requested helper class.
     *
     * @param string $pClass
     * @return mixed
     */
    public static function getHelper($pClass)
    {
        return \Agl\Core\Loader\Loader::helper($pClass);
    }

    /**
     * Get the request instance as a singleton.
     *
     * @param string|null $pRequestUri URI to parse
     * @return Request
     */
    public static function getRequest($pRequestUri = NULL)
    {
        return self::getSingleton(self::AGL_CORE_DIR . '/request/request', array($pRequestUri));
    }

    /**
     * Get the application's session as a singleton.
     *
     * @return Session
     */
    public static function getSession()
    {
        return self::getSingleton(self::AGL_CORE_DIR . '/session/session');
    }

    /**
     * Get the application's Authentication class as a singleton.
     *
     * @return Auth
     */
    public static function auth()
    {
        return self::getSingleton(self::AGL_CORE_DIR . '/acl/auth');
    }

    /**
     * Dispatch the event $pName.
     *
     * @param type $pName Name of the event to dispatch
     * @param type array $pArgs Arguments to pass to the event
     * @return bool
     */
    public static function dispatchEvent($pName, array $pArgs = array())
    {
        return \Agl\Core\Observer\Observer::dispatch($pName, $pArgs);
    }

    /**
     * Log a message to the syslog.
     *
     * @return bool
     */
    public static function log($pMessage)
    {
        return \Agl\Core\Debug\Debug::log($pMessage);
    }

    /**
     * Check if Agl has been initialized.
     *
     * @return bool
     */
    public static function isInitialized()
    {
        return (self::$_instance === NULL) ? false : true;
    }

    /**
     * Return the array of the More modules loaded into AGL.
     *
     * @return array
     */
    public static function getLoadedModules()
    {
        return \Agl\Core\Loader\Loader::getLoadedModules();
    }

    /**
     * Check if a More module is loaded into AGL.
     *
     * @return bool
     */
    public static function isModuleLoaded($pModule)
    {
        return \Agl\Core\Loader\Loader::isModuleLoaded($pModule);
    }

    /**
     * Return a formated URL with module, view, action and parameters.
     *
     * @param string $pUrl URL to get (module/view)
     * @param array $pParams Parameters to include into the request
     * @param bool $pRelative Create a relative URL
     * @return string
     */
    public static function getUrl($pUrl, array $pParams = array(), $pRelative = true)
    {
        return \Agl\Core\Url\Url::getUrl($pUrl, $pParams, $pRelative);
    }

    /**
     * Return the base URL of the application.
     *
     * @param bool $pRelative Create a relative URL
     * @return string
     */
    public static function getBaseUrl($pRelative = true)
    {
        return \Agl\Core\Url\Url::getBaseUrl($pRelative);
    }

    /**
     * Get the skin base URL.
     *
     * @param string $pUrl Relative URL inside the skin directory
     * @param bool $pRelative Create a relative URL
     * @return string
     */
    public static function getSkinUrl($pUrl, $pRelative = true)
    {
        return \Agl\Core\Url\Url::getSkinUrl($pUrl, $pRelative);
    }

    /**
     * Get the public base URL.
     *
     * @param string $pUrl Relative URL inside the public directory
     * @param bool $pRelative Create a relative URL
     * @return string
     */
    public static function getPublicUrl($pUrl, $pRelative = true)
    {
        return \Agl\Core\Url\Url::getPublicUrl($pUrl, $pRelative);
    }

    /**
     * Return the current URL with optional additional params.
     *
     * @param array $pParams Parameters to add to the request (additional)
     * @return string
     */
    public static function getCurrentUrl(array $pParams = array())
    {
        return \Agl\Core\Url\Url::getCurrent($pParams);
    }

    /**
     * Load a library linked to a More module.
     *
     * @param $pPath Absolute path to the module directory
     * @param string $pLib The library filename, relative to the module path
     * @return bool
     */
    public static function loadModuleLib($pPath, $pLib)
    {
        $file = $pPath
                . DS
                . \Agl::AGL_LIB_DIR
                . DS
                . $pLib;

        require_once($file);

        return true;
    }

    /**
     * Route the request.
     *
     * @param string $pRequestUri Request URI to route
     * @return Agl
     */
    public static function route($pRequestUri)
    {
        $router = new \Agl\Core\Mvc\Controller\Router($pRequestUri);
        $router->route();
    }

    /**
     * Initialize the database connection if any.
     */
    private function _initDb()
    {
        $this->_db = new \Agl\Core\Db\Connection\Connection(
            $this->getConfig('@app/db/host'),
            $this->getConfig('@app/db/name'),
            $this->getConfig('@app/db/user'),
            $this->getConfig('@app/db/password')
        );
    }

    /**
     * Initialize Agl.
     *
     * Save the requested App ID.
     *
     * @param bool $pCache
     * @param bool $pDebug
     */
    private function __construct($pCache, $pDebug)
    {
        if (date_default_timezone_get() !== \Agl\Core\Data\Date::DEFAULT_TZ) {
            date_default_timezone_set(\Agl\Core\Data\Date::DEFAULT_TZ);
        }

        $this->_appPath = realpath('.') . DS;
        $this->_cache   = ($pCache) ? true : false;
        $this->_debug   = ($pDebug) ? true : false;

        error_reporting(E_ALL);
        if ($pDebug) {
            $debug = self::getSingleton(self::AGL_CORE_DIR . '/debug/debug');
        }
    }

    /**
     * Class destructor.
     *
     * If the appllication is running in dev mode, display some debug
     * informations.
     */
    public function __destruct()
    {
        if ($this->_debug) {
            var_dump(self::getSingleton(self::AGL_CORE_DIR . '/debug/debug')->getDebugInfos());
        }
    }

    /**
     * Return the current application path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->_appPath;
    }

    /**
     * Check if the application is running on development mode.
     *
     * @return bool
     */
    public function isDebugMode()
    {
        return $this->_debug;
    }

    /**
     * Return the current App ID.
     *
     * @return bool
     */
    public function isCacheEnabled()
    {
        return $this->_cache;
    }

    /**
     * Retrieve a value in the Agl XML configuration file.
     *
     * @param string $pPath The configuration path to retrieve
     * @param array $pForceGlobalArray Return the results in a multidimensional
     * array
     * @return mixed The configuration value corresponding to the path
     */
    public function getConfig($pPath, $pForceGlobalArray = false)
    {
        if ($this->_config === NULL) {
            $this->_config = new \Agl\Core\Config\Json\Json();
        }

        return $this->_config->getConfig($pPath, $pForceGlobalArray);
    }

    /**
     * Det the database instance (or NULL if not exists).
     *
     * @return mixed
     */
    public function getDb()
    {
        if ($this->_db === NULL and $this->getConfig('@app/db/engine')) {
            $this->_initDb();
        }

        return $this->_db;
    }
}
