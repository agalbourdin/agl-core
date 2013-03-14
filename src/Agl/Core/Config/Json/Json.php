<?php
namespace Agl\Core\Config\Json;

use \Agl\Core\Agl,
    \Agl\Core\Cache\Apc\Apc,
    \Agl\Core\Cache\File\FileInterface,
    \Agl\Core\Cache\File\Format\Arr as CacheArr,
    \Agl\Core\Config\ConfigInterface,
    \Agl\Core\Data\Dir as DirData,
    \Agl\Core\Data\Json as JsonData,
    \Agl\Core\Mvc\View\ViewInterface;

/**
 * Access the JSON configuration files.
 *
 * @category Agl_Core
 * @package Agl_Core_Config_Json
 * @version 0.1.0
 */

class Json
    implements ConfigInterface
{
    /**
     * Global configuration file.
     */
    const MAIN_CONFIG_FILE = 'main';

    /**
     * Events file name.
     */
    const MAIN_EVENTS_FILE = 'events';

    /**
     * The XML extension used by the configuration files.
     */
    const CONFIG_EXT = '.json';

    /**
     * The modules config directory.
     */
    const CONFIG_MODULES_DIR = 'config';

    /**
     * Identifier for the configuration cache file.
     */
    const CONFIG_CACHE_ID = 'config_json';

    /**
     * Cache instance if required.
     *
     * @var null|Arr
     */
    private static $_cacheInstance = NULL;

    /**
     * Register Data Json instance.
     *
     * @var array
     */
    private $_instance = array();

    /**
     * Config values are saved in this array when requested for the
     * first time.
     *
     * @var array
     */
    private $_cache = array();

    /**
     * Is configuration cache enabled?
     *
     * @var bool
     */
    private $_cacheEnabled = NULL;

    /**
     * Is APC enabled?
     *
     * @var bool
     */
    private $_apcEnabled = NULL;

    /**
     * Environment prefix to use to load configuration files.
     *
     * @var string
     */
    private $_envPrefix = '';

    /**
     * Create / Get an instance of Array Cache.
     *
     * @return Arr
     */
    public static function getCacheSingleton()
    {
        if (self::$_cacheInstance === NULL) {
            self::$_cacheInstance = new CacheArr(self::CONFIG_CACHE_ID);
        }

        return self::$_cacheInstance;
    }

    /**
     * Initialize the APC cache if enabled.
     */
    public function __construct()
    {
        $envPrefixName = static::ENV_PREFIX_NAME;
        if (isset($_SERVER[$envPrefixName]) and $_SERVER[$envPrefixName]) {
            $this->_envPrefix = $_SERVER[$envPrefixName] . static::ENV_PREFIX_SEPARATOR;
        }

        $this->_cacheEnabled = Agl::app()->isCacheEnabled();

        if ($this->_cacheEnabled) {
            $this->_apcEnabled   = Apc::isEnabled();
            if ($this->_apcEnabled) {
                $cache = Apc::get(self::CONFIG_CACHE_ID);
                if (is_array($cache)) {
                    $this->_cache = $cache;
                }
            } else {
                self::getCacheSingleton();
            }
        }
    }

    /**
     * Return absolute path to the config directory.
     *
     * @return string
     */
    private static function _getConfigPath()
    {
        return APP_PATH
             . Agl::APP_ETC_DIR
             . DS
             . self::CONFIG_MODULES_DIR
             . DS;
    }

    /**
     * Get informations about the JSON instance to call to resolve the
     * requested path.
     *
     * @param string $pPath Path to resolve
     * @return array File and updated path
     */
    private function _getInstance($pPath)
    {
        $path = str_replace('@layout', '@module[' . Agl::AGL_CORE_POOL . '/' . ViewInterface::CONFIG_FILE . ']', $pPath);

        $file = self::_getConfigPath();

        if (strpos($path, '@module') === 0 and preg_match('#^@module\[(' . Agl::AGL_CORE_POOL . '|' . Agl::AGL_MORE_DIR . ')/([a-z0-9]+)\]#i', $path, $matches)) {
                if ($matches[1] === Agl::AGL_CORE_POOL) {
                    $file .= strtolower($matches[1])
                           . DS
                           . $matches[2]
                           . self::CONFIG_EXT;
                } else {
                    $file .= strtolower($matches[1])
                           . DS
                           . $matches[2]
                           . DS
                           . self::MAIN_CONFIG_FILE
                           . self::CONFIG_EXT;
                }
        } else {
            $file .= self::MAIN_CONFIG_FILE
                   . self::CONFIG_EXT;
        }

        if (! isset($this->_instance[$file])) {
            $this->_instance[$file] = new JsonData();
            $this->_instance[$file]->loadFile($file, true);
        }

        return $this->_instance[$file];
    }

    /**
     * Search all events.json files and get config value.
     *
     * @return array
     */
    private function _getEventsConfig()
    {
        $files = DirData::listFilesRecursive(self::_getConfigPath(), self:: MAIN_EVENTS_FILE . self::CONFIG_EXT);

        $content = array(self:: MAIN_EVENTS_FILE => array());
        foreach ($files as $file) {
            $json = new JsonData();
            $json->loadFile($file, true);
            $content['events'] = array_merge($json->getContent(), $content['events']);
        }

        return $content;
    }

    /**
     * Return NULL, the config value or an array of values corresponding to the
     * requested path.
     *
     * @param string $pPath Requested path
     * @param bool $pForceGlobalArray Return the results in a miltidimensional
     * array
     * @return mixed
     */
    private function _getConfigValues($pPath, $pForceGlobalArray)
    {
        if (! array_key_exists($pPath, $this->_cache)) {
            if ($pPath === static::CONFIG_EVENTS_PATH) {
                $content = $this->_getEventsConfig();
            } else {
                $json    = $this->_getInstance($pPath);
                $content = $json->getContent();
            }

            $path = str_replace('@app/', '', $pPath);
            $path = preg_replace('#^(@module\[([a-z0-9/]+)\]|@layout)(/)?#', '', $path);

            if ($path) {
                $pathArr = explode('/', $path);
                $nbKeys  = count($pathArr) - 1;

                foreach($pathArr as $i => $key) {
                    if (! isset($content[$key])) {
                        $this->_cache[$pPath] = NULL;
                        break;
                    } else if ($i < $nbKeys) {
                        $content = $content[$key];
                    } else if ($i == $nbKeys) {
                        $this->_cache[$pPath] = (isset($content[$this->_envPrefix . $key])) ? $content[$this->_envPrefix . $key] : $content[$key];
                    }
                }
            } else {
                $this->_cache[$pPath] = $content;
            }

            if ($pForceGlobalArray and (! is_array($this->_cache[$pPath]) or (is_array($this->_cache[$pPath]) and ! array_key_exists(0, $this->_cache[$pPath])))) {
                $this->_cache[$pPath] = array($this->_cache[$pPath]);
            }
        }

        return $this->_cache[$pPath];
    }

    /**
     * Retrieve a value in the Agl JSON configuration file.
     *
     * @param string $pPath The configuration path to retrieve
     * @param array $pForceGlobalArray Return the results in a multidimensional
     * array
     * @return mixed The configuration value corresponding to the path
     */
    public function getConfig($pPath, $pForceGlobalArray = false)
    {
        if ($this->_cacheEnabled) {
            if ($this->_apcEnabled) {
                if (array_key_exists($pPath, $this->_cache)) {
                    return $this->_cache[$pPath];
                }
            } else {
                $value = self::$_cacheInstance->get($pPath);
                if ($value !== FileInterface::AGL_CACHE_TAG_NOT_FOUND) {
                    return $value;
                }
            }
        }

        $value = $this->_getConfigValues($pPath, $pForceGlobalArray);

        if ($this->_cacheEnabled) {
            if ($this->_apcEnabled) {
                Apc::set(self::CONFIG_CACHE_ID, $this->_cache);
            } else {
                self::$_cacheInstance
                    ->set($pPath, $value)
                    ->save();
            }
        }

        return $value;
    }
}
