<?php
namespace Agl\Core\Mysql\Query;

use \Agl\Core\Agl,
    \Agl\Core\Db\Item\ItemInterface,
    \Agl\Core\Db\Query\Delete\DeleteAbstract,
    \Agl\Core\Db\Query\Delete\DeleteInterface,
    \Exception;

/**
 * Delete an item (and its childs) from the database.
 *
 * @category Agl_Core
 * @package Agl_Core_Mysql_Query
 * @version 0.1.0
 */

class Delete
    extends DeleteAbstract
        implements DeleteInterface
{
    /**
     * Commit the deletion to Mysql and check the query result.
     *
     * @param bool $withChilds Delete also all item's childs in other
     * collections
     * @return int Number of affected rows
     */
    public function commit($withChilds = false)
    {
        try {
            $prepared = Agl::app()->getDb()->getConnection()->prepare("
                DELETE
                FROM
                    `" . $this->getDbPrefix() . $this->_item->getDbContainer() . "`
                WHERE
                    `" . $this->_item->getDbContainer() . ItemInterface::PREFIX_SEPARATOR . ItemInterface::IDFIELD . "` = :" . ItemInterface::IDFIELD . "
            ");

            $preparedValues = array(
                ItemInterface::IDFIELD => $this->_item->getId()->getOrig()
            );

            if (! $prepared->execute($preparedValues)) {
                $error = $prepared->errorInfo();
                throw new Exception("The delete query failed (table '" . $this->getDbPrefix() . $this->_item->getDbContainer() . "') with message '" . $error[2] . "'");
            }

            if ($withChilds) {
                $this->_item->removeJoinFromAllChilds();
            }

            if (Agl::app()->isDebugMode()) {
                Agl::app()->getDb()->incrementCounter();
            }

            return $prepared->rowCount();
        } catch (Exception $e) {
            throw new Exception($e);
        }

        return true;
    }
}
