<?php
namespace Agl\Core\Mysql;

use \Agl\Core\Db\Collection\Collection,
    \Agl\Core\Db\Item\ItemAbstract,
    \Agl\Core\Db\Item\ItemInterface,
    \Agl\Core\Db\Query\Conditions\Conditions,
    \Exception;

/**
 * Management of an item.
 * An item is a Mysql row.
 *
 * This class can create, update or delete an item.
 *
 * @category Agl_Core
 * @package Agl_Core_Mysql
 * @version 0.1.0
 */

class Item
    extends ItemAbstract
        implements ItemInterface
{
    /**
     * Save the item in the database.
     *
     * @return Item
     */
    public function save($pConditions = NULL)
    {
        foreach ($this->_fields as &$value) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }
        }

        return parent::save($pConditions);
    }
}
