<?php
namespace Agl\Core\Auth;

use \Agl\Core\Agl,
	\Agl\Core\Db\Item\ItemInterface,
	\Agl\Core\Db\Query\Conditions\Conditions,
	\Agl\Core\Mvc\Model\Model;

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
	 * Session object.
	 *
	 * @var null|Session
	 */
	private $_session = NULL;

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
		$this->_session = Agl::getSession();
	}

	/**
	 * Initialize User (from DB if logged in).
	 *
	 * @return Auth
	 */
	private function _initUser()
	{
		$this->_user = Agl::getModel(self::USER_DB_CONTAINER);
		if ($this->_session->hasUserId()) {
			$this->_user->loadById($this->_session->getUserId());
		}

		return $this;
	}

	/**
	 * Log in the user with a given User model.
	 *
	 * @param Model
	 * @return bool
	 */
	public function loginByUser(Model $pUser)
	{
		$this->logout();
		$this->_user = $pUser;

		if ($this->_user->getId()) {
			$this->_session->setUserId($this->_user->getId());
			return true;
		}

		return false;
    }

	/**
	 * Log in the user by its ID and register it to the session.
	 *
	 * @param int|string|Id
	 * @return bool
	 */
	public function loginById($pId)
	{
		return $this->login(array(ItemInterface::IDFIELD => $pId));
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

		$conditions = new Conditions();
		foreach ($pFields as $field => $value) {
			$conditions->add($field, Conditions::EQ, $value);
		}

		$this->_user->load($conditions);
		if ($this->_user->getId()) {
			$this->_session->setUserId($this->_user->getId());
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
		$this->_user = Agl::getModel(self::USER_DB_CONTAINER);
		$this->_session->removeUserId();
		return $this;
	}

	/**
	 * Tell if the user is logged in or not.
	 *
	 * @return bool
	 */
	public function isLogged()
	{
		return ($this->_session->getUserId()) ? true : false;
	}

	/**
	 * Return the current User.
	 *
	 * @return Model
	 */
	public function getUser()
	{
		if ($this->_user === NULL) {
			$this->_initUser();
		}

		return $this->_user;
	}

	/**
	 * Return the role of the current user.
	 *
	 * @return string
	 */
	public function getRole()
	{
		if ($this->_user === NULL) {
			$this->_initUser();
		}

		if ($this->_user->getRole()) {
			return $this->_user->getRole();
		}

		return Acl::DEFAULT_ROLE;
	}
}
