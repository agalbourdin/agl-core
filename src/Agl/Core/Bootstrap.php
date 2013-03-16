<?php
/**
 * Checking PHP version.
 */
if (version_compare(PHP_VERSION, '5.3.0', '<')) {
    echo 'Invalid PHP Version (' . PHP_VERSION . ' < 5.3.0)';
    exit;
}

/**
 * Defining some required constants.
 */
define('DS', DIRECTORY_SEPARATOR);
define('APP_PATH', realpath('.') . DS);
define('ROOT', str_replace('index.php', '', $_SERVER['PHP_SELF']));

define('REQUEST_URI', preg_replace(
	'#^http(s)?://' . $_SERVER['HTTP_HOST'] . '#',
	'',
	str_replace('?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI'])));

/**
 * Default umask
 */
umask(0);

/**
 * Require the main AGL class.
 */
require(__DIR__ . DS . 'Agl.php');

/**
 * Import and initialize the Autoload class.
 */
require(__DIR__ . DS . 'Autoload.php');
new \Agl\Core\Autoload();

/**
 * Import Errors and Exceptions handlers.
 */
require(__DIR__ . DS . 'Exception.php');

/**
 * Import Debug class to always log errors.
 */
require(__DIR__ . DS . 'Debug/Debug.php');

/**
 * Run AGL.
 */
\Agl\Core\Agl::run(AGL_CACHE_ENABLED, AGL_LOG_ENABLED, AGL_DEBUG_MODE);
