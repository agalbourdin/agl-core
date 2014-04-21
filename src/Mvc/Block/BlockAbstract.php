<?php
namespace Agl\Core\Mvc\Block;

use \Agl\Core\Agl,
	\Agl\Core\Cache\CacheInterface,
	\Agl\Core\Config\ConfigInterface,
	\Agl\Core\Debug\Debug,
	\Agl\Core\Mvc\View\ViewInterface,
	\Agl\Core\Registry\Registry,
	\Agl\Core\Request\Request;

/**
 * Absract class - Block
 *
 * @category Agl_Core
 * @package Agl_Core_Mvc_Block
 * @version 0.1.0
 */

abstract class BlockAbstract
	implements BlockInterface
{
	/**
	 * Parent view instance.
	 *
	 * @var View
	 */
	private $_view = NULL;

	/**
	 * Template file.
	 *
	 * @var string
	 */
	protected $_file = NULL;

	/**
	 * Array of variables (key => value) that will be passed to block.
	 *
	 * @var array
	 */
	protected $_vars = array();

	/**
	 * Return a TTL and a cache key.
	 *
	 * @return array
	 */
    public static function getCacheInfo(array $blockPathInfos, array $pBlockConfig)
    {
    	$configCacheName     = ConfigInterface::CONFIG_CACHE_NAME;
		$configCacheTtlName  = ConfigInterface::CONFIG_CACHE_TTL_NAME;
		$configCacheTypeName = ConfigInterface::CONFIG_CACHE_TYPE_NAME;
		$cacheInfo           = array();

    	if (! self::isCacheEnabled($pBlockConfig)) {
    		$cacheConfig = Agl::app()->getConfig('core-layout/blocks/' . ConfigInterface::CONFIG_CACHE_NAME);
    		if ($cacheConfig === NULL) {
	    		return $cacheInfo;
	    	}
    	} else {
    		$cacheConfig = $pBlockConfig[$configCacheName];
    	}

		$request   = Agl::getRequest();

        $cacheInfo[$configCacheTtlName] = (isset($cacheConfig[$configCacheTtlName])
        		                           and ctype_digit($cacheConfig[$configCacheTtlName])
        	                              ) ? (int)$cacheConfig[$configCacheTtlName] : 0;

        $type = (isset($cacheConfig[$configCacheTypeName])) ? $cacheConfig[$configCacheTypeName] : ConfigInterface::CONFIG_CACHE_TYPE_STATIC;

		$cacheInfo[CacheInterface::CACHE_KEY] = static::CACHE_FILE_PREFIX
		                                        . $blockPathInfos[1]
		                                        . CacheInterface::CACHE_KEY_SEPARATOR
		                                        . $blockPathInfos[2];

		if (Agl::isModuleLoaded(Agl::AGL_MORE_POOL . '/locale/locale')) {
			$cacheInfo[CacheInterface::CACHE_KEY] .= CacheInterface::CACHE_KEY_SEPARATOR
			                                         . Agl::getSingleton(Agl::AGL_MORE_POOL . '/locale')->getLanguage();
		}

		if ($type == ConfigInterface::CONFIG_CACHE_TYPE_DYNAMIC) {
			$cacheInfo[CacheInterface::CACHE_KEY] .= CacheInterface::CACHE_KEY_SEPARATOR
			                                         . \Agl\Core\Data\String::rewrite($request->getReq());
			if (Request::isAjax()) {
				$cacheInfo[CacheInterface::CACHE_KEY] .= CacheInterface::CACHE_KEY_SEPARATOR
				                                         . ConfigInterface::CONFIG_CACHE_KEY_AJAX;
			}
		}

		return $cacheInfo;
    }

    /**
     * Check if the cache is enabled for the passed block configuration array.
     *
     * @return bool
     */
    public static function isCacheEnabled($pBlockConfig)
    {
    	$configCacheName = ConfigInterface::CONFIG_CACHE_NAME;

    	return (is_array($pBlockConfig)
    			and Agl::app()->isCacheEnabled()
    			and isset($pBlockConfig[$configCacheName])
    			and is_array($pBlockConfig[$configCacheName]));
    }

	/**
	 * Check if the current user can access the block with its Acl
	 * configuration.
	 *
	 * @return bool
	 */
	public static function checkAcl($pGroupId, $pBlockId)
	{
		$blockConfig = Agl::app()->getConfig('core-layout/blocks/' . $pGroupId . '#' . $pBlockId);
		if (is_array($blockConfig) and isset($blockConfig['acl'])) {
			$auth = Agl::getAuth();
			$acl  = Agl::getSingleton(Agl::AGL_CORE_POOL . '/auth/acl');
			if (! $acl->isAllowed($auth->getRole(), $blockConfig['acl'])) {
				return false;
			}
		}

        return true;
	}

	/**
	 * Register the template.
	 *
	 * @param string $pTemplate
	 * @return View
	 */
	public function setFile($pFile)
	{
        $this->_file = $pFile;
        return $this;
	}

	/**
	 * Set block variables.
	 *
	 * @var array
	 * @return BlockAbstract
	 */
	public function setVars(array $pVars = array())
	{
		$this->_vars = $pVars;
		return $this;
	}

	/**
	 * Get the parent View class.
	 *
	 * @return View
	 */
	public function getView()
	{
		if ($this->_view === NULL) {
			$this->_view = Registry::get('view');
		}

		return $this->_view;
	}

	/**
	 * Extract variables and include the template in the current page.
	 *
	 */
	public function render()
	{
		extract($this->_vars);

		$path = APP_PATH
		        . ViewInterface::APP_HTTP_TEMPLATE_DIR
		        . DS
		        . static::APP_HTTP_BLOCK_DIR
		        . DS
		        . $this->_file;

		require($path);
	}
}
