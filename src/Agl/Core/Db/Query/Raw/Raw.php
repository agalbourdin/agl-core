<?php
namespace Agl\Core\Db\Query\Raw;

use \Agl\Core\Agl,
	\Agl\Core\Db\Connection\ConnectionInterface,
	\Agl\Core\Mysql\Query\Raw as MysqlRaw,
	\Exception;

/**
 * Factory - implement the raw query class corresponding to the application
 * database engine.
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Query_Raw
 * @version 0.1.0
 */

switch(Agl::app()->getConfig('@app/db/engine')) {
    case ConnectionInterface::MYSQL:
        class Raw extends MysqlRaw { }
        break;
    default:
        throw new Exception("DB Engine '" . Agl::app()->getConfig('@app/db/engine') . "' is not allowed");
}
