<?php
namespace Agl\Core\Loader;

use \Agl,
    \Agl\Core\Autoload,
    \Agl\Core\Db\Collection\Collection,
    \Agl\Core\Db\Collection\CollectionInterface,
    \Agl\Core\Mvc\Model\Model,
    \Agl\Core\Mvc\Model\ModelInterface,
    \Agl\Core\Registry\Registry,
    \Exception,
    \ReflectionClass;

/**
 * Load instances or singletons when requested.
 *
 * @category Agl_Core
 * @package Agl_Core_Loader
 * @version 0.1.0
 */

class Loader
{
	/**
     * The list of all the More modules loaded into AGL.
     *
     * @var array
     */
    private static $_loadedModules = array();

	/**
     * Create a new instance of the requested class.
     *
     * @param string $pClass Class path
     * @param array $pArgs Arguments to construct the requested class
     * @return mixed
     */
    public static function getInstance($pClass, array $pArgs = array())
    {
        if (stripos($pClass, Agl::AGL_CORE_DIR) === 0  or stripos($pClass, Agl::AGL_MORE_DIR) === 0) {
            $moduleArr = explode(DS, $pClass);
            $moduleArr = array_map('ucfirst', $moduleArr);
            if (count($moduleArr) == 2) {
                $moduleArr[] = $moduleArr[1];
            }

            if (preg_match('#^' . Agl::AGL_MORE_DIR . '/#', $pClass)) {
                self::$_loadedModules[strtolower(implode('/', $moduleArr))] = true;
            }

            $path = '\\' . Autoload::AGL_POOL . '\\' . implode('\\', $moduleArr);

            if (empty($pArgs)) {
                return new $path();
            } else {
                $reflect  = new ReflectionClass($path);
                return $reflect->newInstanceArgs($pArgs);
            }
        } else if (is_string($pClass)) {
            $reflect  = new ReflectionClass($pClass);
            return $reflect->newInstanceArgs($pArgs);
        }

        return NULL;
    }

    /**
     * Create a new instance of the requested class as a singleton.
     *
     * @param string $pClass Class path
     * @param array $pArgs Arguments to construct the requested class
     * @return mixed
     */
    public static function getSingleton($pClass, array $pArgs = array())
    {
        if (stripos($pClass, Agl::AGL_CORE_DIR) === 0 or stripos($pClass, Agl::AGL_MORE_DIR) === 0) {
            $moduleArr = explode(DS, $pClass);
            if (count($moduleArr) == 2) {
                $moduleArr[] = $moduleArr[1];
                $pClass      = implode(DS, $moduleArr);
            }
        }

        $registryKey = '_singleton/' . strtolower($pClass);
        $instance    = Registry::get($registryKey);
        if (! $instance) {
            $instance = self::getInstance($pClass, $pArgs);
            if ($instance === NULL) {
                return NULL;
            }

            Registry::set($registryKey, $instance);
        }

        return $instance;
    }

    /**
     * Return an instance of the requested model.
     *
     * @param string $pModel
     * @param array $pFields Attributes to add to the item
     * @return mixed
     */
    public static function getModel($pModel, array $pFields = array())
    {
        $className = ucfirst($pModel) . ModelInterface::APP_MODEL_SUFFIX;
        $fileName  = ucfirst($pModel);

        if (Agl::isInitialized()) {
            $modelPath = Agl::app()->getPath()
                         . Agl::APP_PHP_DIR
                         . DS
                         . ModelInterface::APP_PHP_MODEL_DIR
                         . DS
                         . $fileName
                         . Agl::PHP_EXT;

            if (is_readable($modelPath)) {
                return self::getInstance($className, array($pModel, $pFields));
            } else {
                return new Model($pModel, $pFields);
            }
        } else {
            return new Model($pModel, $pFields);
        }
    }

    /**
     * Return an instance of the requested collection.
     *
     * @param string $pCollection
     * @return mixed
     */
    public static function getCollection($pCollection)
    {
        $className = ucfirst($pCollection) . CollectionInterface::APP_SUFFIX;
        $fileName  = ucfirst($pCollection);

        if (Agl::isInitialized()) {
            $collectionPath = Agl::app()->getPath()
                            . Agl::APP_PHP_DIR
                            . DS
                            . CollectionInterface::APP_PHP_DIR
                            . DS
                            . $fileName
                            . Agl::PHP_EXT;

            if (is_readable($collectionPath)) {
                return self::getInstance($className, array($pCollection));
            } else {
                return new Collection($pCollection);
            }
        } else {
            return new Collection($pCollection);
        }
    }

    /**
     * Create a singleton instance of the requested helper class.
     *
     * @param string $pClass
     * @return mixed
     */
    public static function helper($pClass)
    {
        $classArr = explode(DS, $pClass);
        if (count($classArr) != 2) {
            throw new Exception("Helper syntax is incorrect ('$pClass')");
        }

        $className = ucfirst($classArr[0]) . ucfirst($classArr[1]) . ModelInterface::APP_HELPER_SUFFIX;
        $fileName = ucfirst($classArr[1]);

        if (Agl::isInitialized()) {
            $helperPath = Agl::app()->getPath()
                         . Agl::APP_PHP_DIR
                         . DS
                         . ModelInterface::APP_PHP_HELPER_DIR
                         . DS
                         . ucfirst($classArr[0])
                         . DS
                         . ucfirst($classArr[1])
                         . Agl::PHP_EXT;

            if (is_readable($helperPath)) {
                return self::getSingleton($className);
            } else {
                throw new Exception("Helper does not exists or isn't readable ('$pClass')");
            }
        } else {
            throw new Exception("The application must be initialized to instanciate a Helper ('$pClass')");
        }
    }

	/**
     * Return the array of the More modules loaded into AGL.
     *
     * @return array
     */
    public static function getLoadedModules()
    {
        return self::$_loadedModules;
    }

    /**
     * Check if a More module is loaded into AGL.
     *
     * @return bool
     */
    public static function isModuleLoaded($pModule)
    {
        return isset(self::$_loadedModules[$pModule]);
    }
}
