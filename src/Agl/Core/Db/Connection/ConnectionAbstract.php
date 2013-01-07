<?php
namespace Agl\Core\Db\Connection;

use \Agl\Core\Agl,
    \Agl\Core\Db\Query\Select\Select;

/**
 * Abstract class - Connection
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Connection
 * @version 0.1.0
 */

abstract class ConnectionAbstract
{
    /**
     * Query counter.
     *
     * @var int
     */
    private $_queries = 0;

    /**
     * Store the database connection.
     *
     * @var mixed
     */
    protected $_connection = NULL;

    /**
     * Return the order ASC constant, depending of the DB engine.
     *
     * @return mixed
     */
    public static function orderAsc()
    {
        return Select::ORDER_ASC;
    }

    /**
     * Return the order DESC constant, depending of the DB engine.
     *
     * @return mixed
     */
    public static function orderDesc()
    {
        return Select::ORDER_DESC;
    }

    /**
     * Return the stored database connection.
     *
     * @return mixed
     */
    public function getConnection()
    {
        if (is_null($this->_connection)) {
            $this->connect(
                Agl::app()->getConfig('@app/db/host'),
                Agl::app()->getConfig('@app/db/name'),
                Agl::app()->getConfig('@app/db/user'),
                Agl::app()->getConfig('@app/db/password')
            );
        }

        return $this->_connection;
    }

    /**
     * Increment the query counter.
     *
     * @return int Le nombre de requêtes effectuées
     */
    public function incrementCounter()
    {
        $this->_queries++;
        return $this->_queries;
    }

    /**
     * Return the number of commited queries.
     *
     * @return int
     */
    public function countQueries()
    {
        return $this->_queries;
    }
}
