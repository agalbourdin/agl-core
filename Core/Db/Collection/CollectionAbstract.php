<?php
namespace Agl\Core\Db\Collection;

/**
 * Abstract class - Collection
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Collection
 * @version 0.1.0
 */

abstract class CollectionAbstract
{
    /**
     * Store the number of items loaded in the collection.
     *
     * @var string
     */
    private $_count = 0;

    /**
     * Database container (table or collection depending of the
     * database engine).
     *
     * @var string
     */
    protected $_dbContainer = NULL;

    /**
     * Pointer used to browse the collection.
     *
     * @var int
     */
    protected $_pointer = 0;

    /**
     * The select instance used to load items into the collection.
     *
     * @var Select
     */
    protected $_select = NULL;

    /**
     * Create a new collection object.
     *
     * @param string $pDbContainer Database container
     */
    public function __construct($pDbContainer)
    {
        \Agl::validateParams(array(
            'RewritedString' => $pDbContainer
        ));

        $this->_dbContainer = $pDbContainer;
    }

    /**
     * Magic method - handle the load call and check the requested attribute
     * and value.
     *
     * @param string $pMethod Called method
     * @param array $pArgs Arguments
     * @return mixed
     */
    public function __call($pMethod, array $pArgs)
    {
        if (preg_match('/^loadBy([a-zA-Z0-9]+)$/', $pMethod, $matches)) {
            if (isset($matches[1]) and is_string($matches[1])
                and ! empty($matches[1]) and isset($pArgs[0])) {
                $attribute = \Agl\Core\Data\String::fromCamelCase($matches[1]);
                return $this->_loadByAttribute($attribute, $pArgs[0]);
            }
        }

        throw new \Agl\Exception("Undefined method '$pMethod'");
    }

    /**
     * Load items by attribute code and value.
     *
     * @param string $pAttribute
     * @param mixed $pValue
     * @return CollectionAbstract
     */
    protected function _loadByAttribute($pAttribute, $pValue)
    {
        $select = new \Agl\Core\Db\Query\Select\Select($this->_dbContainer);
        /*$select->addFields(array(
            \Agl\Core\Db\Item\ItemInterface::IDFIELD => true
        ));*/

        $conditions = new \Agl\Core\Db\Query\Conditions\Conditions();
        $conditions->add(
            $pAttribute,
            $conditions::EQUAL,
            $pValue
        );

        $select->loadConditions($conditions);

        $select->find();

        $this->_count  = $select->count();
        $this->_select = $select;
        $this->resetPointer();

        return $this;
    }

    /**
     * Fetch a result from the Select instance.
     *
     * @return mixed
     */
    protected function _fetch()
    {
        if ($this->_select === NULL) {
            return false;
        }

        $data = $this->_select->fetch($this->_pointer);
        if ($data) {
            return \Agl::getModel($this->_dbContainer, $data);
        }

        return false;
    }

    /**
     * Load all the collection's items with optional filters.
     *
     * @param Conditions $pConditions Filter the results
     * @param mixed $pLimit Limit the number of results
     * @param array $pOrder Order the results
     * @return CollectionAbstract
     */
    public function load($pConditions = NULL, $pLimit = NULL, $pOrder = NULL)
    {
        $select = new \Agl\Core\Db\Query\Select\Select($this->_dbContainer);
        /*$select->addFields(array(
            \Agl\Core\Db\Item\ItemInterface::IDFIELD => true
        ));*/

        if ($pLimit !== NULL) {
            $select->limit($pLimit);
        }

        if ($pOrder !== NULL) {
            $select->addOrder($pOrder);
        }

        if ($pConditions instanceof \Agl\Core\Db\Query\Conditions\Conditions) {
            $select->loadConditions($pConditions);
        }

        $select->find();

        $this->_count  = $select->count();
        $this->_select = $select;
        $this->resetPointer();

        return $this;
    }

    /**
     * Return the database container.
     *
     * @return string
     */
    public function getDbContainer()
    {
        return $this->_dbContainer;
    }

    /**
     * Return the current item.
     *
     * @return mixed
     */
    public function getCurrent()
    {
        return $this->_fetch();
    }

    /**
     * Move the pointer to the next item, and return it.
     *
     * @return mixed
     */
    public function getNext()
    {
        $this->_pointer++;
        $item = $this->_fetch();
        if (! $item) {
            $this->_pointer--;
        }

        return $item;
    }

    /**
     * Move the pointer to the previous item, and return it.
     *
     * @return mixed
     */
    public function getPrevious()
    {
        if ($this->_pointer <= 0) {
            $this->resetPointer();
            return false;
        }

        $this->_pointer--;
        $item = $this->_fetch();
        return $item;
    }

    /**
     * Return the number of items loaded in the collection, or count a
     * collection without loading items.
     *
     * @param Conditions $pConditions Filter the results
     * @param mixed $pLimit Limit the number of results
     * @return int
     */
    public function count($pConditions = NULL, $pLimit = NULL)
    {
        if ($pConditions === NULL and $pLimit === NULL) {
            return $this->_count;
        }

        $count = new \Agl\Core\Db\Query\Count\Count($this->_dbContainer);

        if ($pLimit !== NULL) {
            $count->limit($pLimit);
        }

        if ($pConditions instanceof \Agl\Core\Db\Query\Conditions\Conditions) {
            $count->loadConditions($pConditions);
        }

        return $count->commit();
    }

    /**
     * Save all the collection's items.
     *
     * @return CollectionAbstract
     */
    public function save()
    {
        $this->resetPointer();
        while ($item = $this->getNext()) {
            $item->save();
        }

        return $this;
    }

    /**
     * Delete all items from the database and reset the collection.
     *
     * @return CollectionAbstract
     */
    public function deleteItems()
    {
        $this->resetPointer();
        while ($item = $this->getNext()) {
            if ($item->getId()) {
                $item->delete();
            }
        }

        return $this;
    }

    /**
     * Delete the collection (and all its items and childs) from the database.
     *
     * @return CollectionAbstract
     */
    public function drop()
    {
        $delete = new \Agl\Core\Db\Query\Drop\Drop($this);
        return $delete->commit();
    }

    /**
     * Reset the pointer value to allow a new loop on the collection's items.
     *
     * @return CollectionAbstract
     */
    public function resetPointer()
    {
        $this->_pointer = 0;
        return $this;
    }
}
