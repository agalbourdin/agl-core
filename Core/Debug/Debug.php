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
     * Time of the instance creation.
     *
     * @var string
     */
    private $_startTime = NULL;

    /**
     * Param to use in GET/POST request to launch an xDebug profiling
     */
    const XDEBUG_PROFILE_PARAM = 'XDEBUG_PROFILE';

    /**
     * Class constructor.
     * Initialize the
     */
    public function __construct()
    {
        $this->_startTime = microtime();
        ini_set('display_errors', 'On');
        ini_set('log_errors', 'On');
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
     * Return current time.
     *
     * @return string Current time
     */
    public static function getTime()
    {
        return microtime();
    }

    /**
     * Time index since the script execution start.
     *
     * @return float
     */
    public function getStartDiff()
    {
        if (self::isXdebugEnabled()) {
            return xdebug_time_index();
        }

        return self::getTime() - $this->_startTime;
    }

    /**
     * Get the peak memory usage of the script.
     *
     * @return float
     */
    public static function getMemoryPeakUsage()
    {
        if (self::isXdebugEnabled()) {
            $memory = xdebug_peak_memory_usage();
        } else {
            $memory = memory_get_peak_usage();
        }

        return $memory / 1000;
    }

    /**
     * Get the memory usage of the script.
     *
     * @return float
     */
    public static function getMemoryUsage()
    {
        if (self::isXdebugEnabled()) {
            $memory = xdebug_memory_usage();
        } else {
            $memory = memory_get_usage();
        }

        return $memory / 1000;
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
    public function getDebugInfos()
    {
        $debugInfos = array();

        $debugInfos['app']['path']  = \Agl::app()->getPath();
        $debugInfos['app']['cache'] = \Agl::app()->isCacheEnabled();
        $debugInfos['time']         = $this->getStartDiff();
        $debugInfos['memory']       = self::getMemoryPeakUsage();
        if (\Agl::app()->getDb() !== NULL) {
            $debugInfos['db']['db_engine'] = \Agl::app()->getConfig('@app/db/engine');
            switch($debugInfos['db']['db_engine']) {
                case \Agl\Core\Db\Connection\ConnectionInterface::MYSQL:
                    $result = \Agl::app()->getDb()->getConnection()->query("show variables like 'query_cache_size';")->fetch(\PDO::FETCH_ASSOC);
                    $debugInfos['db']['mysql_cache'] = ($result['Value']) ? true : false;
                    break;
            }
            $debugInfos['db']['queries']   = \Agl::app()->getDb()->count();
        }
        $debugInfos['apc']     = (ini_get('apc.enabled')) ? true : false;
        $debugInfos['xdebug']  = self::isXdebugEnabled();
        $debugInfos['modules'] = \Agl::getLoadedModules();
        $debugInfos['request'] = \Agl::getRequest();

        /*if ($debugInfos['xdebug']) {
            $profilerName = xdebug_get_profiler_filename();
            if ($profilerName) {
                $debugInfos['profiling']['name'] = $profilerName;
                if (isset($_GET[self::XDEBUG_PROFILE_PARAM])) {
                    unset($_GET[self::XDEBUG_PROFILE_PARAM]);
                    $debugInfos['profiling']['unprofile_url'] = '?' . http_build_query($_GET);
                }
            }
            $getParams = array_merge($_GET, array(
                    self::XDEBUG_PROFILE_PARAM => 1
                ));
            $debugInfos['profiling']['profile_url'] = '?' . http_build_query($getParams);
        }*/

        return $debugInfos;
    }
}
