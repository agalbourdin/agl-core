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
     * Keys to store cache value and expire timestamp.
     */
    const AGL_CACHE_VALUE  = 'value';
    const AGL_CACHE_EXPIRE = 'expire';

    /**
     * Section delimiter.
     */
    const SECTION_DELIMITER = '.';

    public function set($pKey, $pValue, $pTtl = 0);
    public function get($pKey);
    public function has($pKey);
    public function remove($pKey);
    public function flush($pSection = '');
}
