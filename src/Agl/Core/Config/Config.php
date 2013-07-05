<?php
namespace Agl\Core\Config;

use \Agl\Core\Agl,
    \Agl\Core\Cache\CacheInterface,
    \Agl\Core\Data\Dir as DirData,
    \Agl\Core\Mvc\View\ViewInterface;

/**
 * Access the JSON configuration files.
 *
 * @category Agl_Core
 * @package Agl_Core_Config_Json
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
    const CONFIG_EVENTS_PATH = '@app/events';

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
     * Initialize the environment prefix.
     */
    public function __construct()
    {
        $envPrefixName = self::ENV_PREFIX_NAME;
        if (isset($_SERVER[$envPrefixName]) and $_SERVER[$envPrefixName]) {
            $this->_envPrefix = $_SERVER[$envPrefixName] . self::ENV_PREFIX_SEPARATOR;
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
             . self::MAIN_DIR
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
                           . self::EXT;
                } else {
                    $file .= strtolower($matches[1])
                           . DS
                           . $matches[2]
                           . DS
                           . self::MAIN_CONFIG_FILE
                           . self::EXT;
                }
        } else {
            $file .= self::MAIN_CONFIG_FILE
                   . self::EXT;
        }

        if (! isset($this->_instance[$file])) {
            $this->_instance[$file] = require($file);
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
        $files = DirData::listFilesRecursive(self::_getConfigPath(), self:: MAIN_EVENTS_FILE . self::EXT);

        $content = array(self:: MAIN_EVENTS_FILE => array());
        foreach ($files as $file) {
            $content['events'] = array_merge_recursive(require($file), $content['events']);
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
        if ($pPath === self::CONFIG_EVENTS_PATH) {
            $content = $this->_getEventsConfig();
        } else {
            $content = $this->_getInstance($pPath);
        }

        $path = str_replace('@app/', '', $pPath);
        $path = preg_replace('#^(@module\[([a-z0-9/]+)\]|@layout)(/)?#', '', $path);

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
                    $this->_cache[$pPath] = (isset($content[$this->_envPrefix . $key])) ? $content[$this->_envPrefix . $key] : $content[$key];
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
     * Retrieve a value in the Agl JSON configuration file.
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

        $this->_cache[$pPath] = $this->_getConfigValues($pPath, $pForceGlobalArray);

        return $this->_cache[$pPath];
    }
}
