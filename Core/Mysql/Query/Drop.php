<?php
namespace Agl\Core\Mysql\Query;

/**
 * Drop a collection (and its items and childs) from the database.
 *
 * @category Agl_Core
 * @package Agl_Core_Mysql_Query
 * @version 0.1.0
 */

class Drop
    extends \Agl\Core\Db\Query\Drop\DropAbstract
        implements \Agl\Core\Db\Query\Drop\DropInterface
{
    /**
     * Commit the deletion to MongoDB and check the query result.
     *
     * @return bool
     */
    public function commit()
    {
        try {
            $prepared = \Agl::app()->getDb()->getConnection()->prepare("
                DROP TABLE
                    `" . $this->_dbPrefix . $this->_collection->getDbContainer() . "`
            ");

            if (! $prepared->execute()) {
                $error = $prepared->errorInfo();
                throw new \Exception("The drop query failed (table '" . $this->_dbPrefix . $this->_collection->getDbContainer() . "') with message '" . $error[2] . "'");
            }

            if (\Agl::app()->isDebugMode()) {
                \Agl::app()->getDb()->incrementCounter();
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }

        return true;
    }
}
