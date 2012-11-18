<?php
namespace Agl\Core\Observer;

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
     * The filename of the events configuration file.
     */
    const CONFIG_FILE = 'events';

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
     * Dispatch the event $pName with the arguments $pArgs.
     *
     * @param type $pName Name of the event to dispatch
     * @param type array $pArgs Arguments to pass to the event
     * @return bool
     */
    public static function dispatch($pName, array $pArgs)
    {
        $eventConfig = \Agl::app()->getConfig('@module[' . \Agl::AGL_CORE_POOL . '/events]/' . $pName, true);
        foreach ($eventConfig as $event) {
            if (is_array($event)) {
                foreach ($event as $class => $methods) {
                    if (is_array($methods)) {
                        $instance = \Agl::getSingleton($class);
                        foreach ($methods as $method) {
                            if ($instance and method_exists($instance, $method)) {
                                $instance::$method($pArgs);
                            }
                        }
                    }
                }
            }
        }

        return false;
    }
}
