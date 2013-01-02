<?php
namespace Agl\Core\Db\Query\Delete;

/**
 * Abstract class - Delete
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Query_Delete
 * @version 0.1.0
 */

abstract class DeleteAbstract
    extends \Agl\Core\Db\Query\QueryAbstract
{
    /**
     * Item to delete.
     *
     * @var \Agl\Core\Db\Item\Item
     */
    protected $_item = NULL;

    /**
     * Prepare the deletion of an item and its childs.
     *
     * @param Item $pItem
     */
    public function __construct(\Agl\Core\Db\Item\Item $pItem)
    {
        if ($pItem->getId()) {
            $this->_item = $pItem;
        } else {
            throw new \Exception("Item cannot be deleted, an ID is required");
        }

        $this->_setDbPrefix();
    }
}
