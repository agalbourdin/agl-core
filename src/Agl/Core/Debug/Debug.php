<?php
namespace Agl\Core\Debug;

use \Agl\Core\Agl,
    \Agl\Core\Data\File as FileData,
    \Agl\Core\Mvc\View\View,
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
    const LOG_DIR  = 'log/';
    const LOG_FILE = 'debug_%s.log';

    /**
     * HTML code to display template/blocks path information.
     */
    const DISPLAY_PATH_START = '<div style="border: 1px dotted Darkred; padding: 5px;">';
    const DISPLAY_PATH_END   = '</div>';
    const DISPLAY_PATH       = '<span style="background-color: IndianRed; padding: 0 5px;">%s</span>';

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
    public static function canDisplayHtmlDebug()
    {
        if (self::$_isHtmlView === NULL) {
            $view = Registry::get('view');
            if ($view instanceof View and $view->getType() == ViewInterface::TYPE_HTML) {
                self::$_isHtmlView = true;
            } else {
                self::$_isHtmlView = false;
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
     * @param bool $pForce Force log event if it is disabled
     * @return null|string
     */
    public static function log($pMessage, $pForce = false)
    {
        if (! $pForce and (! Agl::isInitialized() or ! Agl::app()->isLogEnabled())) {
            return NULL;
        }

        if (is_array($pMessage) or is_object($pMessage)) {
            $message = json_encode($pMessage);
        } else {
            $message = $pMessage;
        }

        $dir = APP_PATH . Agl::APP_VAR_DIR . self::LOG_DIR;
        if ((! is_dir($dir) and ! mkdir($dir, 0777)) or ! is_writable($dir)) {
            throw new Exception("log() error: directory creation");
        }

        $file = $dir . sprintf(self::LOG_FILE, date('Y-m-d'));

        $logId   = uniqid();
        $message = '[agl_' . $logId . '] ' . "\n" . date('Y-m-d H:i:s') . "\n" . APP_PATH . "\n" . $message . "\n\n";
        $logged  = FileData::write($file, $message, true);

        if (! $logged) {
            throw new Exception("log() error: write");
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
            $debugInfos['db']['db_engine'] = Agl::app()->getConfig('@app/db/engine');
            $debugInfos['db']['queries']   = Agl::app()->getDb()->countQueries();
        }

        $debugInfos['apc']     = (ini_get('apc.enabled')) ? true : false;
        $debugInfos['xdebug']  = $xDebugEnabled;
        $debugInfos['more_modules'] = Agl::getLoadedModules();
        $debugInfos['request'] = Agl::getRequest();

        return $debugInfos;
    }
}
