<?php
namespace Agl\Core\Db\Item;

use \Agl\Core\Agl,
	\Agl\Core\Db\DbInterface,
	\Agl\Core\Mysql\Item as MysqlItem,
	\Exception;

/**
 * Factory - implement the item class corresponding to the application database
 * engine.
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Item
 * @version 0.1.0
 */

switch(Agl::app()->getConfig('main/db/engine')) {
    case DbInterface::MYSQL:
        class Item extends MysqlItem { }
        break;
    default:
        throw new Exception("DB Engine '" . Agl::app()->getConfig('main/db/engine') . "' is not allowed");
}
