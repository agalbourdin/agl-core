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
define('AGL_PATH', dirname(__DIR__) . '/src/Agl/Core/');
define('APP_PATH', dirname(__DIR__) . '/');
define('COMPOSER_DIR', dirname(__DIR__) . '/vendor/');
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', '/');

setlocale(LC_ALL, 'en_GB.utf8');

require(COMPOSER_DIR . 'autoload.php');

require(AGL_PATH . 'Agl.php');
require(AGL_PATH . 'Autoload.php');
new \Agl\Core\Autoload();

Agl\Core\Agl::run(false, false, dirname(__FILE__) . DS . 'etc/config1/');
