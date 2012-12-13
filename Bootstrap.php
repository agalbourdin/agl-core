<?php
/**
 * Checking the PHP version.
 */
if (version_compare(phpversion(), '5.3.0', '<')) {
    echo 'Invalid PHP Version (' . phpversion() . ' < 5.3.0)';
    exit;
}

/**
 * Defining some required constants.
 */
define('DS', DIRECTORY_SEPARATOR);
define('BP', AGL_PATH);
define('ROOT', str_replace('index.php', '', $_SERVER['PHP_SELF']));

/**
 * Default umask
 */
umask(0);

/**
 * Require the main AGL class.
 */
require(__DIR__ . DS . 'Agl.php');

/**
 * Run AGL.
 */
\Agl\Agl::run(AGL_CACHE_ENABLED, AGL_DEBUG_MODE);
