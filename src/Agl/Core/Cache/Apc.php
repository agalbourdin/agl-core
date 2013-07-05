<?php
namespace Agl\Core\Cache;

use \Agl\Core\Agl,
    \Exception;

/**
 * APC cache management.
 *
 * @category Agl_Core
 * @package Agl_Core_Cache
 * @version 0.1.0
 */

class Apc
    implements CacheInterface
{
    /**
     * Generate a prefix for cache files, based on the application's path.
     *
     * @return string
     */
    private static function _getKeyPrefix()
    {
        return md5(APP_PATH) . '_';
    }

    /**
     * Get a unique cache key based on the application's path.
     *
     * @param string $pKey Unprefixed cache key
     * @return string
     */
    private static function _getPrefixedKey($pKey)
    {
        return self::_getKeyPrefix() . $pKey;
    }

    /**
     * Set a value to the cached array.
     *
     * @param string $pKey The value key
     * @param mixed $pValue The value to save
     * @param int $pTtl Cache Time To Live (seconds)
     *
     * @return File
     */
    public function set($pKey, $pValue, $pTtl = 0)
    {
        $key = self::_getPrefixedKey($pKey);
        apc_store($key, $pValue, $pTtl);

        return $this;
    }

    /**
     * Get the value corresponding to the key $pKey in the cached array.
     *
     * @param string $pKey
     * @return mixed
     */
    public function get($pKey)
    {
        $key = self::_getPrefixedKey($pKey);
        if ($value = apc_fetch($key)) {
            return $value;
        }

        return NULL;
    }

    /**
     * Check if key exists in cache (a key is considered existing when its value
     * is NULL).
     *
     * @param string $pKey
     * @return bool
     */
    public function has($pKey)
    {
        $key = self::_getPrefixedKey($pKey);
        return (apc_exists($key)) ? true : false;
    }

    /**
     * Unset a value from the cache.
     *
     * @param string $pKey
     * @return File
     */
    public function remove($pKey)
    {
        $key = self::_getPrefixedKey($pKey);
        apc_delete($key);

        return $this;
    }

    /**
     * Flush the entire cache or a cache section.
     *
     * @param string $pSection
     * @return File
     */
    public function flush($pSection = '')
    {
        $info = apc_cache_info('user');
        if (! $info or ! isset($info['cache_list'])) {
            return $this;
        }

        foreach ($info['cache_list'] as $cacheFile) {
            if (($pSection and
                strpos($cacheFile['info'], self::_getKeyPrefix() . $pSection . static::SECTION_DELIMITER) === 0)
                or (! $pSection
                and strpos($cacheFile['info'], self::_getKeyPrefix()) === 0)) {
                apc_delete($cacheFile['info']);
            }
        }

        return $this;
    }
}
