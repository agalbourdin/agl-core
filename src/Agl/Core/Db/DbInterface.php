<?php
namespace Agl\Core\Db;

/**
 * Interface - Db
 *
 * @category Agl_Core
 * @package Agl_Core_Db
 * @version 0.1.0
 */

interface DbInterface
{
    /**
     * Supported database engine : MySQL.
     */
    const MYSQL = 'mysql';

    /**
     * Load filters keys.
     */
    const FILTER_CONDITIONS = 'conditions';
    const FILTER_LIMIT      = 'limit';
    const FILTER_ORDER      = 'order';

    /**
     * Order RANDOM keyword.
     */
    const ORDER_RAND = 'RAND';

    public function setConnection($pHost, $pName, $pUser = NULL, $pPassword = NULL);
    public function getConnection();
    public function incrementCounter();
    public function countQueries();
    public function connect($pHost, $pDb, $pUser = NULL, $pPass = NULL);
    public function listCollections(array $pWithFields = array());
}
