<?php
namespace Agl\Core\Db\Query\Count;

use \Agl\Core\Agl,
	\Agl\Core\Db\Connection\ConnectionInterface,
	\Agl\Core\Mysql\Query\Count as MysqlCount,
	\Exception;

/**
 * Factory - implement the count class corresponding to the application
 * database engine.
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Query_Count
 * @version 0.1.0
 */

switch(Agl::app()->getConfig('@app/db/engine')) {
    case ConnectionInterface::MYSQL:
        class Count extends MysqlCount { }
        break;
    default:
        throw new Exception("DB Engine '" . Agl::app()->getConfig('@app/db/engine') . "' is not allowed");
}
