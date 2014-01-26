<?php
class RequestTest
    extends PHPUnit_Framework_TestCase
{
	const REQUEST = 'home/index/param/value/action/ post';

    protected static $_instance = NULL;

    public static function setUpBeforeClass()
    {
        \Agl\Core\Observer\Observer::setEvents(array());

        $_POST = array(
            'field1' => " t'est>"
        );

        self::$_instance = new \Agl\Core\Request\Request(ROOT . self::REQUEST . ROOT);
    }

    public function testGetModule()
    {
        $this->assertEquals('home', self::$_instance->getModule());
    }

    public function testGetView()
    {
        $this->assertEquals('index', self::$_instance->getView());
    }

    public function testGetAction()
    {
        $this->assertEquals('post', self::$_instance->getAction());
    }

    public function testGetReq()
    {
        $this->assertEquals(self::REQUEST, self::$_instance->getReq());
    }

    public function testGetReqvars()
    {
        $this->assertEquals(explode('/', self::REQUEST), self::$_instance->getReqvars());
    }

    public function testGetParams()
    {
        $this->assertEquals(array(
            'param'  => 'value',
            'action' => 'post',
            0        => 'param',
            1        => 'value',
            2        => 'action',
            3        => 'post'
        ), self::$_instance->getParams());
    }

    /**
     * @dataProvider getParamData
     */
    public function testGetParam($pParam, $pExpected)
    {
        $this->assertEquals($pExpected, self::$_instance->getParam($pParam));
    }

    public function getParamData()
    {
    	return array(
    		array('action', 'post'),
    		array('param', 'value'),
            array('test', ''),
            array(0, 'param'),
            array(3, 'post')
    	);
    }

    public function testGetPost()
    {
        $this->assertEquals(array('field1' => 't&#039;est&gt;'), self::$_instance->getPost());
    }

    /**
     * @dataProvider getPostData
     */
    public function testGetPostValue($pParam, $pExpected)
    {
        $this->assertEquals($pExpected, self::$_instance->getPost($pParam));
    }

    public function getPostData()
    {
        return array(
            array('field1', 't&#039;est&gt;'),
            array('test', '')
        );
    }

    /**
     * @dataProvider ajaxData
     */
    public function testIsAjax($pServer, $pExpected)
    {
    	if ($pServer) {
    		$_SERVER['HTTP_X_REQUESTED_WITH'] = $pServer;
    	} else if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    		unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    	}

        $this->assertEquals($pExpected, self::$_instance->isAjax());
    }

    public function ajaxData()
    {
    	return array(
    		array('', false),
    		array('test', false),
    		array('xmlhttprequest', true),
    		array('XMLHttpRequest', true)
    	);
    }
}
