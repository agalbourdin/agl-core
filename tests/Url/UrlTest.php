<?php
class UrlTest extends PHPUnit_Framework_TestCase
{
    const REQUEST = 'home/index/param/value/action/ post';
    const DOMAIN  = 'domain.tld';

    public static function setUpBeforeClass()
    {
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['HTTP_HOST']       = self::DOMAIN;

        \Agl\Core\Observer\Observer::setEvents(array());
        \Agl\Core\Url\Url::setRequest(new \Agl\Core\Request\Request(ROOT . self::REQUEST . ROOT));
    }

    /**
     * @dataProvider getData
     */
    public function testGet($pPath, $pParams, $pRelative, $pExpected)
    {
        $this->assertEquals($pExpected, \Agl\Core\Url\Url::get($pPath, $pParams, $pRelative));
    }

    public function getData()
    {
        return array(
            array('module/test', array(), true, '/module/test/'),
            array('home/index', array('param' => 'value'), true, '/home/index/param/value/'),
            array('module/test', array(), false, 'http://' . self::DOMAIN . '/module/test/'),
            array('home/index', array('param' => 'value'), false, 'http://' . self::DOMAIN . '/home/index/param/value/'),

            array('public/logo.jpg', array(), true, '/public/logo.jpg'),
            array('public/logo.jpg', array(), false, 'http://' . self::DOMAIN . '/public/logo.jpg'),

            array('*/*/', array(), true, '/home/index/'),
            array('*/test', array('param' => 'value'), true, '/home/test/param/value/'),
            array('*/*/', array(), false, 'http://' . self::DOMAIN . '/home/index/'),
            array('*/test', array('param' => 'value'), false, 'http://' . self::DOMAIN . '/home/test/param/value/')
        );
    }

    /**
     * @dataProvider getCurrentData
     */
    public function testGetCurrent($pNewParams, $pRelative, $pExpected)
    {
        $this->assertEquals($pExpected, \Agl\Core\Url\Url::getCurrent($pNewParams, $pRelative));
    }

    public function getCurrentData()
    {
        return array(
            array(array(), true, '/home/index/param/value/action/post/'),
            array(array('key' => 'value'), true, '/home/index/param/value/action/post/key/value/'),
            array(array(), false, 'http://' . self::DOMAIN . '/home/index/param/value/action/post/'),
            array(array('key' => 'value'), false, 'http://' . self::DOMAIN . '/home/index/param/value/action/post/key/value/'),
        );
    }

    /**
     * @dataProvider getBaseData
     */
    public function testGetBase($pRelative, $pExpected)
    {
        $this->assertEquals($pExpected, \Agl\Core\Url\Url::getBase($pRelative));
    }

    public function getBaseData()
    {
        return array(
            array(true, '/'),
            array(false, 'http://' . self::DOMAIN . '/')
        );
    }

    /**
     * @dataProvider getSkinData
     */
    public function testGetSkin($pUrl, $pRelative, $pExpected)
    {
        $this->assertEquals($pExpected, \Agl\Core\Url\Url::getSkin($pUrl, $pRelative, 'default'));
    }

    public function getSkinData()
    {
        return array(
            array('css/main.css', true, '/public/skin/default/css/main.css'),
            array('main.swf', true, '/public/skin/default/main.swf'),
            array('css/main.css', false, 'http://' . self::DOMAIN . '/public/skin/default/css/main.css'),
            array('main.swf', false, 'http://' . self::DOMAIN . '/public/skin/default/main.swf')
        );
    }

    /**
     * @dataProvider getPublicData
     */
    public function testGetPublic($pUrl, $pRelative, $pExpected)
    {
        $this->assertEquals($pExpected, \Agl\Core\Url\Url::getPublic($pUrl, $pRelative));
    }

    public function getPublicData()
    {
        return array(
            array('logo.jpeg', true, '/public/logo.jpeg'),
            array('flash/main.swf', true, '/public/flash/main.swf'),
            array('logo.jpeg', false, 'http://' . self::DOMAIN . '/public/logo.jpeg'),
            array('flash/main.swf', false, 'http://' . self::DOMAIN . '/public/flash/main.swf')
        );
    }

    /**
     * @dataProvider getHostData
     */
    public function testGetHost($pPath, $pExpected)
    {
        $this->assertEquals($pExpected, \Agl\Core\Url\Url::getHost($pPath));
    }

    public function getHostData()
    {
        return array(
            array('', 'http://' . self::DOMAIN),
            array('/test/path', 'http://' . self::DOMAIN . '/test/path')
        );
    }

    public function testGetProtocol()
    {
        $this->assertEquals('http://', \Agl\Core\Url\Url::getProtocol());
    }
}
