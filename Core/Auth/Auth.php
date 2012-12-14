<?php
namespace Agl\Core\Auth;

/**
 * Authentication management.
 * Log users in and out with session.
 *
 * @category Agl_Core
 * @package Agl_Core_Auth
 * @version 0.1.0
 */

class Auth
{
	/**
	 * User model DB Container.
	 */
	const USER_DB_CONTAINER  = 'user';

 	/**
 	 * Logged in user.
 	 *
 	 * @var null|Model
 	 */
	private $_user = NULL;

	/**
	 * Load the logged in user from the session if exists.
	 */
	public function __construct()
	{
		$this->_user = \Agl::getModel(self::USER_DB_CONTAINER);
		$session     = \Agl::getSession();

		if ($session->hasUserId()) {
			$this->_user->loadById($session->getUserId());
		}
	}

	/**
	 * Log in the user by its ID and register it to the session.
	 *
	 * @param int|string|Id
	 * @return bool
	 */
	public function loginById($pId)
	{
		return $this->login(array(\Agl\Core\Db\Item\ItemInterface::IDFIELD => $pId));
    }

    /**
	 * Log in the user based on specified fields.
	 *
	 * @param array $pFields Associative array of fields to check
	 * @return bool
	 */
	public function login(array $pFields)
	{
		$this->logout();

		$conditions = new \Agl\Core\Db\Query\Conditions\Conditions();
		foreach ($pFields as $field => $value) {
			$conditions->add($field, \Agl\Core\Db\Query\Conditions\Conditions::EQUAL, $value);
		}

		$this->_user->load($conditions);
		if ($this->isLogged()) {
			$session = \Agl::getSession();
			$session->setUserId($this->_user->getId());
			return true;
		}

		return false;
    }

	/**
	 * Log out the user an destroy it from the session.
	 *
	 * @return Auth
	 */
	public function logout()
	{
		$this->_user = \Agl::getModel(self::USER_DB_CONTAINER);
		$session     = \Agl::getSession();
		$session->unsetUserId();
		return $this;
	}

	/**
	 * Tell if the user is logged in or not.
	 *
	 * @return bool
	 */
	public function isLogged()
	{
		return ($this->_user->getId()) ? true : false;
	}

	/**
	 * Return the current User.
	 *
	 * @return Model
	 */
	public function getUser()
	{
		return $this->_user;
	}
}
