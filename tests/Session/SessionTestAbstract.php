<?php
class SessionTestAbstract
    extends PHPUnit_Framework_TestCase
{
    protected static $_instance = NULL;

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

    public function testGetCsrf()
    {
        $this->assertTrue(is_string(self::$_instance->csrfToken));
    }

    public function testCheckCsrfSuccess()
    {
        $this->assertTrue(self::$_instance->checkCsrfToken(self::$_instance->csrfToken));
    }

    /**
     * @dataProvider csrfData
     */
    public function testCheckCsrfFail($pToken)
    {
        $this->assertFalse(self::$_instance->checkCsrfToken($pToken));
    }

    public function csrfData()
    {
        return array(
            array('string'),
            array(1),
            array(true),
            array(NULL),
            array(array()),
            array(new stdClass())
        );
    }
}
