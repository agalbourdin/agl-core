<?php
namespace Agl\Core\Db\Query\Update;

use \Agl\Core\Agl,
	\Agl\Core\Db\DbInterface,
	\Agl\Core\Mysql\Query\Update as MysqlUpdate,
	\Exception;

/**
 * Factory - implement the update class corresponding to the application
 * database engine.
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Query_Update
 * @version 0.1.0
 */

switch(Agl::app()->getConfig('main/db/engine')) {
    case DbInterface::MYSQL:
        class Update extends MysqlUpdate { }
        break;
    default:
        throw new Exception("DB Engine '" . Agl::app()->getConfig('main/db/engine') . "' is not allowed");
}
