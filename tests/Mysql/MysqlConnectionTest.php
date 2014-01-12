<?php
class MysqlConnectionTest
    extends PHPUnit_Framework_TestCase
{
	protected static $_instance = NULL;

	private $_dbStructure = array(
		'comment',
        'session',
		'user'
	);

    public static function setUpBeforeClass()
    {
        self::$_instance = Agl::app()->getDb();
    }

    public function testDefaultCount()
    {
        $this->assertEquals(0, self::$_instance->countQueries());
    }

    public function testCountQueries()
    {
    	self::$_instance->incrementCounter();
    	$this->assertEquals(1, self::$_instance->countQueries());
    }

    public function testOrderAsc()
    {
    	$this->assertEquals($this->_dbStructure, self::$_instance->listCollections());
    }
}
