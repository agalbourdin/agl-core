<?php
namespace Agl\Core\Db\Query\Update;

/**
 * Factory - implement the update class corresponding to the application
 * database engine.
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Query_Update
 * @version 0.1.0
 */

switch(\Agl::app()->getConfig('@app/db/engine')) {
    case \Agl\Core\Db\Connection\ConnectionInterface::MYSQL:
        class Update extends \Agl\Core\Mysql\Query\Update { }
        break;
    default:
        throw new \Exception("DB Engine '" . \Agl::app()->getConfig('@app/db/engine') . "' is not allowed");
}
