<?php
/**
 * Edit this part with your MySQL configuration.
 */
define('MYSQL_HOST', '127.0.0.1');
define('MYSQL_DBNAME', 'agl_core_tests');
define('MYSQL_USER', 'travis');
define('MYSQL_PASSWORD', '');

/**
 * You shouldn't have to edit following lines.
 */
session_start();

define('AGL_PATH', dirname(__DIR__) . '/src/Agl/Core/');
define('APP_PATH', dirname(__DIR__) . '/');
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', '/');

setlocale(LC_ALL, 'en_GB.utf8');

require(AGL_PATH . 'Agl.php');
require(AGL_PATH . 'Autoload.php');
new \Agl\Core\Autoload();
