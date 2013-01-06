<?php
namespace Agl\Core\Db\Id;

/**
 * Abstract class - Id
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Id
 * @version 0.1.0
 */

abstract class IdAbstract
{
    /**
     * The ID value in its original type.
     *
     * @var mixed
     */
    protected $_id = NULL;

    /**
     * Store the Id.
     *
     * @param mixed $pId
     */
    protected function __construct($pId)
    {
        $this->_id = $pId;
    }

    /**
     * Return the ID in its original type.
     *
     * @return mixed
     */
    public function getOrig()
    {
        return $this->_id;
    }
}
