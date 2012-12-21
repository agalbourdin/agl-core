<?php
namespace Agl\Core\Url;

/**
 * The AGL URL generation class.
 *
 * @category Agl_Core
 * @package Agl_Core_Url
 * @version 0.1.0
 */

class Url
{
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
            $request = \Agl::getRequest();
            $pUrl    = str_replace('*/*/', $request->getModule() . DS . $request->getView(), $pUrl);
            $pUrl    = str_replace('*/', $request->getModule(), $pUrl);
        }

        if (\Agl::isModuleLoaded(\Agl::AGL_MORE_POOL . '/locale/locale') and $pUrl) {
            return \Agl::getSingleton(\Agl::AGL_MORE_POOL . '/locale/locale')->getUrl($pUrl, $pParams, $pRelative);
        }

        if (! $pUrl) {
            if ($pRelative) {
                return ROOT;
            }
            return self::getHost();
        }

        if (strpos($pUrl, \Agl::APP_PUBLIC_DIR) === false) {
            if (! empty($pParams)) {
                $params = array();
                foreach ($pParams as $key => $value) {
                    $params[] = $key . DS . $value;
                }

                $url = $pUrl . DS . implode(DS, $params) . DS;
                if ($pRelative) {
                    return ROOT . $url;
                }
                return self::getHost($url);
            }

            $url = $pUrl . DS;
            if ($pRelative) {
                return ROOT . $url;
            }
            return self::getHost($url);
        }

        if ($pRelative) {
            return ROOT . $pUrl;
        }
        return self::getHost($pUrl);
    }

    /**
     * Return the current URL with optional additional params.
     *
     * @param array $pParams Parameters to add to the request (additional)
     * @return string
     */
    public static function getCurrent(array $pNewParams = array())
    {
        $request = \Agl::getRequest();
        $module = $request->getModule();
        $view   = $request->getView();
        $params = $request->getParams();

        $params = array_merge($params, $pNewParams);

        return self::get($module . DS . $view, $params);
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
        $url = \Agl::APP_PUBLIC_DIR
               . DS
               . \Agl\Core\Mvc\View\ViewInterface::APP_HTTP_SKIN_DIR
               . DS
               . \Agl::app()->getConfig('@app/global/theme')
               . DS
               . $pUrl;

        if ($pRelative) {
            return ROOT . $url;
        }

        return self::getHost($url);
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
        $url = \Agl::APP_PUBLIC_DIR
               . DS
               . $pUrl;

        if ($pRelative) {
            return ROOT . $url;
        }

        return self::getHost($url);
    }

    /**
     * Get the base URL with host name and protocol.
     *
     * @return string
     */
    public static function getHost($pPath = '')
    {
        return self::getProtocol() . $_SERVER['HTTP_HOST'] . ROOT . $pPath;
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
                throw new \Agl\Exception("Protocol '" . $_SERVER['SERVER_PROTOCOL'] . "' is not supported");
        }
    }
}
