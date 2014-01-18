<?php
class MysqlCollectionTest
    extends PHPUnit_Framework_TestCase
{
    const TEST_TABLE = 'user';

	protected static $_instance = NULL;
    protected static $_item     = NULL;

    public static function setUpBeforeClass()
    {
        self::$_item     = Agl::getModel(self::TEST_TABLE);
        self::$_instance = Agl::getCollection(self::TEST_TABLE);
    }

    public function testGetDbContainer()
    {
        $this->assertEquals(self::TEST_TABLE, self::$_instance->getDbContainer());
    }

    /**
     * @dataProvider invalidContainerData
     * @expectedException Exception
     */
    public function testInvalidContainer($pType)
    {
        Agl::getCollection($pType);
    }

    public function invalidContainerData()
    {
        return array(
            array('t/est'),
            array('t"est'),
            array(''),
            array(1.33),
            array(false),
            array(true),
            array(new stdClass())
        );
    }

    /**
     * @dataProvider loadXData
     */
    public function testLoadXLast($pNb)
    {
        self::$_instance->loadLast($pNb);
        $this->assertEquals($pNb, self::$_instance->count());
    }

    /**
     * @dataProvider loadXData
     */
    public function testLoadXFirst($pNb)
    {
        self::$_instance->loadFirst($pNb);
        $this->assertEquals($pNb, self::$_instance->count());
    }

    /**
     * @dataProvider loadXData
     */
    public function testLoadXRandom($pNb)
    {
        self::$_instance->loadRandom($pNb);
        $this->assertEquals($pNb, self::$_instance->count());
    }

    public function loadXData()
    {
        return array(
            array(0),
            array(1),
            array(2)
        );
    }

    public function testLoadLast()
    {
        self::$_instance->loadLast();
        $item = self::$_instance->current();
        $this->assertEquals('test2@agl.io', $item->getEmail());
    }

    public function testLoadFirst()
    {
        self::$_instance->loadFirst();
        $item = self::$_instance->current();
        $this->assertEquals('test1@agl.io', $item->getEmail());
    }

    public function testLoadRandom()
    {
        self::$_instance->loadRandom();
        $item = self::$_instance->current();
        $this->assertEquals('0000-00-00 00:00:00', $item->getDateUpdate());
    }

    public function testNext()
    {
        self::$_instance->load();
        self::$_instance->next();
        $item = self::$_instance->current();
        $this->assertEquals('test1@agl.io', $item->getEmail());
    }

    public function testIterator()
    {
        $email = NULL;
        foreach (self::$_instance as $item) {
            $email = $item->getEmail();
            break;
        }

        $this->assertEquals('test2@agl.io', $email);
    }

    public function testIteratorKey()
    {
        $this->assertEquals(0, self::$_instance->key());
    }

    public function testIteratorValid()
    {
        $this->assertEquals(true, self::$_instance->valid(1));
    }

    public function testCountWithConditions()
    {
        $this->assertEquals(1, self::$_instance->count(
            Agl::newConditions()->add('email', Conditions::EQ, 'test1@agl.io')
        ));
    }

    public function testLoadWithConditions()
    {
        $this->assertEquals(0, self::$_instance->load(array(
            Collection::FILTER_CONDITIONS => Agl::newConditions()->add('email', Conditions::EQ, 'test@agl.io')
        ))->count());
    }

    public function testLoadWithLimit()
    {
        $this->assertEquals(1, self::$_instance->load(array(Collection::FILTER_LIMIT => 1))->count());
    }

    public function testLoadWithLimitArray()
    {
        self::$_instance->load(array(Collection::FILTER_LIMIT => array(1, 1)));
        $this->assertEquals('test1@agl.io', self::$_instance->current()->getEmail());
    }

    public function testLoadWithOrder()
    {
        self::$_instance->load(array(Collection::FILTER_ORDER => array('id' => Select::ORDER_ASC)));
        $this->assertEquals('test1@agl.io', self::$_instance->current()->getEmail());
    }

    public function testLoadWithConditionsAndLimit()
    {
        self::$_instance->load(array(
            Collection::FILTER_CONDITIONS => Agl::newConditions()->add('email', Conditions::LIKE, 'test%'),
            Collection::FILTER_LIMIT      => array(1, 1)
        ));
        $this->assertEquals('test1@agl.io', self::$_instance->current()->getEmail());
    }

    public function testLoadWithLimitAndOrder()
    {
        self::$_instance->load(array(
            Collection::FILTER_LIMIT => 1,
            Collection::FILTER_ORDER => array('email' => Select::ORDER_ASC)
        ));
        $this->assertEquals('test1@agl.io', self::$_instance->current()->getEmail());
    }

    public function testLoadWithConditionsAndOrder()
    {
        self::$_instance->load(array(
            Collection::FILTER_CONDITIONS => Agl::newConditions()->add('email', Conditions::LIKE, 'test%'),
            Collection::FILTER_ORDER      => array('id' => SELECT::ORDER_ASC)
        ));
        $this->assertEquals('test1@agl.io', self::$_instance->current()->getEmail());
    }

    public function testLoadFull()
    {
        self::$_instance->load(array(
            Collection::FILTER_CONDITIONS => Agl::newConditions()->add('email', Conditions::LIKE, 'test%'),
            Collection::FILTER_ORDER      => array('id' => SELECT::ORDER_ASC),
            Collection::FILTER_LIMIT      => array(1, 1)
        ));
        $this->assertEquals('test2@agl.io', self::$_instance->current()->getEmail());
    }

    public function testLoadNoResultsWithConditions()
    {
        $this->assertEquals(0, self::$_instance->load(array(
            Collection::FILTER_CONDITIONS => Agl::newConditions()->add('email', Conditions::LIKE, 'testt%')
        ))->count());
    }

    /**
     * @dataProvider limitExceptionData
     * @expectedException Exception
     */
    public function testLimitException($pLimit)
    {
        self::$_instance->load(array(
            Collection::FILTER_LIMIT => $pLimit
        ));
    }

    public function limitExceptionData()
    {
        return array(
            array('test'),
            array(''),
            array(1.33),
            array(false),
            array(true),
            array(new stdClass()),
            array(array()),
            array(array(1, 2, 3)),
            array(array(1, 'test')),
            array(array(1)),
            array(array('0', '1')),
            array(array(0, true)),
        );
    }

    /**
     * @dataProvider limitOrderData
     * @expectedException Exception
     */
    public function testOrderException($pOrder)
    {
        self::$_instance->load(array(
            Collection::FILTER_ORDER => $pOrder
        ));
    }

    public function limitOrderData()
    {
        return array(
            array('t/est'),
            array('t"est'),
            array(''),
            array(1.33),
            array(false),
            array(true),
            array(new stdClass())
        );
    }

    public function testLoadBy()
    {
        self::$_instance->loadByEmail('test2@agl.io');
        $this->assertEquals('test2@agl.io', self::$_instance->current()->getEmail());
    }

    public function testLoadByWithConditions()
    {
        $this->assertEquals(0, self::$_instance->loadByEmail('test2@agl.io', array(
            Collection::FILTER_CONDITIONS => Agl::newConditions()->add('id', Conditions::EQ, 10)
        ))->count());
    }

    /**
     * @expectedException Exception
     */
    public function testLoadByWithoutValue()
    {
        self::$_instance->loadByEmail();
    }

    /**
     * @expectedException Exception
     */
    public function testLoadByWithBadAttribute()
    {
        self::$_instance->loadByUnknown(1);
    }

    /**
     * @expectedException Exception
     */
    public function testLoadByWithoutAttribute()
    {
        self::$_instance->loadBy(1);
    }
}
