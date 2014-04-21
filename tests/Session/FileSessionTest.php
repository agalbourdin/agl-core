<?php
require_once('SessionTestAbstract.php');

class FileSessionTest
    extends SessionTestAbstract
{
    public static function setUpBeforeClass()
    {
        static::$_instance      = new \Agl\Core\Session\Storage\File();
        static::$_instance->key = 'value';
    }
}
