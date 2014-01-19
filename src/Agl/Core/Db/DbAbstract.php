<?php
namespace Agl\Core\Db;

use \Agl\Core\Agl,
    \Agl\Core\Db\Query\QueryAbstract;

/**
 * Abstract class - Db
 *
 * @category Agl_Core
 * @package Agl_Core_Db
 * @version 0.1.0
 */

abstract class DbAbstract
    extends QueryAbstract
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
     * Database name (saved when connected).
     *
     * @var string
     */
    protected $_dbName = '';

    /**
     * Connect to database and register the connection.
     *
     * @param string $pHost
     * @param string $pName
     * @param null|string $pUser
     * @param null|string $pPassword
     * @return Db
     */
    public function setConnection($pHost, $pName, $pUser = NULL, $pPassword = NULL)
    {
        $this->_connection = $this->connect($pHost, $pName, $pUser, $pPassword);
        $this->_dbName = $pName;
        return $this;
    }

    /**
     * Return the stored database connection.
     *
     * @return mixed
     */
    public function getConnection()
    {
        if (is_null($this->_connection)) {
            $this->setConnection(
                Agl::app()->getConfig('main/db/host'),
                Agl::app()->getConfig('main/db/name'),
                Agl::app()->getConfig('main/db/user'),
                Agl::app()->getConfig('main/db/password')
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
