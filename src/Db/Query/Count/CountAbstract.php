<?php
namespace Agl\Core\Db\Query\Count;

use \Agl\Core\Agl,
    \Agl\Core\Db\Query\Conditions\Conditions,
    \Agl\Core\Db\Query\QueryAbstract;

/**
 * Abstract class - Count
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Query_Count
 * @version 0.1.0
 */

abstract class CountAbstract
    extends QueryAbstract
{
    /**
     * Database container.
     *
     * @var string
     */
    protected $_dbContainer = NULL;

    /**
     * Conditions to filter the query results.
     *
     * @var \Agl\Core\Db\Query\Conditions\Conditions
     */
    protected $_conditions = NULL;

    /**
     * Specific field to count.
     *
     * @var array
     */
    protected $_field = array();

    /**
     * Create a new insert object.
     *
     * @param string $pDbContainer Database container
     */
    public function __construct($pDbContainer)
    {
        Agl::validateParams(array(
            'RewritedString' => $pDbContainer
        ));

        $this->_dbContainer = $pDbContainer;

        $this->_conditions  = new Conditions();
    }

    /**
     * Load conditions to filter the query results.
     *
     * @param Conditions $pConditions
     * @return SelectAbstract
     */
    public function loadConditions(Conditions $pConditions)
    {
        $this->_conditions = $pConditions;
        return $this;
    }

    /**
     * Set the main field of the count query.
     *
     * @param array $pField Field name
     * @param bool $pDistinct Don't count duplicated fields
     * @return InsertAbstract
     */
    public function setField($pField, $pDistinct = false)
    {
        Agl::validateParams(array(
            'String' => $pField,
            'Bool'         => $pDistinct
        ));

        $this->_field = array(
            static::FIELD_NAME     => $pField,
            static::FIELD_DISTINCT => $pDistinct
        );
        return $this;
    }
}
