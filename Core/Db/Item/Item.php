<?php
namespace Agl\Core\Db\Item;

/**
 * Factory - implement the item class corresponding to the application database
 * engine.
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Item
 * @version 0.1.0
 */

switch(\Agl::app()->getConfig('@app/db/engine')) {
    case \Agl\Core\Db\Connection\ConnectionInterface::MONGODB:
        class Item extends \Agl\Core\Mongo\Item { }
        break;
    case \Agl\Core\Db\Connection\ConnectionInterface::MYSQL:
        class Item extends \Agl\Core\Mysql\Item { }
        break;
    default:
        throw new \Exception("DB Engine '" . \Agl::app()->getConfig('@app/db/engine') . "' is not allowed");
}
