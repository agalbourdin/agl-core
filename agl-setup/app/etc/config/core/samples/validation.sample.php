<?php
return array(

	// For the "user" model.
	'user' => array(

		// An exception will be thrown when attempting to set a non integer
		// value as a "zipcode" attribute to the "user" model. Validation
		// methods can be found in Core\Data\Validation.
		'zipcode' => 'isInt',
		'email'   => 'isEmail',

		// Validation method can alse be a PCRE regular expression.
		'promocode' => '/^A-[0-9]{3}$/i'

	)

);
