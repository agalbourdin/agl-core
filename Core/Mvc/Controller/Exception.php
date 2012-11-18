<?php
namespace Agl\Core\Mvc\Controller;

/**
 * Handle route Exceptions.
 *
 * @category Agl_Core
 * @package Agl_Core_Mvc_Controller
 * @version 0.1.0
 */

class Exception
	extends \Agl\Exception
{
	/**
	 * Is configured, include the 404 error page. If not, display a generic
	 * error message.
	 */
	protected function _aglError()
	{
		$file = \Agl::app()->getConfig('@layout/errors/404');
		if (! $file) {
			parent::_aglError();
		}

		$path = \Agl::app()->getPath()
		        . \Agl\Core\Mvc\View\ViewInterface::APP_HTTP_TEMPLATE_DIR
		        . DS
                . \Agl::app()->getConfig('@app/global/theme')
		        . DS
		        . $file;

		header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
		require($path);
		exit;
	}
}
