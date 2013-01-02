<?php
namespace Agl\Core\Debug;

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
     * @return bool
     */
    public static function log($pMessage)
    {
        if (is_array($pMessage) or is_object($pMessage)) {
            $message = json_encode($pMessage);
        } else {
            $message = $pMessage;
        }

        $logId = uniqid();
        syslog(LOG_DEBUG, '[agl_' . $logId . '] ' . $message);

        return $logId;
    }

    /**
     * Return some debug informations about the running script.
     *
     * @return array
     */
    public static function getDebugInfos()
    {
        $xDebugEnabled = self::isXdebugEnabled();
        $debugInfos    = array();

        $debugInfos['app']['path']  = \Agl::app()->getPath();
        $debugInfos['app']['cache'] = \Agl::app()->isCacheEnabled();

        if ($xDebugEnabled) {
            $debugInfos['time']   = xdebug_time_index();
            $debugInfos['memory'] = xdebug_peak_memory_usage();
        }

        if (\Agl::app()->getDb() !== NULL) {
            $debugInfos['db']['db_engine'] = \Agl::app()->getConfig('@app/db/engine');
            $debugInfos['db']['queries']   = \Agl::app()->getDb()->countQueries();
        }

        $debugInfos['apc']     = (ini_get('apc.enabled')) ? true : false;
        $debugInfos['xdebug']  = $xDebugEnabled;
        $debugInfos['more_modules'] = \Agl::getLoadedModules();
        $debugInfos['request'] = \Agl::getRequest();

        return $debugInfos;
    }
}
