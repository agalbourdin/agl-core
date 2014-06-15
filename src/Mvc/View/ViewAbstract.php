<?php
namespace Agl\Core\Mvc\View;

use \Agl\Core\Agl,
	\Agl\Core\Cache\CacheInterface,
	\Agl\Core\Config\ConfigInterface,
	\Agl\Core\Debug\Debug,
	\Agl\Core\Mvc\Block\Block,
	\Agl\Core\Mvc\Block\BlockAbstract,
	\Agl\Core\Mvc\Block\BlockInterface,
	\Agl\Core\Mvc\View\ViewInterface,
	\Agl\Core\Observer\Observer,
	\Agl\Core\Request\Request,
	\Exception;

/**
 * Absract class - View
 *
 * @category Agl_Core
 * @package Agl_Core_Mvc_View
 * @version 0.1.0
 */

abstract class ViewAbstract
{
	/**
	 * Template configuration loaded from config files.
	 *
	 * @var mixed
	 */
	protected static $_templateConfig = 0;

	/**
	 * Template file.
	 *
	 * @var string
	 */
	protected $_file = NULL;

	/**
	 * List of the blocks included in the current view.
	 *
	 * @var array
	 */
	protected $_blocks = array();

	/**
	 * Absolute view path.
	 *
	 * @var string
	 */
	protected $_path = NULL;

	/**
	 * View type (html...).
	 *
	 * @var string
	 */
	protected $_type = NULL;

	/**
	 * Get the template configuration.
	 *
	 * @return mixed
	 */
	public static function getTemplateConfig()
	{
		if (self::$_templateConfig === 0) {
			$request = Agl::getRequest();
			$module  = $request->getModule();
			$view    = $request->getView();
			$action  = $request->getAction();

			self::$_templateConfig = Agl::app()->getConfig('core-layout/modules/' . $module . '#' . $view . '#action#' . $action . '/template');

			if (self::$_templateConfig === NULL) {
			    self::$_templateConfig = Agl::app()->getConfig('core-layout/modules/' . $module . '#' . $view . '/template');
			}

			if (self::$_templateConfig === NULL) {
			    self::$_templateConfig = Agl::app()->getConfig('core-layout/modules/' . $module . '/template');
			}

			if (self::$_templateConfig === NULL) {
				self::$_templateConfig = Agl::app()->getConfig('core-layout/template');
			}
		}

		if (! isset(self::$_templateConfig['type'])) {
			self::$_templateConfig['type'] = ViewInterface::TYPE_HTML;
		}

		return self::$_templateConfig;
	}

	/**
	 * Prepare the rendering depending of the view type.
	 *
	 * @param $pBuffer string
	 * @return string
	 */
	protected function _prepareRender($pBuffer)
	{
		return $pBuffer;
	}

	/**
	 * Return the application's skin relative path.
	 *
	 * @return string
	 */
	protected function _getSkinRelativePath()
	{
		return ROOT
			   . Agl::APP_PUBLIC_DIR
			   . static::APP_HTTP_SKIN_DIR
			   . DS;
	}

	/**
	 * Register the template.
	 *
	 * @param string $pFile
	 * @return View
	 */
	public function setFile($pFile)
	{
        $this->_file = $pFile . Agl::PHP_EXT;

        return $this;
	}

	/**
	 * Start the View buffer.
	 *
	 * @return View
	 */
	public function startBuffer()
	{
		ob_start(/*array($this, 'getBuffer')*/);
		return $this;
	}

	/**
	 * Prepare and display the buffer.
	 *
	 * @param string $pBuffer
	 * @return string
	 */
	/*public function getBuffer($pBuffer)
	{
		Agl::validateParams(array(
			'String' => $pBuffer
        ));

        Observer::dispatch(Observer::EVENT_VIEW_RENDER_BUFFER_BEFORE, array(
			'view'   => $this,
			'buffer' => &$pBuffer
		));

		$pBuffer = $this->_prepareRender($pBuffer);

		return $pBuffer;
	}*/

	/**
	 * Include the template in the current page.
	 *
	 * @return View
	 */
	public function render()
	{
		$this->_path = $this->getPath();

		if (! is_readable($this->_path)) {
			Request::setHttpHeader(Request::HEADER_404);
			$this->setFile(static::ERROR_404);
			$this->_path = $this->getPath();

			if (! is_readable($this->_path)) {
				throw new Exception("View file '" . $this->_path . "' doesn't exists");
			}
		}

		$template = self::getTemplateConfig();
		if (! is_array($template) or ! isset($template['type'])) {
			throw new Exception("A template and a template type are required to render the view");
		}

		$this->_type = $template['type'];

		if (isset($template['file'])) {
			$template = APP_PATH
				        . Agl::APP_TEMPLATE_DIR
				        . $template['file']
				        . Agl::PHP_EXT;

			require($template);
		} else {
			require($this->_path);
		}

		$buffer = ob_get_clean();

		Observer::dispatch(Observer::EVENT_VIEW_RENDER_BUFFER_BEFORE, array(
			'view'   => $this,
			'buffer' => &$buffer
		));

		return $this->_prepareRender($buffer);
	}

	/**
	 * Require the view template file.
	 *
	 * @return View
	 */
	public function getView()
	{
		ob_start();
		require($this->_path);
		return ob_get_clean();
	}

	/**
	 * Create a block and include it into the view.
	 *
	 * @param string $pBlock Block identifier
	 * @param array $pVars Array of variables (key => value) that will be
	 * passed to block
	 * @return Block
	 */
	public function getBlock($pBlock, array $pVars = array())
	{
		if (! preg_match('#^([a-z0-9]+)/([a-z0-9_-]+)$#', $pBlock, $blockPathInfos)
			or ! isset($blockPathInfos[1])
			or ! isset($blockPathInfos[2])) {
			throw new Exception("Block identifier '$pBlock' is not correct");
		}

        $this->_blocks[] = array(
        	'group' => $blockPathInfos[1],
        	'block' => $blockPathInfos[2]
        );

        $blockConfig = Agl::app()->getConfig('core-layout/blocks/' . $blockPathInfos[1] . '#' . $blockPathInfos[2]);
        if ($blockConfig === NULL) {
        	$blockConfig = array();
        }

		if (! BlockAbstract::checkAcl($blockPathInfos[1], $blockPathInfos[2])) {
			return '';
		}

		$isCacheEnabled = Agl::app()->isCacheEnabled();

		if ($isCacheEnabled) {
			$cacheInfo = BlockAbstract::getCacheInfo($blockPathInfos, $blockConfig);

			if (! empty($cacheInfo)
				and $cacheInstance = Agl::getCache()
				and $cacheInstance->has($cacheInfo[CacheInterface::CACHE_KEY])) {
				return $cacheInstance->get($cacheInfo[CacheInterface::CACHE_KEY]);
			}
		}

		$blockPath = APP_PATH
					. Agl::APP_PHP_DIR
                    . BlockInterface::APP_PHP_DIR
                    . DS
                    . $blockPathInfos[1]
                    . DS
                    . $blockPathInfos[2]
                    . Agl::PHP_EXT;

        if (file_exists($blockPath)) {
            $className = $blockPathInfos[1] . ucfirst($blockPathInfos[2]) . BlockInterface::APP_SUFFIX;
            $blockModel = Agl::getInstance($className);
        } else {
            $blockModel = new Block();
        }

        ob_start();

        $blockModel
			->setFile($blockPathInfos[1] . DS . $blockPathInfos[2] . Agl::PHP_EXT)
			->setVars($pVars)
			->render();

		$content = ob_get_clean();

		if ($isCacheEnabled and ! empty($cacheInfo)) {
        	$cacheInstance->set($cacheInfo[CacheInterface::CACHE_KEY], $content, $cacheInfo[ConfigInterface::CONFIG_CACHE_TTL_NAME]);
		}

		return $content;
	}

	/**
	 * Return the view type.
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->_type;
	}

	/**
	 * Return the absolute path to the current view template file.
	 *
	 * @return string
	 */
	public function getPath()
	{
		return APP_PATH
	           . Agl::APP_TEMPLATE_DIR
	           . static::APP_HTTP_DIR
	           . DS
	           . $this->_file;
	}
}
