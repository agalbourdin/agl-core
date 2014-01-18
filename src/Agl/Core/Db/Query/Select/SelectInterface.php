<?php
namespace Agl\Core\Db\Query\Select;

use \Agl\Core\Db\Query\Conditions\Conditions;

/**
 * Interface - Select
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Query_Select
 * @version 0.1.0
 */

interface SelectInterface
{
    /**
     * Order RANDOM keyword.
     */
    const ORDER_RAND = 'RAND';

    public function __construct($pDbContainer);
    public function addFields(array $pFields);
    public function addOrder(array $pFields);
    public function loadConditions(Conditions $pConditions);
    public function limit($pNb);
    public function skip($pNb);
    public function find();
    public function findOne();
    public function count();
    public function fetchAll();
    public function closeCursor();
}
