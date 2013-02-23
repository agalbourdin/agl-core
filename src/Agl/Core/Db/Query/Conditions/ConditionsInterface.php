<?php
namespace Agl\Core\Db\Query\Conditions;

/**
 * Interface - Conditions
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Query_Conditions
 * @version 0.1.0
 */

interface ConditionsInterface
{
	/**
     * Accepted group types.
     */
    const TYPE_AND = 'AND';
    const TYPE_OR  = 'OR';

	public function __construct($pType = NULL);
    public function add($pField, $pType, $pValue = NULL);
    public function addGroup();
    public function toArray();
    public function getType();
    public function getSubType();
    public function count();
}
