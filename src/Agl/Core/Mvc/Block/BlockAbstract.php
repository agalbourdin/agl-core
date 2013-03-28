<?php
namespace Agl\Core\Mvc\Block;

use \Agl\Core\Agl,
	\Agl\Core\Cache\Apc\Apc,
	\Agl\Core\Cache\File\FileInterface,
	\Agl\Core\Cache\File\Format\Raw as RawCache,
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
     * Create and get an instance of Raw Cache.
     *
     * @param array $pBlockConfig Block configuration
     * @return Raw|array
     */
    public static function getCacheInstance(array $blockPathInfos, array $pBlockConfig)
    {
		$request             = Agl::getRequest();
		$configCacheName     = ConfigInterface::CONFIG_CACHE_NAME;
		$configCacheTtlName  = ConfigInterface::CONFIG_CACHE_TTL_NAME;
		$configCacheTypeName = ConfigInterface::CONFIG_CACHE_TYPE_NAME;

        $ttl = (isset($pBlockConfig[$configCacheName])
        		and is_array($pBlockConfig[$configCacheName])
        		and isset($pBlockConfig[$configCacheName][$configCacheTtlName])
        		and ctype_digit($pBlockConfig[$configCacheName][$configCacheTtlName])
        	   ) ? (int)$pBlockConfig[$configCacheName][$configCacheTtlName] : 0;

        $type = (isset($pBlockConfig[$configCacheName])
        		 and is_array($pBlockConfig[$configCacheName])
        		 and isset($pBlockConfig[$configCacheName][$configCacheTypeName])
        		) ? $pBlockConfig[$configCacheName][$configCacheTypeName] : ConfigInterface::CONFIG_CACHE_TYPE_STATIC;

		$configKeySeparator = FileInterface::CACHE_FILE_SEPARATOR;
		$configKey          = static::CACHE_FILE_PREFIX . $blockPathInfos[1] . $configKeySeparator . $blockPathInfos[2];

		if (Agl::isModuleLoaded(Agl::AGL_MORE_POOL . '/locale/locale')) {
			$configKey .= $configKeySeparator . Agl::getSingleton(Agl::AGL_MORE_POOL . '/locale')->getLanguage();
		}

		if ($type == ConfigInterface::CONFIG_CACHE_TYPE_DYNAMIC) {
			$configKey .= $configKeySeparator . \Agl\Core\Data\String::rewrite($request->getReq());
			if (Request::isAjax()) {
				$configKey .= $configKeySeparator . ConfigInterface::CONFIG_CACHE_KEY_AJAX;
			}
		}

		$apcEnabled = Apc::isEnabled();
		if ($apcEnabled) {
			return array($configKey, $ttl);
		} else {
			return new RawCache($configKey, $ttl);
		}
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
		$blockConfig = Agl::app()->getConfig('@layout/blocks/' . $pGroupId . '/' . $pBlockId);
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
	 * Include the template in the current page.
	 */
	public function render()
	{
		$path = APP_PATH
		        . ViewInterface::APP_HTTP_TEMPLATE_DIR
		        . DS
                . Agl::app()->getConfig('@app/global/theme')
		        . DS
		        . static::APP_HTTP_BLOCK_DIR
		        . DS
		        . $this->_file;

		require($path);
	}
}
