<?php
namespace Agl\Core\Mysql;

/**
 * Management of an item.
 * An item is a Mysql row.
 *
 * This class can create, update or delete an item.
 *
 * @category Agl_Core
 * @package Agl_Core_Mysql
 * @version 0.1.0
 */

class Item
    extends \Agl\Core\Db\Item\ItemAbstract
        implements \Agl\Core\Db\Item\ItemInterface
{
    /**
     * Save the item in the database.
     *
     * @return Item
     */
    public function save($pConditions = NULL)
    {
        /*$joinsField = $this->getField(\Agl\Core\Db\Item\ItemInterface::JOINS_FIELD);
        $origJoinsField = $this->getOrigField(\Agl\Core\Db\Item\ItemInterface::JOINS_FIELD);
        if ($joinsField !== NULL or $origJoinsField !== NULL) {
            $joins = $this->getJoins();
            $joinsArr = array();
            foreach ($joins as $dbContainer => $joinedIds) {
                $joinsArr[] = $dbContainer . '>' . implode(',', $joinedIds);
            }
            $this->setJoins(implode('|', $joinsArr));
        }

        $ancestorsField = $this->getField(\Agl\Core\Db\Tree\TreeInterface::ANCESTORSFIELD);
        $origAncestorsField = $this->getOrigField(\Agl\Core\Db\Tree\TreeInterface::ANCESTORSFIELD);
        if ($ancestorsField !== NULL or $origAncestorsField !== NULL) {
            $ancestors = $this->getTreeAncestors();
            $this->setTreeAncestors(implode('|', $ancestors));
        }*/
        foreach ($this->_fields as &$value) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }
        }

        return parent::save($pConditions);
    }

    /**
     * Get the item joins as a multidimensional array.
     *
     * @param string $pDbContainer The database container where to search for
     * joins
     * @return array
     */
    public function getJoins($pDbContainer)
    {
        $joinsField = $this->getField(static::JOINS_FIELD_PREFIX . $pDbContainer);

        if (! $joinsField) {
            return array();
        } else if (is_array($joinsField)) {
            return $joinsField;
        }

        return explode(',', $joinsField);
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
            throw new \Exception("The item must be existing in the database before childs can be retrieved.");
        }

        $id = $this->getId()->getOrig();

        $collection = new \Agl\Core\Db\Collection\Collection($pDbContainer);

        $conditions = new \Agl\Core\Db\Query\Conditions\Conditions();
        $conditions->add(
            static::JOINS_FIELD_PREFIX . $this->_dbContainer,
            $conditions::INSET,
            $id
        );

        $collection->load($conditions, $pLimit, $pOrder);

        return $collection;
    }

    /**
     * Return the tree ancestors as an array.
     *
     * @return array
     */
    public function getTreeAncestors()
    {
        $ancestorsField = $this->getField(\Agl\Core\Db\Tree\TreeInterface::ANCESTORSFIELD);

        if (! $ancestorsField) {
            return array();
        } else if (is_array($ancestorsField)) {
            return $ancestorsField;
        }

        $ancestors = explode('|', $ancestorsField);

        return $ancestors;
    }
}
