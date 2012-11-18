<?php
namespace Agl\Core\Db\Query\Update;

/**
 * Interface - Update
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Query_Update
 * @version 0.1.0
 */

interface UpdateInterface
{
    public function __construct(\Agl\Core\Db\Item\Item $pItem);
    public function commit();
}
