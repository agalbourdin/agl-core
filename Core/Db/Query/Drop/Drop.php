<?php
namespace Agl\Core\Db\Query\Drop;

/**
 * Factory - implement the drop class corresponding to the application
 * database engine.
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Query_Drop
 * @version 0.1.0
 */

switch(\Agl::app()->getConfig('@app/db/engine')) {
    case \Agl\Core\Db\Connection\ConnectionInterface::MONGODB:
        class Drop extends \Agl\Core\Mongo\Query\Drop { }
        break;
    case \Agl\Core\Db\Connection\ConnectionInterface::MYSQL:
        class Drop extends \Agl\Core\Mysql\Query\Drop { }
        break;
    default:
        throw new \Exception("DB Engine '" . \Agl::app()->getConfig('@app/db/engine') . "' is not allowed");
}
