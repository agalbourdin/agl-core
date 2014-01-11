<?php
class DateTest
    extends PHPUnit_Framework_TestCase
{
    protected $_date     = '2013-01-19 16:47:35';
    protected $_timezone = 'Europe/Paris';

    public function testNow()
    {
        $result = Agl\Core\Data\Date::now();
        $this->assertRegExp('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/', $result);
    }

    public function testFormatShort()
    {
        $result = Agl\Core\Data\Date::format($this->_date, 'short');
        $this->assertEquals(1, preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{2}$/', $result));
    }

    public function testFormatLong()
    {
        $result = Agl\Core\Data\Date::format($this->_date, 'long');
        $this->assertEquals(1, preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{2} [0-9]{2}:[0-9]{2}$/', $result));
    }

    public function testFormatFull()
    {
        $result = Agl\Core\Data\Date::format($this->_date, 'full');
        $this->assertEquals(1, preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/', $result));
    }

    public function testToTz()
    {
        $result = Agl\Core\Data\Date::toTz($this->_date, $this->_timezone);
        $this->assertEquals('2013-01-19 17:47:35', $result);
    }

    public function testToDefault()
    {
        $result = Agl\Core\Data\Date::toDefault($this->_date, $this->_timezone);
        $this->assertEquals('2013-01-19 15:47:35', $result);
    }
}
