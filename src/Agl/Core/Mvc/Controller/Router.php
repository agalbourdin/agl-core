<?php
namespace Agl\Core\Mvc\Controller;

use \Agl\Core\Agl,
	\Agl\Core\Data\String as StringData,
	\Agl\Core\Mvc\Controller\Controller,
	\Agl\Core\Observer\Observer,
	\Exception;

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
		$request = Agl::getRequest($pRequestUri);

		Observer::dispatch(Observer::EVENT_ROUTER_ROUTE_BEFORE, array(
			'router' => $this
		));

		$this->_module = $request->getModule();
		$this->_view   = $request->getView();
		$this->_action = $request->getAction();

		$controllerPath = APP_PATH
                          . Agl::APP_PHP_DIR
                          . Controller::APP_PHP_CONTROLLER_DIR
                          . DS
                          . $this->_module
                          . DS
                          . $this->_view
                          . Agl::PHP_EXT;

        if (file_exists($controllerPath)) {
            $className = $this->_module . ucfirst($this->_view) . Controller::APP_CONTROLLER_SUFFIX;
            $this->_controller = Agl::getInstance($className);
        } else {
            $this->_controller = new Controller();
        }

    	$this->_checkAcl();
        $this->_actionMethod = $this->_action . Controller::ACTION_METHOD_SUFFIX;

		if (! method_exists($this->_controller, $this->_actionMethod) and ! Agl::app()->isDebugMode()) {
			$this->_actionMethod = Controller::DEFAULT_ACTION . Controller::ACTION_METHOD_SUFFIX;
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
		$aclConfig = Agl::app()->getConfig('@layout/modules/' . $this->_module . '#' . $this->_view . '#action#' . $this->_action . '/acl');

		if ($aclConfig === NULL) {
			$aclConfig = Agl::app()->getConfig('@layout/modules/' . $this->_module . '#' . $this->_view . '/acl');
		}

		if ($aclConfig === NULL) {
			$aclConfig = Agl::app()->getConfig('@layout/modules/' . $this->_module . '/acl');
		}

    	if ($aclConfig !== NULL) {
			$auth = Agl::getAuth();
			$acl  = Agl::getSingleton(Agl::AGL_CORE_POOL . '/auth/acl');
			if (! $acl->isAllowed($auth->getRole(), $aclConfig)) {
	    		$acl->requestLogin();
	    	}
        }

        return true;
	}

	/**
	 * Route the request by invoking the corresponding controller and method.
	 */
	public function route()
	{
		echo $this->_controller->{$this->_actionMethod}();
	}
}
