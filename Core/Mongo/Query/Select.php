<?php
namespace Agl\Core\Mongo\Query;

/**
 * Create and commit a select query to the database.
 *
 * @category Agl_Core
 * @package Agl_Core_Mongo_Query
 * @version 0.1.0
 */

class Select
    extends \Agl\Core\Db\Query\Select\SelectAbstract
        implements \Agl\Core\Db\Query\Select\SelectInterface
{
    /**
     * Order ASC keyword.
     */
    const ORDER_ASC = 1;

    /**
     * Order DESC keyword.
     */
    const ORDER_DESC = -1;

    /**
     * Commit the select query to the database and return the result cursor.
     *
     * @return \MongoCursor|array
     */
    public function find()
    {
        try {
            $cursor = \Agl::app()->getDb()->getResourceDb()->{$this->_dbContainer}->find($this->_conditions->toArray(), $this->_fields);
            if (! $cursor instanceof \MongoCursor) {
                throw new \Agl\Exception("Result must be an object of type MongoCursor");
            }

            if (! empty($this->_order)) {
                $cursor->sort($this->_order);
            }

            if ($this->_limit !== NULL) {
                $cursor->limit($this->_limit);
            }

            if ($this->_skip !== NULL) {
                $cursor->skip($this->_skip);
            }

            if (\Agl::app()->isDebugMode()) {
                \Agl::app()->getDb()->incrementCounter();
            }

            return $cursor;
        } catch (\Exception $e) {
            throw new \Agl\Exception($e);
        }
    }

    /**
     * Commit the select query to the database and return one result.
     *
     * @return array
     */
    public function findOne()
    {
        try {
            $result = \Agl::app()->getDb()->getResourceDb()->{$this->_dbContainer}->findOne($this->_conditions->toArray(), $this->_fields);
            if ($result === NULL) {
                return array();
            } else if (! is_array($result)) {
                throw new \Agl\Exception("Result must be an array");
            }

            if (! empty($this->_order)) {
                $result->sort($this->_order);
            }

            if ($this->_limit !== NULL) {
                $result->limit($this->_limit);
            }

            if ($this->_skip !== NULL) {
                $result->skip($this->_skip);
            }

            if (\Agl::app()->isDebugMode()) {
                \Agl::app()->getDb()->incrementCounter();
            }

            if (array_key_exists(\Agl\Core\Db\Item\ItemInterface::IDFIELD, $result)) {
                $result[\Agl\Core\Db\Item\ItemInterface::IDFIELD] = new \Agl\Core\Db\Id\Id($result[\Agl\Core\Db\Item\ItemInterface::IDFIELD]);
            }

            return $result;
        } catch (\Exception $e) {
            throw new \Agl\Exception($e);
        }
    }
}
