<?php
namespace Agl\Core\Mongo;

/**
 * A collection is a set of items.
 * Each item is identified by a MongoDB document ID.
 *
 * Each item is loaded on demand, for example when browsing the collection, and
 * erased when the pointer is moved to another item, so the size of the
 * collection is globally constant.
 *
 * @category Agl_Core
 * @package Agl_Core_Mongo
 * @version 0.1.0
 */

class Collection
    extends \Agl\Core\Db\Collection\CollectionAbstract
        implements \Agl\Core\Db\Collection\CollectionInterface
{
    /**
     * Remove the joins to the current collection in all items from all
     * collections (except in the current collection).
     *
     * @return Collection
     */
    public function removeJoinsFromAllCollections()
    {
        $collections = \Agl::app()->getDb()->listCollections();
        $dbContainer = $this->_dbContainer;

        foreach ($collections as $collection) {
            $realCollectionName = str_replace(\Agl::app()->getConfig('@app/db/name') . '.', '', $collection);
            if ($realCollectionName == $dbContainer) {
                continue;
            }

            $collection = new \Agl\Core\Db\Collection\Collection($realCollectionName);

            $conditions = new \Agl\Core\Db\Query\Conditions\Conditions();
            $conditions->add(
                \Agl\Core\Db\Item\ItemInterface::JOINS_FIELD . '.' . $dbContainer,
                $conditions::NOTNULL,
                true
            );

            $collection->load(NULL, NULL, $conditions);

            while ($collection->hasNext()) {
                $collection
                    ->getNext()
                    ->unsetJoins($dbContainer)
                    ->save();
            }
        }

        return $this;
    }
}
