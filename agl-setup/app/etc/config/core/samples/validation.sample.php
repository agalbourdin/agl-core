<?php
return array(

	/**
	 * For the "user" model.
	 */
	'user' => array(

		/**
		 * An exception will be thrown when attempting to set a non integer
		 * value as "zipcode". Validation methods can be found in
		 * Core\Data\Validation.
		 */
		'zipcode' => 'isInt',

		/**
		 * Validation method can also be a PCRE regular expression.
		 */
		'promocode' => '/^A-[0-9]{3}$/i'

	)

);
