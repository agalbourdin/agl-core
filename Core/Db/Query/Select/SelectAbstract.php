<?php
namespace Agl\Core\Db\Query\Select;

/**
 * Abstract class - Select
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Query_Select
 * @version 0.1.0
 */

abstract class SelectAbstract
    extends \Agl\Core\Db\Query\QueryAbstract
{
    /**
     * Database container.
     *
     * @var string
     */
    protected $_dbContainer = NULL;

    /**
     * Array of fields to select.
     *
     * @var array
     */
    protected $_fields = array();

    /**
     * Order the query results by specific fields.
     *
     * @var array
     */
    protected $_order = array();

    /**
     * Set a limit to the query.
     *
     * @var int
     */
    protected $_limit = NULL;

    /**
     * Skip query results.
     *
     * @var int
     */
    protected $_skip = NULL;

    /**
     * Conditions to filter the query results.
     *
     * @var \Agl\Core\Db\Query\Conditions\Conditions
     */
    protected $_conditions = NULL;

    /**
     * Create a new select object.
     *
     * @param string $pDbContainer Database container
     */
    public function __construct($pDbContainer)
    {
        \Agl::validateParams(array(
            'RewritedString' => $pDbContainer
        ));

        $this->_dbContainer = $pDbContainer;
        $this->_setDbPrefix();

        $this->_conditions  = new \Agl\Core\Db\Query\Conditions\Conditions();
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
     * Add fields to the select query.
     *
     * @param array $pFields Array of fields
     * @return InsertAbstract
     */
    public function addFields(array $pFields)
    {
        $fields        = $this->_prefixFields($pFields);
        $this->_fields = array_merge($fields, $this->_fields);
        return $this;
    }

    /**
     * Ajoute un ou plusieurs ordres de tri à la requête.
     *
     * @param array $pFields Associative array with field => order
     * @return SelectAbstract
     */
    public function addOrder(array $pFields)
    {
        if (in_array(static::ORDER_RAND, $pFields)) {
            $this->_order[static::ORDER_RAND] = true;
            return $this;
        }

        $orders = array(
            static::ORDER_ASC,
            static::ORDER_DESC
        );

        $fields = $this->_prefixFields($pFields);
        foreach ($fields as $field => $order) {
            if ((! is_string($order) and ! is_int($order)) or ! in_array($order, $orders)) {
                continue;
            }

            $this->_order[$field] = $order;
        }

        return $this;
    }

    /**
     * Load conditions to filter the query results.
     *
     * @param Conditions $pConditions
     * @return SelectAbstract
     */
    public function loadConditions(\Agl\Core\Db\Query\Conditions\Conditions $pConditions)
    {
        $this->_conditions = $pConditions;
        return $this;
    }

    /**
     * Add a limit to the select query.
     *
     * @param int $pNb
     * @return SelectAbstract
     */
    public function limit($pNb)
    {
        if (is_array($pNb) and count($pNb) == 2 and isset($pNb[0])
            and isset($pNb[1]) and is_int($pNb[0]) and is_int($pNb[1])) {
            $nb = $pNb[1];
            $this->skip($pNb[0]);
        } else if (is_int($pNb)) {
            $nb = $pNb;
        } else {
            throw new \Exception("Validation failed for type 'array' or 'int'");
        }

        $this->_limit = $nb;
        return $this;
    }

    /**
     * Add a skip value to the select query.
     *
     * @param int $pNb
     * @return SelectAbstract
     */
    public function skip($pNb)
    {
        \Agl::validateParams(array(
            'Int' => $pNb
        ));

        $this->_skip = $pNb;
        return $this;
    }
}
