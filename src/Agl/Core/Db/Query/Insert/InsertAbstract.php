<?php
namespace Agl\Core\Db\Query\Insert;

use \Agl,
    \Agl\Core\Db\Item\ItemInterface,
    \Agl\Core\Db\Query\QueryAbstract;

/**
 * Abstract class - Insert
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Query_Insert
 * @version 0.1.0
 */

abstract class InsertAbstract
    extends QueryAbstract
{
    /**
     * Database container.
     *
     * @var string
     */
    protected $_dbContainer = NULL;

    /**
     * Array of fields to insert into the database.
     *
     * @var array
     */
    protected $_fields = array();

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
        $this->_setDbPrefix();
    }

    /**
     * Add fields to insert when the query will be commited.
     *
     * @param array $pFields
     * @return InsertAbstract
     */
    public function addFields(array $pFields)
    {
        $this->_fields = array_merge($pFields, $this->_fields);
        return $this;
    }

    /**
     * Return the ID to use for the insertion (if defined).
     *
     * @return mixed
     */
    public function getId()
    {
        $idField = ItemInterface::IDFIELD;

        if (isset($this->_fields[$idField])) {
            return $this->_fields[$idField];
        }

        return NULL;
    }
}
