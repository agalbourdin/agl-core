<?php
namespace Agl\Core\Db\Query\Conditions;

use \Agl\Core\Db\Item\ItemInterface,
    \Agl\Core\Db\Id\Id,
    \Exception;

/**
 * Abstract class - Conditions
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Query_Conditions
 * @version 0.1.0
 */

abstract class ConditionsAbstract
{
    /**
     * The conditions are saved in this array.
     *
     * @var array
     */
    protected $_conditions = array();

    /**
     * Conditions type (AND / OR).
     *
     * @var string
     */
    private $_type = NULL;

    /**
     * Set the conditions type.
     *
     * @param $pType NULL|string
     */
    public function __construct($pType = NULL)
    {
        if ($pType === NULL) {
            $pType = static::TYPE_AND;
        }

        if ($pType !== static::TYPE_AND and $pType !== static::TYPE_OR) {
            throw new Exception("Condition type unknown");
        }

        $this->_type = $pType;
    }

    /**
     * Add a new condition.
     *
     * @param string $pField Field to filter
     * @param string $pType Type of condition
     * @param mixed $pValue The value to filter
     * @return ConditionsAbstract
     */
    public function add($pField, $pType, $pValue = NULL)
    {
        if ($pField == ItemInterface::IDFIELD) {
            if (! is_array($pValue) and ! $pValue instanceof Id) {
                $id     = new Id($pValue);
                $pValue = $id->getOrig();
            } else if (is_array($pValue)) {
                if (empty($pValue)) {
                    throw new Exception("Trying to set an invalid condition value (`$pField`)");
                }

                foreach ($pValue as &$value) {
                    if (! $value instanceof Id) {
                        $id    = new Id($value);
                        $value = $id->getOrig();
                    }
                }
            }
        }

        if ($pType !== static::IN and $pType !== static::NOTIN) {
            $pValue = (string)$pValue;
        } else if (($pType === static::IN or $pType === static::NOTIN) and ! is_array($pValue)) {
            throw new Exception("Trying to set an invalid condition value (`$pField`)");
        }

        if (static::EQ == $pType) {
            $this->_conditions[$pField] = $pValue;
        } else {
            $this->_conditions[$pField] = array(
                $pType => $pValue
            );
        }

        return $this;
    }

    /**
     * Add a group of conditions. An unlimited number of array of conditions
     * (field, type and optional value) could be passed.
     *
     * @return ConditionsAbstract
     * @todo $condition[2] if not exists
     */
    public function addGroup()
    {
        $conditions = func_get_args();
        $arr        = array();

        foreach ($conditions as $condition) {
            if (is_array($condition) and count($condition) >= 2) {
                $this->add($condition[0], $condition[1], $condition[2]);

                end($this->_conditions);
                $arr[] = array(key($this->_conditions) => current($this->_conditions));
                array_pop($this->_conditions);
            }
        }

        $this->_conditions[] = $arr;

        return $this;
    }

    /**
     * Return the conditions as array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->_conditions;
    }

    /**
     * Return the conditions type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Return the sub conditions type.
     *
     * @return string
     */
    public function getSubType()
    {
        return ($this->_type == static::TYPE_AND) ? static::TYPE_OR : static::TYPE_AND;
    }

    /**
     * Get the number of conditions.
     *
     * @return int
     */
    public function count()
    {
        return count($this->_conditions);
    }
}
