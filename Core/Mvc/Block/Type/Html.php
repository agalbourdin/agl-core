<?php
namespace Agl\Core\Mvc\Block\Type;

/**
 * Default AGL HTML Block class.
 *
 * @category Agl_Core
 * @package Agl_Core_Mvc_Block_Type
 * @version 0.1.0
 */

class Html
	extends \Agl\Core\Mvc\Block\BlockAbstract
		implements \Agl\Core\Mvc\Block\BlockInterface
{
	/**
     * Redirect the calls to the parent view instance.
     *
     * @param string $pMethod Called method
     * @param array $pArgs Arguments
     * @return mixed
     */
    public function __call($pMethod, array $pArgs)
    {
        return call_user_func_array(array($this->getView(), $pMethod), $pArgs);
    }
}
