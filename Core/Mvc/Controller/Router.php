<?php
namespace Agl\Core\Mvc\Controller;

/**
 * Application's router.
 *
 * @category Agl_Core
 * @package Agl_Core_Mvc_Controller
 * @version 0.1.0
 */

class Router
{
	/**
	 * The requested module.
	 *
	 * @var string
	 */
	protected $_module = NULL;

	/**
	 * The requested view.
	 *
	 * @var string
	 */
	protected $_view = NULL;

	/**
	 * The requested action.
	 *
	 * @var string
	 */
	protected $_action = NULL;

	/**
	 * The controller to invoke.
	 *
	 * @var string
	 */
	protected $_controller = NULL;

	/**
	 * The controller method to invoke.
	 *
	 * @var string
	 */
	protected $_actionMethod = NULL;

	/**
	 * Initialize the router by registering the requested module, view and
	 * action.
	 *
	 * @param string $pRequestUri Request URI to route
	 */
	public function __construct($pRequestUri)
	{
		$request       = \Agl::getRequest($pRequestUri);
		$this->_module = $request->getModule();
		$this->_view   = $request->getView();
		$this->_action = $request->getAction();

		$controllerPath = \Agl::app()->getPath()
                          . \Agl::APP_PHP_DIR
                          . DS
                          . \Agl\Core\Mvc\Controller\Controller::APP_PHP_CONTROLLER_DIR
                          . DS
                          . ucfirst($this->_module)
                          . DS
                          . ucfirst($this->_view)
                          . \Agl::PHP_EXT;

        if (file_exists($controllerPath)) {
            $className = ucfirst($this->_module) . ucfirst($this->_view) . \Agl\Core\Mvc\Controller\Controller::APP_CONTROLLER_SUFFIX;
            $this->_controller = \Agl::getInstance($className);
        } else {
            $this->_controller = new \Agl\Core\Mvc\Controller\Controller();
        }

    	$this->_checkAcl();
        $this->_actionMethod = \Agl\Core\Data\String::toCamelCase($this->_action) . \Agl\Core\Mvc\Controller\Controller::ACTION_METHOD_SUFFIX;

		if (! method_exists($this->_controller, $this->_actionMethod)) {
			throw new Exception("Invalid action '$this->_actionMethod' requested to controller");
		}
	}

	/**
	 * Check if the current user can trigger the action with its Acl
	 * configuration.
	 *
	 * @return bool
	 */
	private function _checkAcl()
	{
		$aclConfig = \Agl::app()->getConfig('@layout/modules/' . $this->_module . '/' . $this->_view . '/acl/all');

		if (! $aclConfig) {
			$aclConfig = \Agl::app()->getConfig('@layout/modules/' . $this->_module . '/' . $this->_view . '/acl/' . $this->_action);
		}

    	if ($aclConfig and ! \Agl::getSingleton(self::AGL_CORE_DIR . '/auth/acl')->isAllowed('admin', $aclConfig)) {
    		throw new \Agl\Exception("Invalid ACL to request the action '" . $this->_action . "'");
        }

        return true;
	}

	/**
	 * Route the request by invoking the corresponding controller and method.
	 */
	public function route()
	{
		\Agl\Core\Observer\Observer::dispatch(\Agl\Core\Observer\Observer::EVENT_ROUTER_ROUTE_BEFORE, array(
			'router' => $this
		));

		$this->_controller->{$this->_actionMethod}();
	}
}
