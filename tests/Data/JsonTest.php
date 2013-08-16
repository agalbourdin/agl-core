<?php
class JsonTest
    extends PHPUnit_Framework_TestCase
{
    const JSON_FILE = '/data.json';

    protected $_instance = NULL;

    public function setUp()
    {
        $this->_instance = new Agl\Core\Data\Json();
    }

    public function testLoadStringArray()
    {
        $this->_instance->loadString('{"value":"1","key":{"value":"2"}}', true);
        $this->assertEquals(array(
                'value' => '1',
                'key'   => array(
                    'value' => '2'
                )
            ),
            $this->_instance->getContent()
        );
    }

    public function testLoadFileArray()
    {
        $this->_instance->loadFile(__DIR__ . self::JSON_FILE, true);
        $this->assertEquals(array(
                'value' => '1',
                'key'   => array(
                    'value' => '2'
                )
            ),
            $this->_instance->getContent()
        );
    }

    public function testLoadString()
    {
        $this->_instance->loadString('{"value":"1","key":{"value":"2"}}');
        $result = new stdClass();
        $result->value = '1';
        $result->key = new stdClass();
        $result->key->value = '2';
        $this->assertEquals($result, $this->_instance->getContent()
        );
    }

    public function testLoadFile()
    {
        $this->_instance->loadFile(__DIR__ . self::JSON_FILE);
        $result = new stdClass();
        $result->value = '1';
        $result->key = new stdClass();
        $result->key->value = '2';
        $this->assertEquals($result, $this->_instance->getContent()
        );
    }
}
