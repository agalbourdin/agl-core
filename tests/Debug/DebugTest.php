<?php
class DebugTest
	extends PHPUnit_Framework_TestCase
{
    public function testXdebugEnabled()
    {
        $this->assertTrue(is_bool(\Agl\Core\Debug\Debug::isXdebugEnabled()));
    }

    public function testLog()
    {
        $this->assertTrue(is_int(\Agl\Core\Debug\Debug::log('test')));
    }
}
