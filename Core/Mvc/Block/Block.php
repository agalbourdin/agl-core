<?php
namespace Agl\Core\Mvc\Block;

/**
 * Factory - implement the block class corresponding to the parent view's type.
 *
 * @category Agl_Core
 * @package Agl_Core_Mvc_View
 * @version 0.1.0
 */

$view = \Agl\Core\Registry\Registry::get('view');

switch($view->getType()) {
    case \Agl\Core\Mvc\View\ViewInterface::TYPE_HTML:
        class Block extends \Agl\Core\Mvc\Block\Type\Html { }
        break;
    default:
        throw new \Exception("View type '" . $view->getType() . "' is not valid");
}
