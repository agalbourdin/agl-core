<?php
namespace Agl\Core\Db\Query\Delete;

use \Agl\Core\Db\Item\ItemAbstract;

/**
 * Interface - Delete
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Query_Delete
 * @version 0.1.0
 */

interface DeleteInterface
{
    public function __construct(ItemAbstract $pItem);
    public function commit($pWithChilds = false);
}
