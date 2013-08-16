<?php
class ObserverTest
	extends PHPUnit_Framework_TestCase
{
	public static $_events = array(
		'agl_view_render' => array(
			'ObserverTest' => array(
				'observer'
			)
		),
		'agl_view_render_buffer_before' => array(
			'ObserverTest' => array(
				'observer', // Valid
				'method'
			)
		),
		'agl_view_render_buffer' => array(
			'Observer' => array(
				'observer'
			)
		),
		'agl_view' => array(
			'ObserverTest' => array(
				'method'
			)
		)
	);

	public static $_exceptionEvents = array(
		'agl_view_render_buffer_before' => array(
			'Test' => array(
				'method'
			)
		)
	);

	public static function observer() { }

    public function testDispatch()
    {
    	\Agl\Core\Observer\Observer::setEvents(self::$_events);
    	$observer = \Agl\Core\Observer\Observer::dispatch(\Agl\Core\Observer\Observer::EVENT_VIEW_RENDER_BUFFER_BEFORE);
    	$this->assertEquals(1, $observer);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testDispatchException($pKey)
    {
        \Agl\Core\Observer\Observer::setEvents(self::$_exceptionEvents);
    	\Agl\Core\Observer\Observer::dispatch(\Agl\Core\Observer\Observer::EVENT_VIEW_RENDER_BUFFER_BEFORE);
    }
}
