<?php
namespace Agl {

/**
 * Add a global application exception handler.
 *
 * @category Agl
 * @package Agl
 * @version 0.1.0
 */

class Exception
    extends \Exception
{
    /**
     * Handle exceptions and display error messages based on the app
     * configuration.
     *
     * @var null|string $pMessage
     * @var int $pCode
     * @var null|Exception $pPrevious
     */
    public function __construct($pMessage = null, $pCode = 0, \Exception $pPrevious = null)
    {
        if (\Agl::isInitialized() and \Agl::app()->isDebugMode()) {
            parent::__construct($pMessage, $pCode, $pPrevious);
        } else {
            \Agl::log($pMessage);
            $this->_aglError();
        }
    }

    public static function errorHandler($pErrno, $pErrstr, $pErrfile, $pErrline)
    {
        throw new self("Error '$pErrstr' in '$pErrfile' on line $pErrline");
    }

    /**
     * Display a generic error message.
     */
    protected function _aglError()
    {
        echo 'An error occured.';
        exit;
    }
}

}

namespace {

set_error_handler(array('\Agl\Exception', 'errorHandler'));

}
