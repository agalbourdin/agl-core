<?php
return array(

    /**
     * When the "agl_init_after" event is fired.
     */
	'agl_init_after' => array(

        /**
         * Call userHelper::cleanState().
         */
        'helper/user' => array(
            'cleanState'
        )

    ),

	/**
     * When the "agl_router_route_before" event is fired.
     */
	'agl_router_route_before' => array(

		/**
         * Call FacebookHelper::login() and FacebookHelper::setLang().
         */
        'helper/facebook' => array(
            'login',
            'setLang'
        ),

		/**
         * Call userHelper::getPreferedRoute().
         */
        'helper/user' => array(
            'getPreferedRoute'
        )
    )

);
