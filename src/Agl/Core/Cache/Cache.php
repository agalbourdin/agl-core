<?php
namespace Agl\Core\Cache;

use \Agl\Core\Agl,
	\Agl\Core\Cache\Apc as ApcCache,
    \Agl\Core\Cache\Apcu as ApcuCache,
	\Agl\Core\Cache\File as FileCache,
	\Exception;

/**
 * Factory - implement the cache class corresponding to the application
 * configuration.
 *
 * @category Agl_Core
 * @package Agl_Core_Cache
 * @version 0.1.0
 */

switch(Agl::app()->getConfig('@app/cache/type')) {
    case CacheInterface::TYPE_APC:
        class Cache extends ApcCache { }
        break;
    case CacheInterface::TYPE_APCU:
        class Cache extends ApcuCache { }
        break;
    case CacheInterface::TYPE_FILE:
        class Cache extends FileCache { }
        break;
    default:
        throw new Exception("Cache type '" . Agl::app()->getConfig('@app/cache/type') . "' is not allowed");
}
