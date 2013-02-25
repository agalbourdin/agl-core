<?php
namespace Agl\Core\Db\Id;

/**
 * Interface - Id
 *
 * @category Agl_Core
 * @package Agl_Core_Db_Id
 * @version 0.1.0
 */

interface IdInterface
{
    public function __construct($pId);
    public function getOrig();
    public function __toString();
}
