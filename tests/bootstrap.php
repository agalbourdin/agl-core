<?php
/**
 * Edit this part with your MySQL configuration.
 */
define('MYSQL_HOST', 'localhost');
define('MYSQL_DBNAME', 'agl-tests');
define('MYSQL_USER', 'root');
define('MYSQL_PASSWORD', 'root');

/**
 * You shouldn't have to edit following lines.
 */
session_start();

define('AGL_PATH', realpath('./') . '/src/Agl/Core/');
define('APP_PATH', realpath('./') . '/');
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', '/');

setlocale(LC_ALL, 'en_GB.utf8');

require(AGL_PATH . 'Agl.php');

require(AGL_PATH . 'Autoload.php');
new \Agl\Core\Autoload();
