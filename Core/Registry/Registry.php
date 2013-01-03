<?php
namespace Agl\Core\Registry;

/**
 * The AGL registry, used to save singletons and miscellaneous variables.
 *
 * @category Agl_Core
 * @package Agl_Core_Registry
 * @version 0.1.0
 */

class Registry
{
	/**
     * Agl Registry.
     *
     * @var array
     */
    private static $_registry = array();

	/**
     * Add an entrey to the registry.
     *
     * @param string $pKey
     * @param mixed $value
     * @return bool
     */
    public static function set($pKey, $pValue)
    {
        if (array_key_exists($pKey, self::$_registry)) {
            throw new \Exception("The registry key '$pKey' already exists");
        }

        return self::$_registry[$pKey] = $pValue;
    }

    /**
     * Get a value from the registry.
     *
     * @param string $pKey
     * @return mixed
     */
    public static function get($pKey)
    {
        if (array_key_exists($pKey, self::$_registry)) {
            return self::$_registry[$pKey];
        }

        return NULL;
    }

    /**
     * Remove an entry from the registry.
     *
     * @param string $pKey
     * @return bool
     */
    public static function remove($pKey)
    {
        if (! array_key_exists($pKey, self::$_registry)) {
            throw new \Exception("The registry key '$pKey' doesn't exist");
        }

        if (is_object(self::$_registry[$pKey]) and method_exists(self::$_registry[$pKey], '__destruct')) {
            self::$_registry[$pKey]->__destruct();
        }

        unset(self::$_registry[$pKey]);

        return true;
    }
}
