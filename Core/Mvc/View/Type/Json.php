<?php
namespace Agl\Core\Mvc\View\Type;

/**
 * Default AGL JSON View class.
 *
 * @category Agl_Core
 * @package Agl_Core_Mvc_View_Type
 * @version 0.1.0
 */

class Json
	extends \Agl\Core\Mvc\View\ViewAbstract
		implements \Agl\Core\Mvc\View\ViewInterface
{
	/**
	 * JSON type: template file extension.
	 */
	const FILE_EXT = '.php';
}
