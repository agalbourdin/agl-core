<?php
namespace Agl\Core\Mysql;

/**
 * A collection is a set of items.
 * Each item is identified by an ID.
 *
 * Each item is loaded on demand, for example when browsing the collection, and
 * erased when the pointer is moved to another item, so the size of the
 * collection is globally constant.
 *
 * @category Agl_Core
 * @package Agl_Core_Mysql
 * @version 0.1.0
 */

class Collection
    extends \Agl\Core\Db\Collection\CollectionAbstract
        implements \Agl\Core\Db\Collection\CollectionInterface
{

}
