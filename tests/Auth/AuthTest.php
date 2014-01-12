<?php
class AuthTest
    extends PHPUnit_Framework_TestCase
{
    protected static $_instance = NULL;

    public static function setUpBeforeClass()
    {
        self::$_instance = new \Agl\Core\Auth\Auth(new \Agl\Core\Session\Storage\File());
    }

    public function testIsNotLogged()
    {
        self::$_instance->loginById(100);
        $this->assertFalse(self::$_instance->isLogged());
    }

    public function testGetUser()
    {
        $this->assertInstanceOf('\Agl\Core\Mvc\Model\Model', self::$_instance->getUser());
    }

    public function testUserIdNull()
    {
        $user = self::$_instance->getUser();
        $this->assertEquals(NULL, $user->getId());
    }

    public function testGetRole()
    {
        $this->assertEquals(\Agl\Core\Auth\Acl::DEFAULT_ROLE, self::$_instance->getRole());
    }

    public function testIsLogged()
    {
        self::$_instance->loginById(1);
        $this->assertTrue(self::$_instance->isLogged());
    }

    public function testUserId()
    {
        $user = self::$_instance->getUser();
        $this->assertInstanceOf('\Agl\Core\Db\Id\Id', $user->getId());
    }

    public function testSetAnonymUser()
    {
        self::$_instance->loginByUser(new \Agl\Core\Mvc\Model\Model('user'));
        $user = self::$_instance->getUser();
        $this->assertEquals(NULL, $user->getId());
    }

    public function testSetUser()
    {
        $user = Agl::getModel('user')->loadById(1);

        self::$_instance->loginByUser($user);
        $user = self::$_instance->getUser();
        $this->assertInstanceOf('\Agl\Core\Db\Id\Id', $user->getId());
    }
}
