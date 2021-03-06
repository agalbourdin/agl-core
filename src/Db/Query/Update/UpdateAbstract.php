<?php
namespace Agl\Core\Db\Query\Update;

use \Agl\Core\Db\Query\Conditions\Conditions,
    \Agl\Core\Db\Item\ItemAbstract,
    \Agl\Core\Db\Query\QueryAbstract,
    \Agl\Core\Db\Query\Update\Update,
    \Exception;

/**
 * Abstract class - Update
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Query_Update
 * @version 0.1.0
 */

abstract class UpdateAbstract
    extends QueryAbstract
{
    /**
     * Item to delete.
     *
     * @var ItemAbstract|null
     */
    protected $_item = NULL;

    /**
     * Conditions to filter the query (additionally to the item ID).
     *
     * @var \Agl\Core\Db\Query\Conditions\Conditions
     */
    protected $_conditions = NULL;

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
            throw new Exception("Item cannot be updated, an ID is required");
        }
    }

    /**
     * Return an array of the fields to update (compared to origFields).
     *
     * @return array
     */
    protected function getFieldsToUpdate()
    {
        $toUpdate = array();

        $fields     = $this->_item->getFields();
        $origFields = $this->_item->getOrigFields();
        foreach ($fields as $field => $value) {
            if (! array_key_exists($field, $origFields) or $value != $origFields[$field]) {
                $toUpdate[$field] = $value;
            }
        }

        return $toUpdate;
    }

    /**
     * Return an array of the fields to delete (compared to origFields).
     *
     * @return array
     */
    protected function getFieldsToDelete()
    {
        $toDelete = array();

        $fields     = $this->_item->getFields();
        $origFields = $this->_item->getOrigFields();
        foreach ($origFields as $field => $value) {
            if (! array_key_exists($field, $fields)) {
                $toDelete[$field] = Update::DELETE_VALUE;
            }
        }

        return $toDelete;
    }
}
