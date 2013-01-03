<?php
namespace Agl\Core\Db\Query\Count;

/**
 * Factory - implement the count class corresponding to the application
 * database engine.
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Query_Count
 * @version 0.1.0
 */

switch(\Agl::app()->getConfig('@app/db/engine')) {
    case \Agl\Core\Db\Connection\ConnectionInterface::MONGODB:
        class Count extends \Agl\Core\Mongo\Query\Count { }
        break;
    case \Agl\Core\Db\Connection\ConnectionInterface::MYSQL:
        class Count extends \Agl\Core\Mysql\Query\Count { }
        break;
    default:
        throw new \Exception("DB Engine '" . \Agl::app()->getConfig('@app/db/engine') . "' is not allowed");
}
