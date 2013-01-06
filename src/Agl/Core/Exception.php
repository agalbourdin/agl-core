<?php
namespace Agl\Core {

use \Agl\Core\Debug\Debug,
    \Agl\Core\Request\Request;

/**
 * Add a global application exception handler.
 *
 * @category Agl_Core
 * @package Agl_Core
 * @version 0.1.0
 */

class Exception
{
    /**
     * Handle exceptions and display error messages based on the app
     * configuration.
     *
     * @var string $pMessage
     * @var string $pLogId
     */
    private static function render($pMessage, $pLogId)
    {
        Request::setHttpHeader(Request::HEADER_500);

        while (ob_get_level()) {
            ob_end_clean();
        }

        if (Agl::isInitialized() and Agl::app()->isDebugMode()) {
            echo $pMessage;
            exit;
        } else {
            self::aglError($pMessage, $pLogId);
        }
    }

    /**
     * Display a generic error message.
     */
    private static function aglError($pMessage, $pLogId)
    {
        if (Agl::isInitialized()) {
            $file = Agl::app()->getConfig('@layout/errors/static/file');
            if ($file) {
                $path = Agl::app()->getPath() . $file;
                if (is_readable($path)) {
                    require($path);
                    exit;
                }
            }
        }

        echo '<pre><strong>An error occured</strong> Logged: ' . $pLogId . '</pre>';
        exit;
    }

    /**
     * Handle PHP errors.
     *
     * @param int $pErrno
     * @param string $pErrstr
     * @param string $pErrfile
     * @param int $pErrline
     */
    public static function errorHandler($pErrno, $pErrstr, $pErrfile, $pErrline)
    {
        $logId = Debug::log("Error '$pErrstr' in '$pErrfile' on line $pErrline");

        $message = '<pre><strong>AGL Error</strong>
<strong>Message </strong>' . $pErrstr . '
<strong>File</strong> ' . $pErrfile . '
<strong>Line</strong> ' . $pErrline . '
</pre>';

        self::render($message, $logId);
    }

    /**
     * Handle exceptions.
     *
     * @param Exception $pException
     */
    public static function exceptionHandler(\Exception $pException)
    {
        $logId = Debug::log("Exception '" . $pException->getMessage() . "' in '" . $pException->getFile() . "' on line " . $pException->getLine());

        $message = '<pre><strong>AGL Exception</strong>
<strong>Message </strong>' . $pException->getMessage() . '
<strong>File</strong> ' . $pException->getFile() . '
<strong>Line</strong> ' . $pException->getLine() . '

<strong>Trace</strong>
' . $pException->getTraceAsString() . '
</pre>';

        self::render($message, $logId);
    }

    /**
     * Shutdown method to handle fatal errors.
     */
    public static function shutdownHandler()
    {
        $error = error_get_last();
        if ($error) {
            $logId = Debug::log("Error '" . $error['message'] . "' in '" . $error['file'] . "' on line " . $error['line']);

                    $message = '<pre><strong>AGL Error</strong>
<strong>Message </strong>' . $error['message'] . '
<strong>File</strong> ' . $error['file'] . '
<strong>Line</strong> ' . $error['line'] . '
</pre>';

            self::render($message, $logId);
        }
    }
}

}

namespace {

set_error_handler(array('\Agl\Core\Exception', 'errorHandler'));
set_exception_handler(array('\Agl\Core\Exception', 'exceptionHandler'));
register_shutdown_function(array('\Agl\Core\Exception', 'shutdownHandler'));

}
