<?php
namespace Agl\Core\Db\Collection;

/**
 * Interface - Collection
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Collection
 * @version 0.1.0
 */

interface CollectionInterface
{
    /**
     * The suffix used by the application's collection class names.
     */
    const APP_SUFFIX = 'Collection';

    /**
     * The application directory to search a Collection class.
     */
    const APP_PHP_DIR = 'collection';

    public function __construct($pDbContainer);
    public function __call($pMethod, array $pArgs);
    public function load($pConditions = NULL, $pLimit = NULL, $pOrder = NULL);
    public function getDbContainer();
    public function current();
    public function next();
    public function prev();
    public function count($pConditions = NULL, $pLimit = NULL);
    public function save();
    public function deleteItems();
    public function drop();
    public function resetPointer();
}
