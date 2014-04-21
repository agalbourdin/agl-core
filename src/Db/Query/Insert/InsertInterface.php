<?php
namespace Agl\Core\Db\Query\Insert;

/**
 * Interface - Insert
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Query_Insert
 * @version 0.1.0
 */

interface InsertInterface
{
    public function __construct($pDbContainer);
    public function addFields(array $pField);
    public function commit();
}
