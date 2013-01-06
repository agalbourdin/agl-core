<?php
namespace Agl\Core\Config;

/**
 * Main interface of the configuration class.
 *
 * @category Agl_Core
 * @package Agl_Core_Config
 * @version 0.1.0
 */

interface ConfigInterface
{
	/**
     * The names used in the Layout to enable the cache.
     */
    const CONFIG_CACHE_NAME      = 'cache';
    const CONFIG_CACHE_TTL_NAME  = 'ttl';
    const CONFIG_CACHE_TYPE_NAME = 'type';

    /**
     * Cache types.
     */
    const CONFIG_CACHE_TYPE_STATIC  = 'static';
    const CONFIG_CACHE_TYPE_DYNAMIC = 'dynamic';

    /**
     * Environment prefix name in the $_SERVER array (optional).
     */
    const ENV_PREFIX_NAME      = 'AGL_ENV';
    const ENV_PREFIX_SEPARATOR = '_';

    /**
     * Ajax tag for config keys.
     */
    const CONFIG_CACHE_KEY_AJAX = 'ajax';

    public function __construct();
    public static function getCacheSingleton();
    public function getConfig($pPath);
}
