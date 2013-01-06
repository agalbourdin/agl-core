<?php
namespace Agl\Core\Db\Item;

/**
 * Interface - Item
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Item
 * @version 0.1.0
 */

interface ItemInterface
{
    /**
     * The field containing the item ID
     */
    const IDFIELD = 'id';

    /**
     * Date add field
     */
    const DATEADDFIELD = 'date_add';

    /**
     * Date update field
     */
    const DATEUPDATEFIELD = 'date_update';

    /**
     * Field prefix separator.
     */
    const PREFIX_SEPARATOR = '_';

    /**
     * The field containing the item joins.
     */
    const JOINS_FIELD_PREFIX = 'joins_';

    public function __construct($pDbContainer, array $pFields = array());
    public function __call($pMethod, array $pArgs);
    public function __get($pVar);
    public function __set($pVar, $pValue);
    public function loadById($pId);
    public function load($pConditions = NULL, $pOrder = NULL);
    public function setId($pValue);
    public function getIdField();
    public function getFields();
    public function getOrigFields();
    public function getField($pField);
    public function getOrigField($pField);
    public function getDbContainer();
    public function save($pConditions = NULL);
    public function delete();
    public function addParent(\Agl\Core\Db\Item\Item $pItem);
    public function removeParent(\Agl\Core\Db\Item\Item $pItem);
    public function getParents($pDbContainer, $pLimit = NULL, $pOrder = NULL);
    public function removeJoinFromAllChilds();
    public function getChilds($pDbContainer, $pLimit = NULL, $pOrder = NULL);
}
