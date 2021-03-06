<?php
namespace Agl\Core\Db\Query\Delete;

use \Agl\Core\Db\Item\ItemAbstract,
    \Agl\Core\Db\Query\QueryAbstract,
    \Exception;

/**
 * Abstract class - Delete
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Query_Delete
 * @version 0.1.0
 */

abstract class DeleteAbstract
    extends QueryAbstract
{
    /**
     * Item to delete.
     *
     * @var ItemAbstract|null
     */
    protected $_item = NULL;

    /**
     * Prepare the deletion of an item and its childs.
     *
     * @param Item $pItem
     */
    public function __construct(ItemAbstract $pItem)
    {
        if ($pItem->getId()) {
            $this->_item = $pItem;
        } else {
            throw new Exception("Item cannot be deleted, an ID is required");
        }
    }
}
