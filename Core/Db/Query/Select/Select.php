<?php
namespace Agl\Core\Db\Query\Select;

/**
 * Factory - implement the select class corresponding to the application
 * database engine.
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Query_Select
 * @version 0.1.0
 */

switch(\Agl::app()->getConfig('@app/db/engine')) {
    case \Agl\Core\Db\Connection\ConnectionInterface::MONGODB:
        class Select extends \Agl\Core\Mongo\Query\Select { }
        break;
    case \Agl\Core\Db\Connection\ConnectionInterface::MYSQL:
        class Select extends \Agl\Core\Mysql\Query\Select { }
        break;
    default:
        throw new \Agl\Exception("DB Engine '" . \Agl::app()->getConfig('@app/db/engine') . "' is not allowed");
}
