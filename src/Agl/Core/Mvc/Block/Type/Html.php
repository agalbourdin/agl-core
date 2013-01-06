<?php
namespace Agl\Core\Mvc\Block\Type;

use \Agl\Core\Mvc\Block\BlockAbstract,
    \Agl\Core\Mvc\Block\BlockInterface;

/**
 * Default AGL HTML Block class.
 *
 * @category Agl_Core
 * @package Agl_Core_Mvc_Block_Type
 * @version 0.1.0
 */

class Html
	extends BlockAbstract
		implements BlockInterface
{
	/**
     * Forwarded calls.
     */

    public function getCss()
    {
        return $this->getView()->getCss();
    }

    public function getJs()
    {
        return $this->getView()->getJs();
    }

    public function getTitle()
    {
        return $this->getView()->getTitle();
    }

    public function getMeta()
    {
        return $this->getView()->getMeta();
    }

    public function getBlock($pBlock)
    {
        return $this->getView()->getBlock($pBlock);
    }
}
