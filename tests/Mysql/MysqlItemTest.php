<?php
class MysqlItemTest
    extends PHPUnit_Framework_TestCase
{
    const TEST_TABLE = 'user';

	protected static $_instance = NULL;

    public static function setUpBeforeClass()
    {
        self::$_instance = Agl::getModel(self::TEST_TABLE, array('email' => 'test3@agl.io'));
    }

    public function testGetDbContainer()
    {
        $this->assertEquals(self::TEST_TABLE, self::$_instance->getDbContainer());
    }

    public function testGet()
    {
        $this->assertEquals('test3@agl.io', self::$_instance->getEmail());
    }

    public function testGetIdField()
    {
        $this->assertEquals('user_id', self::$_instance->getIdField());
    }

    public function testGetFields()
    {
        $this->assertEquals(array('user_email' => 'test3@agl.io'), self::$_instance->getFields());
    }

    public function testGetOrigFields()
    {
        $this->assertEquals(array('user_email' => 'test3@agl.io'), self::$_instance->getOrigFields());
    }

    public function testGetField()
    {
        $this->assertEquals('test3@agl.io', self::$_instance->getField('email'));
    }

    public function testGetOrigField()
    {
        $this->assertEquals('test3@agl.io', self::$_instance->getOrigField('email'));
    }

    public function testLoadById()
    {
        $this->assertInstanceOf('Agl\Core\Db\Id\Id', self::$_instance->loadById(1)->getId());
    }

    public function testLoadByWithConditionsNoResults()
    {
        self::$_instance->loadByEmail('test1@agl.io', array(
            Collection::FILTER_CONDITIONS => Agl::newConditions()->add('id', Conditions::EQ, 10)
        ));
        $this->assertEquals(NULL, self::$_instance->getEmail());
    }

    public function testLoadByWithConditionsResults()
    {
        self::$_instance->loadByEmail('test1@agl.io', array(
            Collection::FILTER_CONDITIONS => Agl::newConditions()->add('id', Conditions::IN, array(1, 2))
        ));
        $this->assertEquals('test1@agl.io', self::$_instance->getEmail());
    }

    public function testLoadWithOrder()
    {
        self::$_instance->load(array(
            Collection::FILTER_ORDER => array('id' => Select::ORDER_ASC)
        ));
        $this->assertEquals('test1@agl.io', self::$_instance->getEmail());
    }

    /**
     * @dataProvider idExceptionData
     * @expectedException Exception
     */
    public function testSetIdException($pId)
    {
        self::$_instance->setId($pId);
    }

    public function idExceptionData()
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

    /**
     * @dataProvider idData
     */
    public function testSetId($pId)
    {
        self::$_instance->setId($pId);
        $this->assertEquals($pId, self::$_instance->getId()->getOrig());
    }

    public function idData()
    {
        return array(
            array('test'),
            array('10'),
            array(10)
        );
    }

    public function testSetOrigId()
    {
        $this->assertTrue(self::$_instance->getId()->getOrig() == self::$_instance->getOrigField('id')->getOrig());
    }
}
