<?php
namespace Agl\Core\Mongo\Query;

/**
 * Drop a collection (and its items and childs) from the database.
 *
 * @category Agl_Core
 * @package Agl_Core_Mongo_Query
 * @version 0.1.0
 */

class Drop
    extends \Agl\Core\Db\Query\Drop\DropAbstract
        implements \Agl\Core\Db\Query\Drop\DropInterface
{
    /**
     * Check if there is drop errors and throw Exceptions if necessary.
     *
     * @param array $result The return value of the MongoDB drop query
     */
    private function _checkCommitResult(array $result)
    {
        if (! $result['ok'] and $result['errmsg']) {
            throw new \Exception("Delete failed to MongoDB collection '" . $this->_prefix . $this->_collection->getDbContainer() . "' with error message '" . $result['errmsg'] . "'");
        } else if (! $result['ok']) {
            throw new \Exception("Delete failed to MongoDB collection '" . $this->_prefix . $this->_collection->getDbContainer() . "' with no error message");
        }
    }

    /**
     * Commit the deletion to MongoDB and check the query result.
     *
     * @return bool
     */
    public function commit()
    {
        try {
            $this->_collection->removeJoinsFromAllCollections();

            $result = \Agl::app()->getDb()->getResourceDb()->{$this->_prefix . $this->_collection->getDbContainer()}->drop();
            $this->_checkCommitResult($result);

            if (\Agl::app()->isDebugMode()) {
                \Agl::app()->getDb()->incrementCounter();
            }
        } catch (\MongoCursorException $e) {
            throw new \Exception("Insert (safe mode) failed to MongoDB collection '" . $this->_prefix . $this->_collection->getDbContainer() . "' with message '" . $e->getMessage() . "'");
        } catch (\Exception $e) {
            throw new \Exception($e);
        }

        return true;
    }
}
