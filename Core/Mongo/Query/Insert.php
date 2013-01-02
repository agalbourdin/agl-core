<?php
namespace Agl\Core\Mongo\Query;

/**
 * Insert an item in the database.
 *
 * @category Agl_Core
 * @package Agl_Core_Mongo_Query
 * @version 0.1.0
 */

class Insert
    extends \Agl\Core\Db\Query\Insert\InsertAbstract
        implements \Agl\Core\Db\Query\Insert\InsertInterface
{
    /**
     * Check if there is insertion errors and throw Exceptions if necessary.
     *
     * @param array $result The return value of the MongoDB insert
     */
    private function _checkCommitResult(array $result)
    {
        if (! $result['ok'] and $result['errmsg']) {
            throw new \Exception("Insert failed to MongoDB collection '$this->_dbContainer' with error message '" . $result['errmsg'] . "'");
        } else if (! $result['ok']) {
            throw new \Exception("Insert failed to MongoDB collection '$this->_dbContainer' with no error message");
        }
    }

    /**
     * Commit the insertion to MongoDB and check the query result.
     *
     * @return bool
     */
    public function commit()
    {
        try {
            $result = \Agl::app()->getDb()->getResourceDb()->{$this->_dbContainer}->insert($this->_fields, array(
                'safe' => true
            ));
            $this->_checkCommitResult($result);

            if (\Agl::app()->isDebugMode()) {
                \Agl::app()->getDb()->incrementCounter();
            }
        } catch (\MongoCursorException $e) {
            throw new \Exception("Insert (safe mode) failed to MongoDB collection '$this->_dbContainer' with message '" . $e->getMessage() . "'");
        } catch (\Exception $e) {
            throw new \Exception($e);
        }

        return true;
    }
}
