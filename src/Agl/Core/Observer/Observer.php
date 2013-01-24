<?php
namespace Agl\Core\Observer;

use \Agl\Core\Agl,
    \Agl\Core\Loader\Loader;

/**
 * Dispatch events to the corresponding instance.
 *
 * @category Agl_Core
 * @package Agl_Core_Observer
 * @version 0.1.0
 */

class Observer
{
    /**
     * List of allowed events.
     */
    const EVENT_VIEW_RENDER_BUFFER_BEFORE = 'agl_view_render_buffer_before';
    const EVENT_SET_REQUEST_BEFORE        = 'agl_set_request_before';
    const EVENT_SET_REQUEST_AFTER         = 'agl_set_request_after';
    const EVENT_ROUTER_ROUTE_BEFORE       = 'agl_router_route_before';
    const EVENT_ITEM_INSERT_BEFORE        = 'agl_item_insert_before';
    const EVENT_ITEM_INSERT_AFTER         = 'agl_item_insert_after';
    const EVENT_ITEM_SAVE_BEFORE          = 'agl_item_save_before';
    const EVENT_ITEM_SAVE_AFTER           = 'agl_item_save_after';
    const EVENT_ITEM_DELETE_BEFORE        = 'agl_item_delete_before';
    const EVENT_ITEM_DELETE_AFTER         = 'agl_item_delete_after';

    /**
     * Registered events.
     *
     * @var null|array
     */
    private static $_events = NULL;

    /**
     * Register events. Get events from the configuration by default.
     *
     * @param $pEvents null|array
     * @return array
     */
    public static function setEvents($pEvents = NULL)
    {
        if ($pEvents === NULL) {
            $pEvents = Agl::app()->getConfig('@module[' . Agl::AGL_CORE_POOL . '/events]/');
        }

        if (is_array($pEvents)) {
            self::$_events = $pEvents;
        } else {
            self::$_events = array();
        }

        return self::$_events;
    }

    /**
     * Dispatch the event $pName with the arguments $pArgs.
     *
     * @param type $pName Name of the event to dispatch
     * @param type array $pArgs Arguments to pass to the event
     * @return int
     */
    public static function dispatch($pName, array $pArgs = array())
    {
        if (self::$_events === NULL) {
            self::setEvents();
        }

        $i = 0;
        foreach (self::$_events as $name => $event) {
            if ($name === $pName and is_array($event)) {
                foreach ($event as $class => $methods) {
                    if (is_array($methods)) {
                        $class = Loader::getClassName($class);
                        foreach ($methods as $method) {
                            if (method_exists($class, $method)) {
                                $class::$method($pArgs);
                                $i++;
                            }
                        }
                    }
                }
            }
        }

        return $i;
    }
}
