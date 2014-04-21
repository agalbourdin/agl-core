<?php
namespace Agl\Core\Mysql\Query;

use \Agl\Core\Db\Query\Conditions\ConditionsAbstract,
    \Agl\Core\Db\Query\Conditions\ConditionsInterface,
    \Agl\Core\Db\Item\ItemInterface;

/**
 * Create a group of conditions.
 *
 * @category Agl_Core
 * @package Agl_Core_Mysql_Conditions
 * @version 0.1.0
 */

class Conditions
    extends ConditionsAbstract
        implements ConditionsInterface
{
    /**
     * Accepted conditions types
     */
    const EQ      = '%s = %s';
    const NOTEQ   = '%s != %s';
    const LT      = '%s < %s';
    const LTEQ    = '%s <= %s';
    const GT      = '%s > %s';
    const GTEQ    = '%s >= %s';
    const IN      = '%s IN (%s)';
    const NOTIN   = '%s NOT IN (%s)';
    const REGEX   = '%s REGEXP "%s"';
    const NULL    = '%s IS NULL';
    const NOTNULL = '%s IS NOT NULL';
    const INSET   = 'FIND_IN_SET(%s, %s)';
    const LIKE    = '%s LIKE %s';

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
        $escapedField = '`' . $pField . '`';

        switch ($pType) {
            case self::IN:
            case self::NOTIN:
                if (is_array($pValue)) {
                    $markers = '?' . str_repeat(', ?', count($pValue) - 1);
                    return sprintf($pType, $escapedField, $markers);
                }
                break;
            case self::INSET:
                return sprintf($pType, '?', $escapedField);
                break;
            default:
                if ($pValue !== NULL) {
                    return sprintf($pType, $escapedField, '?');
                } else {
                    return sprintf($pType, $escapedField);
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
                $pPrepared[] = (string)$subValue;
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
                                        $prefixedField = (stripos($subField, ItemInterface::PREFIX_SEPARATOR . ItemInterface::IDFIELD) !== false) ? $subField : $pDbContainer . ItemInterface::PREFIX_SEPARATOR . $subField;
                                        $subPrepared[] = $this->_processTypeCondition($prefixedField, $type, $value);
                                    }
                                } else {
                                    $prefixedField = (stripos($subField, ItemInterface::PREFIX_SEPARATOR . ItemInterface::IDFIELD) !== false) ? $subField : $pDbContainer . ItemInterface::PREFIX_SEPARATOR . $subField;
                                    $subPrepared[] = '`' . $prefixedField . '` = ?';
                                }
                            }
                        } else {
                            $prefixedField = (stripos($field, ItemInterface::PREFIX_SEPARATOR . ItemInterface::IDFIELD) !== false) ? $field : $pDbContainer . ItemInterface::PREFIX_SEPARATOR . $field;
                            $subPrepared[] = $this->_processTypeCondition($prefixedField, $type, $value);
                        }
                    }
                } else {
                    $prefixedField = (stripos($field, ItemInterface::PREFIX_SEPARATOR . ItemInterface::IDFIELD) !== false) ? $field : $pDbContainer . ItemInterface::PREFIX_SEPARATOR . $field;
                    $prepared[]    = '(`' . $prefixedField . '` = ?)';
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
