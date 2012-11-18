<?php
namespace Agl\Core\Db\Query\Count;

/**
 * Interface - Count
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Query_Count
 * @version 0.1.0
 */

interface CountInterface
{
	const FIELD_NAME     = 'field';
	const FIELD_DISTINCT = 'distinct';

    public function __construct($pDbContainer);
    public function loadConditions(\Agl\Core\Db\Query\Conditions\Conditions $pConditions);
    public function limit($pNb);
    public function setField($pField, $pDistinct = false);
}
