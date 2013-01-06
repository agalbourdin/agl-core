<?php
namespace Agl\Core\Db\Item;

use \Agl,
    \Agl\Core\Data\Date as DateData,
    \Agl\Core\Data\String as StringData,
    \Agl\Core\Db\Id\Id,
    \Agl\Core\Db\Item\Item,
    \Agl\Core\Db\Item\ItemInterface,
    \Agl\Core\Db\Query\Conditions\Conditions,
    \Agl\Core\Db\Query\Delete\Delete,
    \Agl\Core\Db\Query\Insert\Insert,
    \Agl\Core\Db\Query\Select\Select,
    \Agl\Core\Db\Query\Update\Update,
    \Agl\Core\Observer\Observer,
    \Exception;

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
        Agl::validateParams(array(
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
                $attribute = StringData::fromCamelCase($matches[1]);
                if (! isset($pArgs[1])) {
                    return $this->_loadByAttribute($attribute, $pArgs[0]);
                }

                return $this->_loadByAttribute($attribute, $pArgs[0], $pArgs[1]);
            }
        }

        throw new \Exception("Undefined method '$pMethod'");
    }

    /**
     * Return the requested attribute value.
     *
     * @param string $pVar Requested attribute
     * @return mixed Attribute value or NULL if the attribute does not exists
     */
    public function __get($pVar)
    {
        $attribute = $this->_dbContainer . static::PREFIX_SEPARATOR . StringData::fromCamelCase($pVar);

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
        $attribute = $this->_dbContainer . static::PREFIX_SEPARATOR . StringData::fromCamelCase($pVar);
        /*if ($attribute == $this->getIdField()) {
            $this->setId($pValue);
        }*/

        /*$validation = \Agl\Core\Data\Attribute\Validation::validate($pVar);
        if (! $validation) {
            throw new \Exception("Validation failed for attribute '$attribute'");
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
        $attribute = $this->_dbContainer . static::PREFIX_SEPARATOR . StringData::fromCamelCase($pVar);
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
    protected function _joinExists(Item $pItem)
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
        $prefix = $this->_dbContainer . static::PREFIX_SEPARATOR;
        foreach ($pFields as $key => $value) {
            if (strpos($key, $prefix) === false) {
                $newKey           = $prefix . $key;
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
        $select = new Select($this->_dbContainer);

        if ($pOrder !== NULL) {
            $select->addOrder($pOrder);
        }

        $conditions = new Conditions();
        $conditions->add(
            $pAttribute,
            Conditions::EQUAL,
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
        Observer::dispatch(Observer::EVENT_ITEM_INSERT_BEFORE, array(
            'item' => $this
        ));

        $this->{static::DATEADDFIELD} = DateData::now();
        $insert = new Insert($this->_dbContainer);
        $insert->addFields($this->_fields);
        $insert->commit();
        $this->setId($insert->getId());
        $this->_origFields = $this->_fields;

        Observer::dispatch(Observer::EVENT_ITEM_INSERT_AFTER, array(
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
        if (! $pId instanceof Id) {
            $id = new Id($pId);
        } else {
            $id = $pId;
        }

        return $this->_loadByAttribute(static::IDFIELD, $id->getOrig());
    }

    /**
     * Load an item with conditions filtering.
     *
     * @param Conditions $pConditions
     * @param null|array $pOrder Order the select query
     * @return type
     */
    public function load($pConditions = NULL, $pOrder = NULL)
    {
        $select = new Select($this->_dbContainer);

        if ($pOrder !== NULL) {
            $select->addOrder($pOrder);
        }

        if ($pConditions instanceof Conditions) {
            $select->loadConditions($pConditions);
        }

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
     * Load the last inserted element of the item's collection.
     *
     * @return ItemAbstract
     */
    public function loadLast()
    {
        return $this->load(NULL, array(ItemInterface::IDFIELD => Select::ORDER_DESC));
    }

    /**
     * Load the first inserted element of the item's collection.
     *
     * @return ItemAbstract
     */
    public function loadFirst()
    {
        return $this->load(NULL, array(ItemInterface::IDFIELD => Select::ORDER_ASC));
    }

    /**
     * Load a random element.
     *
     * @return ItemAbstract
     */
    public function loadRandom()
    {
        return $this->load(NULL, array(ItemInterface::IDFIELD => Select::ORDER_RAND));
    }

    /**
     * Set the Item ID.
     *
     * @param mixed $pValue
     * @return mixed
     */
    public function setId($pValue)
    {
        $idField = $this->_dbContainer . static::PREFIX_SEPARATOR . static::IDFIELD;

        if (! $pValue instanceof Id) {
            $id = new Id($pValue);
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
        return $this->_dbContainer . static::PREFIX_SEPARATOR . static::IDFIELD;
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
        $field = $this->_dbContainer . static::PREFIX_SEPARATOR . $pField;
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
        $field = $this->_dbContainer . static::PREFIX_SEPARATOR . $pField;

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
        Observer::dispatch(Observer::EVENT_ITEM_SAVE_BEFORE, array(
            'item' => $this
        ));

        if (! $this->getOrigField(static::IDFIELD)) {
            return $this->_insert();
        }

        $this->{static::DATEUPDATEFIELD} = DateData::now();
        $update = new Update($this);

        if ($pConditions instanceof Conditions) {
            $update->loadConditions($pConditions);
        }

        $update->commit();

        $this->_origFields = $this->_fields;

        Observer::dispatch(Observer::EVENT_ITEM_SAVE_AFTER, array(
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
        Observer::dispatch(Observer::EVENT_ITEM_DELETE_BEFORE, array(
            'item' => $this
        ));

        $delete = new Delete($this);
        $delete->commit();

        Observer::dispatch(Observer::EVENT_ITEM_DELETE_AFTER, array(
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
        return $this->__set(static::JOINS_FIELD_PREFIX . $pDbContainer, $pJoins);
    }

    /**
     * Delete all item's joins to a db container.
     *
     * @param string $pDbContainer Joins database container
     * @return array
     */
    public function unsetJoins($pDbContainer)
    {
        return $this->__unset(static::JOINS_FIELD_PREFIX . $pDbContainer);
    }

    /**
     * Create a join with an item from another collection.
     *
     * @param Item $pItem The item to join
     * @return Item
     */
    public function addParent(Item $pItem)
    {
        if (! $this->getId() or ! $pItem->getId()) {
            throw new Exception("The items must be existing in the database before being joined");
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
    public function removeParent(Item $pItem)
    {
        if (! $this->getId() or ! $pItem->getId()) {
            throw new Exception("The items must be existing in the database before being joined");
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
        Agl::validateParams(array(
            'RewritedString' => $pDbContainer
        ));

        if (! $this->getId()) {
            throw new Exception("The item must be existing in the database before being joined");
        }

        $collection = new \Agl\Core\Db\Collection\Collection($pDbContainer);

        $joins = $this->getJoins($pDbContainer);

        if (is_array($joins)) {
            $conditions = new Conditions();
            $conditions->add(
                static::IDFIELD,
                Conditions::IN,
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
        $collections = Agl::app()->getDb()->listCollections();

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