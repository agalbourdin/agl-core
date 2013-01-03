<?php
namespace Agl\Core\Mongo\Query;

/**
 * Update an item saved to the database.
 *
 * @category Agl_Core
 * @package Agl_Core_Mongo_Query
 * @version 0.1.0
 */

class Update
    extends \Agl\Core\Db\Query\Update\UpdateAbstract
        implements \Agl\Core\Db\Query\Update\UpdateInterface
{
    /**
     *The value to set when an attribute has been deleted.
     */
    const DELETE_VALUE = 1;

    /**
     * Check if there is update errors and throw Exceptions if necessary.
     *
     * @param array $result The return value of the MongoDB update query
     */
    private function _checkCommitResult(array $result)
    {
        if (! $result['ok'] and $result['errmsg']) {
            throw new \Exception("Update failed to MongoDB collection '" . $this->_prefix . $this->_item->getDbContainer() . "' with error message '" . $result['errmsg'] . "'");
        } else if (! $result['ok']) {
            throw new \Exception("Update failed to MongoDB collection '" . $this->_prefix . $this->_item->getDbContainer() . "' with no error message");
        }
    }

    /**
     * Commit the update to MongoDB and check the query result.
     *
     * @return bool
     */
    public function commit()
    {
        try {
            $toUpdate = $this->getFieldsToUpdate();
            $toDelete = $this->getFieldsToDelete();

            if (! empty($toUpdate) or ! empty($toDelete)) {
                $result = \Agl::app()->getDb()->getResourceDb()->{$this->_prefix . $this->_item->getDbContainer()}->update(array(
                    \Agl\Core\Db\Item\ItemInterface::IDFIELD => $this->_item->getId()->getOrig()
                ), array(
                    '$set'   => $toUpdate,
                    '$unset' => $toDelete
                ), array(
                    'safe' => true
                ));
                $this->_checkCommitResult($result);

                if (\Agl::app()->isDebugMode()) {
                    \Agl::app()->getDb()->incrementCounter();
                }
            }
        } catch (\MongoCursorException $e) {
            throw new \Exception("Update (safe mode) failed to MongoDB collection '" . $this->_prefix . $this->_item->getDbContainer() . "' with message '" . $e->getMessage() . "'");
        } catch (\Exception $e) {
            throw new \Exception($e);
        }

        return true;
    }
}
