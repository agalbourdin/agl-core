<?php
namespace Agl\Core\Session;

/**
 * Interface - Session
 *
 * @category Agl_Core
 * @package Agl_Core_Session
 * @version 0.1.0
 */

interface SessionInterface
{
    /**
     * The allowed session storages types.
     */
    const STORAGE_FILE = 'file';
    const STORAGE_DB   = 'db';

    public function __construct();
    public function __call($pMethod, array $pArgs);
    public function __get($pVar);
    public function __set($pVar, $pValue);
    public function __unset($pVar);
    public function hasAttribute($pVar);
}
