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

    public function testGetIdFieldOtherContainer()
    {
        $this->assertEquals('comment_id', self::$_instance->getIdField('comment'));
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
        $this->assertEquals('test3@agl.io', self::$_instance->getFieldValue('email'));
    }

    public function testGetOrigField()
    {
        $this->assertEquals('test3@agl.io', self::$_instance->getOrigFieldValue('email'));
    }

    public function testLoadById()
    {
        $this->assertInstanceOf('Agl\Core\Db\Id\Id', self::$_instance->loadById(1)->getId());
    }

    public function testLoadByWithConditionsNoResults()
    {
        self::$_instance->loadByEmail('test1@agl.io', array(
            Db::FILTER_CONDITIONS => Agl::newConditions()->add('id', Conditions::EQ, 10)
        ));
        $this->assertEquals(NULL, self::$_instance->getEmail());
    }

    public function testLoadByWithConditionsResults()
    {
        self::$_instance->loadByEmail('test1@agl.io', array(
            Db::FILTER_CONDITIONS => Agl::newConditions()->add('id', Conditions::IN, array(1, 2))
        ));
        $this->assertEquals('test1@agl.io', self::$_instance->getEmail());
    }

    public function testLoadWithOrder()
    {
        self::$_instance->load(array(
            Db::FILTER_ORDER => array('id' => Db::ORDER_ASC)
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
        $this->assertTrue(self::$_instance->getId()->getOrig() == self::$_instance->getOrigFieldValue('id')->getOrig());
    }

    /**
     * @dataProvider parentsIdData
     */
    public function testGetParents($pComment)
    {
        $comment = Agl::getModel('comment')->load($pComment);
        $parents = $comment->getParents('user');

        $this->assertInstanceOf('Agl\Core\Db\Collection\Collection', $parents);
    }

    /**
     * @dataProvider parentsIdData
     */
    public function testGetParentsId($pComment, $pExpected)
    {
        $comment = Agl::getModel('comment')->load($pComment);
        $parents = $comment->getParents('user');

        $this->assertEquals($pExpected, $parents->count());
    }

    public function parentsIdData()
    {
        return array(
            array(1, 1),
            array(1, 1),
            array(2, 0),
        );
    }

    /**
     * @dataProvider parentsIdExceptionData
     * @expectedException Exception
     */
    public function testGetParentsException($pComment)
    {
        $comment = Agl::getModel('comment')->load($pComment);
        $parents = $comment->getParents('user');
    }

    public function parentsIdExceptionData()
    {
        return array(
            array(3),
            array(''),
            array(1.33),
            array(false),
            array(true),
            array(NULL),
            array(new stdClass())
        );
    }

    /**
     * @dataProvider parentIdData
     */
    public function testGetParent($pComment)
    {
        $comment = Agl::getModel('comment')->load($pComment);
        $parent  = $comment->getParents('user', array(), true);

        $this->assertInstanceOf('Agl\Core\Mvc\Model\Model', $parent);
    }

    /**
     * @dataProvider parentIdData
     */
    public function testGetParentId($pComment, $pExpected)
    {
        $comment = Agl::getModel('comment')->load($pComment);
        $parent  = $comment->getParents('user', array(), true);

        $this->assertInstanceOf('\Agl\Core\Db\Id\Id', $parent->getId());
    }

    public function parentIdData()
    {
        return array(
            array(1, 1),
            array(1, 1)
        );
    }

    /**
     * @dataProvider parentIdNullData
     */
    public function testGetParentIdNull($pComment)
    {
        $comment = Agl::getModel('comment')->load($pComment);
        $parent  = $comment->getParents('user', array(), true);

        $this->assertEquals(NULL, $parent->getId());
    }

    public function parentIdNullData()
    {
        return array(
            array(2)
        );
    }
}
