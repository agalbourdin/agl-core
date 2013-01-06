<?php
namespace Agl\Core\Db\Query\Update;

use \Agl\Core\Db\Query\Conditions\Conditions,
	\Agl\Core\Db\Item\Item;

/**
 * Interface - Update
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Query_Update
 * @version 0.1.0
 */

interface UpdateInterface
{
    public function __construct(Item $pItem);
    public function loadConditions(Conditions $pConditions);
    public function commit();
}
