<?php
namespace Agl\Core;

use \Agl\Core\Data\String as StringData,
    \Exception;

/**
 * Load class on demand.
 *
 * @category Agl_Core
 * @package Agl_Core
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
        'Agl'        => '\Agl\Core\Agl',
        'Arr'        => '\Agl\Core\Data\Arr',
        'Block'      => '\Agl\Core\Mvc\Block\Block',
        'Collection' => '\Agl\Core\Db\Collection\Collection',
        'Conditions' => '\Agl\Core\Db\Query\Conditions\Conditions',
        'Controller' => '\Agl\Core\Mvc\Controller\Controller',
        'Date'       => '\Agl\Core\Data\Date',
        'Debug'      => '\Agl\Core\Debug\Debug',
        'Dir'        => '\Agl\Core\Data\Dir',
        'File'       => '\Agl\Core\Data\File',
        'Model'      => '\Agl\Core\Mvc\Model\Model',
        'Registry'   => '\Agl\Core\Registry\Registry',
        'Select'     => '\Agl\Core\Db\Query\Select\Select',
        'String'     => '\Agl\Core\Data\String',
        'Url'        => '\Agl\Core\Url\Url',
        'Validation' => '\Agl\Core\Data\Validation',
        'View'       => '\Agl\Core\Mvc\View\View'
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
            $realPath = AGL_PATH . $path . Agl::PHP_EXT;
        } else {
            $realPath = self::_loadFromApp($pClassName);
        }

        if ($realPath and is_readable($realPath)) {
            require($realPath);
        } else {
            throw new Exception("'$pClassName' not found, real path empty");
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
        return str_replace(array(
            self::AGL_POOL . '\\' . Agl::AGL_CORE_DIR . '\\',
            '\\'
        ), array(
            '',
            '/'
        ), $pClassName);
    }

    /**
     * Retrieve the class path in the application pool.
     *
     * @param string $pClassName Class name
     * @return string Class path
     */
    private static function _loadFromApp($pClassName)
    {
        $classNameArr = explode('_', StringData::fromCamelCase($pClassName));
        $pool         = array_pop($classNameArr);

        return APP_PATH
               . Agl::APP_PHP_DIR
               . $pool
               . DS
               . implode(DS, $classNameArr)
               . Agl::PHP_EXT;
    }
}
