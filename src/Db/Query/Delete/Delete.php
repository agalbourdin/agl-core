<?php
namespace Agl\Core\Db\Query\Delete;

use \Agl\Core\Agl,
	\Agl\Core\Db\DbInterface,
	\Agl\Core\Mysql\Query\Delete as MysqlDelete,
	\Exception;

/**
 * Factory - implement the delete class corresponding to the application
 * database engine.
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Query_Delete
 * @version 0.1.0
 */

switch(Agl::app()->getConfig('main/db/engine')) {
    case DbInterface::MYSQL:
        class Delete extends MysqlDelete { }
        break;
    default:
        throw new Exception("DB Engine '" . Agl::app()->getConfig('main/db/engine') . "' is not allowed");
}
