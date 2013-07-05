<?php
namespace Agl\Core\Cache\Apc;

/**
 * Generic methods to store data in the APC cache.
 *
 * @category Agl_Core
 * @package Agl_Core_Cache_Apc
 * @version 0.1.0
 * @deprecated
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
	 * Save the PHP apc.enabled_cli configuration value.
	 *
	 * @var bool
	 */
	private static $_apcCliEnabled = NULL;

	/**
	 * Generate a prefix for cache files, based on the application's path.
	 *
	 * @return string
	 */
	private static function _getAppIdentifierPrefix()
	{
		return md5(APP_PATH) . '_';
	}

	/**
	 * Get a unique cache identifier based on the application path.
	 *
	 * @param string $pIdentifier Cache identifier
	 * @return string
	 */
	private static function _getAppCacheIdentifier($pIdentifier)
	{
        return self::_getAppIdentifierPrefix() . $pIdentifier;
	}

	/**
	 * Check if APC is enabled on the server.
	 *
	 * @return bool
	 */
	public static function isEnabled()
	{
		if (! isset(self::$_apcEnabled)) {
			self::$_apcEnabled = (ini_get('apc.enabled')) ? true : false;
		}

		return self::$_apcEnabled;
	}

	/**
	 * Check if APC Cli is enabled on the server.
	 *
	 * @return bool
	 */
	public static function isCliEnabled()
	{
		if (! isset(self::$_apcCliEnabled)) {
			self::$_apcCliEnabled = (ini_get('apc.enable_cli')) ? true : false;
		}

		return self::$_apcCliEnabled;
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
	 * Delete a value from the memory cache.
	 *
	 * @param string $pIdentifier Cache identifier
	 * @return mixed
	 */
	public static function delete($pIdentifier)
	{
		$identifier = self::_getAppCacheIdentifier($pIdentifier);
		return apc_delete($identifier);
	}

	/**
	 * Delete all the Application's cache variables.
	 * Return the number on deleted entries.
	 *
	 * @return int
	 */
	public static function deleteAll()
	{
		$i    = 0;
		$info = apc_cache_info('user');
		if ($info and isset($info['cache_list'])) {
			foreach ($info['cache_list'] as $cacheFile) {
				if (strpos($cacheFile['info'], self::_getAppIdentifierPrefix()) === 0
					and apc_delete($cacheFile['info'])) {
					$i++;
				}
			}
		}

		return $i;
	}
}
