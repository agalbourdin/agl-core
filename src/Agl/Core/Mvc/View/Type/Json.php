<?php
namespace Agl\Core\Mvc\View\Type;

use \Agl\Core\Mvc\View\ViewAbstract,
	\Agl\Core\Mvc\View\ViewInterface;

/**
 * Default AGL JSON View class.
 *
 * @category Agl_Core
 * @package Agl_Core_Mvc_View_Type
 * @version 0.1.0
 */

class Json
	extends ViewAbstract
		implements ViewInterface
{
	/**
	 * JSON type: template file extension.
	 */
	const FILE_EXT = '.php';

	/**
	 * Content to JSON encode when rendering the page.
	 *
	 * @var array
	 */
	protected $_content = array();

	/**
	 * Add content to the page. Will be saved in $_content array and JSON
	 * encoded when rendering the page.
	 *
	 * @param string $pKey
	 * @param mixed $pContent
	 */
	public function addContent($pKey, $pContent)
	{
		$this->_content[$pKey] = $pContent;
		return $this;
	}

	/**
	 * Return a JSON encoded string of $_content.
	 *
	 * @return string
	 */
	public function toJson()
	{
		return json_encode($this->_content);
	}

	/**
	 * If there is no template generated output, we return a JSON encoded string
	 * of $_content.
	 *
	 * @param $pBuffer string
	 * @return string
	 */
	protected function _prepareRender($pBuffer)
	{
		if ($pBuffer === '') {
			$pBuffer = json_encode($this->_content);
		}

		return $pBuffer;
	}
}
