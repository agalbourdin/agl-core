<?php
namespace Agl\Core\Db\Query\Drop;

use \Agl\Core\Agl,
	\Agl\Core\Db\Connection\ConnectionInterface,
	\Agl\Core\Mysql\Query\Drop as MysqlDrop,
	\Exception;

/**
 * Factory - implement the drop class corresponding to the application
 * database engine.
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Query_Drop
 * @version 0.1.0
 */

switch(Agl::app()->getConfig('main/db/engine')) {
    case ConnectionInterface::MYSQL:
        class Drop extends MysqlDrop { }
        break;
    default:
        throw new Exception("DB Engine '" . Agl::app()->getConfig('main/db/engine') . "' is not allowed");
}
