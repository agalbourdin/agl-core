<?php
require_once('CacheTestAbstract.php');

class ApcuCacheTest
    extends CacheTestAbstract
{
    protected static $_className = '\Agl\Core\Cache\Apcu';

    public static function setUpBeforeClass()
    {
        self::$_instance = new self::$_className();
    }
}
