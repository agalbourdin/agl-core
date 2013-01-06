<?php
namespace Agl\Core\Mysql\Query;

use \Agl,
    \Agl\Core\Db\Query\Count\CountAbstract,
    \Agl\Core\Db\Query\Count\CountInterface,
    \Exception;

/**
 * Create and commit a count query to the database.
 *
 * @category Agl_Core
 * @package Agl_Core_Mysql_Query
 * @version 0.1.0
 */

class Count
    extends CountAbstract
        implements CountInterface
{
    /**
     * Commit the count query to the database and return the result.
     *
     * @return array
     */
    public function commit()
    {
        try {
            if (empty($this->_fields)) {
                $field = 'COUNT(1)';
            } else {
                $field = 'COUNT(';
                if ($this->_fields[static::FIELD_DISTINCT]) {
                    $field .= 'DISTINCT(';
                }
                $field .= '`' . $this->_fields[static::FIELD_NAME] . '`';
                if ($this->_fields[static::FIELD_DISTINCT]) {
                    $field .= ')';
                }
                $field = ')';
            }

            $query = "
                SELECT
                    $field AS nb
                FROM
                    `" . $this->_dbPrefix . $this->_dbContainer . "`";

            if ($this->_conditions->count()) {
                $query .= "
                WHERE
                    " . $this->_conditions->getPreparedConditions($this->_dbContainer) . "";
            }

            $prepared = Agl::app()->getDb()->getConnection()->prepare($query);

            if ($prepared->execute($this->_conditions->getPreparedValues())) {
                $result = $prepared->fetchObject();
                if (! $result) {
                    throw new Exception("The count query failed (table '" . $this->_dbPrefix . $this->_dbContainer . "')");
                }
            } else {
                $error = $prepared->errorInfo();
                throw new Exception("The count query failed (table '" . $this->_dbPrefix . $this->_dbContainer . "') with message '" . $error[2] . "'");
            }

            if (Agl::app()->isDebugMode()) {
                Agl::app()->getDb()->incrementCounter();
            }

            return (int)$result->{'nb'};
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }
}
