<?php
namespace Agl\Core\Db\Collection;

use \Agl\Core\Agl,
    \Agl\Core\Data\String as StringData,
    \Agl\Core\Db\Item\ItemInterface,
    \Agl\Core\Db\Query\Conditions\Conditions,
    \Agl\Core\Db\Query\Count\Count,
    \Agl\Core\Db\Query\Select\Select,
    \Exception,
    \Iterator;

/**
 * Abstract class - Collection
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Collection
 * @version 0.1.0
 */

abstract class CollectionAbstract
    implements Iterator
{
    /**
     * Store the number of items loaded in the collection.
     *
     * @var null|int
     */
    private $_count = NULL;

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
     * Data loaded via PDO fetchAll().
     *
     * @var null|array
     */
    protected $_data = NULL;

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
        Agl::validateParams(array(
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
     * Load items by attribute code and value.
     *
     * @param string $pAttribute
     * @param mixed $pValue
     * @param array $pArgs
     * @return CollectionAbstract
     */
    protected function _loadByAttribute($pAttribute, $pValue, array $pArgs = array())
    {
        $args = $pArgs;

        if (! isset($args[static::FILTER_CONDITIONS])
            or ! $args[static::FILTER_CONDITIONS] instanceof Conditions) {
            $args[static::FILTER_CONDITIONS] = new Conditions();
        }

        $args[static::FILTER_CONDITIONS]->add(
            $pAttribute,
            Conditions::EQ,
            $pValue
        );

        return $this->load($args);
    }

    /**
     * Fetch MySQL results.
     *
     * @return bool
     */
    protected function _fetch()
    {
        if ($this->_select === NULL) {
            return false;
        }

        $this->_data = $this->_select->fetchAllAsItems();

        return true;
    }

    /**
     * Load all the collection's items with optional filters.
     *
     * @param array $pArgs Optional arguments (Conditions, Limit, Order)
     * @return CollectionAbstract
     */
    public function load(array $pArgs = array())
    {
        $select = new Select($this->_dbContainer);
        /*$select->addFields(array(
            ItemInterface::IDFIELD => true
        ));*/

        if (isset($pArgs[static::FILTER_LIMIT])) {
            $select->limit($pArgs[static::FILTER_LIMIT]);
        }

        if (isset($pArgs[static::FILTER_ORDER])) {
            $select->addOrder($pArgs[static::FILTER_ORDER]);
        } else {
            $select->addOrder(array(
                $this->_dbContainer . ItemInterface::PREFIX_SEPARATOR . ItemInterface::IDFIELD => Select::ORDER_DESC
            ));
        }

        if (isset($pArgs[static::FILTER_CONDITIONS]) and  $pArgs[static::FILTER_CONDITIONS] instanceof Conditions) {
            $select->loadConditions($pArgs[static::FILTER_CONDITIONS]);
        }

        $select->find();

        $this->_count  = $select->count();
        $this->_select = $select;
        $this->_fetch();

        return $this;
    }

    /**
     * Load $pNb elements ordered by ID DESC.
     *
     * @param int $pNb
     * @return CollectionAbstract
     */
    public function loadLast($pNb = 1)
    {
        return $this->load(array(
            static::FILTER_LIMIT => $pNb,
            static::FILTER_ORDER => array(ItemInterface::IDFIELD => Select::ORDER_DESC)
        ));
    }

    /**
     * Load $pNb elements ordered by ID ASC.
     *
     * @param int $pNb
     * @return CollectionAbstract
     */
    public function loadFirst($pNb = 1)
    {
        return $this->load(array(
            static::FILTER_LIMIT => $pNb,
            static::FILTER_ORDER => array(ItemInterface::IDFIELD => Select::ORDER_ASC)
        ));
    }

    /**
     * Load $pNb elements ordered by RAND().
     *
     * @param int $pNb
     * @return CollectionAbstract
     */
    public function loadRandom($pNb = 1)
    {
        return $this->load(array(
            static::FILTER_LIMIT => $pNb,
            static::FILTER_ORDER => array(ItemInterface::IDFIELD => Select::ORDER_RAND)
        ));
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
     * Return the number of items loaded in the collection, or count a
     * collection without loading items.
     *
     * @param Conditions $pConditions Filter the results
     * @return int
     */
    public function count($pConditions = NULL)
    {
        if ($pConditions === NULL and $this->_count !== NULL) {
            return $this->_count;
        }

        $count = new Count($this->_dbContainer);

        if ($pConditions instanceof Conditions) {
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
        foreach ($this as $item) {
            if ($item->getId()) {
                $item->save();
            }
        }

        return $this;
    }

    /**
     * Insert all collection's items that aren't already saved to database.
     *
     * @return CollectionAbstract
     */
    public function insert()
    {
        foreach ($this as $item) {
            if (! $item->getId()) {
                $item->insert();
            }
        }

        return $this;
    }

    /**
     * Delete all items from the database and reset the collection.
     *
     * @param bool $withChilds Delete also all item's childs in other
     * collections
     * @return CollectionAbstract
     */
    public function deleteItems($withChilds = false)
    {
        foreach ($this as $item) {
            if ($item->getId()) {
                $item->delete($withChilds);
            }
        }

        return $this;
    }

    /**
     * Rewind iterator.
     */
    public function rewind()
    {
        $this->_pointer = 0;
    }

    /**
     * Return the current item.
     *
     * @return Item
     */
    public function current()
    {
        return $this->_data[$this->_pointer];
    }

    /**
     * Return the current iterator position.
     *
     * @return int
     */
    public function key()
    {
        return $this->_pointer;
    }

    /**
     * Move the pointer to the next item.
     */
    public function next()
    {
        $this->_pointer++;
    }

    /**
     * Check if current iterator position is valid.
     *
     * @return bool
     */
    public function valid() {
        return isset($this->_data[$this->_pointer]);
    }
}
