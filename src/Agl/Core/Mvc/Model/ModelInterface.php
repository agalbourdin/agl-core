<?php
namespace Agl\Core\Mvc\Model;

/**
 * Interface - Model
 *
 * @category Agl_Core
 * @package Agl_Core_Mvc_Model
 * @version 0.1.0
 */

interface ModelInterface
{
	/**
     * The suffix used by the application's model class names.
     */
    const APP_MODEL_SUFFIX = 'Model';

    /**
     * The application directory to search a Model class.
     */
    const APP_PHP_MODEL_DIR = 'Model';

    /**
     * The suffix used by the application's helper class names.
     */
    const APP_HELPER_SUFFIX = 'Helper';

    /**
     * The application directory to search a Helper class.
     */
    const APP_PHP_HELPER_DIR = 'Helper';
}
