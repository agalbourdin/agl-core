<?php
namespace Agl\Core\Session;

/**
 * Factory - implement the session class corresponding to the application
 * configured session storage.
 *
 * @category Agl_Core
 * @package Agl_Core_Session
 * @version 0.1.0
 */

switch(\Agl::app()->getConfig('@app/session/storage')) {
    case SessionInterface::STORAGE_FILE:
        class Session extends \Agl\Core\Session\Storage\File { }
        break;
    case SessionInterface::STORAGE_DB:
        class Session extends \Agl\Core\Session\Storage\Db { }
        break;
    default:
        throw new \Agl\Exception("Session storage type '" . \Agl::app()->getConfig('@app/session:storage') . "' is not allowed");
}
