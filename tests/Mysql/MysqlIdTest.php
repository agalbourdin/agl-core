<?php
class MysqlIdTest
	extends PHPUnit_Framework_TestCase
{
	public function testId()
	{
		$id = new \Agl\Core\Mysql\Id('test');
		$this->assertInstanceOf('\Agl\Core\Mysql\Id', $id);
	}

	public function testStringIdAsOrig()
	{
		$id = new \Agl\Core\Mysql\Id('test');
		$this->assertEquals('test', $id->getOrig());
	}

	public function testIntIdAsOrig()
	{
		$id = new \Agl\Core\Mysql\Id(1);
		$this->assertEquals('1', $id->getOrig());
	}

	/**
	 * @dataProvider otherIdData
     * @expectedException Exception
     */
    public function testOtherId($pValue)
    {
    	$id = new \Agl\Core\Mysql\Id($pValue);
    }

    public function otherIdData()
    {
    	return array(
    		array(1.33),
    		array(NULL),
    		array(true),
    		array(array('key' => 'value', 'value')),
            array(new stdClass())
    	);
    }
}
