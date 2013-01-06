<?php
namespace Agl\Core\Mysql\Query;

use \Agl,
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
     * @return int Number of affected rows
     */
    public function commit()
    {
        try {
            $prepared = Agl::app()->getDb()->getConnection()->prepare("
                DELETE
                FROM
                    `" . $this->_dbPrefix . $this->_item->getDbContainer() . "`
                WHERE
                    `" . $this->_item->getDbContainer() . ItemInterface::PREFIX_SEPARATOR . ItemInterface::IDFIELD . "` = :" . ItemInterface::IDFIELD . "
            ");

            $preparedValues = array(
                ItemInterface::IDFIELD => $this->_item->getId()->getOrig()
            );

            if (! $prepared->execute($preparedValues)) {
                $error = $prepared->errorInfo();
                throw new Exception("The delete query failed (table '" . $this->_dbPrefix . $this->_item->getDbContainer() . "') with message '" . $error[2] . "'");
            }

            $this->_item->removeJoinFromAllChilds();

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
