<?php
require_once('CacheTestAbstract.php');

class FileCacheTest
    extends CacheTestAbstract
{
    protected static $_className = '\Agl\Core\Cache\File';

    public static function setUpBeforeClass()
    {
        self::$_instance = new self::$_className('/tmp/cache/');
    }
}
