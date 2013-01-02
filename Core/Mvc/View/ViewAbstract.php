<?php
namespace Agl\Core\Mvc\View;

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
	protected $_viewPath = NULL;

	/**
	 * View type (html...).
	 *
	 * @var string
	 */
	protected $_type = NULL;

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
			   . \Agl::APP_PUBLIC_DIR
			   . DS
			   . static::APP_HTTP_SKIN_DIR
			   . DS
			   . \Agl::app()->getConfig('@app/global/theme')
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
        $this->_file = $pFile . static::FILE_EXT;

        return $this;
	}

	/**
	 * Start the View buffer.
	 *
	 * @return View
	 */
	public function startBuffer()
	{
		ob_start(array($this, 'getBuffer'));
		return $this;
	}

	/**
	 * Prepare and display the buffer.
	 *
	 * @param string $pBuffer
	 * @return string
	 */
	public function getBuffer($pBuffer)
	{
		\Agl::validateParams(array(
			'StrictString' => $pBuffer
        ));

        \Agl\Core\Observer\Observer::dispatch(\Agl\Core\Observer\Observer::EVENT_VIEW_RENDER_BUFFER_BEFORE, array(
			'view'   => $this,
			'buffer' => &$pBuffer
		));

		$pBuffer = $this->_prepareRender($pBuffer);

		return $pBuffer;
	}

	/**
	 * Include the template in the current page.
	 *
	 * @return View
	 */
	public function render()
	{
		$this->_viewPath = $this->getViewPath();

		if (! is_readable($this->_viewPath)) {
			\Agl\Core\Request\Request::setHttpHeader(\Agl\Core\Request\Request::HEADER_404);
			$template404 = \Agl::app()->getConfig('@layout/errors/404');
			if ($template404) {
				$this->setFile($template404);
				$this->_viewPath = $this->getViewPath();
			}

			if (! $template404 or ! is_readable($this->_viewPath)) {
				throw new \Exception("View file '" . $this->_viewPath . "' doesn't exists");
			}
		}

		$module   = \Agl::getRequest()->getModule();
		$view     = \Agl::getRequest()->getView();
		$template = \Agl::app()->getConfig('@layout/modules/' . $module . '/' . $view . '/template');

		if ($template === NULL) {
			$template = \Agl::app()->getConfig('@layout/template');
		}

		$this->_type = $template['type'];

		if (isset($template['file'])) {
			$template = \Agl::app()->getPath()
				        . static::APP_HTTP_TEMPLATE_DIR
				        . DS
	           			. \Agl::app()->getConfig('@app/global/theme')
				        . DS
				        . $template['file']
				        . static::FILE_EXT;
			require($template);
		} else {
			echo $this->getView();
		}

		ob_end_flush();
		return $this;
	}

	/**
	 * Require the view template file.
	 *
	 * @return View
	 */
	public function getView()
	{
		ob_start();
		require($this->_viewPath);
		return ob_get_clean();
	}

	/**
	 * Create a block and include it into the view.
	 *
	 * @param string $pBlock Block identifier
	 * @return Block
	 */
	public function getBlock($pBlock)
	{
		if (! preg_match('#^([a-z0-9]+)/([a-z0-9_-]+)$#', $pBlock, $blockPathInfos)
			or ! isset($blockPathInfos[1])
			or ! isset($blockPathInfos[2])) {
			throw new \Exception("Block identifier '$pBlock' is not correct");
		}

        $this->_blocks[] = array(
        	'group' => $blockPathInfos[1],
        	'block' => $blockPathInfos[2]
        );

        $blockConfig = \Agl::app()->getConfig('@layout/blocks/' . $blockPathInfos[1] . '/' . $blockPathInfos[2]);

		if (! \Agl\Core\Mvc\Block\BlockAbstract::checkAcl($blockPathInfos[1], $blockPathInfos[2])) {
			return '';
		}

		$cacheEnabled = \Agl\Core\Mvc\Block\BlockAbstract::isCacheEnabled($blockConfig);

		if ($cacheEnabled) {
			$apcEnabled = \Agl\Core\Cache\Apc\Apc::isApcEnabled();
			$cache      = \Agl\Core\Mvc\Block\BlockAbstract::getCacheInstance($blockConfig);

			if ($apcEnabled) {
				$content = \Agl\Core\Cache\Apc\Apc::get($cache[0]);
	            if ($content !== false) {
	                return $content;
	            }
			} else {
	            $content = $cache->getContent();
	            if ($content) {
	                return $content;
	            }
	        }
		}

		$blockPath = \Agl::app()->getPath()
					. \Agl::APP_PHP_DIR
                    . DS
                    . \Agl\Core\Mvc\Block\BlockInterface::APP_PHP_BLOCK_DIR
                    . DS
                    . ucfirst($blockPathInfos[1])
                    . DS
                    . ucfirst($blockPathInfos[2])
                    . \Agl::PHP_EXT;

        if (file_exists($blockPath)) {
            $className = ucfirst($blockPathInfos[1]) . ucfirst($blockPathInfos[2]) . \Agl\Core\Mvc\Block\BlockInterface::APP_BLOCK_SUFFIX;
            $blockModel = \Agl::getInstance($className);
        } else {
            $blockModel = new \Agl\Core\Mvc\Block\Block();
        }

        ob_start();

        $blockModel
			->setFile($blockPathInfos[1] . DS . $blockPathInfos[2] . static::FILE_EXT)
			->render();

		$content = ob_get_clean();

		if ($cacheEnabled) {
        	if ($apcEnabled) {
        		\Agl\Core\Cache\Apc\Apc::set($cache[0], $content, $cache[1]);
        	} else {
        		$cache
	                ->setContent($content)
	                ->save();
        	}
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
	public function getViewPath()
	{
		return \Agl::app()->getPath()
	           . static::APP_HTTP_TEMPLATE_DIR
	           . DS
               . \Agl::app()->getConfig('@app/global/theme')
	           . DS
	           . static::APP_HTTP_VIEW_DIR
	           . DS
	           . $this->_file;
	}
}
