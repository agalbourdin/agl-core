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
    public function __construct($pDbContainer);
    public function __call($pMethod, array $pArgs);
    public function load($pConditions = NULL, $pLimit = NULL, $pOrder = NULL);
    public function getDbContainer();
    public function getCurrent();
    public function getNext();
    public function getPrevious();
    public function count($pConditions = NULL, $pLimit = NULL);
    public function save();
    public function deleteItems();
    public function drop();
    public function resetPointer();
}
