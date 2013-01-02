<?php
namespace Agl\Core\Mysql;

/**
 * Specific database connection management.
 *
 * @category Agl_Core
 * @package Agl_Core_Mysql
 * @version 0.1.0
 */

class Connection
    extends \Agl\Core\Db\Connection\ConnectionAbstract
        implements \Agl\Core\Db\Connection\ConnectionInterface
{
    /**
     * Close the database connection.
     *
     * We don't close the connection here because we need it if the session
     * is stored in database.
     */
    /*public function __destruct()
    {
        $this->_connection = NULL;
    }*/

    /**
     * Establish the database connection.
     *
     * @param string $pHost Database host
     * @param string $pDb Database name
     * @param string $pUser Database user
     * @param string $pPass Database password
     */
    public function __construct($pHost, $pDb, $pUser = NULL, $pPass = NULL)
    {
        try {
            if ($pUser !== NULL and $pPass !== NULL and is_string($pUser) and is_string($pPass)) {
                $this->_connection = new \PDO("mysql:host=$pHost;dbname=$pDb", $pUser, $pPass);
            } else {
                $this->_connection = new \PDO("mysql:host=$pHost;dbname=$pDb");
            }
        } catch(\PDOException $e) {
            throw new \Exception("Unable to establish a connection to MySQL: Host '$pHost', DB '$pDb', User '$pUser', Password '$pPass' with message '" . $e->getMessage() . "'");
        }

        $this->_connection->query("SET NAMES 'utf8';");
    }

    /**
     * List all the database's collections.
     *
     * @return array
     */
    public function listCollections()
    {
        $tables = array();

        $prepared = \Agl::app()->getDb()->getConnection()->prepare('SHOW TABLES FROM `' . \Agl::app()->getConfig('@app/db/name') . '`');
        if ($prepared->execute()) {
            while ($row = $prepared->fetchObject()) {
                $tables[] = current($row);
            }

        }

        return $tables;
    }
}
