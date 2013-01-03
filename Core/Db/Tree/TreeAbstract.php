<?php
namespace Agl\Core\Db\Tree;

/**
 * Abstract class - Tree
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Tree
 * @version 0.1.0
 */

abstract class TreeAbstract
    extends \Agl\Core\Db\Collection\Collection
{
    /**
     * The tree's main item's ID.
     *
     * @var Id
     */
    protected $_mainItem = NULL;

    /**
     * Save the item has childs boolean to avoid multiple queries.
     *
     * @var null|bool
     */
    protected $_hasChilds = NULL;

    /**
     * Rebuild the main item's ancestors field.
     *
     * @return TreeAbstract
     */
    protected function _rebuildAncestors()
    {
        $ancestors = array();

        $item = $this->_mainItem;
        while ($item instanceof \Agl\Core\Db\Item\Item) {
            $parent = $item->getTreeParent();
            if ($parent) {
                $ancestors[] = $parent;
                $item = \Agl::app()->getModel($this->_mainItem->getDbContainer());
                $item->loadById($parent);
            } else {
                $item = false;
            }
        }

        $this->_mainItem->setTreeAncestors($ancestors);

        return $this;
    }

    /**
     * Add items to the collection (from a recursive array).
     *
     * @param type array $pTreeArr
     * @return TreeAbstract
     */
    /*protected function _addTreeArrItems(array $pTreeArr)
    {
        if (! empty($pTreeArr)) {
            foreach ($pTreeArr as $subData) {
                if (! is_array($subData)) {
                    $this->addItem($subData);
                } else {
                    if (isset($subData['item'])) {
                        $this->addItem($subData['item']);
                        unset($subData['item']);
                    }
                    $this->_addTreeArrItems($subData);
                }
            }
        }

        return $this;
    }*/

    /**
     * Set the tree's main item.
     *
     * @param Item $pItem
     * @return TreeAbstract
     */
    public function setMainItem(\Agl\Core\Db\Item\Item $pItem)
    {
        if ($this->_mainItem === NULL) {
            if (! $pItem->getId()) {
                throw new \Exception("The main item must be saved to database before its insertion in a tree");
            }

            $this->_mainItem = $pItem;
            return $this;
        }

        throw new \Exception("The tree's main item is already set");
    }

    /**
     * Return the tree's main item.
     *
     * @return mixed
     */
    public function getMainItem()
    {
        return $this->_mainItem;
    }

    /**
     * Check if the main item has a parent.
     *
     * @return bool
     */
    public function hasParent()
    {
        if ($this->_mainItem->getTreeParent()) {
            return true;
        }

        return false;
    }

    /**
     * Check if the main item has at least one direct child.
     *
     * @return int
     */
    public function hasChilds()
    {
        if ($this->_hasChilds === NULL) {
            $count = new \Agl\Core\Db\Query\Count\Count($this->_mainItem->getDbContainer());

            $conditions = new \Agl\Core\Db\Query\Conditions\Conditions();
            $conditions->add(
                static::PARENTFIELD,
                $conditions::EQUAL,
                $this->_mainItem->getId()->getOrig()
            );

            $count
                ->loadConditions($conditions)
                ->limit(1);
            $this->_hasChilds = ($count->commit()) ? true : false;
        }

        return $this->_hasChilds;
    }

    /**
     * Get the main item's parent.
     *
     * @return mixed
     */
    public function getParent()
    {
        $item   = \Agl::getModel($this->_mainItem->getDbContainer());
        $parent = $this->_mainItem->getTreeParent();
        if ($parent) {
            $item->loadById($parent);
        }

        return $item;
    }

    /**
     * Load the main item's ancestors and add them to the collection.
     *
     * @return TreeAbstract
     * @todo Test this method
     */
    public function loadAncestors($pConditions = NULL, $pLimit = NULL, $pOrder = NULL)
    {
        $ancestors = $this->_mainItem->getTreeAncestors();
        if (is_array($ancestors) and ! empty($ancestors)) {
            $select = new \Agl\Core\Db\Query\Select\Select($this->_mainItem->getDbContainer());

            if ($pLimit !== NULL) {
                $select->limit($pLimit);
            }

            if ($pOrder !== NULL) {
                $select->addOrder($pOrder);
            }

            if ($pConditions instanceof \Agl\Core\Db\Query\Conditions\Conditions) {
                $conditions = $pConditions;
            } else {
                $conditions = new \Agl\Core\Db\Query\Conditions\Conditions();
            }
            $conditions->add(
                static::PARENTFIELD,
                $conditions::IN,
                $ancestors
            );

            $select->loadConditions($conditions);

            $select->find();

            $this->_count  = $select->count();
            $this->_select = $select;
            $this->resetPointer();
        }

        return $this;
    }

    /**
     * Load the main item's direct childs (non recursive) and add them to the
     * collection.
     *
     * @param mixed $pConditions Conditions to filter the childs
     * @param $pLimit Limit the number of childs to add
     * @param $pOrder Order the childs
     * @return TreeAbstract
     */
    public function loadDirectChilds($pConditions = NULL, $pLimit = NULL, $pOrder = NULL)
    {
        $select = new \Agl\Core\Db\Query\Select\Select($this->_mainItem->getDbContainer());

        if ($pLimit !== NULL) {
            $select->limit($pLimit);
        }

        if ($pOrder !== NULL) {
            $select->addOrder($pOrder);
        }

        if ($pConditions instanceof \Agl\Core\Db\Query\Conditions\Conditions) {
            $conditions = $pConditions;
        } else {
            $conditions = new \Agl\Core\Db\Query\Conditions\Conditions();
        }
        $conditions->add(
            static::PARENTFIELD,
            $conditions::EQUAL,
            $this->_mainItem->getId()->getOrig()
        );

        $select->loadConditions($conditions);

        $select->find();

        $this->_count  = $select->count();
        $this->_select = $select;
        $this->resetPointer();

        return $this;
    }

    /**
     * Load the main item's childs (recursive) and add them to the collection.
     *
     * @param mixed $pConditions Conditions to filter the childs
     * @param $pLimit Limit the number of childs to add
     * @param $pOrder Order the childs
     * @return TreeAbstract
     */
    /*public function loadAllChilds($pConditions = NULL, $pLimit = NULL, $pOrder = NULL)
    {
        $this->_addTreeArrItems($this->toArray());
        return $this;
    }*/

    /**
     * Return the numer of ancestors of the tree's main item.
     *
     * @return int
     */
    public function countAncestors()
    {
        $ancestors = $this->_mainItem->getTreeAncestors();
        if (is_array($ancestors)) {
            return count($ancestors);
        }

        return 0;
    }

    /**
     * Return the numer of childs from the tree's main item.
     *
     * @return int
     */
    public function countChilds()
    {
        $count = new \Agl\Core\Db\Query\Count\Count($this->_mainItem->getDbContainer());

        $conditions = new \Agl\Core\Db\Query\Conditions\Conditions();
        $conditions->add(
            static::PARENTFIELD,
            $conditions::EQUAL,
            $this->_mainItem->getId()->getOrig()
        );

        $count->loadConditions($conditions);
        return $count->commit();
    }

    /**
     * Set the main item's parent and ancestors fields.
     *
     * @param Item $pItem
     * @return TreeAbstract
     */
    public function setParent(\Agl\Core\Db\Item\Item $pItem)
    {
        if (! $pItem->getId()) {
            throw new \Exception("The item must be saved to database before its insertion in a tree");
        }

        $this->_mainItem->setTreeParent($pItem->getId()->getOrig());

        $this->_rebuildAncestors();

        $this->_mainItem->save();

        return $this;
    }

    /**
     * Add a child to the tree's main item and set parent and ancestors fields.
     *
     * @param Item $pItem
     * @return Item
     */
    public function addChild(\Agl\Core\Db\Item\Item $pItem)
    {
        if (! $pItem->getId()) {
            throw new \Exception("The item must be saved to database before its insertion in a tree");
        }

        $pItem->setTreeParent($this->_mainItem->getId()->getOrig());

        $ancestors = array(
            $this->_mainItem->getId()->getOrig()
        );

        $parentAncestors = $this->_mainItem->getTreeAncestors();
        if (is_array($parentAncestors)) {
            $ancestors = array_merge($parentAncestors, $ancestors);
        }

        $pItem
            ->setTreeAncestors($ancestors)
            ->save();

        return $this;
    }

    /**
     * Return the tree as a multidimensional array, including all childs
     * recursively.
     *
     * @return array
     */
    public function toArray($pConditions = NULL, $pLimit = NULL, $pOrder = NULL)
    {
        $arr = array();
        if ($this->hasChilds()) {
            $this->loadDirectChilds($pConditions, $pLimit, $pOrder);
            while ($item = $this->getNext()) {
                $tree = new \Agl\Core\Db\Tree\Tree($item->getDbContainer());
                $tree->setMainItem($item);

                if ($tree->hasChilds()) {
                    $arr[] = array(
                        'item' => $item,
                        $tree->toArray($pConditions, $pLimit, $pOrder)
                    );
                } else {
                    $arr[] = $item;
                }
            }
        }

        $this->resetPointer();

        return $arr;
    }
}
