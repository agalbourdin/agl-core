<?php
namespace Agl\Core\Mysql\Query;

/**
 * Create and commit a select query to the database.
 *
 * @category Agl_Core
 * @package Agl_Core_Mysql_Query
 * @version 0.1.0
 */

class Select
    extends \Agl\Core\Db\Query\Select\SelectAbstract
        implements \Agl\Core\Db\Query\Select\SelectInterface
{
    /**
     * Order ASC keyword.
     */
    const ORDER_ASC = 'ASC';

    /**
     * Order DESC keyword.
     */
    const ORDER_DESC = 'DESC';

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

        if (isset($this->_order[static::ORDER_RAND])) {
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
                    `" . $this->_dbPrefix . $this->_dbContainer . "`";

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

            $this->_stm = \Agl::app()->getDb()->getConnection()->prepare($query, array(
                \PDO::ATTR_CURSOR, \PDO::CURSOR_SCROLL
            ));

            if (! $this->_stm->execute($this->_conditions->getPreparedValues())) {
                $error = $this->_stm->errorInfo();
                throw new \Agl\Exception("The select query failed (table '" . $this->_dbPrefix . $this->_dbContainer . "') with message '" . $error[2] . "'");
            }

            if (\Agl::app()->isDebugMode()) {
                \Agl::app()->getDb()->incrementCounter();
            }

            return $this;
        } catch (\Exception $e) {
            throw new \Agl\Exception($e);
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
        if ($this->_stm instanceof \PDOStatement) {
            return $this->_stm->rowCount();
        }

        return 0;
    }

    /**
     * Fetch the result row corresponding to the requested pointer.
     *
     * @param int $pPointer
     * @return bool|array
     */
    public function fetch($pPointer)
    {
        if ($this->_stm === false) {
            return false;
        }

        return $this->_stm->fetch(\PDO::FETCH_ASSOC, \PDO::FETCH_ORI_ABS, $pPointer);
    }

    /**
     * Fetch all the results.
     *
     * @return bool|array
     */
    public function fetchAll()
    {
        if ($this->_stm === false) {
            return false;
        }

        return $this->_stm->fetchAll(\PDO::FETCH_ASSOC);
    }
}
