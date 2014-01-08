<?php
namespace Agl\Core\Db\Connection;

use \Agl\Core\Agl,
	\Agl\Core\Db\Connection\ConnectionInterface,
	\Agl\Core\Mysql\Connection as MysqlConnection,
	\Exception;

/**
 * Factory - implement the connection class corresponding to the application
 * database engine.
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Connection
 * @version 0.1.0
 */

switch(Agl::app()->getConfig('main/db/engine')) {
    case ConnectionInterface::MYSQL:
        class Connection extends MysqlConnection { }
        break;
    default:
        throw new Exception("DB Engine '" . Agl::app()->getConfig('main/db/engine') . "' is not allowed");
}
