<?php
namespace Agl\Core\Db\Connection;

/**
 * Interface - Connection
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Connection
 * @version 0.1.0
 */

interface ConnectionInterface
{
    /**
     * Supported database engine : MySQL.
     */
    const MYSQL = 'mysql';

    public function __construct($pHost, $pDb, $pUser = NULL, $pPass = NULL);
    public static function orderAsc();
    public static function orderDesc();
    public function getConnection();
    public function incrementCounter();
    public function countQueries();
}
