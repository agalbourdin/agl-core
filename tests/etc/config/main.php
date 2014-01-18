<?php
return array(

	/**
	 * Global configuration.
	 */
	'global' => array(

		/**
		 * Application's timezone.
		 * All dates are stored as UTC in database. This setting is used to
		 * display dates with the correct timezone.
		 *
		 * http://www.php.net/manual/en/timezones.php
		 */
		'timezone' => 'Europe/Paris'
	),

	/**
	 * Main Database configuration.
	 */
	'db' => array(

		/**
		 * Database engine.
		 *
		 * Correct values are:
		 * - mysql
		 */
		'engine' => 'mysql',

		/**
		 * Database host.
		 *
		 * For example: "localhost", "127.0.0.1"
		 */
		'host' => '127.0.0.1',

		/**
		 * Database name.
		 */
		'name' => 'agl_core_tests',

		/**
		 * Username to use to connect to the database.
		 */
		'user' => 'travis',

		/**
		 * Password associated to the previously defined username.
		 */
		'password' => '',

		/**
		 * Optional: table prefix.
		 *
		 * For example: "agl_"
		 */
		'prefix' => ''
	),

	/**
	 * Session configuration.
	 */
	'session' => array(

		/**
		 * Set the database storage engine.
		 *
		 * Correct values are:
		 * - file [default]
		 * - db
		 */
		'storage' => 'file'
	),

	/**
	 * Cache configuration.
	 */
	'cache' => array(

		/**
		 * Set the cache storage engine.
		 *
		 * Correct values are:
		 * - file [default]
		 * - apcu
		 */
		'type' => 'file'
	)

);
