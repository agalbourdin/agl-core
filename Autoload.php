<?php
namespace Agl;

/**
 * Load class on demand.
 *
 * @category Agl
 * @package Agl
 * @version 0.1.0
 */

class Autoload
{
    /**
     * The AGL pool is used to detect if a class should be loaded from AGL.
     */
    const AGL_POOL = 'Agl';

    /**
     * Class aliases. Allow the use of a short class name instead of the full
     * name with namespace.
     *
     * @var array
     */
    private static $_aliases = array(
        'Agl'        => '\Agl\Agl',
        'Conditions' => '\Agl\Core\Db\Query\Conditions\Conditions',
        'Registry'   => '\Agl\Core\Registry\Registry',
        'Validation' => '\Agl\Core\Data\Validation'
    );

    /**
     * Register the autoload methods.
     *
     * @return Autoload
     */
    public function __construct()
    {
        spl_autoload_register(array($this, '_load'));
    }

    /**
     * Load class file if exists.
     *
     * @param sting $pClassName Class name
     */
    private function _load($pClassName)
    {
        if (array_key_exists($pClassName, self::$_aliases)) {
            return class_alias(self::$_aliases[$pClassName], $pClassName);
        }

        if (strpos($pClassName, self::AGL_POOL) === 0) {
            $path     = self::_loadFromAgl($pClassName);
            $realPath = BP . $path . \Agl::PHP_EXT;
        } else {
            $realPath = self::_loadFromApp($pClassName);
        }

        if ($realPath) {
            require($realPath);
            return $realPath;
        } else {
            throw new Exception("$pClassName not found, real path empty");
        }
    }

    /**
     * Retrieve the class path in the AGL pool.
     *
     * @param string $pClassName Class name
     * @return string Class path
     */
    private static function _loadFromAgl($pClassName)
    {
        $toReplace = array(
            self::AGL_POOL . '\\',
            '\\'
        );
        $replaceBy = array(
            '',
            '/'
        );

        return str_replace($toReplace, $replaceBy, $pClassName);
    }

    /**
     * Retrieve the class path in the application pool.
     *
     * @param string $pClassName Class name
     * @return string Class path
     */
    private static function _loadFromApp($pClassName)
    {
        $classNameArr     = explode('_', \Agl\Core\Data\String::fromCamelCase($pClassName));
        $appPath          = \Agl::app()->getPath();
        $phpModelDir      = \Agl\Core\Mvc\Model\ModelInterface::APP_PHP_MODEL_DIR;
        $phpHelperDir     = \Agl\Core\Mvc\Model\ModelInterface::APP_PHP_HELPER_DIR;
        $phpControllerDir = \Agl\Core\Mvc\Controller\Controller::APP_PHP_CONTROLLER_DIR;
        $phpViewDir       = \Agl\Core\Mvc\View\ViewInterface::APP_PHP_VIEW_DIR;
        $phpBlockDir      = \Agl\Core\Mvc\Block\BlockInterface::APP_PHP_BLOCK_DIR;
        $phpDir           = \Agl::APP_PHP_DIR;
        $phpExt           = \Agl::PHP_EXT;

        if (isset($classNameArr[1]) and strcasecmp($classNameArr[1], $phpModelDir) == 0) {
            return $appPath . $phpDir . DS . $phpModelDir . DS . ucfirst($classNameArr[0]) . $phpExt;
        } else if (isset($classNameArr[2]) and strcasecmp($classNameArr[2], $phpHelperDir) == 0) {
            return $appPath . $phpDir . DS . $phpHelperDir .  DS . ucfirst($classNameArr[0]) . DS . ucfirst($classNameArr[1]) . $phpExt;
        } else if (isset($classNameArr[2]) and strcasecmp($classNameArr[2], $phpControllerDir) == 0) {
            return $appPath . $phpDir . DS . $phpControllerDir .  DS . ucfirst($classNameArr[0]) . DS . ucfirst($classNameArr[1]) . $phpExt;
        } else if (isset($classNameArr[2]) and strcasecmp($classNameArr[2], $phpViewDir) == 0) {
            return $appPath . $phpDir . DS . $phpViewDir .  DS . ucfirst($classNameArr[0]) . DS . ucfirst($classNameArr[1]) . $phpExt;
        } else if (isset($classNameArr[2]) and strcasecmp($classNameArr[2], $phpBlockDir) == 0) {
            return $appPath . $phpDir . DS . $phpBlockDir . DS . ucfirst($classNameArr[0]) . DS . ucfirst($classNameArr[1]) . $phpExt;
        }

        return '';
    }
}
