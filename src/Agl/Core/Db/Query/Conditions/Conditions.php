<?php
namespace Agl\Core\Db\Query\Conditions;

use \Agl\Core\Agl,
	\Agl\Core\Db\Connection\ConnectionInterface,
	\Agl\Core\Mysql\Query\Conditions as MysqlConditions,
	\Exception;

/**
 * Factory - implement the conditions class corresponding to the application
 * database engine.
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Query_Conditions
 * @version 0.1.0
 */

switch(Agl::app()->getConfig('main/db/engine')) {
    case ConnectionInterface::MYSQL:
        class Conditions extends MysqlConditions { }
        break;
    default:
        throw new Exception("DB Engine '" . Agl::app()->getConfig('main/db/engine') . "' is not allowed");
}
