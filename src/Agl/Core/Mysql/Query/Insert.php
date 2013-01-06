<?php
namespace Agl\Core\Mysql\Query;

use \Agl,
    \Agl\Core\Db\Item\ItemInterface,
    \Agl\Core\Db\Query\Insert\InsertAbstract,
    \Agl\Core\Db\Query\Insert\InsertInterface,
    \Exception;

/**
 * Insert an item in the database.
 *
 * @category Agl_Core
 * @package Agl_Core_Mysql_Query
 * @version 0.1.0
 */

class Insert
    extends InsertAbstract
        implements InsertInterface
{
    /**
     * Return the list of the fields to insert.
     *
     * @return string
     */
    private function _getFields()
    {
        return '`' . implode('`, `', array_keys($this->_fields)) . '`';
    }

    /**
     * Return the list of the prepared fields (to be replaced by values by
     * PDO).
     *
     * @return string
     */
    private function _getPreparedFields()
    {
        return ':' . implode(', :', array_keys($this->_fields));
    }

    /**
     * Set the insertion ID.
     *
     * @param string $pId
     * @return Insert
     */
    private function _setId($pId)
    {
        $idField = ItemInterface::IDFIELD;

        if (! isset($this->_fields[$idField])
            or ! $this->_fields[$idField]) {
            $this->_fields[$idField] = $pId;
        }

        return $this;
    }

    /**
     * Commit the insertion to MySQL and check the query result.
     *
     * @return int Number of affected rows
     */
    public function commit()
    {
        try {
            $prepared = Agl::app()->getDb()->getConnection()->prepare("
                INSERT INTO
                    `" . $this->_dbPrefix . $this->_dbContainer . "`
                (" . $this->_getFields() . ")
                VALUES
                (" . $this->_getPreparedFields() . ")
            ");

            if (! $prepared->execute($this->_fields)) {
                $error = $prepared->errorInfo();
                throw new Exception("The insert query failed (table '" . $this->_dbPrefix . $this->_dbContainer . "') with message '" . $error[2] . "'");
            }

            if (Agl::app()->isDebugMode()) {
                Agl::app()->getDb()->incrementCounter();
            }

            $this->_setId(Agl::app()->getDb()->getConnection()->lastInsertId());

            return $prepared->rowCount();
        } catch (Exception $e) {
            throw new Exception($e);
        }

        return true;
    }
}
