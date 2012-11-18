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
    const CONFIG_CACHE_NAME         = 'cache';
    const CONFIG_CACHE_TTL_NAME     = 'ttl';
    const CONFIG_CACHE_TYPE_NAME    = 'type';

    /**
     * Cache types.
     */
    const CONFIG_CACHE_TYPE_STATIC  = 'static';
    const CONFIG_CACHE_TYPE_DYNAMIC = 'dynamic';

    public function __construct();
    public static function getCacheSingleton();
    public function getConfig($pPath);
}
