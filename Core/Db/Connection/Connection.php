<?php
namespace Agl\Core\Db\Connection;

/**
 * Factory - implement the connection class corresponding to the application
 * database engine.
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Connection
 * @version 0.1.0
 */

switch(\Agl::app()->getConfig('@app/db/engine')) {
    case \Agl\Core\Db\Connection\ConnectionInterface::MONGODB:
        class Connection extends \Agl\Core\Mongo\Connection { }
        break;
    case \Agl\Core\Db\Connection\ConnectionInterface::MYSQL:
        class Connection extends \Agl\Core\Mysql\Connection { }
        break;
    default:
        throw new \Agl\Exception("DB Engine '" . \Agl::app()->getConfig('@app/db/engine') . "' is not allowed");
}
