<?php
namespace Agl\Core\Mvc\Block;

use \Agl\Core\Mvc\Block\Type\Html as HtmlBlock,
	\Agl\Core\Mvc\View\ViewInterface,
	\Agl\Core\Registry\Registry,
	\Exception;

/**
 * Factory - implement the block class corresponding to the parent view's type.
 *
 * @category Agl_Core
 * @package Agl_Core_Mvc_View
 * @version 0.1.0
 */

$view = Registry::get('view');

switch($view->getType()) {
    case ViewInterface::TYPE_HTML:
        class Block extends HtmlBlock { }
        break;
    default:
        throw new Exception("View type '" . $view->getType() . "' is not valid");
}
