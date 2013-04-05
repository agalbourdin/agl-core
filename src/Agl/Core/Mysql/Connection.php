<?php
namespace Agl\Core\Mysql;

use \Agl\Core\Agl,
    \Agl\Core\Db\Connection\ConnectionAbstract,
    \Agl\Core\Db\Connection\ConnectionInterface,
    \Exception,
    \PDO,
    \PDOException;

/**
 * Specific database connection management.
 *
 * @category Agl_Core
 * @package Agl_Core_Mysql
 * @version 0.1.0
 */

class Connection
    extends ConnectionAbstract
        implements ConnectionInterface
{
    /**
     * Close the database connection.
     *
     * We don't close the connection here because we need it if the session
     * is stored in database.
     */
    /*public function __destruct()
    {
        $connection = NULL;
    }*/

    /**
     * Establish the database connection.
     *
     * @param string $pHost Database host
     * @param string $pDb Database name
     * @param string $pUser Database user
     * @param string $pPass Database password
     * @return PDO
     */
    public function connect($pHost, $pDb, $pUser = NULL, $pPass = NULL)
    {
        try {
            if ($pUser !== NULL and $pPass !== NULL and is_string($pUser) and is_string($pPass)) {
                $connection = new PDO("mysql:host=$pHost;dbname=$pDb", $pUser, $pPass);
            } else {
                $connection = new PDO("mysql:host=$pHost;dbname=$pDb");
            }
        } catch(PDOException $e) {
            throw new Exception("Unable to establish a connection to MySQL: Host '$pHost', DB '$pDb', User '$pUser', Password '$pPass' with message '" . $e->getMessage() . "'");
        }

        $connection->query("SET NAMES 'utf8';");

        return $connection;
    }

    /**
     * List all the database's collections.
     *
     * @param $pWithJoinTo Item DB container if we want to only get a list of
     * tables with joins to a DB container.
     * @return array
     */
    public function listCollections($pWithJoinTo = NULL)
    {
        $tables = array();

        $prepared = $this->getConnection()->prepare('SHOW TABLES FROM `' . $this->_dbName . '`');
        if ($prepared->execute()) {
            while ($row = $prepared->fetchObject()) {
                $table = str_replace($this->getDbPrefix(), '', current($row));

                if ($pWithJoinTo !== NULL) {
                    $preparedJoin = $this->getConnection()->prepare('SHOW COLUMNS FROM `' . current($row) . '` LIKE "' . $table . \Agl\Core\Db\Item\Item::PREFIX_SEPARATOR . \Agl\Core\Db\Item\Item::JOINS_FIELD_PREFIX . $pWithJoinTo . '"');
                    if ($preparedJoin->execute() and $preparedJoin->rowCount()) {
                        $tables[] = $table;
                    }
                } else {
                    $tables[] = $table;
                }
            }

        }

        return $tables;
    }
}
