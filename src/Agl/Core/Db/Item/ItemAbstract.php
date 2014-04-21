<?php
namespace Agl\Core\Db\Item;

use \Agl\Core\Agl,
    \Agl\Core\Data\Date as DateData,
    \Agl\Core\Data\String as StringData,
    \Agl\Core\Data\Validation,
    \Agl\Core\Db\Db,
    \Agl\Core\Db\DbInterface,
    \Agl\Core\Db\Id\Id,
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
     * Load model validations rules from configuration.
     *
     * @var array
     */
    protected $_validation = array();

    /**
     * Create a new item and load its Validation config.
     *
     * @param string $pDbContainer Database container
     * @param array $pFields Attributes to add to the item
     * @param array $pValidationRules Custom validation rules. If empty, rules
     * will be loaded from configuration.
     */
    public function __construct($pDbContainer, array $pFields = array(), array $pValidationRules = array())
    {
        Agl::validateParams(array(
            'RewritedString' => $pDbContainer
        ));

        $this->_dbContainer = $pDbContainer;
        $this->_origFields  = $this->_prefixFields($pFields);
        $this->_fields      = $this->_prefixFields($pFields);

        $idField = $this->getIdField();
        if (isset($this->_fields[$idField])) {
            $this->setId($this->_fields[$idField]);
        }

        if (! empty($pValidationRules)) {
            $this->_validation = $pValidationRules;
        } else {
            $this->_validation = Agl::app()->getConfig('core-validation/' . $pDbContainer);

            if ($this->_validation === NULL) {
                $this->_validation = array();
            }
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
            $var = substr($pMethod, 3);
            return $this->$var;
        } else if (strpos($pMethod, 'set') === 0) {
            $var = substr($pMethod, 3);
            if (isset($pArgs[0])) {
                $this->$var = $pArgs[0];
                return $this;
            } else {
                unset($this->$var);
                return true;
            }
        } else if (strpos($pMethod, 'unset') === 0) {
            $var = substr($pMethod, 5);
            unset($this->$var);
            return true;
        } else if (preg_match('/^loadBy([a-zA-Z0-9]+)$/', $pMethod, $matches)) {
            if (isset($pArgs[0])) {
                $attribute = StringData::fromCamelCase($matches[1]);
                if (isset($pArgs[1])) {
                    return $this->_loadByAttribute($attribute, $pArgs[0], $pArgs[1]);
                }

                return $this->_loadByAttribute($attribute, $pArgs[0]);
            }
        }

        throw new Exception("Undefined method '$pMethod'");
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
        $attribute = StringData::fromCamelCase($pVar);

        if (isset($this->_validation[$attribute])) {
            $func = $this->_validation[$attribute];

            if (strpos($func, 'is') === 0) {
                if (Validation::$func($pValue) === false) {
                    throw new Exception("'$this->_dbContainer' model: validation failed for attribute '$attribute' ($func)");
                }
            } else if (Validation::isRegex($pValue, $func) === false) {
                throw new Exception("'$this->_dbContainer' model: regex validation failed for attribute '$attribute' ($func)");
            }
        }

        $attribute = $this->_dbContainer . static::PREFIX_SEPARATOR . $attribute;

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
    protected function _joinExists(ItemAbstract $pItem)
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
    protected function _prefixFields(array $pFields, $pDbContainer = NULL)
    {
        if ($pDbContainer === NULL) {
            $dbContainer = $this->_dbContainer;
        } else {
            $dbContainer = $pDbContainer;
        }

        $prefix = $dbContainer . static::PREFIX_SEPARATOR;
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
     * Prefix field according to DB container.
     *
     * @param string $pField Field to prefix
     * @return string
     */
    protected function _prefixField($pField, $pDbContainer = NULL)
    {
        if ($pDbContainer === NULL) {
            $dbContainer = $this->_dbContainer;
        } else {
            $dbContainer = $pDbContainer;
        }

        return $dbContainer . static::PREFIX_SEPARATOR . $pField;
    }

    /**
     * Load item by attribute code and value.
     *
     * @param string $pAttribute
     * @param mixed $pValue
     * @param array $pArgs
     * @return ItemAbstract
     */
    protected function _loadByAttribute($pAttribute, $pValue, array $pArgs = array())
    {
        $args = $pArgs;

        if (! isset($args[DbInterface::FILTER_CONDITIONS])
            or ! $args[DbInterface::FILTER_CONDITIONS] instanceof Conditions) {
            $args[DbInterface::FILTER_CONDITIONS] = new Conditions();
        }

        $args[DbInterface::FILTER_CONDITIONS]->add(
            $pAttribute,
            Conditions::EQ,
            $pValue
        );

        return $this->load($args);
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
     * @param array $pArgs Optional arguments (Conditions, Limit, Order)
     * @return type
     */
    public function load($pArgs = array())
    {
        if (! is_array($pArgs)) {
            return $this->loadById($pArgs);
        }

        $select = new Select($this->_dbContainer);

        if (isset($pArgs[DbInterface::FILTER_ORDER])) {
            $select->addOrder($pArgs[DbInterface::FILTER_ORDER]);
        } else {
            $select->addOrder(array(
                $this->getIdField() => Db::ORDER_DESC
            ));
        }

        if (isset($pArgs[DbInterface::FILTER_CONDITIONS]) and  $pArgs[DbInterface::FILTER_CONDITIONS] instanceof Conditions) {
            $select->loadConditions($pArgs[DbInterface::FILTER_CONDITIONS]);
        }

        $select->findOne();
        if ($select->count()) {
            $fields = $select->fetchAll(true);
        } else {
            $fields = array();
        }

        $this->_fields     = $fields;
        $this->_origFields = $fields;

        $idField = $this->getIdField();
        if (isset($this->_fields[$idField])) {
            $this->setId($this->_fields[$idField]);
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
        $idField = $this->_dbContainer . static::PREFIX_SEPARATOR . static::IDFIELD;

        if (! $pValue instanceof Id) {
            $id = new Id($pValue);
        } else {
            $id = $pValue;
        }

        $this->_fields[$idField]     = $id;
        $this->_origFields[$idField] = $id;
        return $this;
    }

    /**
     * Return the prefixed ID field name.
     *
     * @param string|null $pDbContainer
     * @return string
     */
    public function getIdField($pDbContainer = NULL)
    {
        if ($pDbContainer === NULL) {
            $pDbContainer = $this->_dbContainer;
        }

        return $pDbContainer . static::PREFIX_SEPARATOR . static::IDFIELD;
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
     * @param bool $pRaw If raw is true, field will not be prefixed
     * @return mixed
     */
    public function getFieldValue($pField, $pRaw = false)
    {
        if (! $pRaw) {
            $field = $this->_dbContainer . static::PREFIX_SEPARATOR . $pField;
        } else {
            $field = $pField;
        }

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
     * @param bool $pRaw If raw is true, field will not be prefixed
     * @return mixed
     */
    public function getOrigFieldValue($pField, $pRaw = false)
    {
        if (! $pRaw) {
            $field = $this->_dbContainer . static::PREFIX_SEPARATOR . $pField;
        } else {
            $field = $pField;
        }

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
     * @return Item
     */
    public function save()
    {
        if (! $this->getOrigFieldValue(static::IDFIELD)) {
            throw new Exception("Cannot save an item without ID");
        }

        Observer::dispatch(Observer::EVENT_ITEM_SAVE_BEFORE, array(
            'item' => $this
        ));

        $this->{static::DATEUPDATEFIELD} = DateData::now();
        $update = new Update($this);

        $update->commit();

        $this->_origFields = $this->_fields;

        Observer::dispatch(Observer::EVENT_ITEM_SAVE_AFTER, array(
            'item' => $this
        ));

        return $this;
    }

    /**
     * Insert a new item in the database.
     * @return Item
     */
    public function insert()
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
     * Delete the item from the database.
     *
     * @param bool $pWithChilds Delete also all item's childs in other
     * collections
     * @return bool
     */
    public function delete($pWithChilds = false)
    {
        Observer::dispatch(Observer::EVENT_ITEM_DELETE_BEFORE, array(
            'item' => $this
        ));

        $delete = new Delete($this);
        $delete->commit($pWithChilds);

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
    /*public function setJoins($pDbContainer, array $pJoins)
    {
        return $this->__set(static::JOINS_FIELD_PREFIX . $pDbContainer, $pJoins);
    }*/

    /**
     * Delete all item's joins to a db container.
     *
     * @param string $pDbContainer Joins database container
     * @return array
     */
    /*public function unsetJoins($pDbContainer)
    {
        return $this->__unset(static::JOINS_FIELD_PREFIX . $pDbContainer);
    }*/

    /**
     * Create a join with an item from another collection.
     *
     * @param Item $pItem The item to join
     * @return Item
     */
    /*public function addParent(ItemAbstract $pItem)
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
    }*/

    /**
     * Remove one or more joined items.
     * If $pId is null, all joined items from the collection $pCollection will
     * be removed.
     *
     * @param string $pCollection The joined collection
     * @param mixed $pId ID or array of IDs
     * @return Item
     */
    /*public function removeParent(ItemAbstract $pItem)
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
    }*/

    /**
     * Return a collection of parents, or a single parent, in the required
     * collection.
     *
     * @param string $pDbContainer Database container
     * @param array $pArgs Loading arguments (Conditions, Order, Limit)
     * @param bool $pFirst Return a single Item instead of a Collection
     * @return Item|Collection
     */
    public function getParents($pDbContainer, array $pArgs = array(), $pFirst = false)
    {
        Agl::validateParams(array(
            'RewritedString' => $pDbContainer
        ));

        if (! $this->getId()) {
            throw new Exception("getParents: Item must exist in database");
        }

        $args = $pArgs;

        if (isset($args[DbInterface::FILTER_CONDITIONS]) and $args[DbInterface::FILTER_CONDITIONS] instanceof Conditions) {
            $conditions = $args[DbInterface::FILTER_CONDITIONS];
        } else {
            $conditions = new Conditions();
        }

        $ids = $this->getFieldValue($this->_prefixField(static::IDFIELD, $pDbContainer), true);
        if ($ids === NULL) {
            if ($pFirst) {
                return Agl::getModel($pDbContainer);
            }

            return Agl::getCollection($pDbContainer);
        }

        $conditions->add(
            static::IDFIELD,
            Conditions::IN,
            array($ids)
        );

        $args[DbInterface::FILTER_CONDITIONS] = $conditions;

        if ($pFirst) {
            $args[DbInterface::FILTER_LIMIT] = array(0, 1);
        }

        $collection = Agl::getCollection($pDbContainer);
        $collection->load($args);

        if ($pFirst) {
            if ($collection->count()) {
                return $collection->current();
            }

            return Agl::getModel($pDbContainer);
        }

        return $collection;
    }

    /**
     * Return a single parent, in the required collection.
     *
     * @param string $pDbContainer Database container
     * @param array $pArgs Loading arguments (Conditions, Order, Limit)
     * @return Item
     */
    public function getParent($pDbContainer, array $pArgs = array())
    {
        return $this->getParents($pDbContainer, $pArgs, true);
    }

    /**
     * Return a collection of childs, or a single child, in the required
     * collection.
     *
     * @param string $pDbContainer Database container
     * @param array $pArgs Loading arguments (Conditions, Order, Limit)
     * @param bool $pFirst Return a single Item instead of a Collection
     * @return Item|Collection
     */
    public function getChilds($pDbContainer, array $pArgs = array(), $pFirst = false)
    {
        Agl::validateParams(array(
            'RewritedString' => $pDbContainer
        ));

        if (! $this->getId()) {
            throw new Exception("getChilds: Item must exist in database");
        }

        $args = $pArgs;

        if (isset($args[DbInterface::FILTER_CONDITIONS]) and $args[DbInterface::FILTER_CONDITIONS] instanceof Conditions) {
            $conditions = $args[DbInterface::FILTER_CONDITIONS];
        } else {
            $conditions = new Conditions();
        }

        $conditions->addGroup(
            array(
                $this->getIdField(),
                Conditions::EQ,
                $this->getId()
            ),
            array(
                $this->getIdField(),
                Conditions::INSET,
                $this->getId()
            )
        );

        $args[DbInterface::FILTER_CONDITIONS] = $conditions;

        if ($pFirst) {
            $args[DbInterface::FILTER_LIMIT] = array(0, 1);
        }

        $collection = Agl::getCollection($pDbContainer);
        $collection->load($args);

        if ($pFirst) {
            if ($collection->count()) {
                return $collection->current();
            }

            return Agl::getModel($pDbContainer);
        }

        return $collection;
    }

    /**
     * Return a single child, in the required collection.
     *
     * @param string $pDbContainer Database container
     * @param array $pArgs Loading arguments (Conditions, Order, Limit)
     * @return Item
     */
    public function getChild($pDbContainer, array $pArgs = array())
    {
        return $this->getChilds($pDbContainer, $pArgs, true);
    }

    /**
     * Add a parent relation to the current Item.
     *
     * @param ItemAbstract $pItem
     * @return Item
     */
    public function addParent(ItemAbstract $pItem)
    {
        if (! $pItem->getId()) {
            throw new Exception("addParent: Parent must exist in database");
        }

        $parentsValue = $this->getFieldValue($pItem->getIdField(), true);
        if (! $parentsValue) {
            $parents = array();
        } else {
            $parents = explode(',', $parentsValue);
        }

        $parents[] = $pItem->getId()->getOrig();
        $parents   = array_unique($parents);

        $this->_fields[$pItem->getIdField()] = implode(',', $parents);

        return $this;
    }

    /**
     * Remove a parent relation to the current Item.
     *
     * @param ItemAbstract $pItem
     * @return Item
     */
    public function removeParent(ItemAbstract $pItem)
    {
        if (! $pItem->getId()) {
            throw new Exception("addParent: Parent must exist in database");
        }

        $parentsValue = $this->getFieldValue($pItem->getIdField(), true);
        if (! $parentsValue) {
            $parents = array();
        } else {
            $parents = explode(',', $parentsValue);
        }

        $key = array_search($pItem->getId()->getOrig(), $parents);
        if ($key !== false) {
            unset($parents[$key]);
        }

        if (empty($parents)) {
            $this->_fields[$pItem->getIdField()] = NULL;
        } else {
            $this->_fields[$pItem->getIdField()] = implode(',', $parents);
        }

        return $this;
    }

    /**
     * Remove Item's childs in the given DB container, or in all containers.
     *
     * @param string|null $pDbContainer
     * @return Item
     */
    public function removeChilds($pDbContainer = NULL)
    {
        if ($pDbContainer === NULL) {
            $containers = Agl::app()->getDb()->listCollections(array($this->getIdField()));
        } else {
            $containers = array($pDbContainer);
        }

        foreach ($containers as $container) {
            if ($container === $this->getDbContainer()) {
                continue;
            }

            $childs = $this->getChilds($container);
            foreach ($childs as $child) {
                $child->removeParent($this)->save();
            }
        }

        return $this;
    }
}
