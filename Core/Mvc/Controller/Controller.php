<?php
namespace Agl\Core\Mvc\Controller;

/**
 * Base Controller class.
 *
 * @category Agl_Core
 * @package Agl_Core_Mvc_Controller
 * @version 0.1.0
 */

class Controller
{
	/**
	 * Suffix used to name the action methods.
	 */
	const ACTION_METHOD_SUFFIX = 'Action';

	/**
	 * Default action to trigger.
	 */
	const DEFAULT_ACTION = 'index';

	/**
	 * The suffix used by the application's controller class names.
	 */
	const APP_CONTROLLER_SUFFIX = 'Controller';

	/**
	 * The application directory to search a Controller class.
	 */
	const APP_PHP_CONTROLLER_DIR = 'Controller';

	/**
	 * The cache instance if required.
	 *
	 * @var null|Raw
	 */
	protected $_cacheInstance = NULL;

	/**
	 * Render the view page.
	 *
	 * @return Controller
	 */
	protected function _renderView()
	{
		if ($this->_cacheInstance) {
			$apcEnabled = \Agl\Core\Cache\Apc\Apc::isApcEnabled();

			if ($apcEnabled) {
				$content = \Agl\Core\Cache\Apc\Apc::get($this->_cacheInstance[0]);
	            if ($content !== false) {
	            	echo $content;
	                return $this;
	            }
			} else {
	            $content = $this->_cacheInstance->getContent();
	            if ($content) {
	                echo $content;
	                return $this;
	            }
	        }
		}

        $viewModel = $this->_setView();

        if ($this->_cacheInstance) {
        	ob_start();
		}

		$viewModel
			->startBuffer()
			->render();

		if ($this->_cacheInstance) {
        	$content = ob_get_clean();

        	if ($apcEnabled) {
        		\Agl\Core\Cache\Apc\Apc::set($this->_cacheInstance[0], $content, $this->_cacheInstance[1]);
        	} else {
        		$this->_cacheInstance
	                ->setContent($content)
	                ->save();
        	}

            echo $content;
		}

		return $this;
	}

	/**
	 * Create and return the current view instance.
	 *
	 * @return mixed
	 */
	protected function _setView()
	{
		$request = \Agl::getRequest();
		$module  = $request->getModule();
		$view    = $request->getView();

		$viewPath = \Agl::app()->getPath()
                    . \Agl::APP_PHP_DIR
                    . DS
                    . \Agl\Core\Mvc\View\ViewInterface::APP_PHP_VIEW_DIR
                    . DS
                    . ucfirst($module)
                    . DS
                    . ucfirst($view)
                    . \Agl::PHP_EXT;

        if (file_exists($viewPath)) {
            $className    = ucfirst($module) . ucfirst($view) . \Agl\Core\Mvc\View\ViewInterface::APP_VIEW_SUFFIX;
            $viewInstance = \Agl::getInstance($className);
        } else {
            $viewInstance = new \Agl\Core\Mvc\View\View();
        }

        $viewInstance->setFile($module . DS . $view);

        \Agl::register('view', $viewInstance);

        return $viewInstance;
	}

	/**
	 * Instanciate the cache if required.
	 * Tha cache key is composed of the module, the view and the actions names,
	 * and of the locale code and the request string if required.
	 *
	 * @return ViewAbstract
	 */
	public function setCacheInstance()
	{
		if (! \Agl::app()->isCacheEnabled()) {
			return $this;
		}

		$request     = \Agl::getRequest();
		$module      = $request->getModule();
		$view        = $request->getView();
		$action      = $request->getAction();
		$cacheConfig = \Agl::app()->getConfig('@layout/modules/' . $module . '/' . $view . '/cache/all');

		if (! $cacheConfig) {
			$cacheConfig = \Agl::app()->getConfig('@layout/modules/' . $module . '/' . $view . '/cache/' . $action);
		}

		if (is_array($cacheConfig)) {
			$configCacheTtlName  = \Agl\Core\Config\ConfigInterface::CONFIG_CACHE_TTL_NAME;
			$configCacheTypeName = \Agl\Core\Config\ConfigInterface::CONFIG_CACHE_TYPE_NAME;

			$ttl = (isset($cacheConfig[$configCacheTtlName]) and ctype_digit($cacheConfig[$configCacheTtlName])) ? (int)$cacheConfig[$configCacheTtlName] : 0;

			$type = (isset($cacheConfig[$configCacheTypeName])) ? $cacheConfig[$configCacheTypeName] : \Agl\Core\Config\ConfigInterface::CONFIG_CACHE_TYPE_STATIC;

			$configKeySeparator = \Agl\Core\Cache\File\FileInterface::CACHE_FILE_SEPARATOR;

			$configKey = \Agl\Core\Mvc\View\ViewInterface::CACHE_FILE_PREFIX . $module . $configKeySeparator . $view . $configKeySeparator . $action;

			if (\Agl::isModuleLoaded(\Agl::AGL_MORE_POOL . '/locale/locale')) {
	            $configKey .= $configKeySeparator . \Agl::getSingleton(\Agl::AGL_MORE_POOL . '/locale/locale')->getLanguage();
	        }

			if ($type == \Agl\Core\Config\ConfigInterface::CONFIG_CACHE_TYPE_DYNAMIC) {
				$configKey .= $configKeySeparator . \Agl\Core\Data\String::rewrite($request->getReq());
				if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
					$configKey .= $configKeySeparator . 'ajax';
				}
			}

			if (\Agl\Core\Cache\Apc\Apc::isApcEnabled()) {
				$this->_cacheInstance = array($configKey, $ttl);
			} else {
				$this->_cacheInstance = new \Agl\Core\Cache\File\Format\Raw($configKey, $ttl);
			}
		}

		return $this;
	}

	/**
	 * Default index action.
	 */
	public function indexAction()
	{
		$this->_renderView();
	}
}
