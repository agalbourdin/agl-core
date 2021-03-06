<?php
namespace Agl\Core\Debug;

use \Agl\Core\Agl,
    \Agl\Core\Data\File as FileData,
    \Agl\Core\Mvc\View\View,
    \Agl\Core\Mvc\View\ViewAbstract,
    \Agl\Core\Mvc\View\ViewInterface,
    \Agl\Core\Registry\Registry,
    \Exception;

/**
 * A set of debug tools, with xDebug support.
 *
 * @category Agl_Core
 * @package Agl_Core_Debug
 * @version 0.1.0
 */

class Debug
{
    /**
     * File to log messages.
     */
    const LOG_DIR  = 'log/%s/%s/';
    const LOG_FILE = 'debug_%s.log';

    /**
     * Is the current view of HTML type?
     *
     * @var null|bool
     */
    private static $_isHtmlView = NULL;

    /**
     * Check if the view is of HTML type.
     *
     * @return bool
     */
    public static function isHtmlView()
    {
        if (self::$_isHtmlView === NULL) {
            self::$_isHtmlView = false;

            $view = Registry::get('view');
            if ($view === NULL) {
                $template = ViewAbstract::getTemplateConfig();
                if (is_array($template)
                    and isset($template['type'])
                    and $template['type'] === ViewInterface::TYPE_HTML) {
                    self::$_isHtmlView = true;
                }
            } else if ($view instanceof View and $view->getType() == ViewInterface::TYPE_HTML) {
                self::$_isHtmlView = true;
            }
        }

        return self::$_isHtmlView;
    }

    /**
     * Check if xDebug is enabled.
     *
     * @return bool
     */
    public static function isXdebugEnabled()
    {
        return (function_exists('xdebug_is_enabled')) ? xdebug_is_enabled() : false;
    }

    /**
     * Log a message in the syslog.
     *
     * @param string $pMessage
     * @return null|string
     */
    public static function log($pMessage)
    {
        if (is_array($pMessage) or is_object($pMessage)) {
            $message = json_encode($pMessage);
        } else {
            $message = (string)$pMessage;
        }

        $logId = mt_rand();

        if (Agl::isInitialized()) {
            $message = '[agl_' . $logId . '] [' . date('Y-m-d H:i:s') . '] [' . APP_PATH . '] ' . $message . "\n";

            $dir = APP_PATH . Agl::APP_VAR_DIR . sprintf(self::LOG_DIR, date('Y'), date('m'));
            if (! is_writable(APP_PATH . Agl::APP_VAR_DIR) or (! is_dir($dir) and ! mkdir($dir, 0777, true)) or ! is_writable($dir)) {
                return 0;
            }

            $file   = $dir . sprintf(self::LOG_FILE, date('Y-m-d'));
            $logged = FileData::write($file, $message, true);

            if (! $logged) {
                return 0;
            }
        } else {
            return 0;
        }

        return $logId;
    }

    /**
     * Return some debug informations about the running script.
     *
     * @return array
     */
    public static function getInfos()
    {
        $xDebugEnabled = self::isXdebugEnabled();
        $debugInfos    = array();

        $debugInfos['app']['path']  = APP_PATH;
        $debugInfos['app']['cache'] = Agl::app()->isCacheEnabled();

        if ($xDebugEnabled) {
            $debugInfos['time']   = xdebug_time_index();
            $debugInfos['memory'] = xdebug_peak_memory_usage();
        }

        if (Agl::app()->getDb() !== NULL) {
            $debugInfos['db']['db_engine'] = Agl::app()->getConfig('main/db/engine');
            $debugInfos['db']['queries']   = Agl::app()->getDb()->countQueries();
        }

        $debugInfos['opcache']      = (ini_get('opcache.enable')) ? true : false;
        $debugInfos['apcu']         = (ini_get('apc.enabled')) ? true : false;
        $debugInfos['xdebug']       = $xDebugEnabled;
        $debugInfos['more_modules'] = Agl::getLoadedModules();
        $debugInfos['request']      = Agl::getRequest();

        return $debugInfos;
    }
}
