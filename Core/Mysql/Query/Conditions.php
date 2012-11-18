<?php
namespace Agl\Core\Mysql\Query;

/**
 * Create a group of conditions.
 *
 * @category Agl_Core
 * @package Agl_Core_Mysql_Conditions
 * @version 0.1.0
 */

class Conditions
    extends \Agl\Core\Db\Query\Conditions\ConditionsAbstract
        implements \Agl\Core\Db\Query\Conditions\ConditionsInterface
{
    /**
     * Accepted conditions types
     */
    const EQUAL    = '%s = %s';
    const NOTEQUAL = '%s != %s';
    const LT       = '%s < %s';
    const LTEQ     = '%s <= %s';
    const GT       = '%s > %s';
    const GTEQ     = '%s >= %s';
    const IN       = '%s IN (%s)';
    const NOTIN    = '%s NOT IN (%s)';
    const REGEX    = '%s REGEXP "%s"';
    const NULL     = '%s IS NULL';
    const NOTNULL  = '%s IS NOT NULL';
    const INSET    = 'FIND_IN_SET(%s, %s)';

    /**
     * Accepted group types.
     */
    const TYPE_AND = 'AND';
    const TYPE_OR  = 'OR';

    /**
     * Prepare the conditions for PDO.
     *
     * @param string $pField
     * @param string $pType
     * @param string $pValue
     * @return string
     */
    private function _processTypeCondition($pField, $pType, $pValue)
    {
        switch ($pType) {
            case self::IN:
                if (is_array($pValue)) {
                    $markers = '?' . str_repeat(', ?', count($pValue) - 1);
                    return sprintf($pType, $pField, $markers);
                }
                break;
            case self::INSET:
                return sprintf($pType, $pValue, $pField);
                break;
            default:
                if ($pValue !== NULL) {
                    return sprintf($pType, $pField, $pValue);
                } else {
                    return sprintf($pType, $pField);
                }
        }
    }

    /**
     * Prepare the values for PDO.
     *
     * @param string $pType
     * @param string $pValue
     * @param string $pPrepared
     */
    private function _processTypeValue($pType, $pValue, &$pPrepared)
    {
        if (is_array($pValue)) {
            foreach($pValue as $subValue) {
                $pPrepared[] = $subValue;
            }
        } else if ($pValue !== NULL) {
            $pPrepared[] = $pValue;
        }
    }

    /**
     * Return a PDO prepared string representation of the conditions.
     *
     * @return string
     */
    public function getPreparedConditions($pDbContainer)
    {
        $prepared = array();
        if (! empty($this->_conditions)) {
            foreach ($this->_conditions as $field => $condition) {
                $subPrepared = array();
                if (is_array($condition)) {
                    foreach ($condition as $type => $value) {
                        if (is_int($type) and is_array($value)) {
                            foreach ($value as $subField => $subCondition) {
                                if (is_array($subCondition)) {
                                    foreach ($subCondition as $type => $value) {
                                        $subPrepared[] = $this->_processTypeCondition($pDbContainer . \Agl\Core\Db\Item\ItemInterface::PREFIX_SEPARATOR . $subField, $type, $value);
                                    }
                                } else {
                                    $subPrepared[] = '`' . $pDbContainer . \Agl\Core\Db\Item\ItemInterface::PREFIX_SEPARATOR . $subField . '` = ?';
                                }
                            }
                        } else {
                            $subPrepared[] = $this->_processTypeCondition($pDbContainer . \Agl\Core\Db\Item\ItemInterface::PREFIX_SEPARATOR . $field, $type, $value);
                        }
                    }
                } else {
                    $prepared[] = '`' . $pDbContainer . \Agl\Core\Db\Item\ItemInterface::PREFIX_SEPARATOR . $field . '` = ?';
                }

                if (! empty($subPrepared)) {
                    $prepared[] = '(' . implode(' ' . $this->getSubType() . ' ', $subPrepared) . ')';
                }
            }
        }

        return implode(' ' . $this->getType() . ' ', $prepared);
    }

    /**
     * Return a PDO array with the prepared values.
     *
     * @return array
     */
    public function getPreparedValues()
    {
        $prepared = array();
        if (! empty($this->_conditions)) {
            foreach ($this->_conditions as $condition) {
                if (is_array($condition)) {
                    foreach ($condition as $type => $value) {
                        if (is_int($type) and is_array($value)) {
                            foreach ($value as $subCondition) {
                                if (is_array($subCondition)) {
                                    foreach ($subCondition as $type => $value) {
                                        $this->_processTypeValue($type, $value, $prepared);
                                    }
                                } else {
                                   $prepared[] = $subCondition;
                                }
                            }
                        } else {
                            $this->_processTypeValue($type, $value, $prepared);
                        }
                    }
                } else {
                    $prepared[] = $condition;
                }
            }
        }

        return $prepared;
    }
}
