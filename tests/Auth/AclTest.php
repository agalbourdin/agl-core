<?php
class AclTest
    extends PHPUnit_Framework_TestCase
{
    protected static $_instance = NULL;

    protected static $_aclConfig = array(
        'guest' => array(
            'resources' => array(
                'read'
            )
        ),

        'member' => array(
            'resources' => array(
                'write'
            ),

            'inherit' => array(
                'guest'
            )
        ),

        'admin' => array(
            'resources' => array(
                'delete'
            ),

            'inherit' => array(
                'guest',
                'member'
            )
        ),
    );

    public static function setUpBeforeClass()
    {
        self::$_instance = new \Agl\Core\Auth\Acl(self::$_aclConfig);
    }

    /**
     * @dataProvider allowedData
     */
    public function testIsAllowed($pRole, $pResources)
    {
        $this->assertTrue(self::$_instance->isAllowed($pRole, $pResources));
    }

    /**
     * @dataProvider notAllowedData
     */
    public function testIsNotAllowed($pRole, $pResources)
    {
        $this->assertFalse(self::$_instance->isAllowed($pRole, $pResources));
    }

    /**
     * @dataProvider isAllowedExceptionData
     * @expectedException PHPUnit_Framework_Error
     */
    public function testIsAllowedException($pRole, $pResources)
    {
        $this->assertFalse(self::$_instance->isAllowed($pRole, $pResources));
    }

    public function allowedData()
    {
        return array(
            array('guest', array('read')),
            array('member', array('read')),
            array('admin', array('read')),
            array('member', array('write')),
            array('admin', array('write')),
            array('admin', array('delete')),
            array('member', array('read', 'write')),
            array('admin', array('read', 'write', 'delete'))
        );
    }

    public function notAllowedData()
    {
        return array(
            array('guest', array('write')),
            array('guest', array('delete')),
            array('member', array('delete')),
            array('member', array('write', 'delete')),
            array('guest', array('comment')),
            array('admin', array('comment', 'delete')),
        );
    }

    public function isAllowedExceptionData()
    {
        return array(
            array('guest', 'write'),
            array(array(), array('write')),
            array(new stdClass(), new stdClass())
        );
    }
}
