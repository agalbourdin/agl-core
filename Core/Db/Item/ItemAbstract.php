<?php
namespace Agl\Core\Db\Item;

/**
 * Abstract class - Item
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Item
 * @version 0.1.0
 */

abstract class ItemAbstract
{
    /**
     * Store the item's fields.
     *
     * @var array Associative array
     */
    protected $_fields = array();

    /**
     * Original Item fields (associative array).
     *
     * This array is never updated by the set* methods, it can be safely used
     * to determine which fields have been added, updated or deleted. The array
     * is updated when the save() method is called.
     *
     * @var array Associative array
     */
    protected $_origFields = array();

    /**
     * Database container (table or collection depending of the
     * database engine).
     *
     * @var string
     */
    protected $_dbContainer = NULL;

    /**
     * Create a new item.
     *
     * @param string $pDbContainer Database container
     * @param array $pFields Attributes to add to the item
     */
    public function __construct($pDbContainer, array $pFields = array())
    {
        \Agl::validateParams(array(
            'RewritedString' => $pDbContainer
        ));

        $this->_dbContainer = $pDbContainer;
        $this->_origFields  = $this->_prefixFields($pFields);
        $this->_fields      = $this->_prefixFields($pFields);

        if (isset($this->_fields[$this->getIdField()])) {
            $this->setId($this->_fields[$this->getIdField()]);
        }
    }

    /**
     * Magic method - handle the set, get and remove call.
     *
     * @param string $pMethod Called method
     * @param array $pArgs Arguments
     * @return mixed
     */
    public function __call($pMethod, array $pArgs)
    {
        if (strpos($pMethod, 'get') === 0) {
            $var = str_replace('get', '', $pMethod);
            return $this->$var;
        } else if (strpos($pMethod, 'set') === 0 and isset($pArgs[0])) {
            $var = str_replace('set', '', $pMethod);
            $this->$var = $pArgs[0];
            return $this;
        } else if (strpos($pMethod, 'unset') === 0) {
            $var = str_replace('unset', '', $pMethod);
            unset($this->$var);
            return true;
        } else if (preg_match('/^loadBy([a-zA-Z0-9]+)$/', $pMethod, $matches)) {
            if (isset($matches[1]) and is_string($matches[1])
                and ! empty($matches[1]) and isset($pArgs[0])) {
                $attribute = \Agl\Core\Data\String::fromCamelCase($matches[1]);
                if (! isset($pArgs[1])) {
                    return $this->_loadByAttribute($attribute, $pArgs[0]);
                }

                return $this->_loadByAttribute($attribute, $pArgs[0], $pArgs[1]);
            }
        }

        throw new \Agl\Exception("Undefined method '$pMethod'");
    }

    /**
     * Return the requested attribute value.
     *
     * @param string $pVar Requested attribute
     * @return mixed Attribute value or NULL if the attribute does not exists
     */
    public function __get($pVar)
    {
        $attribute = $this->_dbContainer . \Agl\Core\Db\Item\ItemInterface::PREFIX_SEPARATOR . \Agl\Core\Data\String::fromCamelCase($pVar);

        if (isset($this->_fields[$attribute])) {
            return $this->_fields[$attribute];
        }

        return NULL;
    }

    /**
     * Create an attribute, or update its value.
     *
     * @param string $pVar The attribute to create / update
     * @param string $pValue The attribute value to set
     * @return mixed The attribute value
     */
    public function __set($pVar, $pValue)
    {
        $attribute = $this->_dbContainer . \Agl\Core\Db\Item\ItemInterface::PREFIX_SEPARATOR . \Agl\Core\Data\String::fromCamelCase($pVar);
        /*if ($attribute == $this->getIdField()) {
            $this->setId($pValue);
        }*/

        /*$validation = \Agl\Core\Data\Attribute\Validation::validate($pVar);
        if (! $validation) {
            throw new \Agl\Exception("Validation failed for attribute '$attribute'");
        }*/

        $this->_fields[$attribute] = $pValue;
        return $this->_fields[$attribute];
    }

    /**
     * Delete an attribute from the item.
     *
     * @param string $pVar The attribute to delete
     * @return bool
     */
    public function __unset($pVar)
    {
        $attribute = $this->_dbContainer . \Agl\Core\Db\Item\ItemInterface::PREFIX_SEPARATOR . \Agl\Core\Data\String::fromCamelCase($pVar);
        if (array_key_exists($attribute, $this->_fields)) {
            unset($this->_fields[$attribute]);
            return true;
        }

        return false;
    }

    /**
     * Check if a join exists between the item and $pItem.
     *
     * @param Item $pItem
     * @return bool
     */
    protected function _joinExists(\Agl\Core\Db\Item\Item $pItem)
    {
        $joins = $this->getJoins($pItem->getDbContainer());

        return (is_array($joins) and in_array($pItem->getId()->getOrig(), $joins));
    }

    /**
     * Prefix fields according to DB container.
     *
     * @param array $pFields Array of fields to prefix
     * @return array
     */
    protected function _prefixFields(array $pFields)
    {
        $prefix = $this->_dbContainer . \Agl\Core\Db\Item\ItemInterface::PREFIX_SEPARATOR;
        foreach ($pFields as $key => $value) {
            if (strpos($key, $prefix) === false) {
                $newKey = $this->_dbContainer . \Agl\Core\Db\Item\ItemInterface::PREFIX_SEPARATOR . $key;
                $pFields[$newKey] = $value;
                unset($pFields[$key]);
            }
        }

        return $pFields;
    }

    /**
     * Load item by attribute code and value.
     *
     * @param string $pAttribute
     * @param mixed $pValue
     * @param null|array $pOrder Order the select query
     * @return ItemAbstract
     */
    protected function _loadByAttribute($pAttribute, $pValue, $pOrder = NULL)
    {
        $select = new \Agl\Core\Db\Query\Select\Select($this->_dbContainer);

        if ($pOrder !== NULL) {
            $select->addOrder($pOrder);
        }

        $conditions = new \Agl\Core\Db\Query\Conditions\Conditions();
        $conditions->add(
            $pAttribute,
            $conditions::EQUAL,
            $pValue
        );

        $select->loadConditions($conditions);

        $select->findOne();
        if ($select->count()) {
            $fields            = $select->fetch(0);
            $this->_fields     = $fields;
            $this->_origFields = $fields;
            $this->setId($this->_fields[$this->getIdField()]);
        }

        return $this;
    }

    /**
     * Insert a new item in the database.
     * @return Item
     */
    protected function _insert()
    {
        \Agl\Core\Observer\Observer::dispatch(\Agl\Core\Observer\Observer::EVENT_ITEM_INSERT_BEFORE, array(
            'item' => $this
        ));

        $this->{\Agl\Core\Db\Item\ItemInterface::DATEADDFIELD} = \Agl\Core\Data\Date::now();
        $insert = new \Agl\Core\Db\Query\Insert\Insert($this->_dbContainer);
        $insert->addFields($this->_fields);
        $insert->commit();
        $this->setId($insert->getId());
        $this->_origFields = $this->_fields;

        \Agl\Core\Observer\Observer::dispatch(\Agl\Core\Observer\Observer::EVENT_ITEM_INSERT_AFTER, array(
            'item' => $this
        ));

        return $this;
    }

    /**
     * Load item by ID.
     *
     * @param mixed $pId The item ID
     * @return Item
     */
    public function loadById($pId)
    {
        if (! $pId instanceof \Agl\Core\Db\Id\Id) {
            $id = new \Agl\Core\Db\Id\Id($pId);
        } else {
            $id = $pId;
        }

        return $this->_loadByAttribute(\Agl\Core\Db\Item\ItemInterface::IDFIELD, $id->getOrig());
    }

    /**
     * Load an item with conditions filtering.
     *
     * @param Conditions $pConditions
     * @param null|array $pOrder Order the select query
     * @return type
     */
    public function load(\Agl\Core\Db\Query\Conditions\Conditions $pConditions, $pOrder = NULL)
    {
        $select = new \Agl\Core\Db\Query\Select\Select($this->_dbContainer);

        if ($pOrder !== NULL) {
            $select->addOrder($pOrder);
        }

        $select->loadConditions($pConditions);

        $select->findOne();
        if ($select->count()) {
            $fields            = $select->fetch(0);
            $this->_fields     = $fields;
            $this->_origFields = $fields;
            $this->setId($this->_fields[$this->getIdField()]);
        }

        return $this;
    }

    /**
     * Set the Item ID.
     *
     * @param mixed $pValue
     * @return mixed
     */
    public function setId($pValue)
    {
        $idField = $this->_dbContainer . \Agl\Core\Db\Item\ItemInterface::PREFIX_SEPARATOR . \Agl\Core\Db\Item\ItemInterface::IDFIELD;

        if (! $pValue instanceof \Agl\Core\Db\Id\Id) {
            $id = new \Agl\Core\Db\Id\Id($pValue);
        } else {
            $id = $pValue;
        }

        $this->_fields[$idField] = $id;
        return $this;
    }

    /**
     * Return the prefixed ID field name.
     *
     * @return string
     */
    public function getIdField()
    {
        return $this->_dbContainer . \Agl\Core\Db\Item\ItemInterface::PREFIX_SEPARATOR . \Agl\Core\Db\Item\ItemInterface::IDFIELD;
    }

    /**
     * Return the "fields" array.
     *
     * @return array Associative array
     */
    public function getFields()
    {
        return $this->_fields;
    }

    /**
     * Return the "original fields" array.
     *
     * @return array Associative array
     */
    public function getOrigFields()
    {
        return $this->_origFields;
    }

    /**
     * Return the value corresponding to and attribute code, if exists.
     *
     * @param string $pField The attribute code
     * @return mixed
     */
    public function getField($pField)
    {
        $field = $this->_dbContainer . \Agl\Core\Db\Item\ItemInterface::PREFIX_SEPARATOR . $pField;
        if (isset($this->_fields[$field])) {
            return $this->_fields[$field];
        }

        return NULL;
    }

    /**
     * Return the value corresponding to and attribute code, if exists.
     * Search in the origFields array.
     *
     * @param string $pField The attribute code
     * @return mixed
     */
    public function getOrigField($pField)
    {
        $field = $this->_dbContainer . \Agl\Core\Db\Item\ItemInterface::PREFIX_SEPARATOR . $pField;

        if (isset($this->_origFields[$field])) {
            return $this->_origFields[$field];
        }

        return NULL;
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
     * Save the item in the database.
     * By default the update query is conditioned to the item's ID.
     *
     * @param Conditions $pConditions Add filters to the update query
     * @return Item
     */
    public function save($pConditions = NULL)
    {
        \Agl\Core\Observer\Observer::dispatch(\Agl\Core\Observer\Observer::EVENT_ITEM_SAVE_BEFORE, array(
            'item' => $this
        ));

        if (! $this->getOrigField(\Agl\Core\Db\Item\ItemInterface::IDFIELD)) {
            return $this->_insert();
        }

        $this->{\Agl\Core\Db\Item\ItemInterface::DATEUPDATEFIELD} = \Agl\Core\Data\Date::now();
        $update = new \Agl\Core\Db\Query\Update\Update($this);

        if ($pConditions instanceof \Agl\Core\Db\Query\Conditions\Conditions) {
            $update->loadConditions($pConditions);
        }

        $update->commit();

        $this->_origFields = $this->_fields;

        \Agl\Core\Observer\Observer::dispatch(\Agl\Core\Observer\Observer::EVENT_ITEM_SAVE_AFTER, array(
            'item' => $this
        ));

        return $this;
    }

    /**
     * Delete the item from the database.
     *
     * @return bool
     */
    public function delete()
    {
        \Agl\Core\Observer\Observer::dispatch(\Agl\Core\Observer\Observer::EVENT_ITEM_DELETE_BEFORE, array(
            'item' => $this
        ));

        $delete = new \Agl\Core\Db\Query\Delete\Delete($this);
        $delete->commit();

        \Agl\Core\Observer\Observer::dispatch(\Agl\Core\Observer\Observer::EVENT_ITEM_DELETE_AFTER, array(
            'item' => $this
        ));

        return $this;
    }

    /**
     * Set item's joins to a db container.
     *
     * @param string $pDbContainer Joins database container
     * @param array $pJoins Array of item IDs
     * @return array
     */
    public function setJoins($pDbContainer, array $pJoins)
    {
        return $this->__set(\Agl\Core\Db\Item\ItemInterface::JOINS_FIELD_PREFIX . $pDbContainer, $pJoins);
    }

    /**
     * Delete all item's joins to a db container.
     *
     * @param string $pDbContainer Joins database container
     * @return array
     */
    public function unsetJoins($pDbContainer)
    {
        return $this->__unset(\Agl\Core\Db\Item\ItemInterface::JOINS_FIELD_PREFIX . $pDbContainer);
    }

    /**
     * Create a join with an item from another collection.
     *
     * @param Item $pItem The item to join
     * @return Item
     */
    public function addParent(\Agl\Core\Db\Item\Item $pItem)
    {
        if (! $this->getId() or ! $pItem->getId()) {
            throw new \Agl\Exception("The items must be existing in the database before being joined");
        }

        if (! $this->_joinExists($pItem)) {
            $dbContainer = $pItem->getDbContainer();
            $joins       = $this->getJoins($dbContainer);

            if (! is_array($joins)) {
                $joins = array();
            }

            $joins[] = $pItem->getId()->getOrig();

            $this->setJoins($dbContainer, $joins);
        }

        return $this;
    }

    /**
     * Remove one or more joined items.
     * If $pId is null, all joined items from the collection $pCollection will
     * be removed.
     *
     * @param string $pCollection The joined collection
     * @param mixed $pId ID or array of IDs
     * @return Item
     */
    public function removeParent(\Agl\Core\Db\Item\Item $pItem)
    {
        if (! $this->getId() or ! $pItem->getId()) {
            throw new \Agl\Exception("The items must be existing in the database before being joined");
        }

        if ($this->_joinExists($pItem)) {
            $dbContainer = $pItem->getDbContainer();
            $joins       = $this->getJoins($dbContainer);

            unset($joins[array_search($pItem->getId()->getOrig(), $joins)]);

            if (empty($joins)) {
                $this->unsetJoins($dbContainer);
            } else {
                $this->setJoins($dbContainer, $joins);
            }
        }

        return $this;
    }

    /**
     * Search for all the joined items in the given database container.
     *
     * @param string $pDbContainer Database container
     * @return Collection
     */
    public function getParents($pDbContainer, $pLimit = NULL, $pOrder = NULL)
    {
        \Agl::validateParams(array(
            'RewritedString' => $pDbContainer
        ));

        if (! $this->getId()) {
            throw new \Agl\Exception("The item must be existing in the database before being joined");
        }

        $collection = new \Agl\Core\Db\Collection\Collection($pDbContainer);

        $joins = $this->getJoins($pDbContainer);

        if (is_array($joins)) {
            $conditions = new \Agl\Core\Db\Query\Conditions\Conditions();
            $conditions->add(
                \Agl\Core\Db\Item\ItemInterface::IDFIELD,
                $conditions::IN,
                $joins
            );

            $collection->load($conditions, $pLimit, $pOrder);
        }

        return $collection;
    }

    /**
     * Remove the joins to the current item in all items from all collections
     * (except in the item's collection).
     *
     * @return Item
     */
    public function removeJoinFromAllChilds()
    {
        $collections = \Agl::app()->getDb()->listCollections();

        foreach ($collections as $collection) {
            if ($collection == $this->_dbContainer) {
                continue;
            }

            $childs = $this->getChilds($collection);
            while ($item = $childs->getNext()) {
                $item
                    ->removeParent($this)
                    ->save();
            }
        }

        return $this;
    }
}
