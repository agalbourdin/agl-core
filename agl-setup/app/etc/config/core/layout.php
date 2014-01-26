<?php
/**
 * Layout configuration file.
 * Define templates, CSS, JS, ACL and Cache configuration for a module, a view,
 * a block or a specific action.
 */

return array(

	'template' => array(
		'type' => 'html',
		'file' => 'main',

		'css' => array(
			'normalize.css',
			'preset.css',
			'main.css'
		),

		'js' => array(
			'//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js',
			'vendor/modernizr-2.6.2.min.js',
			'plugins.js',
			'main.js'
		)
	),

	'modules' => array(

    ),

	'blocks' => array(

    )

);
