<?php
namespace Agl\Core\Db;

use \Agl\Core\Agl,
	\Agl\Core\Mysql\Db as MysqlDb,
	\Exception;

/**
 * Factory - implement the database class corresponding to the application
 * database engine.
 *
 * @category Agl_Core
 * @package Agl_Core_Db
 * @version 0.1.0
 */

switch(Agl::app()->getConfig('main/db/engine')) {
    case DbInterface::MYSQL:
        class Db extends MysqlDb { }
        break;
    default:
        throw new Exception("DB Engine '" . Agl::app()->getConfig('main/db/engine') . "' is not allowed");
}
