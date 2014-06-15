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
     * @param string|array $pParams Parameters to include into the request
     * @param bool $pRelative Create a relative URL
     * @return string
     */
    public static function get($pUrl, $pParams = array(), $pRelative = true)
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
            if (is_array($pParams) and ! empty($pParams)) {
                $params = array();
                foreach ($pParams as $key => $value) {
                    $params[] = $key . DS . $value;
                }

                $url = $pUrl . DS . implode(DS, $params) . DS;
            } else if (is_string($pParams) and $pParams) {
                $url = $pUrl . DS . $pParams . DS;
            } else {
                $url = $pUrl . DS;
            }

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
     * @param bool $pRelative Return a relative URL or a full HTTP URL.
     * @param array $pNewParams Parameters to add to the request (additional)
     * @return string
     */
    public static function getCurrent($pRelative = true, array $pNewParams = array())
    {
        if (self::$_request === NULL) {
            self::setRequest();
        }

        $request = self::$_request;

        $module = $request->getModule();
        $view   = $request->getView();

        $params = str_replace($module . DS . $view, '', $request->getReq());
        if (! empty($pNewParams)) {
            $newParams = array();
            foreach ($pNewParams as $key => $value) {
                $newParams[] = $key . DS . $value;
            }

            $params .= DS . implode(DS, $newParams);
        }

        $params = trim($params, DS);

        if ($module == $request::DEFAULT_MODULE and $view == $request::DEFAULT_VIEW and ! $params) {
            return self::get('', $params, $pRelative);
        }

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
    public static function getSkin($pUrl, $pRelative = true)
    {
        $url = Agl::APP_PUBLIC_DIR
               . ViewInterface::APP_HTTP_SKIN_DIR
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
        if ((isset($_SERVER['HTTPS'])
            and (strtolower($_SERVER['HTTPS']) === 'on' or $_SERVER['HTTPS'] === '1'))
            or (isset($_SERVER['SERVER_PORT']) and $_SERVER['SERVER_PORT'] === '443')) {
            return 'https://';
        }

        return 'http://';
    }
}
