<?php
namespace Agl\Core\Cache;

/**
 * Interface - Cache
 *
 * @category Agl_Core
 * @package Agl_Core_Cache
 * @version 0.1.0
 */

interface CacheInterface
{
    /**
     * Cache types.
     */
    const TYPE_FILE = 'file';
    const TYPE_APC  = 'apc';

    /**
     * The directory name where to save cache files.
     */
    const AGL_VAR_CACHE_DIR = 'cache';

    /**
     * The extension to use for cache files.
     */
    const AGL_VAR_CACHE_EXT = '.cache';

    /**
     * The tag returned when the requested cache path doesn't exist (file type).
     */
    const AGL_CACHE_TAG_NOT_FOUND = '_agl_not_found';

    public function set($pKey, $pValue, $pTtl = 0);
    public function get($pKey);
    public function has($pKey);
    public function remove($pKey);
}
