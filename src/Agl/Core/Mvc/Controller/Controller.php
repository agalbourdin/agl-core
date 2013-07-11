<?php
namespace Agl\Core\Mvc\Controller;

use \Agl\Core\Agl,
	\Agl\Core\Cache\CacheInterface,
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
	private function _renderView()
	{
		$isCacheEnabled = Agl::app()->isCacheEnabled();

		if ($isCacheEnabled) {
			$cacheInfo = $this->_getCacheInfo();

			if (! empty($cacheInfo)
				and $cacheInstance = Agl::getCache()
				and $cacheInstance->has($cacheInfo[CacheInterface::CACHE_KEY])) {
				return $cacheInstance->get($cacheInfo[CacheInterface::CACHE_KEY]);
			}
		}

        $viewModel = $this->_setView();

		$content = $viewModel
			->startBuffer()
			->render();

		if ($isCacheEnabled and ! empty($cacheInfo)) {
        	$cacheInstance->set($cacheInfo[CacheInterface::CACHE_KEY], $content, $cacheInfo[ConfigInterface::CONFIG_CACHE_TTL_NAME]);
		}

		return $content;
	}

	/**
	 * Create and return the current view instance.
	 *
	 * @return mixed
	 */
	private function _setView()
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
	 * Return a TTL and a cache key composed of the module, the view, the
	 * actions and of the locale code and the request string if required.
	 *
	 * @return array
	 */
	private function _getCacheInfo()
	{
		$request     = Agl::getRequest();
		$module      = $request->getModule();
		$view        = $request->getView();
		$action      = $request->getAction();
		$cacheInfo   = array();
		$cacheConfig = Agl::app()->getConfig('@layout/modules/' . $module . '#' . $view . '#action#' . $action . '/' . ConfigInterface::CONFIG_CACHE_NAME);

		if ($cacheConfig === NULL) {
			$cacheConfig = Agl::app()->getConfig('@layout/modules/' . $module . '#' . $view . '/' . ConfigInterface::CONFIG_CACHE_NAME);
		}

		if ($cacheConfig === NULL) {
			$cacheConfig = Agl::app()->getConfig('@layout/modules/' . $module . '/' . ConfigInterface::CONFIG_CACHE_NAME);
		}

		if ($cacheConfig === NULL) {
			$cacheConfig = Agl::app()->getConfig('@layout/modules/' . ConfigInterface::CONFIG_CACHE_NAME);
		}

		if (is_array($cacheConfig)) {
			$configCacheTtlName  = ConfigInterface::CONFIG_CACHE_TTL_NAME;
			$configCacheTypeName = ConfigInterface::CONFIG_CACHE_TYPE_NAME;

			$cacheInfo[$configCacheTtlName] = (isset($cacheConfig[$configCacheTtlName]) and ctype_digit($cacheConfig[$configCacheTtlName])) ? (int)$cacheConfig[$configCacheTtlName] : 0;

			$type = (isset($cacheConfig[$configCacheTypeName])) ? $cacheConfig[$configCacheTypeName] : ConfigInterface::CONFIG_CACHE_TYPE_STATIC;

			$cacheInfo[CacheInterface::CACHE_KEY] = ViewInterface::CACHE_FILE_PREFIX
			                                        . $module
			                                        . CacheInterface::CACHE_KEY_SEPARATOR
			                                        . $view
			                                        . CacheInterface::CACHE_KEY_SEPARATOR
			                                        . $action;

			if (Agl::isModuleLoaded(Agl::AGL_MORE_POOL . '/locale/locale')) {
	            $cacheInfo[CacheInterface::CACHE_KEY] .= CacheInterface::CACHE_KEY_SEPARATOR
	                                                     . Agl::getSingleton(Agl::AGL_MORE_POOL . '/locale')->getLanguage();
	        }

			if ($type == ConfigInterface::CONFIG_CACHE_TYPE_DYNAMIC) {
				$cacheInfo[CacheInterface::CACHE_KEY] .= CacheInterface::CACHE_KEY_SEPARATOR
				                                         . StringData::rewrite($request->getReq());
				if (Request::isAjax()) {
					$cacheInfo[CacheInterface::CACHE_KEY] .= CacheInterface::CACHE_KEY_SEPARATOR
					                                         . ConfigInterface::CONFIG_CACHE_KEY_AJAX;
				}
			}
		}

		return $cacheInfo;
	}

	/**
	 * Default index action.
	 */
	public function indexAction()
	{
		return $this->_renderView();
	}
}
