<?php
namespace Agl\Core\Session;

use \Agl\Core\Data\String as StringData,
    \Exception;

/**
 * Abstract class - Session
 *
 * @category Agl_Core
 * @package Agl_Core_Session
 * @version 0.1.0
 */

abstract class SessionAbstract
{
	/**
	 * Initialize the session.
	 */
	public function __construct()
	{
        if (! isset($_SESSION)) {
    		session_start();
        }
	}

    /**
     * Return the requested attribute value.
     *
     * @param string $pVar Requested attribute
     * @return mixed Attribute value or NULL if the attribute does not exists
     */
    public function __get($pVar)
    {
        $attribute = StringData::fromCamelCase($pVar);

        if (isset($_SESSION[$attribute])) {
            return $_SESSION[$attribute];
        }

        return NULL;
    }

    /**
     * Create an attribute, or update its value.
     *
     * @param string $pVar The attribute to create / update
     * @param string $pValue The attribute value to set
     */
    public function __set($pVar, $pValue)
    {
        $attribute            = StringData::fromCamelCase($pVar);
        $_SESSION[$attribute] = $pValue;
    }

    /**
     * Create an attribute, or update its value.
     *
     * @param string $pVar The attribute to create / update
     * @param string $pValue The attribute value to set
     */
    public function __unset($pVar)
    {
        $attribute = StringData::fromCamelCase($pVar);
        if (array_key_exists($attribute, $_SESSION)) {
            unset($_SESSION[$attribute]);
        }
    }

    /**
     * Destroy the session.
     *
     * @return bool
     */
    public function destroy()
    {
        $_SESSION = array();

        if (ini_get('session.use_cookies') and ! headers_sent()) {
            $params = session_get_cookie_params();
            setcookie(session_name(),
                      '',
                      time() - 42000,
                      $params['path'],
                      $params['domain'],
                      $params['secure'],
                      $params['httponly']
            );
        }

        return session_destroy();
    }
}
