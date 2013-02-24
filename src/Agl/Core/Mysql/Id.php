<?php
namespace Agl\Core\Mysql;

use \Agl\Core\Db\Id\IdAbstract,
    \Agl\Core\Db\Id\IdInterface,
    \Exception;

/**
 * Specific ID management (based on the database engine).
 *
 * @category Agl_Core
 * @package Agl_Core_Mysql
 * @version 0.1.0
 */

class Id
    extends IdAbstract
        implements IdInterface
{
    /**
     * Create the ID object dans check the $pId type.
     *
     * @param string|int $pId
     */
    public function __construct($pId)
    {
        if (is_string($pId)) {
            $id = $pId;
        } else if (is_int($pId)) {
            $id = (string)$pId;
        } else {
            throw new Exception('Trying to set an invalid ID (must be a string or an integer)');
        }

        parent::__construct($id);
    }

    /**
     * Return the ID as a string.
     *
     * @return string
     */
    /*public function __toString()
    {
        return $this->_id;
    }*/
}
