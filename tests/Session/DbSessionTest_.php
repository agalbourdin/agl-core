<?php
require_once('SessionTestAbstract.php');

class DbSessionTest
    extends SessionTestAbstract
{
    public static function setUpBeforeClass()
    {
        static::$_instance      = new \Agl\Core\Session\Storage\Db();
        static::$_instance->key = 'value';
    }
}
