<?php
namespace Agl\Core\Db\Query\Conditions;

/**
 * Factory - implement the conditions class corresponding to the application
 * database engine.
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Query_Conditions
 * @version 0.1.0
 */

switch(\Agl::app()->getConfig('@app/db/engine')) {
    case \Agl\Core\Db\Connection\ConnectionInterface::MYSQL:
        class Conditions extends \Agl\Core\Mysql\Query\Conditions { }
        break;
    default:
        throw new \Exception("DB Engine '" . \Agl::app()->getConfig('@app/db/engine') . "' is not allowed");
}
