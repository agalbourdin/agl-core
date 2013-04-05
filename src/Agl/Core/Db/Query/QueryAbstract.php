<?php
namespace Agl\Core\Db\Query;

use \Agl\Core\Agl;

/**
 * Abstract class - All queries
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Query
 * @version 0.1.0
 */

abstract class QueryAbstract
{
    /**
     * Database prefix.
     *
     * @var string
     */
    private $_dbPrefix = NULL;

    /**
     * Set the database prefix from configuration.
     *
     * @return QueryAbstract
     */
    private function _setDbPrefix()
    {
        $dbPrefix = Agl::app()->getConfig('@app/db/prefix');
        if ($dbPrefix) {
            $this->_dbPrefix = $dbPrefix;
        }

        return $this;
    }

    /**
     * Get the database prefix.
     *
     * @return string
     */
    public function getDbPrefix()
    {
        if ($this->_dbPrefix === NULL) {
            $this->_setDbPrefix();
        }

        return $this->_dbPrefix;
    }
}
