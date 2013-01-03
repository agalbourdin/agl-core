<?php
namespace Agl\Core\Db\Query\Raw;

/**
 * Factory - implement the raw query class corresponding to the application
 * database engine.
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Query_Raw
 * @version 0.1.0
 */

switch(\Agl::app()->getConfig('@app/db/engine')) {
    case \Agl\Core\Db\Connection\ConnectionInterface::MYSQL:
        class Raw extends \Agl\Core\Mysql\Query\Raw { }
        break;
    default:
        throw new \Exception("DB Engine '" . \Agl::app()->getConfig('@app/db/engine') . "' is not allowed");
}
