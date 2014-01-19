<?php
namespace Agl\Core\Db\Collection;

use \Agl\Core\Agl,
	\Agl\Core\Db\DbInterface,
	\Agl\Core\Mysql\Collection as MysqlCollection,
	\Exception;

/**
 * Factory - implement the collection class corresponding to the application
 * database engine.
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Collection
 * @version 0.1.0
 */

switch(Agl::app()->getConfig('main/db/engine')) {
    case DbInterface::MYSQL:
        class Collection extends MysqlCollection { }
        break;
    default:
        throw new Exception("DB Engine '" . Agl::app()->getConfig('main/db/engine') . "' is not allowed");
}
