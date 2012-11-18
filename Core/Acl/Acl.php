<?php
namespace Agl\Core\Acl;

/**
 * Access Control List Management.
 *
 * @category Agl_Core
 * @package Agl_Core_Acl
 * @version 0.1.0
 */

class Acl
{
	/**
	 * ID field name in the Acl configuration file.
	 */
	const CONFIG_FIELD_ID = 'id';

	/**
	 * Resource field name in the Acl configuration file.
	 */
	const CONFIG_FIELD_RESOURCE = 'resource';

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
		$aclConfig = \Agl::app()->getConfig('@module[' . \Agl::AGL_CORE_POOL . '/acl]/role', true);
		foreach ($aclConfig as $acl) {
			if (! is_array($acl)) {
				continue;
			}

			if (isset($acl[self::CONFIG_FIELD_RESOURCE])) {
				if (is_array($acl[self::CONFIG_FIELD_RESOURCE])) {
					$this->_roles[$acl[self::CONFIG_FIELD_ID]] = $acl[self::CONFIG_FIELD_RESOURCE];
				} else {
					$this->_roles[$acl[self::CONFIG_FIELD_ID]] = array($acl[self::CONFIG_FIELD_RESOURCE]);
				}
			} else {
				$this->_roles[$acl[self::CONFIG_FIELD_ID]] = array();
			}

			if (isset($acl[self::CONFIG_FIELD_INHERIT])
				and isset($this->_roles[$acl[self::CONFIG_FIELD_INHERIT]])) {
				$this->_roles[$acl[self::CONFIG_FIELD_ID]] = array_merge($this->_roles[$acl[self::CONFIG_FIELD_ID]], $this->_roles[$acl[self::CONFIG_FIELD_INHERIT]]);
			}
		}

		return $this->_roles;
	}

	/**
	 * Check if the role exists and if the resource is available with this role.
	 * The resource parameter should be an array with a "resource" key.
	 * $pResource['resource'] can be a string or an array of strings to validate
	 * multiple resources.
	 *
	 * @param string $pRole Identifiant du rôle
	 * @param array $pResource Resource(s) à vérifier
	 */
	public function isAllowed($pRole, array $pResource)
	{
		\Agl::validateParams(array(
            'StrictString' => $pRole
        ));

        if (! isset($pResource[self::CONFIG_FIELD_RESOURCE])) {
        	return false;
        }

        if (is_string($pResource[self::CONFIG_FIELD_RESOURCE])) {
        	return (isset($this->_roles[$pRole]) and in_array($pResource[self::CONFIG_FIELD_RESOURCE], $this->_roles[$pRole]));
        } else if (is_array($pResource[self::CONFIG_FIELD_RESOURCE])) {
        	foreach ($pResource[self::CONFIG_FIELD_RESOURCE] as $resource) {
        		if (! isset($this->_roles[$pRole]) or ! in_array($resource, $this->_roles[$pRole])) {
        			return false;
        		}
        	}
        	return true;
        }

        return false;
	}
}
