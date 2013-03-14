<?php
namespace Agl\Core\Url;

use \Agl\Core\Agl,
    \Agl\Core\Mvc\View\ViewInterface,
    \Exception;

/**
 * The AGL URL generation class.
 *
 * @category Agl_Core
 * @package Agl_Core_Url
 * @version 0.1.0
 */

class Url
{
    private static $_request = NULL;

    public static function setRequest($pRequest = NULL)
    {
        if ($pRequest === NULL) {
            self::$_request = Agl::getRequest();
        } else {
            self::$_request = $pRequest;
        }

        return self::$_request;
    }

    /**
     * Return a formated URL with module, view, action and parameters.
     *
     * @param string $pUrl URL to get (module/view)
     * @param array $pParams Parameters to include into the request
     * @param bool $pRelative Create a relative URL
     * @return string
     */
    public static function get($pUrl, array $pParams = array(), $pRelative = true)
    {
        if (strpos($pUrl, '*/') !== false) {
            if (self::$_request === NULL) {
                self::setRequest();
            }

            $pUrl = str_replace('*/*/', self::$_request->getModule() . DS . self::$_request->getView(), $pUrl);
            $pUrl = str_replace('*/', self::$_request->getModule() . DS, $pUrl);
        }

        if (Agl::isModuleLoaded(Agl::AGL_MORE_POOL . '/locale/locale')) {
            return Agl::getSingleton(Agl::AGL_MORE_POOL . '/locale')->getUrl($pUrl, $pParams, $pRelative);
        }

        if (! $pUrl) {
            if ($pRelative) {
                return ROOT;
            }
            return self::getHost(ROOT);
        }

        if (strpos($pUrl, Agl::APP_PUBLIC_DIR) === false) {
            if (! empty($pParams)) {
                $params = array();
                foreach ($pParams as $key => $value) {
                    $params[] = $key . DS . $value;
                }

                $url = $pUrl . DS . implode(DS, $params) . DS;
                if ($pRelative) {
                    return ROOT . $url;
                }
                return self::getHost(ROOT . $url);
            }

            $url = $pUrl . DS;
            if ($pRelative) {
                return ROOT . $url;
            }
            return self::getHost(ROOT . $url);
        }

        if ($pRelative) {
            return ROOT . $pUrl;
        }
        return self::getHost(ROOT . $pUrl);
    }

    /**
     * Return the current URL with optional additional params.
     *
     * @param array $pParams Parameters to add to the request (additional)
     * @return string
     */
    public static function getCurrent(array $pNewParams = array(), $pRelative = true)
    {
        if (self::$_request === NULL) {
            self::setRequest();
        }

        $module = self::$_request->getModule();
        $view   = self::$_request->getView();
        $params = self::$_request->getParams();

        $params = array_merge($params, $pNewParams);

        return self::get($module . DS . $view, $params, $pRelative);
    }

    /**
     * Return the base URL of the application.
     *
     * @param bool $pRelative Create a relative URL
     * @return string
     */
    public static function getBase($pRelative = true)
    {
        return self::get('', array(), $pRelative);
    }

    /**
     * Get the skin base URL.
     *
     * @param string $pUrl Relative URL inside the skin directory
     * @param bool $pRelative Create a relative URL
     * @return string
     */
    public static function getSkin($pUrl, $pRelative = true, $pDir = NULL)
    {
        if ($pDir === NULL) {
            $pDir = Agl::app()->getConfig('@app/global/theme');
        }

        $url = Agl::APP_PUBLIC_DIR
               . DS
               . ViewInterface::APP_HTTP_SKIN_DIR
               . DS
               . $pDir
               . DS
               . $pUrl;

        if ($pRelative) {
            return ROOT . $url;
        }

        return self::getHost(ROOT . $url);
    }

    /**
     * Get the public base URL.
     *
     * @param string $pUrl Relative URL inside the public directory
     * @param bool $pRelative Create a relative URL
     * @return string
     */
    public static function getPublic($pUrl, $pRelative = true)
    {
        $url = Agl::APP_PUBLIC_DIR
               . DS
               . $pUrl;

        if ($pRelative) {
            return ROOT . $url;
        }

        return self::getHost(ROOT . $url);
    }

    /**
     * Get the base URL with host name and protocol.
     *
     * @return string
     */
    public static function getHost($pPath = '', $pHost = '')
    {
        if (! $pHost) {
            $pHost = $_SERVER['HTTP_HOST'];
        }

        return self::getProtocol() . $pHost . $pPath;
    }

    /**
     * Get the current protocol string to use in URL.
     *
     * @return string
     */
    public static function getProtocol()
    {
        switch ($_SERVER['SERVER_PROTOCOL']) {
            case 'HTTP/1.1':
                return 'http://';
                break;
            default:
                throw new Exception("Protocol '" . $_SERVER['SERVER_PROTOCOL'] . "' is not supported");
        }
    }
}
