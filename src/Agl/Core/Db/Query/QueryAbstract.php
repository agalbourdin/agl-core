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
    protected $_dbPrefix = '';

    /**
     * Get the database prefix from configuration and register it.
     *
     * @return QueryAbstract
     */
    protected function _setDbPrefix()
    {
        $dbPrefix = Agl::app()->getConfig('@app/db/prefix');
        if ($dbPrefix) {
            $this->_dbPrefix = $dbPrefix;
        }

        return $this;
    }
}
