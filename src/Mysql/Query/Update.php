<?php
namespace Agl\Core\Mysql\Query;

use \Agl\Core\Agl,
    \Agl\Core\Db\Item\ItemInterface,
    \Agl\Core\Db\Query\Update\UpdateAbstract,
    \Agl\Core\Db\Query\Update\UpdateInterface,
    \Exception;

/**
 * Update an item saved to the database.
 *
 * @category Agl_Core
 * @package Agl_Core_Mysql_Query
 * @version 0.1.0
 */

class Update
    extends UpdateAbstract
        implements UpdateInterface
{
    /**
     *The value to set when an attribute has been deleted.
     */
    const DELETE_VALUE = NULL;

    /**
     * Return the list of the prepared fields (to be replaced by values by
     * PDO).
     *
     * @return string
     */
    private function _getPreparedFields(array $pFields)
    {
        $fields = array();
        $keys   = array_keys($pFields);
        foreach ($keys as $key) {
            $fields[] = "`$key` = ?";
        }

        return implode(', ', $fields);
    }

    /**
     * Commit the update to MySQL and check the query result.
     *
     * @return int Number of affected rows
     */
    public function commit()
    {
        try {
            $toUpdate = array_merge($this->getFieldsToUpdate(), $this->getFieldsToDelete());

            if (! empty($toUpdate)) {
                $idField  = $this->_item->getIdField();

                $query = "
                    UPDATE
                        `" . $this->getDbPrefix() . $this->_item->getDbContainer() . "`
                    SET
                        " . $this->_getPreparedFields($toUpdate) . "
                    WHERE
                        `$idField` = ?";

                if (! array_key_exists($idField, $toUpdate)) {
                    $toUpdate[$idField] = $this->_item->getId()->getOrig();
                }

                $prepared = Agl::app()->getDb()->getConnection()->prepare($query);

                if (! $prepared->execute(array_values($toUpdate))) {
                    $error = $prepared->errorInfo();
                    throw new Exception("The update query failed (table '" . $this->getDbPrefix() . $this->_item->getDbContainer() . "') with message '" . $error[2] . "'");
                }

                if (Agl::app()->isDebugMode()) {
                    Agl::app()->getDb()->incrementCounter();
                }

                return $prepared->rowCount();
            }
        } catch (Exception $e) {
            throw new Exception($e);
        }

        return true;
    }
}
