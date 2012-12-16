<?php
namespace Agl\Core\Auth;

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
	 * Roles and resources are loaded in this array.
	 *
	 * @var array
	 */
	private $_roles = array();

	/**
	 * Initialize the Acl instance.
	 */
	public function __construct()
	{
		$this->_loadRoles();
	}

	/**
	 * Load the roles from the ACL configuration file.
	 *
	 * @return array
	 */
	private function _loadRoles()
	{
		$aclConfig = \Agl::app()->getConfig('@module[' . \Agl::AGL_CORE_POOL . '/acl]');
		if (is_array($aclConfig)) {
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
		}

		return $this->_roles;
	}

	/**
	 * Check if the role exists and if the resource is available with this role.
	 *
	 * @param string $pRole Role identifier
	 * @param array $pResource Required resources
	 */
	public function isAllowed($pRole, array $pResource)
	{
    	foreach ($pResource as $resource) {
    		if (! isset($this->_roles[$pRole]) or ! in_array($resource, $this->_roles[$pRole])) {
    			return false;
    		}
    	}

    	return true;
	}

	public function renderError()
	{
		$file = \Agl::app()->getConfig('@layout/errors/403');
		if (\Agl::app()->isDebugMode() or ! $file) {
			throw new \Agl\Exception("Invalid ACL to access the view");
		}

		$path = \Agl::app()->getPath()
		        . \Agl\Core\Mvc\View\ViewInterface::APP_HTTP_TEMPLATE_DIR
		        . DS
                . \Agl::app()->getConfig('@app/global/theme')
		        . DS
		        . $file;

		header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
		require($path);
		exit;
	}
}
