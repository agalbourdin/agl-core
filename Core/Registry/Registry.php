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
    public static function register($pKey, $pValue)
    {
        if (array_key_exists($pKey, self::$_registry)) {
            throw new \Agl\Exception("The registry key '$pKey' already exists");
        }

        return self::$_registry[$pKey] = $pValue;
    }

    /**
     * Unregister an entry from the registry.
     *
     * @param string $pKey
     * @return bool
     */
    public static function unregister($pKey)
    {
        if (! array_key_exists($pKey, self::$_registry)) {
            throw new \Agl\Exception("The registry key '$pKey' doesn't exist");
        }

        if (is_object(self::$_registry[$pKey]) and method_exists(self::$_registry[$pKey], '__destruct')) {
            self::$_registry[$pKey]->__destruct();
        }

        unset(self::$_registry[$pKey]);

        return true;
    }

    /**
     * Get a value from the registry.
     *
     * @param string $pKey
     * @return mixed
     */
    public static function registry($pKey)
    {
        if (array_key_exists($pKey, self::$_registry)) {
            return self::$_registry[$pKey];
        }

        return NULL;
    }
}
