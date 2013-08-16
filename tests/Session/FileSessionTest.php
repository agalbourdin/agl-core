<?php
class FileSessionTest
    extends PHPUnit_Framework_TestCase
{
    protected static $_instance = NULL;

    public static function setUpBeforeClass()
    {
        self::$_instance      = new \Agl\Core\Session\Storage\File();
        self::$_instance->key = 'value';
    }

    public function getData()
    {
        return array(
            array('key', 'value'),
            array('key', 1),
            array('key', 1.33),
            array('key', NULL),
            array('key', array('key' => 'value', 'value')),
            array('key', new stdClass())
        );
    }

    public function testGet()
    {
        $this->assertEquals('value', self::$_instance->key);
    }

    /**
     * @dataProvider getData
     */
    public function testSetGet($pKey, $pValue)
    {
        self::$_instance->$pKey = $pValue;
        $this->assertEquals($pValue, self::$_instance->$pKey);
    }

    /**
     * @dataProvider getData
     */
    public function testRemove($pKey, $pValue)
    {
        self::$_instance->$pKey = $pValue;
        unset(self::$_instance->$pKey);
        $this->assertNull(self::$_instance->$pKey);
    }

    public function testDestroy()
    {
        self::$_instance->key = 'value';
        self::$_instance->destroy();
        $this->assertNull(self::$_instance->key);
    }
}
