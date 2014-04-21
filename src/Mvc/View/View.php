<?php
namespace Agl\Core\Mvc\View;

use \Agl\Core\Mvc\View\Type\Html as HtmlView,
    \Agl\Core\Mvc\View\Type\Json as JsonView,
    \Agl\Core\Mvc\View\ViewAbstract,
    \Agl\Core\Mvc\View\ViewInterface,
    \Agl\Core\Request\Request,
    \Exception;

/**
 * Factory - implement the view class corresponding configured view's type.
 *
 * @category Agl_Core
 * @package Agl_Core_Mvc_View
 * @version 0.1.0
 */

$template = ViewAbstract::getTemplateConfig();

if (! is_array($template) or ! isset($template['type'])) {
	throw new Exception("A template and a template type are required to render the view");
}

switch($template['type']) {
    case ViewInterface::TYPE_HTML:
        class View extends HtmlView { }
        Request::setHeader(Request::HEADER_HTML);
        break;
    case ViewInterface::TYPE_JSON:
        class View extends JsonView { }
        Request::setHeader(Request::HEADER_JSON);
        break;
    default:
        throw new Exception("View type '" . $template['type'] . "' is not valid");
}
