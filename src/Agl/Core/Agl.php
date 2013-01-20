<?php
namespace Agl\Core;

use \Agl\Core\Config\Json\Json as JsonConfig,
    \Agl\Core\Data\Validation,
    \Agl\Core\Db\Connection\Connection,
    \Agl\Core\Db\Query\Conditions\Conditions,
    \Agl\Core\Debug\Debug,
    \Agl\Core\Loader\Loader,
    \Agl\Core\Mvc\Controller\Router,
    \Exception;

/**
 * Import and initialize the Autoload class.
 */
require(__DIR__ . DS . 'Autoload.php');
new Autoload();

/**
 * Import Errors and Exceptions handlers.
 */
require(__DIR__ . DS . 'Exception.php');

/**
 * Import Debug class to always log errors.
 */
require(__DIR__ . DS . 'Debug/Debug.php');

/**
 * Mother application class.
 *
 * @category Agl_Core
 * @package Agl_Core
 * @version 0.1.0
 */

final class Agl
{
    /**
     * Available protocols and dirs.
     */
    const AGL_CORE_DIR   = 'Core';
    const AGL_MORE_DIR   = 'More';
    const AGL_LIB_DIR    = 'Lib';

    const AGL_CORE_POOL  = 'core';
    const AGL_MORE_POOL  = 'more';

    const APP_PHP_DIR    = 'app/php';
    const APP_ETC_DIR    = 'app/etc';
    const APP_VAR_DIR    = 'app/var';
    const APP_PUBLIC_DIR = 'public';

    const PHP_EXT        = '.php';

    /**
     * Agl Singleton.
     *
     * @var Agl
     */
    private static $_instance = NULL;

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
        if (! Validation::check($pParams)) {
            throw new Exception("Validation failed for data '" . json_encode($pParams) . "'");
        }

        return true;
    }

    /**
     * Create and return a new Collection.
     *
     * @param string $pCollection The type of the collection to create
     * @return Collection
     */
    public static function getCollection($pCollection)
    {
        return Loader::getCollection($pCollection);
    }

    /**
     * Create and return a new Conditions instance.
     *
     * @return Conditions
     */
    public static function newConditions()
    {
        return new Conditions();
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
        return Loader::getInstance($pClass, $pArgs);
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
        return Loader::getSingleton($pClass, $pArgs);
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
        return Loader::getModel($pModel, $pFields);
    }

    /**
     * Create a singleton instance of the requested helper class.
     *
     * @param string $pClass
     * @return mixed
     */
    public static function getHelper($pClass)
    {
        return Loader::getHelper($pClass);
    }

    /**
     * Get the request instance as a singleton.
     *
     * @param string|null $pRequestUri URI to parse
     * @return Request
     */
    public static function getRequest($pRequestUri = NULL)
    {
        return self::getSingleton(self::AGL_CORE_POOL . '/request/request', array($pRequestUri));
    }

    /**
     * Get the application's session as a singleton.
     *
     * @return Session
     */
    public static function getSession()
    {
        return self::getSingleton(self::AGL_CORE_POOL . '/session/session');
    }

    /**
     * Get the application's Authentication class as a singleton.
     *
     * @return Auth
     */
    public static function getAuth()
    {
        return self::getSingleton(self::AGL_CORE_POOL . '/auth/auth');
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
        return Loader::getLoadedModules();
    }

    /**
     * Check if a More module is loaded into AGL.
     *
     * @return bool
     */
    public static function isModuleLoaded($pModule)
    {
        return Loader::isModuleLoaded($pModule);
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
                . self::AGL_LIB_DIR
                . DS
                . $pLib;

        require_once($file);

        return true;
    }

    /**
     * Route the request.
     *
     * @param null|string $pRequestUri Request URI to route
     * @return Agl
     */
    public static function route($pRequestUri = NULL)
    {
        if ($pRequestUri === NULL) {
            $requestUri = $_SERVER['REQUEST_URI'];
        } else {
            $requestUri = $pRequestUri;
        }

        $router = new Router($requestUri);
        $router->route();
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
        /*if (date_default_timezone_get() !== \Agl\Core\Data\Date::DEFAULT_TZ) {
            date_default_timezone_set(\Agl\Core\Data\Date::DEFAULT_TZ);
        }*/

        $this->_cache = ($pCache) ? true : false;
        $this->_debug = ($pDebug) ? true : false;

        error_reporting(-1);

        if ($pDebug) {
            ini_set('display_errors', 'On');
        } else {
            ini_set('display_errors', 'Off');
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
            var_dump(\Agl\Core\Debug\Debug::getInfos());
        }
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
            $this->_config = new JsonConfig();
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
            $this->_db = new Connection();
        }

        return $this->_db;
    }
}
