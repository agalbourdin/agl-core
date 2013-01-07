<?php
namespace Agl\Core\Db\Id;

use \Agl\Core\Agl,
	\Agl\Core\Db\Connection\ConnectionInterface,
	\Agl\Core\Mysql\Id as MysqlId,
	\Exception;

/**
 * Factory - implement the id class corresponding to the application
 * database engine.
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Id
 * @version 0.1.0
 */

switch(Agl::app()->getConfig('@app/db/engine')) {
    case ConnectionInterface::MYSQL:
        class Id extends MysqlId { }
        break;
    default:
        throw new Exception("DB Engine '" . Agl::app()->getConfig('@app/db/engine') . "' is not allowed");
}
