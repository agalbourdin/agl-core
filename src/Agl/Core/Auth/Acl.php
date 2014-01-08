<?php
namespace Agl\Core\Auth;

use \Agl\Core\Agl,
	\Agl\Core\Mvc\View\ViewInterface,
	\Agl\Core\Request\Request,
	\Exception;

/**
 * Access Control List Management.
 *
 * @category Agl_Core
 * @package Agl_Core_Auth
 * @version 0.1.0
 */

class Acl
{
	/**
	 * Resource field name in the Acl configuration file.
	 */
	const CONFIG_FIELD_RESOURCE = 'resources';

	/**
	 * Inherit field name in the Acl configuration file.
	 */
	const CONFIG_FIELD_INHERIT = 'inherit';

	/**
	 * Default role for non logged in users.
	 */
	const DEFAULT_ROLE = 'guest';

	/**
	 * Roles and resources are loaded in this array.
	 *
	 * @var array
	 */
	private $_roles = array();

	/**
	 * Initialize the Acl instance.
	 *
	 * @param mixed $aclConfig Roles and Resources configuration
	 */
	public function __construct($aclConfig = NULL)
	{
		if ($aclConfig === NULL) {
			$aclConfig = Agl::app()->getConfig(Agl::AGL_CORE_POOL . '-acl/');
		}

		if (is_array($aclConfig)) {
			$this->_loadRoles($aclConfig);
		}
	}

	/**
	 * Load the roles from the ACL configuration file.
	 *
	 * @param array $aclConfig Roles and Resources configuration
	 * @return array
	 */
	private function _loadRoles(array $aclConfig)
	{
		foreach ($aclConfig as $role => $acl) {
			if (isset($acl[self::CONFIG_FIELD_RESOURCE])
				and is_array($acl[self::CONFIG_FIELD_RESOURCE])) {
				$this->_roles[$role] = $acl[self::CONFIG_FIELD_RESOURCE];
			} else {
				$this->_roles[$role] = array();
			}

			if (isset($acl[self::CONFIG_FIELD_INHERIT])
				and is_array($acl[self::CONFIG_FIELD_INHERIT])) {
				foreach ($acl[self::CONFIG_FIELD_INHERIT] as $inherit) {
					if (isset($aclConfig[$inherit][self::CONFIG_FIELD_RESOURCE])
						and is_array($aclConfig[$inherit][self::CONFIG_FIELD_RESOURCE])) {
						$this->_roles[$role] = array_merge($this->_roles[$role], $aclConfig[$inherit][self::CONFIG_FIELD_RESOURCE]);
					}
				}
			}
		}

		return $this->_roles;
	}

	/**
	 * Check if the role exists and if the resource is available with this role.
	 *
	 * @param string $pRole Role identifier
	 * @param array $pResource Required resources
	 */
	public function isAllowed($pRole, array $pResources)
	{
    	foreach ($pResources as $resource) {
    		if (! isset($this->_roles[$pRole]) or ! in_array($resource, $this->_roles[$pRole])) {
    			return false;
    		}
    	}

    	return true;
	}

	/**
	 * Redirect the user to the configured 403 page (login page).
	 */
	public function requestLogin()
	{
		Request::redirect(ViewInterface::ERROR_403);
		exit;
	}
}
