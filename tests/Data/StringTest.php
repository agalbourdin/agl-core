<?php
class StringTest
    extends PHPUnit_Framework_TestCase
{
    public function testRewrite()
    {
        $rewrited = \Agl\Core\Data\String::rewrite('Aért ty7 O thkl.^a');
        $this->assertEquals('aert-ty7-thkl', $rewrited);
    }

    public function testToCamelCase()
    {
        $string = \Agl\Core\Data\String::toCamelCase('camel_case_string');
        $this->assertEquals('CamelCaseString', $string);
    }

    public function testToCamelCaseWithoutFirstUppercase()
    {
        $string = \Agl\Core\Data\String::toCamelCase('camel_case_string', false);
        $this->assertEquals('camelCaseString', $string);
    }

    public function testFromCamelCase()
    {
        $string = \Agl\Core\Data\String::fromCamelCase('CamelCaseString');
        $this->assertEquals('camel_case_string', $string);
    }

    public function testRandomString()
    {
        $string = \Agl\Core\Data\String::getRandomString();
        $this->assertEquals(6, strlen($string));
    }

    public function testRandomStringLength()
    {
        $string = \Agl\Core\Data\String::getRandomString(4);
        $this->assertEquals(4, strlen($string));
    }

    public function testRandomStringStrength()
    {
        $string = \Agl\Core\Data\String::getRandomString(6, 2);
        $this->assertRegExp('/@|#|\$|%/', $string);
    }
}
