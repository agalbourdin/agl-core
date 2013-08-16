<?php
class MysqlConnectionTest
    extends PHPUnit_Framework_TestCase
{
	protected static $_instance = NULL;

	private $_dbStructure = array(
		'comment',
		'user'
	);

    public static function setUpBeforeClass()
    {
        self::$_instance = new \Agl\Core\Mysql\Connection();
    }

    /**
     * @expectedException Exception
     */
    public function testSetConnectionException()
    {
    	self::$_instance->setConnection('host', 'dbname');
    }

    public function testSetConnection()
    {
    	self::$_instance->setConnection(MYSQL_HOST, MYSQL_DBNAME, MYSQL_USER, MYSQL_PASSWORD);
    	$this->assertInstanceOf('PDO', self::$_instance->getConnection());
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
