<?php
require_once('TestAbstract.php');

class FileCacheTest
    extends TestAbstract
{
    protected static $_className = '\Agl\Core\Cache\File';

    public static function setUpBeforeClass()
    {
        self::$_instance = new self::$_className('/tmp/cache/');
    }
}
