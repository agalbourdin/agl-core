<?php
namespace Agl\Core\Mvc\Controller;

use \Agl\Core\Agl,
	\Agl\Core\Cache\Apc\Apc,
	\Agl\Core\Cache\File\FileInterface,
	\Agl\Core\Cache\File\Format\Raw as RawCache,
	\Agl\Core\Config\ConfigInterface,
	\Agl\Core\Data\String as StringData,
	\Agl\Core\Mvc\View\View,
	\Agl\Core\Mvc\View\ViewInterface,
	\Agl\Core\Registry\Registry,
	\Agl\Core\Request\Request;

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
	const APP_PHP_CONTROLLER_DIR = 'controller';

	/**
	 * Render the view page.
	 *
	 * @return Controller
	 */
	protected function _renderView()
	{
		$cacheInstance = $this->getCacheInstance();

		if ($cacheInstance) {
			$apcEnabled = Apc::isEnabled();

			if ($apcEnabled) {
				$content = Apc::get($cacheInstance[0]);
	            if ($content !== false) {
	            	echo $content;
	                return $this;
	            }
			} else {
	            $content = $cacheInstance->get();
	            if ($content) {
	                echo $content;
	                return $this;
	            }
	        }
		}

        $viewModel = $this->_setView();

        if ($cacheInstance) {
        	ob_start();
		}

		$viewModel
			->startBuffer()
			->render();

		if ($cacheInstance) {
        	$content = ob_get_clean();

        	if ($apcEnabled) {
        		Apc::set($cacheInstance[0], $content, $cacheInstance[1]);
        	} else {
        		$cacheInstance
	                ->set($content)
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
		$request = Agl::getRequest();
		$module  = $request->getModule();
		$view    = $request->getView();

		$viewPath = APP_PATH
                    . Agl::APP_PHP_DIR
                    . ViewInterface::APP_PHP_VIEW_DIR
                    . DS
                    . $module
                    . DS
                    . $view
                    . Agl::PHP_EXT;

        if (file_exists($viewPath)) {
            $className    = ucfirst($module) . ucfirst($view) . ViewInterface::APP_VIEW_SUFFIX;
            $viewInstance = Agl::getInstance($className);
        } else {
            $viewInstance = new View();
        }

        $viewInstance->setFile($module . DS . $view);

        Registry::set('view', $viewInstance);

        return $viewInstance;
	}

	/**
	 * Instanciate the cache if required.
	 * Tha cache key is composed of the module, the view and the actions names,
	 * and of the locale code and the request string if required.
	 *
	 * @return null|Raw|array
	 */
	public function getCacheInstance()
	{
		if (! Agl::app()->isCacheEnabled()) {
			return NULL;
		}

		$request     = Agl::getRequest();
		$module      = $request->getModule();
		$view        = $request->getView();
		$action      = $request->getAction();
		$cacheConfig = Agl::app()->getConfig('@layout/modules/' . $module . '/views/' . $view . '/actions/' . $action . '/cache');

		if ($cacheConfig === NULL) {
			$cacheConfig = Agl::app()->getConfig('@layout/modules/' . $module . '/views/' . $view . '/cache');
		}

		if ($cacheConfig === NULL) {
			$cacheConfig = Agl::app()->getConfig('@layout/modules/' . $module . '/cache');
		}

		if ($cacheConfig !== NULL and is_array($cacheConfig)) {
			$configCacheTtlName  = ConfigInterface::CONFIG_CACHE_TTL_NAME;
			$configCacheTypeName = ConfigInterface::CONFIG_CACHE_TYPE_NAME;

			$ttl = (isset($cacheConfig[$configCacheTtlName]) and ctype_digit($cacheConfig[$configCacheTtlName])) ? (int)$cacheConfig[$configCacheTtlName] : 0;

			$type = (isset($cacheConfig[$configCacheTypeName])) ? $cacheConfig[$configCacheTypeName] : ConfigInterface::CONFIG_CACHE_TYPE_STATIC;

			$configKeySeparator = FileInterface::CACHE_FILE_SEPARATOR;

			$configKey = ViewInterface::CACHE_FILE_PREFIX . $module . $configKeySeparator . $view . $configKeySeparator . $action;

			if (Agl::isModuleLoaded(Agl::AGL_MORE_POOL . '/locale/locale')) {
	            $configKey .= $configKeySeparator . Agl::getSingleton(Agl::AGL_MORE_POOL . '/locale')->getLanguage();
	        }

			if ($type == ConfigInterface::CONFIG_CACHE_TYPE_DYNAMIC) {
				$configKey .= $configKeySeparator . StringData::rewrite($request->getReq());
				if (Request::isAjax()) {
					$configKey .= $configKeySeparator . ConfigInterface::CONFIG_CACHE_KEY_AJAX;
				}
			}

			if (Apc::isEnabled()) {
				return array($configKey, $ttl);
			} else {
				return new RawCache($configKey, $ttl);
			}
		}

		return NULL;
	}

	/**
	 * Default index action.
	 */
	public function indexAction()
	{
		$this->_renderView();
	}
}
