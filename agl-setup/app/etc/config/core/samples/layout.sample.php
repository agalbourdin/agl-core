<?php
return array(

	/**
	 * Default template to render views.
	 * Can be overrided in specific module, view or action configuration.
	 */
	'template' => array(

		/**
		 * Template's type.
		 *
		 * Correct values are:
		 * - html
		 * - json
		 */
		'type' => 'html',

		/**
		 * Template's file name, without extension.
		 * Extension depends of the template's type previously defined:
		 * - html: .phtml
		 * - json: .php
		 *
		 * The file must be located into "app/template/".
		 */
		'file' => 'main'
	),

	/**
	 * Modules configuration.
	 *
	 * In this section, you can configure templates, CSS, JS, ACL and Cache for:
	 * - a specific module
	 * - a specific view
	 * - a specific action
	 */
	'modules' => array(

		/**
		 * For the "home" module.
		 */
		'home' => array(

		),

		/**
		 * For the "index" view of the "home" module.
		 */
		'home/index' => array(

			/**
			 * The view will be cached with a TTL of 3600 sec.
			 *
			 * Cache type is "static", cache will be generated once, regardless
			 * of the parameters passed in the URL.
			 *
			 * Cache type can also be "dynamic", in this case it will be
			 * generated depending of the request parameters.
			 */
			'cache' => array(
				'type' => 'static',
				'ttl'  => 3600
			)

		),

		/**
		 * For the "api" view of the "home" module.
		 */
		'home/api' => array(

			/**
			 * We set a specific template for this view, with a JSON type.
			 */
			'template' => array(
				'type' => 'json',
				'file' => 'api'
			)

		),

		/**
		 * For the "post" action of the "index" view of the "home" module.
		 */
		'home/index/action/post' => array(

			/**
			 * We set ACL: only users with the "update"
			 * resource will be allowed to access this action.
			 */
			'acl' => array(
				'update'
			)

		)

    ),

	/**
	 * Blocks configuration.
	 *
	 * In this section, you can configure CSS, JS and ACL for a specific block.
	 */
	'blocks' => array(

		/**
		 * For the "product/bestsellers" block.
		 */
		'product/bestsellers' => array(

			/**
			 * We set ACL: only users with the "stats" resource will see the
			 * block.
			 */
			'acl' => array(
				'stats'
			),

			/**
			 * The block will be cached with an unlimited TTL.
			 */
			'cache' => array(
				'type' => 'static',
				'ttl'  => 0
			)

		)

    )

);
