<?php
class MysqlItemTest
    extends PHPUnit_Framework_TestCase
{
    const TEST_TABLE = 'user';

	protected static $_instance = NULL;

    public static function setUpBeforeClass()
    {
        self::$_instance = Agl::getModel(self::TEST_TABLE, array('email' => 'test3@agl.io'));
        self::$_instance->setValidationRules(array(
            'zipcode'   => 'isInt',
            'email'     => 'isEmail',
            'promocode' => '/^A-[0-9]{3}$/i'
        ));
    }

    public static function tearDownAfterClass()
    {
        $user    = Agl::getModel('user')->load(1);
        $comment = Agl::getModel('comment')->load(1);
        $comment->addParent($user)->save();
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
        $parent  = $comment->getParent('user');

        $this->assertInstanceOf('Agl\Core\Mvc\Model\Model', $parent);
    }

    /**
     * @dataProvider parentIdData
     */
    public function testGetParentId($pComment, $pExpected)
    {
        $comment = Agl::getModel('comment')->load($pComment);
        $parent  = $comment->getParent('user');

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
        $parent  = $comment->getParent('user');

        $this->assertEquals(NULL, $parent->getId());
    }

    public function parentIdNullData()
    {
        return array(
            array(2)
        );
    }

    /**
     * @dataProvider childsIdData
     */
    public function testGetChilds($pUser)
    {
        $user   = Agl::getModel('user')->load($pUser);
        $childs = $user->getChilds('comment');

        $this->assertInstanceOf('Agl\Core\Db\Collection\Collection', $childs);
    }

    /**
     * @dataProvider childsIdData
     */
    public function testGetChildsId($pUser, $pExpected)
    {
        $user   = Agl::getModel('user')->load($pUser);
        $childs = $user->getChilds('comment');

        $this->assertEquals($pExpected, $childs->count());
    }

    public function childsIdData()
    {
        return array(
            array(1, 1),
            array(1, 1),
            array(2, 0),
        );
    }

    /**
     * @dataProvider childsIdExceptionData
     * @expectedException Exception
     */
    public function testGetChildsException($pUser)
    {
        $user   = Agl::getModel('user')->load($pUser);
        $childs = $user->getChilds('comment');
    }

    public function childsIdExceptionData()
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
     * @dataProvider childIdData
     */
    public function testGetChild($pUser)
    {
        $user  = Agl::getModel('user')->load($pUser);
        $child = $user->getChild('comment');

        $this->assertInstanceOf('Agl\Core\Mvc\Model\Model', $child);
    }

    /**
     * @dataProvider childIdData
     */
    public function testGetChildId($pUser, $pExpected)
    {
        $user  = Agl::getModel('user')->load($pUser);
        $child = $user->getChild('comment');

        $this->assertInstanceOf('\Agl\Core\Db\Id\Id', $child->getId());
    }

    public function childIdData()
    {
        return array(
            array(1, 1),
            array(1, 1)
        );
    }

    /**
     * @dataProvider childIdNullData
     */
    public function testGetChildIdNull($pUser)
    {
        $user  = Agl::getModel('user')->load($pUser);
        $child = $user->getChild('comment');

        $this->assertEquals(NULL, $child->getId());
    }

    public function childIdNullData()
    {
        return array(
            array(2)
        );
    }

    /**
     * @dataProvider addParentData
     */
    public function testAddParent($pId, $pExpected)
    {
        $user    = Agl::getModel('user')->load($pId);
        $comment = Agl::getModel('comment')->load(2);
        $comment->addParent($user)->save();

        $this->assertEquals($pExpected, $comment->getFieldValue($user->getIdField(), true));
    }

    public function addParentData()
    {
        return array(
            array(2, '2'),
            array(1, '2,1')
        );
    }

    /**
     * @dataProvider removeParentData
     */
    public function testRemoveParent($pId, $pExpected)
    {
        $user    = Agl::getModel('user')->load($pId);
        $comment = Agl::getModel('comment')->load(2);
        $comment->removeParent($user)->save();

        $this->assertEquals($pExpected, $comment->getFieldValue($user->getIdField(), true));
    }

    public function removeParentData()
    {
        return array(
            array(2, '1'),
            array(1, NULL)
        );
    }

    /**
     * @dataProvider addRemoveParentIdExceptionData
     * @expectedException Exception
     */
    public function testAddParentException($pUser)
    {
        $user    = Agl::getModel('user')->load($pUser);
        $comment = Agl::getModel('comment')->load(2);
        $comment->addParent($user);
    }

    /**
     * @dataProvider addRemoveParentIdExceptionData
     * @expectedException Exception
     */
    public function testRemoveParentException($pUser)
    {
        $user    = Agl::getModel('user')->load($pUser);
        $comment = Agl::getModel('comment')->load(2);
        $comment->removeParent($user);
    }

    public function addRemoveParentIdExceptionData()
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

    public function testRemoveChilds()
    {
        $user = Agl::getModel('user')->load(1);
        $user->removeChilds();

        $childs = $user->getChilds('comment');
        $this->assertEquals(0, $childs->count());
    }

    /**
     * @dataProvider setValidationData
     */
    public function testValidation($pField, $pValue)
    {
        self::$_instance->$pField = $pValue;
        $this->assertEquals($pValue, self::$_instance->$pField);
    }

    public function setValidationData()
    {
        return array(
            array('zipcode', 75002),
            array('email', 'test@tld.com'),
            array('promocode', 'A-123')
        );
    }

    /**
     * @dataProvider setValidationExceptionData
     * @expectedException Exception
     */
    public function testValidationException($pField, $pValue)
    {
        self::$_instance->$pField = $pValue;
    }

    public function setValidationExceptionData()
    {
        return array(
            array('zipcode', 'test'),
            array('zipcode', ''),
            array('zipcode', 1.33),
            array('zipcode', false),
            array('zipcode', true),
            array('zipcode', NULL),
            array('zipcode', new stdClass()),

            array('email', 'test'),
            array('email', 3),
            array('email', ''),
            array('email', 1.33),
            array('email', false),
            array('email', true),
            array('email', NULL),
            array('email', new stdClass()),

            array('promocode', 'test'),
            array('promocode', 3),
            array('promocode', ''),
            array('promocode', 1.33),
            array('promocode', false),
            array('promocode', true),
            array('promocode', NULL),
            array('promocode', new stdClass())
        );
    }
}
