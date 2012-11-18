<?php
namespace Agl\Core\Mongo\Query;

/**
 * Create and commit a count query to the database.
 *
 * @category Agl_Core
 * @package Agl_Core_Mongo_Query
 * @version 0.1.0
 */

class Count
    extends \Agl\Core\Db\Query\Count\CountAbstract
        implements \Agl\Core\Db\Query\Count\CountInterface
{
    /**
     * Commit the count query to the database and return the result.
     *
     * @return array
     */
    public function commit()
    {
        try {
            $result = \Agl::app()->getDb()->getResourceDb()->{$this->_dbContainer}->count($this->_conditions->toArray(), $this->_limit);
            if (! is_int($result)) {
                throw new \Agl\Exception("The count result must be an integer");
            }

            if (\Agl::app()->isDebugMode()) {
                \Agl::app()->getDb()->incrementCounter();
            }

            return $result;
        } catch (\Exception $e) {
            throw new \Agl\Exception($e);
        }
    }
}
