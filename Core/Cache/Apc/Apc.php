<?php
namespace Agl\Core\Cache\Apc;

/**
 * Generic methods to store data in the APC cache.
 *
 * @category Agl_Core
 * @package Agl_Core_Cache_Apc
 * @version 0.1.0
 */

class Apc
{
	/**
	 * Save the PHP apc.enabled configuration value.
	 *
	 * @var bool
	 */
	private static $_apcEnabled = NULL;

	/**
	 * Get a unique cache identifier based on the application path.
	 *
	 * @param string $pIdentifier Cache identifier
	 * @return string
	 */
	private static function _getAppCacheIdentifier($pIdentifier)
	{
        return md5(\Agl::app()->getPath()) . '_' . $pIdentifier;
	}

	/**
	 * Check if APC is enabled on the server.
	 *
	 * @return bool
	 */
	public static function isApcEnabled()
	{
		if (! isset(self::$_apcEnabled)) {
			self::$_apcEnabled = (ini_get('apc.enabled')) ? true : false;
		}

		return self::$_apcEnabled;
	}

	/**
	 * Set a new variable in the memory cache.
	 *
	 * @param string $pIdentifier Cache identifier
	 * @param mixed $pValue The value to cache
	 * @param int $pTtl Cache TTL
	 * @return bool
	 */
	public static function set($pIdentifier, $pValue, $pTtl = 0)
	{
		$identifier = self::_getAppCacheIdentifier($pIdentifier);
        return apc_store($identifier, $pValue, $pTtl);
	}

	/**
	 * Get a value from the memory cache.
	 *
	 * @param string $pIdentifier Cache identifier
	 * @return mixed
	 */
	public static function get($pIdentifier)
	{
		$identifier = self::_getAppCacheIdentifier($pIdentifier);
		return apc_fetch($identifier);
	}

	/**
	 * Clean the memory cache.
	 *
	 * @return bool
	 */
	public static function clean()
	{
		return apc_clear_cache();
	}
}
