<?php
namespace Agl\Core\Db\Query\Drop;

/**
 * Interface - Drop
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Query_Drop
 * @version 0.1.0
 */

interface DropInterface
{
    public function __construct(\Agl\Core\Db\Collection\Collection $pCollection);
    public function commit();
}
