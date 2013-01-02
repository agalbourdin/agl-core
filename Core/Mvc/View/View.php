<?php
namespace Agl\Core\Mvc\View;

/**
 * Factory - implement the view class corresponding configured view's type.
 *
 * @category Agl_Core
 * @package Agl_Core_Mvc_View
 * @version 0.1.0
 */

$module = \Agl::getRequest()->getModule();
$view   = \Agl::getRequest()->getView();

$template = \Agl::app()->getConfig('@layout/modules/' . $module . '/' . $view . '/template');
if ($template === NULL) {
	$template = \Agl::app()->getConfig('@layout/template');
}

if (! is_array($template) or ! isset($template['type'])) {
	throw new \Exception("A template is required to render the view");
}

switch($template['type']) {
    case \Agl\Core\Mvc\View\ViewInterface::TYPE_HTML:
        class View extends \Agl\Core\Mvc\View\Type\Html { }
        \Agl\Core\Request\Request::setHeader(\Agl\Core\Request\Request::HEADER_HTML);
        break;
    case \Agl\Core\Mvc\View\ViewInterface::TYPE_JSON:
        class View extends \Agl\Core\Mvc\View\Type\Json { }
        \Agl\Core\Request\Request::setHeader(\Agl\Core\Request\Request::HEADER_JSON);
        break;
    default:
        throw new \Exception("View type '" . $template['type'] . "' is not valid");
}
