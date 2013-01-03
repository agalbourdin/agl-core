<?php
namespace Agl\Core\Db\Id;

/**
 * Factory - implement the id class corresponding to the application
 * database engine.
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Id
 * @version 0.1.0
 */

switch(\Agl::app()->getConfig('@app/db/engine')) {
    case \Agl\Core\Db\Connection\ConnectionInterface::MYSQL:
        class Id extends \Agl\Core\Mysql\Id { }
        break;
    default:
        throw new \Exception("DB Engine '" . \Agl::app()->getConfig('@app/db/engine') . "' is not allowed");
}
