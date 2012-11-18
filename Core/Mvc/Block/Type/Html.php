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
     * Magic method - redirect the calls to the main view.
     *
     * @param string $pMethod Called method
     * @param array $pArgs Arguments
     * @return mixed
     */
    public function __call($pMethod, array $pArgs)
    {
        if (method_exists($this->getView(), $pMethod)) {
            return call_user_func_array(array($this->getView(), $pMethod), $pArgs);
        }

        throw new \Agl\Exception("Method '$pMethod' doesn't exists");
    }
}
