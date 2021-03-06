<?php
namespace Agl\Core\Mysql\Query;

use \Agl\Core\Agl,
    \Agl\Core\Db\DbInterface,
    \Agl\Core\Db\Query\Select\SelectAbstract,
    \Agl\Core\Db\Query\Select\SelectInterface,
    \Exception,
    \PDO,
    \PDOStatement;

/**
 * Create and commit a select query to the database.
 *
 * @category Agl_Core
 * @package Agl_Core_Mysql_Query
 * @version 0.1.0
 */

class Select
    extends SelectAbstract
        implements SelectInterface
{
    /**
     * PDO Statement.
     *
     * @var bool|PDOStatement
     */
    private $_stm = false;

    /**
     * Format the order fields to be included into the query.
     *
     * @return string
     */
    private function _formatOrder()
    {
        $orders = array();

        if (isset($this->_order[DbInterface::ORDER_RAND])) {
            $orders[] = 'RAND()';
        } else {
            foreach ($this->_order as $field => $order) {
                $orders[] = "`$field` $order";
            }
        }

        return ' ORDER BY ' . implode(', ', $orders);
    }

    /**
     * Format the limit / skip fields to be included into the query.
     *
     * @return string
     */
    private function _formatLimit()
    {
        $str = ' LIMIT ';

        if ($this->_skip !== NULL) {
            $str .= $this->_skip;
        }
        if ($this->_skip !== NULL and $this->_limit !== NULL) {
            $str .= ', ';
        }
        if ($this->_limit !== NULL) {
            $str .= $this->_limit;
        }

        return $str;
    }

    /**
     * Commit the select query to the database.
     *
     * @return Select
     */
    private function _doQuery($pLimitOne)
    {
        try {
            if (empty($this->_fields)) {
                $fields = '*';
            } else {
                $fields = '`' . implode('`, `', $this->_fields) . '`';
            }

            $query = "
                SELECT
                    $fields
                FROM
                    `" . $this->getDbPrefix() . $this->_dbContainer . "`";

            if ($this->_conditions->count()) {
                $query .= "
                WHERE
                    " . $this->_conditions->getPreparedConditions($this->_dbContainer) . "";
            }

            if (! empty($this->_order)) {
                $query .= $this->_formatOrder();
            }

            if ($pLimitOne) {
                $query .= ' LIMIT 0, 1';
            } else if ($this->_limit !== NULL or $this->_skip !== NULL) {
                $query .= $this->_formatLimit();
            }

            $this->_stm = Agl::app()->getDb()->getConnection()->prepare($query, array(
                PDO::ATTR_CURSOR,
                PDO::CURSOR_SCROLL
            ));

            if (! $this->_stm->execute($this->_conditions->getPreparedValues())) {
                $error = $this->_stm->errorInfo();
                throw new Exception("The select query failed (table '" . $this->getDbPrefix() . $this->_dbContainer . "') with message '" . $error[2] . "'");
            }

            if (Agl::app()->isDebugMode()) {
                Agl::app()->getDb()->incrementCounter();
            }

            return $this;
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }

    /**
     * Commit the select query to the database and return the result cursor.
     *
     * @return Select
     */
    public function find()
    {
        return $this->_doQuery(false);
    }

    /**
     * Commit the select query to the database and return one result.
     *
     * @return Select
     */
    public function findOne()
    {
        return $this->_doQuery(true);
    }

    /**
     * Count the number of results returned by the query.
     *
     * @return int
     */
    public function count()
    {
        if ($this->_stm instanceof PDOStatement) {
            return $this->_stm->rowCount();
        }

        return 0;
    }

    /**
     * Fetch all the results as array.
     *
     * @param bool $pSingle Return a single row or an array of rows.
     * @return array
     */
    public function fetchAll($pSingle = false)
    {
        if ($this->_stm === false) {
            return array();
        }

        if ($pSingle) {
            return $this->_stm->fetch(PDO::FETCH_ASSOC);
        }

        return $this->_stm->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch all the results as array of items.
     *
     * @param bool $pSingle Return a single Item or an array of Items.
     * @return array|Item
     */
    public function fetchAllAsItems($pSingle = false)
    {
        $data = array();

        if ($this->_stm === false) {
            return $data;
        }

        while ($row = $this->_stm->fetch(PDO::FETCH_ASSOC)) {
            if ($pSingle) {
                return Agl::getModel($this->_dbContainer, $row);
            }

            $data[] = Agl::getModel($this->_dbContainer, $row);
        }

        return $data;
    }

    /**
     * Close cursor.
     */
    public function closeCursor()
    {
        $this->_stm->closeCursor();
    }
}
