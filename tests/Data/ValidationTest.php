<?php
class ValidationTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider boolTrueData
     */
    public function testBoolTrue($pParam)
    {
        $valid = \Agl\Core\Data\Validation::isBool($pParam);
        $this->assertTrue($valid);
    }

    public function boolTrueData()
    {
        return array(
            array(true),
            array(false)
        );
    }

    /**
     * @dataProvider boolFalseData
     */
    public function testBoolFalse($pParam)
    {
        $valid = \Agl\Core\Data\Validation::isBool($pParam);
        $this->assertFalse($valid);
    }

    public function boolFalseData()
    {
        return array(
            array(1),
            array('1'),
            array('true'),
            array(array()),
            array(NULL),
            array(new stdClass())
        );
    }

    public function testIntTrue()
    {
        $valid = \Agl\Core\Data\Validation::isInt(1);
        $this->assertTrue($valid);
    }

    /**
     * @dataProvider intFalseData
     */
    public function testIntFalse($pParam)
    {
        $valid = \Agl\Core\Data\Validation::isInt($pParam);
        $this->assertFalse($valid);
    }

    public function intFalseData()
    {
        return array(
            array(false),
            array(true),
            array('1'),
            array('true'),
            array(array()),
            array(NULL),
            array(new stdClass())
        );
    }

    public function testDoubleTrue()
    {
        $valid = \Agl\Core\Data\Validation::isDouble(1.33);
        $this->assertTrue($valid);
    }

    /**
     * @dataProvider doubleFalseData
     */
    public function testDoubleFalse($pParam)
    {
        $valid = \Agl\Core\Data\Validation::isDouble($pParam);
        $this->assertFalse($valid);
    }

    public function doubleFalseData()
    {
        return array(
            array(false),
            array(true),
            array(1),
            array('true'),
            array(array()),
            array(NULL),
            array(new stdClass())
        );
    }

    public function testStringTrue()
    {
        $valid = \Agl\Core\Data\Validation::isString('true');
        $this->assertTrue($valid);
    }

    /**
     * @dataProvider stringFalseData
     */
    public function testStringFalse($pParam)
    {
        $valid = \Agl\Core\Data\Validation::isString($pParam);
        $this->assertFalse($valid);
    }

    public function stringFalseData()
    {
        return array(
            array(false),
            array(true),
            array(1),
            array(1.33),
            array(array()),
            array(NULL),
            array(new stdClass())
        );
    }

    public function testNullTrue()
    {
        $valid = \Agl\Core\Data\Validation::isNull(NULL);
        $this->assertTrue($valid);
    }

    /**
     * @dataProvider nullFalseData
     */
    public function testNullFalse($pParam)
    {
        $valid = \Agl\Core\Data\Validation::isNull($pParam);
        $this->assertFalse($valid);
    }

    public function nullFalseData()
    {
        return array(
            array(false),
            array(true),
            array(1),
            array(1.33),
            array(array()),
            array('null'),
            array(new stdClass())
        );
    }

    public function testDigitTrue()
    {
        $valid = \Agl\Core\Data\Validation::isDigit('1');
        $this->assertTrue($valid);
    }

    /**
     * @dataProvider digitFalseData
     */
    public function testDigitFalse($pParam)
    {
        $valid = \Agl\Core\Data\Validation::isDigit($pParam);
        $this->assertFalse($valid);
    }

    public function digitFalseData()
    {
        return array(
            array(false),
            array(true),
            array(1),
            array(1.33),
            array(array()),
            array(NULL),
            array('true'),
            array(new stdClass())
        );
    }

    /**
     * @dataProvider numericTrueData
     */
    public function testNumericTrue($pData)
    {
        $valid = \Agl\Core\Data\Validation::isNumeric($pData);
        $this->assertTrue($valid);
    }

    /**
     * @dataProvider numericFalseData
     */
    public function testNumericFalse($pParam)
    {
        $valid = \Agl\Core\Data\Validation::isNumeric($pParam);
        $this->assertFalse($valid);
    }

    public function numericTrueData()
    {
        return array(
            array(1),
            array(1.33),
            array('1'),
            array('1.33'),
            array(+0123.45e6),
            array('+0123.45e6')
        );
    }

    public function numericFalseData()
    {
        return array(
            array(false),
            array(true),
            array(array()),
            array(NULL),
            array('true'),
            array(new stdClass())
        );
    }

    /**
     * @dataProvider scalarTrueData
     */
    public function testScalarTrue($pData)
    {
        $valid = \Agl\Core\Data\Validation::isScalar($pData);
        $this->assertTrue($valid);
    }

    /**
     * @dataProvider scalarFalseData
     */
    public function testScalarFalse($pParam)
    {
        $valid = \Agl\Core\Data\Validation::isScalar($pParam);
        $this->assertFalse($valid);
    }

    public function scalarTrueData()
    {
        return array(
            array(1),
            array(1.33),
            array('1'),
            array(false),
            array(true)
        );
    }

    public function scalarFalseData()
    {
        return array(
            array(array()),
            array(NULL),
            array(new stdClass())
        );
    }

    /**
     * @dataProvider rewritedTrueData
     */
    public function testRewritedTrue($pData)
    {
        $valid = \Agl\Core\Data\Validation::isRewritedString($pData);
        $this->assertTrue($valid);
    }

    /**
     * @dataProvider rewritedFalseData
     */
    public function testRewritedFalse($pParam)
    {
        $valid = \Agl\Core\Data\Validation::isRewritedString($pParam);
        $this->assertFalse($valid);
    }

    public function rewritedTrueData()
    {
        return array(
            array('1'),
            array('a1'),
            array('fij67jk9'),
            array('fi_j67jk-9'),
            array('_-fij6-7jk9-_')
        );
    }

    public function rewritedFalseData()
    {
        return array(
            array(false),
            array(true),
            array(NULL),
            array(1),
            array(1.33),
            array('ghk/lf'),
            array('ghklf.'),
            array('ghk$lf')
        );
    }

    /**
     * @dataProvider alnumTrueData
     */
    public function testAlnumTrue($pData)
    {
        $valid = \Agl\Core\Data\Validation::isAlNum($pData);
        $this->assertTrue($valid);
    }

    /**
     * @dataProvider alnumFalseData
     */
    public function testAlnumFalse($pParam)
    {
        $valid = \Agl\Core\Data\Validation::isAlNum($pParam);
        $this->assertFalse($valid);
    }

    public function alnumTrueData()
    {
        return array(
            array('1'),
            array('a1'),
            array('fij67jk9')
        );
    }

    public function alnumFalseData()
    {
        return array(
            array(false),
            array(true),
            array(NULL),
            array(1),
            array(1.33),
            array('ghk/lf'),
            array('ghklf.'),
            array('ghk$lf'),
            array('fi_j67jk-9'),
            array('_-fij6-7jk9-_')
        );
    }

    public function testEmailTrue()
    {
        $valid = \Agl\Core\Data\Validation::isEmail('ask@agl.io');
        $this->assertTrue($valid);
    }

    /**
     * @dataProvider emailFalseData
     */
    public function testEmailFalse($pParam)
    {
        $valid = \Agl\Core\Data\Validation::isEmail($pParam);
        $this->assertFalse($valid);
    }

    public function emailFalseData()
    {
        return array(
            array(NULL),
            array(1.33),
            array('ghk/lf'),
            array('http://agl.io/'),
            array('gh@k$lf'),
            array('_-fij6-7jk9-_'),
            array(false),
            array(true),
            array(array()),
            array(new stdClass())
        );
    }

    /**
     * @dataProvider notEmptyTrueData
     */
    public function testNotEmptyTrue($pParam)
    {
        $valid = \Agl\Core\Data\Validation::isNotEmpty($pParam);
        $this->assertTrue($valid);
    }

    public function notEmptyTrueData()
    {
        $obj = new stdClass();
        $obj->test = 1;

        return array(
            array(1.33),
            array('ghk/lf'),
            array('http://agl.io/'),
            array('gh@k$lf'),
            array('_-fij6-7jk9-_'),
            array(array(0)),
            array(true),
            array(new stdClass()),
            array($obj)
        );
    }

    /**
     * @dataProvider notEmptyFalseData
     */
    public function testNotEmptyFalse($pParam)
    {
        $valid = \Agl\Core\Data\Validation::isNotEmpty($pParam);
        $this->assertFalse($valid);
    }

    public function notEmptyFalseData()
    {
        return array(
            array(''),
            array(array()),
            array(0),
            array(0.0),
            array(NULL),
            array(false),
            array('0')
        );
    }

    /**
     * @dataProvider checkTrueData
     */
    public function testCheckTrue($pParam)
    {
        $valid = \Agl\Core\Data\Validation::check($pParam);
        $this->assertTrue($valid);
    }

    /**
     * @dataProvider checkFalseData
     */
    public function testCheckFalse($pParam)
    {
        $valid = \Agl\Core\Data\Validation::check($pParam);
        $this->assertFalse($valid);
    }

    /**
     * @dataProvider checkExceptionData
     * @expectedException PHPUnit_Framework_Error
     */
    public function testCheckException($pParam)
    {
        $valid = \Agl\Core\Data\Validation::check($pParam);
    }

    public function checkTrueData()
    {
        return array(
            array(array(
                'Int'  => 1,
                'Bool' => false,
                'Email' => array(
                    'ask@agl.io',
                    'i@alexisg.net'
                )
            ))
        );
    }

    public function checkFalseData()
    {
        return array(
            array(array(
                'Int'  => 1,
                'Bool' => false,
                'Email' => array(
                    'ask@agl.io',
                    'i@alexisgnet'
                )
            ))
        );
    }

    public function checkExceptionData()
    {
        return array(
            array(''),
            array(1),
            array(new stdClass())
        );
    }
}
