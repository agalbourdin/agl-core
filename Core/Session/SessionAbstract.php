<?php
namespace Agl\Core\Session;

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
		session_start();
	}

	/**
     * Magic method - handle the set, get and remove call.
     *
     * @param string $pMethod Called method
     * @param array $pArgs Arguments
     * @return mixed
     */
    public function __call($pMethod, array $pArgs)
    {
        if (preg_match('/^get/', $pMethod)) {
            $var = str_replace('get', '', $pMethod);
            return $this->$var;
        } else if (preg_match('/^set/', $pMethod) and isset($pArgs[0])) {
            $var = str_replace('set', '', $pMethod);
            $this->$var = $pArgs[0];
            return $this;
        } else if (preg_match('/^unset/', $pMethod)) {
            $var = str_replace('unset', '', $pMethod);
            unset($this->$var);
            return true;
        } else if (preg_match('/^has/', $pMethod)) {
            $var = str_replace('has', '', $pMethod);
            return $this->hasAttribute($var);
        }

        throw new \Exception("Undefined method '$pMethod'");
    }

    /**
     * Return the requested attribute value.
     *
     * @param string $pVar Requested attribute
     * @return mixed Attribute value or NULL if the attribute does not exists
     */
    public function __get($pVar)
    {
        $attribute = \Agl\Core\Data\String::fromCamelCase($pVar);

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
     * @return mixed The attribute value
     */
    public function __set($pVar, $pValue)
    {
        $attribute = \Agl\Core\Data\String::fromCamelCase($pVar);
        $_SESSION[$attribute] = $pValue;
        return $_SESSION[$attribute];
    }

    /**
     * Create an attribute, or update its value.
     *
     * @param string $pVar The attribute to create / update
     * @param string $pValue The attribute value to set
     * @return mixed The attribute value
     */
    public function __unset($pVar)
    {
        $attribute = \Agl\Core\Data\String::fromCamelCase($pVar);
        if (array_key_exists($attribute, $_SESSION)) {
            unset($_SESSION[$attribute]);
            return true;
        }

        return false;
    }

    /**
     * Check if an attribute is set.
     *
     * @return bool
     */
    public function hasAttribute($pVar)
    {
    	$attribute = \Agl\Core\Data\String::fromCamelCase($pVar);
    	return (array_key_exists($attribute, $_SESSION));
    }

    /**
     * Destroy the session.
     *
     * @return bool
     */
    public function destroy()
    {
        $_SESSION = array();

        if (ini_get('session.use_cookies')) {
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
