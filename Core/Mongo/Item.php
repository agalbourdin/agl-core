<?php
namespace Agl\Core\Mongo;

/**
 * Management of an item.
 * An item is a MongoDB document.
 *
 * This class can create, update or delete an item.
 *
 * @category Agl_Core
 * @package Agl_Core_Mongo
 * @version 0.1.0
 */

class Item
    extends \Agl\Core\Db\Item\ItemAbstract
        implements \Agl\Core\Db\Item\ItemInterface
{
    /**
     * Remove all item's joins to the collection $pDbContainer.
     *
     * @param string $pDbContainer
     * @return Item
     */
    public function removeAllJoins($pDbContainer)
    {
        if (! $this->getId()) {
            throw new \Agl\Exception("The item must be existing in the database before being joined");
        }

        $joins = $this->getJoins();

        if (is_array($joins) and array_key_exists($pDbContainer, $joins)) {
            unset($joins[$pDbContainer]);

            if (empty($joins)) {
                $this->unsetJoins();
            } else {
                $this->setJoins($joins);
            }
        }

        return $this;
    }

    /**
     * Search for items in $pDbContainer with a join to the current item.
     *
     * @param string $pDbContainer Database container
     * @return Collection
     */
    public function getChilds($pDbContainer, $pLimit = NULL, $pOrder = NULL)
    {
        if (! $this->getId()) {
            throw new \Agl\Exception("The item must be existing in the database before being joined");
        }

        $collection = new \Agl\Core\Db\Collection\Collection($pDbContainer);

        $conditions = new \Agl\Core\Db\Query\Conditions\Conditions();
        $conditions->add(
            \Agl\Core\Db\Item\ItemInterface::JOINS_FIELD . '.' . $this->_dbContainer,
            $conditions::EQUAL,
            $this->getId()->getOrig()
        );

        $collection->load($conditions, $pLimit, $pOrder);

        return $collection;
    }
}
