<?php
namespace Agl\Core\Mysql;

/**
 * Specific ID management (based on the database engine).
 *
 * @category Agl_Core
 * @package Agl_Core_Mongo
 * @version 0.1.0
 */

class Id
    extends \Agl\Core\Db\Id\IdAbstract
        implements \Agl\Core\Db\Id\IdInterface
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
            throw new \Agl\Exception('Trying to set an invalid ID (must be a string or an integer)');
        }

        parent::__construct($id);
    }

    /**
     * Return the ID as a string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->_id;
    }
}
