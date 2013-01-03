<?php
namespace Agl\Core\Db\Query\Delete;

/**
 * Factory - implement the delete class corresponding to the application
 * database engine.
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Query_Delete
 * @version 0.1.0
 */

switch(\Agl::app()->getConfig('@app/db/engine')) {
    case \Agl\Core\Db\Connection\ConnectionInterface::MONGODB:
        class Delete extends \Agl\Core\Mongo\Query\Delete { }
        break;
    case \Agl\Core\Db\Connection\ConnectionInterface::MYSQL:
        class Delete extends \Agl\Core\Mysql\Query\Delete { }
        break;
    default:
        throw new \Exception("DB Engine '" . \Agl::app()->getConfig('@app/db/engine') . "' is not allowed");
}
