<?php
namespace Agl\Core\Mongo\Query;

/**
 * Delete an item (and its childs) from the database.
 *
 * @category Agl_Core
 * @package Agl_Core_Mongo_Query
 * @version 0.1.0
 */

class Delete
    extends \Agl\Core\Db\Query\Delete\DeleteAbstract
        implements \Agl\Core\Db\Query\Delete\DeleteInterface
{
    /**
     * Check if there is deletion errors and throw Exceptions if necessary.
     *
     * @param array $result The return value of the MongoDB delete query
     */
    private function _checkCommitResult(array $result)
    {
        if (! $result['ok'] and $result['errmsg']) {
            throw new \Agl\Exception("Delete failed to MongoDB collection '" . $this->_prefix . $this->_item->getDbContainer() . "' with error message '" . $result['errmsg'] . "'");
        } else if (! $result['ok']) {
            throw new \Agl\Exception("Delete failed to MongoDB collection '" . $this->_prefix . $this->_item->getDbContainer() . "'");
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
            $result = \Agl::app()->getDb()->getResourceDb()->{$this->_prefix . $this->_item->getDbContainer()}->remove(array(
                \Agl\Core\Db\Item\ItemInterface::IDFIELD => $this->_item->getId()->getOrig()
            ), array(
                'justOne' => true,
                'safe'    => true
            ));
            $this->_checkCommitResult($result);

            $this->_item->removeJoinFromAllChilds();

            if (\Agl::app()->isDebugMode()) {
                \Agl::app()->getDb()->incrementCounter();
            }
        } catch (\MongoCursorException $e) {
            throw new \Agl\Exception("Delete (safe mode) failed to MongoDB collection '" . $this->_prefix . $this->_item->getDbContainer() . "' with message '" . $e->getMessage() . "'");
        } catch (\Exception $e) {
            throw new \Agl\Exception($e);
        }

        return true;
    }
}
