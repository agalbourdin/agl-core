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
            array(''),
            array(1.33),
            array(false),
            array(true),
            array(NULL),
            array(new stdClass()),
            array(array()),
            array(array(1, 2, 3)),
            array(array(1, 'test')),
            array(array(1)),
            array(array('0', '1')),
            array(array(0, true)),
            array(array('key' => 'value', 'value'))
        );
    }
}
