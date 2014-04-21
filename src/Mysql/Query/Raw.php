<?php
namespace Agl\Core\Mysql\Query;

use \Agl\Core\Agl,
    \Agl\Core\Db\Query\Raw\RawAbstract,
    \Agl\Core\Db\Query\Raw\RawInterface,
    \Exception,
    \PDO;

/**
 * Create and commit a raw query to the database.
 *
 * @category Agl_Core
 * @package Agl_Core_Mysql_Query
 * @version 0.1.0
 */

class Raw
    extends RawAbstract
        implements RawInterface
{
    /**
     * PDO Statement.
     *
     * @var bool|PDOStatement
     */
    private $_stm = false;

    /**
     * Commit the query to the database and return the result.
     *
     * @return Select
     */
    public function query($pQuery, array $pParams = array())
    {
        $this->_stm = Agl::app()->getDb()->getConnection()->prepare($pQuery);

        if (! $this->_stm->execute($pParams)) {
            $error = $this->_stm->errorInfo();
            throw new Exception("The query failed with message '" . $error[2] . "'");
        }

        if (Agl::app()->isDebugMode()) {
            Agl::app()->getDb()->incrementCounter();
        }

        return $this->_stm->fetchAll(PDO::FETCH_ASSOC);
    }
}
