<?php
class RegistryTest
    extends PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider setData
	 */
    public function testSetGet($pValue)
    {
    	\Agl\Core\Registry\Registry::set('key', $pValue);
    	$this->assertEquals($pValue, \Agl\Core\Registry\Registry::get('key'));
    	\Agl\Core\Registry\Registry::remove('key');

    }

    public function setData()
    {
    	return array(
    		array('value'),
    		array(1),
    		array(new stdClass()),
    		array('key' => array('key' => 'value'))
    	);
    }

    /**
     * @expectedException Exception
     */
    public function testSetException()
    {
    	\Agl\Core\Registry\Registry::set('key', 'value1');
    	\Agl\Core\Registry\Registry::set('key', 'value2');
    }

    /**
     * @expectedException Exception
     */
    public function testRemoveException()
    {
    	\Agl\Core\Registry\Registry::remove('key1');
    }
}
