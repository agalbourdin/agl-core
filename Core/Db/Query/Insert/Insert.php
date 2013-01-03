<?php
namespace Agl\Core\Db\Query\Insert;

/**
 * Factory - implement the insert class corresponding to the application
 * database engine.
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Query_Insert
 * @version 0.1.0
 */

switch(\Agl::app()->getConfig('@app/db/engine')) {
    case \Agl\Core\Db\Connection\ConnectionInterface::MYSQL:
        class Insert extends \Agl\Core\Mysql\Query\Insert { }
        break;
    default:
        throw new \Exception("DB Engine '" . \Agl::app()->getConfig('@app/db/engine') . "' is not allowed");
}
