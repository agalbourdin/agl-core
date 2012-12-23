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
            $this->_aglError($pMessage);
        }
    }

    public static function errorHandler($pErrno, $pErrstr, $pErrfile, $pErrline)
    {
        throw new self("Error '$pErrstr' in '$pErrfile' on line $pErrline");
    }

    /**
     * Display a generic error message.
     */
    protected function _aglError($pMessage)
    {
        $logId = \Agl\Core\Debug\Debug::log($pMessage);

        if (\Agl::isInitialized()) {
            $file = \Agl::app()->getConfig('@layout/errors/general');
            if ($file) {
                $path = \Agl::app()->getPath()
                        . \Agl\Core\Mvc\View\ViewInterface::APP_HTTP_TEMPLATE_DIR
                        . DS
                        . \Agl::app()->getConfig('@app/global/theme')
                        . DS
                        . $file;
                if (is_readable($path)) {
                    require($path);
                    exit;
                }
            }
        }

        echo 'An error occured. Logged: ' . $logId;
        exit;
    }
}

}

namespace {

set_error_handler(array('\Agl\Exception', 'errorHandler'));

}
