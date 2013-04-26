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
     * Specific PHP errors that should be catched by script and not stop
     * execution.
     *
     * @var array
     */
    private static $_ignoreErrors = array(
        '/^POST Content-Length of [0-9]+ bytes exceeds the limit of [0-9]+ bytes$/'
    );

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
                $path = APP_PATH . $file;
                if (is_readable($path)) {
                    require($path);
                    exit;
                }
            }
        }

        echo '<pre><strong>An error occured</strong></pre>';
        if ($pLogId) {
            echo '<pre>Error log ID: ' . $pLogId . '</pre>';
        }
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
        foreach (self::$_ignoreErrors as $pattern) {
            if (preg_match($pattern, $pErrstr)) {
                return;
            }
        }

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
        $logId = Debug::log("Exception '" . $pException->getMessage() . "' in '" . $pException->getFile() . "' on line " . $pException->getLine() . "\n" . $pException->getTraceAsString());

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
            self::errorHandler($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }
}

}

namespace {

set_error_handler(array('\Agl\Core\Exception', 'errorHandler'));
set_exception_handler(array('\Agl\Core\Exception', 'exceptionHandler'));
register_shutdown_function(array('\Agl\Core\Exception', 'shutdownHandler'));

}
