<?php
class MysqlConditionsTest
    extends PHPUnit_Framework_TestCase
{
    const TEST_TABLE = 'user';

	protected $_instance = NULL;

    public function setUp()
    {
        $this->_instance = new \Agl\Core\Mysql\Query\Conditions();
    }

    public function operatorData()
    {
        return array(
            array('string', \Agl\Core\Mysql\Query\Conditions::EQ, '='),
            array('', \Agl\Core\Mysql\Query\Conditions::GT, '>'),
            array(NULL, \Agl\Core\Mysql\Query\Conditions::LT, '<'),
            array(1.33, \Agl\Core\Mysql\Query\Conditions::LTEQ, '<='),
            array(false, \Agl\Core\Mysql\Query\Conditions::GTEQ, '>='),
            array(true, \Agl\Core\Mysql\Query\Conditions::NOTEQ, '!=')
        );
    }

    public function operatorExceptionData()
    {
        return array(
            array(1.33),
            array(false),
            array(true),
            array(NULL),
            array(new stdClass()),
            array(array('key' => 'value', array())),
            array(array('key' => 'value', 1.33)),
            array(array('key' => 'value', false)),
            array(array('key' => 'value', true)),
            array(array('key' => 'value', NULL)),
            array(array('key' => 'value', new stdClass()))
        );
    }

    public function testDefaultType()
    {
    	$this->assertEquals('AND', $this->_instance->getType());
    }

    public function testDefaultSubType()
    {
    	$this->assertEquals('OR', $this->_instance->getSubType());
    }

    public function testDefaultCount()
    {
    	$this->assertEquals(0, $this->_instance->count());
    }

    public function testDefaultToArray()
    {
    	$this->assertEquals(array(), $this->_instance->toArray());
    }

    /**
	 * @dataProvider invalidTypesData
     * @expectedException Exception
     */
    public function testInvalidType($pType)
    {
    	$conditions = new \Agl\Core\Mysql\Query\Conditions($pType);
    }

    public function invalidTypesData()
    {
    	return array(
    		array('test'),
    		array(1.33),
    		array(false),
    		array(true),
    		array(array('key' => 'value', 'value')),
            array(new stdClass())
    	);
    }

    /**
     * @dataProvider operatorData
     */
    public function testAddOperator($pValue, $pType, $pOperator)
    {
        $this->_instance->add(
            'field',
            $pType,
            $pValue
        );

        $this->assertEquals('(`user_field` ' . $pOperator . ' ?)', $this->_instance->getPreparedConditions(self::TEST_TABLE));
    }

    /**
     * @dataProvider operatorExceptionData
     * @expectedException Exception
     */
    public function testAddOperatorException($pValue)
    {
        $this->_instance->add(
            'id',
            \Agl\Core\Mysql\Query\Conditions::EQ,
            $pValue
        );
    }

    /**
     * @dataProvider operatorData
     */
    public function testAddGroupOperator($pValue, $pType, $pOperator)
    {
        $this->_instance->addGroup(
            array(
                'field',
                $pType,
                $pValue
            ),
            array(
                'field',
                $pType,
                $pValue
            )
        );
        $this->assertEquals('(`user_field` ' . $pOperator . ' ? OR `user_field` ' . $pOperator . ' ?)', $this->_instance->getPreparedConditions(self::TEST_TABLE));
    }

    /**
     * @dataProvider operatorExceptionData
     * @expectedException Exception
     */
    public function testAddGroupOperatorException($pValue)
    {
        $this->_instance->addGroup(
            array(
                'field',
                \Agl\Core\Mysql\Query\Conditions::EQ,
                'string'
            ),
            array(
                'id',
                \Agl\Core\Mysql\Query\Conditions::EQ,
                $pValue
            )
        );
    }
}
