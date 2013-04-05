<?php
namespace Agl\Core\Db\Query\Drop;

use \Agl\Core\Db\Collection\Collection,
    \Agl\Core\Db\Query\QueryAbstract;

/**
 * Abstract class - Drop
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Query_Drop
 * @version 0.1.0
 */

abstract class DropAbstract
    extends QueryAbstract
{
    /**
     * Items to delete from the database.
     *
     * @var array
     */
    protected $_collection = NULL;

    /**
     * Prepare the dropping of a collection and its items and childs.
     *
     * @param Collection $pCollection
     */
    public function __construct(Collection $pCollection)
    {
        $this->_collection = $pCollection;
    }
}
