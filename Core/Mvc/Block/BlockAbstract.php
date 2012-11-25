<?php
namespace Agl\Core\Mvc\Block;

/**
 * Absract class - Block
 *
 * @category Agl_Core
 * @package Agl_Core_Mvc_Block
 * @version 0.1.0
 */

abstract class BlockAbstract
{
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
    public static function getCacheInstance(array $pBlockConfig)
    {
		$request             = \Agl::getRequest();
		$configCacheName     = \Agl\Core\Config\ConfigInterface::CONFIG_CACHE_NAME;
		$configCacheTtlName  = \Agl\Core\Config\ConfigInterface::CONFIG_CACHE_TTL_NAME;
		$configCacheTypeName = \Agl\Core\Config\ConfigInterface::CONFIG_CACHE_TYPE_NAME;

        $ttl = (isset($pBlockConfig[$configCacheName]) and is_array($pBlockConfig[$configCacheName]) and isset($pBlockConfig[$configCacheName][$configCacheTtlName]) and ctype_digit($pBlockConfig[$configCacheName][$configCacheTtlName])) ? (int)$pBlockConfig[$configCacheName][$configCacheTtlName] : 0;

        $type = (isset($pBlockConfig[$configCacheName]) and is_array($pBlockConfig[$configCacheName]) and isset($pBlockConfig[$configCacheName][$configCacheTypeName])) ? $pBlockConfig[$configCacheName][$configCacheTypeName] : \Agl\Core\Config\ConfigInterface::CONFIG_CACHE_TYPE_STATIC;

        $configKeySeparator = \Agl\Core\Cache\File\FileInterface::CACHE_FILE_SEPARATOR;
		$configKey = \Agl\Core\Mvc\Block\BlockInterface::CACHE_FILE_PREFIX . $pBlockConfig['id'];

		if (\Agl::isModuleLoaded(\Agl::AGL_MORE_POOL . '/locale/locale')) {
			$configKey .= $configKeySeparator . \Agl::getSingleton(\Agl::AGL_MORE_POOL . '/locale/locale')->getLanguage();
		}

		if ($type == \Agl\Core\Config\ConfigInterface::CONFIG_CACHE_TYPE_DYNAMIC) {
			$configKey .= $configKeySeparator . \Agl\Core\Data\String::rewrite($request->getReq());
			if ($request->isAjax()) {
				$configKey .= $configKeySeparator . \Agl\Core\Config\ConfigInterface::CONFIG_CACHE_KEY_AJAX;
			}
		}

		$apcEnabled = \Agl\Core\Cache\Apc\Apc::isApcEnabled();
		if ($apcEnabled) {
			return array($configKey, $ttl);
		} else {
			return new \Agl\Core\Cache\File\Format\Raw($configKey, $ttl);
		}
    }

    /**
     * Check if the cache is enabled for the passed block configuration array.
     *
     * @return bool
     */
    public static function isCacheEnabled($pBlockConfig)
    {
    	$configCacheName        = \Agl\Core\Config\ConfigInterface::CONFIG_CACHE_NAME;

    	return (is_array($pBlockConfig) and \Agl::app()->isCacheEnabled() and isset($pBlockConfig[$configCacheName]) and is_array($pBlockConfig[$configCacheName]));
    }

	/**
	 * Check if the current user can access the block with its Acl
	 * configuration.
	 *
	 * @return bool
	 */
	public static function checkAcl($pGroupId, $pBlockId)
	{
        $blockConfig = \Agl::app()->getConfig('@layout/blocks/' . $pGroupId . '/' . $pBlockId);
		if (is_array($blockConfig) and isset($pBlockConfig['acl']) and ! \Agl::getAcl()->isAllowed('admin', $pBlockConfig['acl'])) {
			throw new \Agl\Exception("Invalid ACL to request the block '" . $pBlockConfig['id'] . "'");
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
		return \Agl::registry('view');
	}

	/**
	 * Include the template in the current page.
	 */
	public function render()
	{
		$path = \Agl::app()->getPath()
		        . \Agl\Core\Mvc\View\ViewInterface::APP_HTTP_TEMPLATE_DIR
		        . DS
                . \Agl::app()->getConfig('@app/global/theme')
		        . DS
		        . \Agl\Core\Mvc\Block\BlockInterface::APP_HTTP_BLOCK_DIR
		        . DS
		        . $this->_file;

		require($path);
	}
}
