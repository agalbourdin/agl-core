<?php
require_once('TestAbstract.php');

class ApcuCacheTest
    extends TestAbstract
{
    protected static $_className = '\Agl\Core\Cache\Apcu';

    public static function setUpBeforeClass()
    {
        self::$_instance = new self::$_className();

        if (ini_get('apc.enable_cli')) {
            self::$_enabled = true;
        }
    }
}
