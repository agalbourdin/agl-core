<?php
class IniTest
    extends PHPUnit_Framework_TestCase
{
    const INI_FILE = '/data.ini';

    protected $_instance = NULL;

    public function setUp()
    {
        $this->_instance = new Agl\Core\Data\Ini();
    }

    public function testLoad()
    {
        $this->_instance->loadFile(__DIR__ . self::INI_FILE);
        $this->assertEquals(array(
            'section' => array(
                'value'     => '1',
                'key.value' => '2'
            )),
            $this->_instance->getContent()
        );
    }

    public function testLoadWithKeys()
    {
        $this->_instance->loadFile(__DIR__ . self::INI_FILE, true);
        $this->assertEquals(array(
            'section' => array(
                'value' => '1',
                'key'   => array(
                    'value' => '2'
                )
            )),
            $this->_instance->getContent()
        );
    }
}
