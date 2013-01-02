<?php
namespace Agl\Core\Mongo;

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
     * @param \MongoId $pId
     */
    public function __construct($pId)
    {
        if ($pId instanceof \MongoId) {
            $id = $pId;
        } else if (is_string($pId)) {
            $id = new \MongoId($pId);
        } else {
            throw new \Exception('Trying to set an invalid ID (must be a string or an instance of MongoId)');
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
        if ($this->_id instanceof \MongoId) {
            return $this->_id->__toString();
        }

        throw new \Exception('Trying to get an invalid ID (must be a string or an instance of MongoId)');
    }
}
