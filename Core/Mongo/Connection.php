<?php
namespace Agl\Core\Mongo;

/**
 * Specific database connection management.
 *
 * @category Agl_Core
 * @package Agl_Core_Mongo
 * @version 0.1.0
 */

class Connection
    extends \Agl\Core\Db\Connection\ConnectionAbstract
        implements \Agl\Core\Db\Connection\ConnectionInterface
{
    /**
     * Store the database resource.
     *
     * @var MongoDB
     */
    private $_resourceDb = NULL;

    /**
     * Close the database connection.
     *
     * We don't close the connection here because we need it if the session
     * is stored in database.
     */
    /*public function __destruct()
    {
        if (is_object($this->_connection)) {
            $this->_connection->close();
        }
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
            if ($pUser !== NULL and $pPass !== NULL) {
                $this->_connection = new \Mongo('mongodb://' . $pUser . ':' . $pPass . '@' . $pHost);
            } else {
                $this->_connection = new \Mongo('mongodb://' . $pHost);
            }
        } catch(\MongoConnectionException $e) {
            throw new \Agl\Exception("Unable to establish a connection to MongoDB: Host '$pHost', DB '$pDb', User '$pUser', Password '$pPass' with error '" . $e->getMessage() . "'");
        }

        $this->_resourceDb = $this->_connection->selectDB($pDb);
    }

    /**
     * Return the stored database resource.
     *
     * @return MongoDB
     */
    public function getResourceDb()
    {
        return $this->_resourceDb;
    }

    /**
     * List all the database's collections.
     *
     * @return array
     */
    public function listCollections()
    {
        if ($this->_resourceDb instanceof \MongoDB) {
            return $this->_resourceDb->listCollections();
        }

        return array();
    }
}
