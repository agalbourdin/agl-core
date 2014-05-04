<?php
namespace Agl\Core\Config;

use \Agl\Core\Agl,
    \Agl\Core\Cache\CacheInterface,
    \Agl\Core\Data\Dir as DirData,
    \Agl\Core\Mvc\View\ViewInterface;

/**
 * Access configuration files.
 *
 * @category Agl_Core
 * @package Agl_Core_Config
 * @version 0.1.0
 */

class Config
    implements ConfigInterface
{
        /**
     * Environment prefix name in the $_SERVER array (optional).
     */
    const ENV_PREFIX_NAME      = 'AGL_ENV';
    const ENV_PREFIX_SEPARATOR = '_';

    /**
     * Events path (rewrited as events are splitted into multiple files).
     */
    const CONFIG_EVENTS_PATH = 'main/events';

    /**
     * Global configuration file.
     */
    const MAIN_CONFIG_FILE = 'main';

    /**
     * Events file name.
     */
    const MAIN_EVENTS_FILE = 'events';

    /**
     * Extension used by configuration files.
     */
    const EXT = '.php';

    /**
     * The modules config directory.
     */
    const MAIN_DIR = 'config';

    /**
     * Config values are saved in this array when requested for the
     * first time.
     *
     * @var array
     */
    private $_cache = array();

    /**
     * Main configuration directory absolute path.
     *
     * @var string
     */
    private $_configPath = NULL;

    /**
     * Config files loaded are stored in this array.
     *
     * @var array
     */
    private $_instance = array();

    /**
     * Environment prefix to use to load configuration files.
     *
     * @var string
     */
    private $_envPrefix = '';

    /**
     * Is AGL cache enabled?
     *
     * @var bool
     */
    private $_cacheEnabled = false;

    /**
     * Cache instance.
     *
     * @var Cache
     */
    private $_cacheInstance = NULL;

    /**
     * Initialize the environment prefix.
     */
    public function __construct()
    {
        $envPrefixName = self::ENV_PREFIX_NAME;
        if (isset($_SERVER[$envPrefixName]) and $_SERVER[$envPrefixName]) {
            $this->_envPrefix = $_SERVER[$envPrefixName] . self::ENV_PREFIX_SEPARATOR;
        }

        $this->_cacheEnabled = Agl::app()->isCacheEnabled();
        $this->_configPath   = Agl::app()->getConfigPath();
    }

    /**
     * Load configuration file content if not already loaded.
     *
     * @param string $pFileStr File path to resolve
     * @return array Configuration file content
     */
    private function _getInstance($pFileStr)
    {
        $filePath = $this->_configPath;
        $file     = $filePath . str_replace('-', DS, $pFileStr) . self::EXT;

        if ($this->_envPrefix) {
            $file = preg_replace('/([a-z0-9]+\.)/i', $this->_envPrefix . '$1', $file);
        }

        if (! isset($this->_instance[$file])) {
            if (is_readable($file)) {
                $this->_instance[$file] = require($file);
            } else if ($this->_envPrefix
                and $file = $filePath . str_replace('-', DS, $pFileStr) . self::EXT
                and is_readable($file)) {
                $this->_instance[$file] = require($file);
            } else {
                $this->_instance[$file] = array();
            }
        }

        return $this->_instance[$file];
    }

    /**
     * Search all events config files and get config value.
     *
     * @return array
     */
    private function _getEventsConfig()
    {
        $content = array(self:: MAIN_EVENTS_FILE => array());

        $configPath = $this->_configPath;
        if (! is_dir($configPath)) {
            return $content;
        }

        $files = DirData::listFilesRecursive($configPath, self:: MAIN_EVENTS_FILE . self::EXT);
        foreach ($files as $file) {
            $content[self:: MAIN_EVENTS_FILE] = array_merge_recursive(require($file), $content[self:: MAIN_EVENTS_FILE]);
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
        $pathArr = explode(DS, $pPath, 2);
        if (! isset($pathArr[1])) {
            return NULL;
        }

        $path = rtrim($pathArr[1], '/');

        if ($pPath === self::CONFIG_EVENTS_PATH) {
            $content = $this->_getEventsConfig();
        } else {
            $content = $this->_getInstance($pathArr[0]);
        }

        if ($path) {
            $pathArr = explode('/', $path);
            $nbKeys  = count($pathArr) - 1;

            foreach($pathArr as $i => $key) {
                $key = str_replace('#', '/', $key);

                if (! isset($content[$key])) {
                    $this->_cache[$pPath] = NULL;
                    break;
                } else if ($i < $nbKeys) {
                    $content = $content[$key];
                } else if ($i == $nbKeys) {
                    $this->_cache[$pPath] = $content[$key];
                }
            }
        } else {
            $this->_cache[$pPath] = $content;
        }

        if ($pForceGlobalArray and (! is_array($this->_cache[$pPath]) or (is_array($this->_cache[$pPath]) and ! array_key_exists(0, $this->_cache[$pPath])))) {
            $this->_cache[$pPath] = array($this->_cache[$pPath]);
        }

        return $this->_cache[$pPath];
    }

    /**
     * Retrieve a value in the configuration files.
     *
     * @param string $pPath The configuration path to retrieve
     * @param array $pForceGlobalArray Return the results in a multidimensional
     * array
     * @return mixed The configuration value corresponding to the path
     */
    public function getConfig($pPath, $pForceGlobalArray = false)
    {
        if (array_key_exists($pPath, $this->_cache)) {
            return $this->_cache[$pPath];
        }

        if ($this->_cacheEnabled and $pPath != static::CONFIG_CACHE_TYPE_KEY) {
            if ($this->_cacheInstance === NULL) {
                $this->_cacheInstance = Agl::getCache();
            }

            if ($this->_cacheInstance->has('config.' . $pPath)) {
                $this->_cache[$pPath] = $this->_cacheInstance->get('config.' . $pPath);
                return $this->_cache[$pPath];
            }
        }

        $this->_cache[$pPath] = $this->_getConfigValues($pPath, $pForceGlobalArray);

        if ($this->_cacheInstance !== NULL) {
            $this->_cacheInstance->set('config.' . $pPath, $this->_cache[$pPath]);
        }

        return $this->_cache[$pPath];
    }
}
