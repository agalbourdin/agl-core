<?php
namespace Agl\Core\Db\Tree;

/**
 * Interface - Tree
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Tree
 * @version 0.1.0
 */

interface TreeInterface
{
    /**
     * Parent field
     */
    const PARENTFIELD = 'tree_parent';

    /**
     * Ancestor field
     */
    const ANCESTORSFIELD = 'tree_ancestors';

    public function setMainItem(\Agl\Core\Db\Item\Item $pItem);
    public function getMainItem();
    public function hasParent();
    public function hasChilds();
    public function getParent();
    public function loadAncestors($pConditions = NULL, $pLimit = NULL, $pOrder = NULL);
    public function loadDirectChilds($pConditions = NULL, $pLimit = NULL, $pOrder = NULL);
    //public function loadAllChilds($pConditions = NULL, $pLimit = NULL, $pOrder = NULL);
    public function countAncestors();
    public function countChilds();
    public function setParent(\Agl\Core\Db\Item\Item $pItem);
    public function addChild(\Agl\Core\Db\Item\Item $pItem);
    public function toArray($pConditions = NULL, $pLimit = NULL, $pOrder = NULL);
}
