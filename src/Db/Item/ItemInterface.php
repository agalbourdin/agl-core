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
    public function load($pArgs = array());
    public function setId($pValue);
    public function getIdField($pDbContainer = NULL);
    public function getFields();
    public function getOrigFields();
    public function getFieldValue($pField);
    public function getOrigFieldValue($pField);
    public function getDbContainer();
    public function save($pConditions = NULL);
    public function delete($pWithChilds = false);
    public function getParents($pDbContainer, array $pArgs = array(), $pFirst = false);
    public function getParent($pDbContainer, array $pArgs = array());
    public function getChilds($pDbContainer, array $pArgs = array(), $pFirst = false);
    public function getChild($pDbContainer, array $pArgs = array());
    public function addParent(\Agl\Core\Db\Item\ItemAbstract $pItem);
    public function removeParent(\Agl\Core\Db\Item\ItemAbstract $pItem);
    public function removeChilds($pDbContainer = NULL);
}
