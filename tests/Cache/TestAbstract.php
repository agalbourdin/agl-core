<?php
class TestAbstract
    extends PHPUnit_Framework_TestCase
{
    protected static $_instance = NULL;

    public function dataProvider()
    {
        return array(
            array('key', 'value', true),
            array('key2', NULL, false),
            array(1, NULL, false),
            array('1', NULL, false),
            array('true', NULL, false),
            array(NULL, NULL, false),
            array(+0123.45e6, NULL, false)
        );
    }

    public function exceptionDataProvider()
    {
        return array(
            array(new stdClass())
        );
    }

    public function testSet()
    {
        $this->assertInstanceOf(static::$_className, self::$_instance->set('key', 'value'));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGet($pKey, $pResult)
    {
        $this->assertEquals($pResult, self::$_instance->get($pKey));
    }

    /**
     * @dataProvider exceptionDataProvider
     * @expectedException PHPUnit_Framework_Error
     */
    public function testGetException($pKey)
    {
        self::$_instance->get($pKey);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testHas($pKey, $pResult, $pHas)
    {
        $this->assertEquals($pHas, self::$_instance->has($pKey));
    }

    /**
     * @dataProvider exceptionDataProvider
     * @expectedException PHPUnit_Framework_Error
     */
    public function testHasException($pKey)
    {
        self::$_instance->has($pKey);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testRemove($pKey)
    {
        self::$_instance->remove($pKey);
        $this->assertNull(self::$_instance->get($pKey));
    }

    /**
     * @dataProvider exceptionDataProvider
     * @expectedException PHPUnit_Framework_Error
     */
    public function testRemoveException($pKey)
    {
        self::$_instance->has($pKey);
    }

    public function testFlushSection()
    {
        self::$_instance
            ->set('section1.key', 'value')
            ->set('section2.key', 'value')
            ->flush('section1');

        $this->assertNull(self::$_instance->get('section1.key'));
    }

    public function testFlush()
    {
        self::$_instance
            ->set('section1.key', 'value')
            ->set('section2.key', 'value')
            ->flush();

        $this->assertNull(self::$_instance->get('section2.key'));
    }
}
