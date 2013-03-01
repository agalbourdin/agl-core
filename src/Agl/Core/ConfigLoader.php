<?php
namespace Agl\Core;

/**
 * Register additional modules (More) config.
 *
 * @category Agl_Core
 * @package Agl_Core
 * @version 0.1.0
 */

class ConfigLoader
{
	/**
	 * Config values are saved into this array.
	 *
	 * @var array
	 */
	private static $_config = array();

	/**
	 * Add config values for a specific module.
	 *
	 * @param string $pModule Module instance (for example: more/locale)
	 * @param array Config values
	 * @return array
	 */
	public static function add($pModule, array $pConfig)
	{
		self::$_config[$pModule] = $pConfig;
		return self::$_config;
	}

	/**
	 * Return the saved config values.
	 *
	 * @param null|string $pModule Return only config values of this module
	 * @return array
	 */
	public static function get($pModule = NULL)
	{
		if ($pModule !== NULL and isset(self::$_config[$pModule])) {
			return self::$_config[$pModule];
		}

		return self::$_config;
	}
}
