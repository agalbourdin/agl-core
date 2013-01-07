<?php
namespace Agl\Core\Session;

use \Agl\Core\Agl,
	\Agl\Core\Session\Storage\File as FileSession,
	\Agl\Core\Session\Storage\Db as DbSession,
	\Exception;

/**
 * Factory - implement the session class corresponding to the application
 * configured session storage.
 *
 * @category Agl_Core
 * @package Agl_Core_Session
 * @version 0.1.0
 */

switch(Agl::app()->getConfig('@app/session/storage')) {
    case SessionInterface::STORAGE_FILE:
        class Session extends FileSession { }
        break;
    case SessionInterface::STORAGE_DB:
        class Session extends DbSession { }
        break;
    default:
        throw new Exception("Session storage type '" . Agl::app()->getConfig('@app/session:storage') . "' is not allowed");
}
