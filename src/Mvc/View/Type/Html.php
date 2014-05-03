<?php
namespace Agl\Core\Mvc\View\Type;

use \Agl\Core\Agl,
	\Agl\Core\Data\String as StringData,
	\Agl\Core\Mvc\View\ViewAbstract,
	\Agl\Core\Mvc\View\ViewInterface;

/**
 * Default AGL HTML View class.
 *
 * @category Agl_Core
 * @package Agl_Core_Mvc_View_Type
 * @version 0.1.0
 */

class Html
	extends ViewAbstract
		implements ViewInterface
{
	/**
	 * CSS Marker identifier.
	 */
	const CSS_MARKER = 'HTML_CSS';

	/**
	 * JavaScript Marker identifier.
	 */
	const JS_MARKER        = 'HTML_JS::';
	const JS_MARKER_FOOTER = 'footer';
	const JS_MARKER_HEADER = 'header';

	/**
	 * HTML Title Marker identifier.
	 */
	const TITLE_MARKER = 'HTML_TITLE';

	/**
	 * HTML Meta Marker identifier.
	 */
	const META_MARKER = 'HTML_META';

	/**
	 * Application's CSS directory name (in the skin directory)
	 */
	const APP_HTTP_CSS_DIR = 'css';

	/**
	 * Application's JS directory name (in the skin directory)
	 */
	const APP_HTTP_JS_DIR = 'js';

	/**
	 * HTML type: template file extension.
	 */
	const FILE_EXT = '.phtml';

	/**
	 * The list of the CSS files to include into the HTML page.
	 *
	 * @var array
	 */
	protected $_css = array();

	/**
	 * The list of the JS files to include into the HTML page.
	 *
	 * @var array
	 */
	protected $_js = array();

	/**
	 * The HTML page title.
	 *
	 * @var string
	 */
	protected $_title = '';

	/**
	 * The HTML Meta content.
	 *
	 * @var array
	 */
	protected $_meta = array();

	/**
	 * The HTML page title prefix.
	 *
	 * @var string
	 */
	protected $_titlePrefix = '';

	/**
	 * The HTML page title separator (between prefix and title).
	 *
	 * @var string
	 */
	protected $_titleSeparator = '';

	/**
	 * Return the HTML CSS Marker tag.
	 *
	 * @return string
	 */
	public static function getCssMarker()
	{
		return '/' . static::VIEW_MARKER . self::CSS_MARKER . '/';
	}

	/**
	 * Return the HTML JS Marker tag.
	 *
	 * @param bool $pFooter Generate header or footer marker
	 * @return string
	 */
	public static function getJsMarker($pFooter = false)
	{
		return ($pFooter) ?
			'/' . static::VIEW_MARKER . self::JS_MARKER . self::JS_MARKER_FOOTER . '/' :
			'/' . static::VIEW_MARKER . self::JS_MARKER . self::JS_MARKER_HEADER . '/';
	}

	/**
	 * Return the HTML Title Marker tag.
	 *
	 * @return string
	 */
	public static function getTitleMarker()
	{
		return '/' . static::VIEW_MARKER . self::TITLE_MARKER . '/';
	}

	/**
	 * Return the HTML Meta Marker tag.
	 *
	 * @return string
	 */
	public static function getMetaMarker()
	{
		return '/' . static::VIEW_MARKER . self::META_MARKER . '/';
	}

	/**
	 * Replace the AGL CSS Marker in the buffer.
	 *
	 * @param $pBuffer string
	 * @return string
	 */
	private function _processHtmlCssMarker(&$pBuffer)
	{
		$this->_css = array_unique($this->_css);

		$skinPath = $this->_getSkinRelativePath();
		$cssTags = array();
		foreach ($this->_css as $css) {
			if (! filter_var($css, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED) and strpos($css, '//') === false) {
				$filePath = $skinPath
							. self::APP_HTTP_CSS_DIR
							. DS
							. $css;
			} else {
				$filePath = $css;
			}

			$cssTags[] = '<link href="' . $filePath . '" rel="stylesheet" type="text/css">';
		}

		$pBuffer = str_replace(self::getCssMarker(), implode("\n", $cssTags) . "\n", $pBuffer);
		return $this;
	}

	/**
	 * Replace the AGL JS Marker in the buffer.
	 *
	 * @param $pBuffer string
	 * @param string $pId JS belong to header or footer ID
	 * @return string
	 */
	private function _processHtmlJsMarker(&$pBuffer, $pId)
	{
		$this->_js[$pId] = array_unique($this->_js[$pId]);

		$skinPath = $this->_getSkinRelativePath();
		$jsTags = array();
		foreach ($this->_js[$pId] as $js) {
			if (! filter_var($js, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED) and strpos($js, '//') === false) {
				$filePath = $skinPath
					. self::APP_HTTP_JS_DIR
					. DS
					. $js;
			} else {
				$filePath = $js;
			}
			$jsTags[] = '<script src="' . $filePath . '" type="text/javascript"></script>';
		}

		$footer  = ($pId === self::JS_MARKER_FOOTER) ? true : false;
		$pBuffer = str_replace(self::getJsMarker($footer), implode("\n", $jsTags) . "\n", $pBuffer);
		return $this;
	}

	/**
	 * Replace the AGL Title Marker in the buffer.
	 *
	 * @param $pBuffer string
	 * @return string
	 */
	private function _processHtmlTitleMarker(&$pBuffer)
	{
		$title = ($this->_titlePrefix and $this->_title) ? $this->_titlePrefix . $this->_titleSeparator . $this->_title : $this->_titlePrefix . $this->_title;
		$pBuffer = str_replace(self::getTitleMarker(), $title, $pBuffer);

		if (! isset($this->_meta['itemprop.name'])) {
			$this->addMeta('itemprop.name', array(
				'itemprop' => 'name',
				'content'  => $title
			));
		}

		if (! isset($this->_meta['og:title'])) {
			$this->addMeta('og:title', array(
				'property' => 'og:title',
				'content'  => $title
			));
		}

		return $this;
	}

	/**
	 * Replace the AGL Meta Marker in the buffer.
	 *
	 * @param $pBuffer string
	 * @return string
	 */
	private function _processHtmlMetaMarker(&$pBuffer)
	{
		$pBuffer = str_replace(self::getMetaMarker(), implode("\n", $this->_meta) . "\n", $pBuffer);
		return $this;
	}

	/**
	 * Replace the AGL Markers in the buffer before rendering it.
	 *
	 * @param $pBuffer string
	 * @return string
	 */
	protected function _prepareRender($pBuffer)
	{
		$hasMarkers = preg_match_all('#(/' . static::VIEW_MARKER . '([A-Z0-9_]+)(::([a-z]+))?/)#', $pBuffer, $matches);
		if ($hasMarkers !== false) {
			foreach ($matches[2] as $i => $marker) {
				$method = '_process' . StringData::toCamelCase($marker) . 'Marker';
				if (method_exists($this, $method)) {
					$this->$method($pBuffer, $matches[4][$i]);
				}
			}
		}

		return $pBuffer;
	}

	/**
	 * Add CSS to page.
	 *
	 * @param string $pFile CSS file, relative to public/skin/css/
	 * @return Html
	 */
	public function addCss($pFile)
	{
		$this->_css[] = $pFile;
		return $this;
	}

	/**
	 * Display the CSS Marker in the page. It will be replaced by CSS tags
	 * when the buffer will be rendered.
	 *
	 * @return string
	 */
	public function getCss()
	{
		return self::getCssMarker();
	}

	/**
	 * Add JS to page.
	 *
	 * @param string $pFile JS file, relative to public/skin/js/
	 * @param bool $pFooter Add JS file in header or footer section
	 * @return Html
	 */
	public function addJs($pFile, $pFooter = false)
	{
		if (! $pFooter) {
			$this->_js[self::JS_MARKER_HEADER][] = $pFile;
		} else {
			$this->_js[self::JS_MARKER_FOOTER][] = $pFile;
		}

		return $this;
	}

	/**
	 * Display the JS Marker in the page. It will be replaced by JS tags
	 * when the buffer will be rendered.
	 *
	 * @param bool $pFooter Generate header or footer marker
	 * @return string
	 */
	public function getJs($pFooter = false)
	{
		return self::getJsMarker($pFooter);
	}

	/**
	 * Display the Title Marker in the page. It will be replaced by the page
	 * title when the buffer will be rendered.
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return self::getTitleMarker();
	}

	/**
	 * Display the Meta Marker in the page. It will be replaced
	 * when the buffer will be rendered.
	 *
	 * @return string
	 */
	public function getMeta()
	{
		return self::getMetaMarker();
	}

	/**
	 * Set the page title.
	 *
	 * @param string $pTitle
	 * @return View
	 */
	public function setTitle($pTitle)
	{
		$this->_title = $pTitle;
		return $this;
	}

	/**
	 * Set the page title prefix.
	 *
	 * @param string $pTitle
	 * @return View
	 */
	public function setTitlePrefix($pTitle)
	{
		$this->_titlePrefix = $pTitle;
		return $this;
	}

	/**
	 * Set the page title separator (between prefix and title).
	 *
	 * @param string $pSeparator
	 * @return View
	 */
	public function setTitleSeparator($pSeparator)
	{
		$this->_titleSeparator = $pSeparator;
		return $this;
	}

	/**
	 * Set the META Description tags.
	 *
	 * @param string $pDescription
	 * @return View
	 */
	public function setDescription($pDescription)
	{
		$this->addMeta('description', array(
			'name'    => 'description',
			'content' => $pDescription
		));

		$this->addMeta('itemprop.description', array(
			'itemprop' => 'description',
			'content'  => $pDescription
		));

		$this->addMeta('og:description', array(
			'property' => 'og:description',
			'content'  => $pDescription
		));

		return $this;
	}

	/**
	 * Set the META Keywords tags.
	 *
	 * @param string $pKeywords
	 * @return View
	 */
	public function setKeywords($pKeywords)
	{
		$this->addMeta('keywords', array(
			'name'    => 'keywords',
			'content' => $pKeywords
		));

		return $this;
	}

	/**
	 * Add a META tag.
	 *
	 * @param string $pId META tag ID
	 * @param array $pAttributes META tag attributes and values
	 * @return View
	 */
	public function addMeta($pId, array $pAttributes)
	{
		$meta = array();
        foreach ($pAttributes as $attribute => $value) {
	        $meta[] = $attribute . '="' . $value . '"';
	    }

		$this->_meta[$pId] = '<meta ' . implode(' ', $meta) . '>';
		return $this;
	}

	/**
	 * Get CSS array.
	 *
	 * @return array
	 */
	public function cssToArray()
	{
		return $this->_css;
	}

	/**
	 * Get JS array.
	 *
	 * @return array
	 */
	public function jsToArray()
	{
		return $this->_js;
	}
}
