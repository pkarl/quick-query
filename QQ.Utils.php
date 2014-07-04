<?php

Class QQ_Utils {

	/**
	 * qq_warn is an interface to PHP's trigger_error function; it will be used to
	 * provide users will debugging information about how QQ is functioning
	 *
	 * @param  string $warning_code a string index that corresponds to warning messages
	 *                              in $qq_warnings
	 */
	private static function warn( $warning_code ) {
		$qq_warnings = array(
			'EMPTY_SET' => 'QQuery has received data that will return an empty set',
			'ARR_CONVERSION' => 'QQuery is attempting to convert an unusual object to an array',
			'EMPTY_ARR' => 'QQuery has found or generated an empty array',
			'NON_OBJECT_RETURNED' = 'WP_Post has been requested, but WP did not return one'
		);

		trigger_error( $qq_warnings[$warning_code], E_USER_WARNING);
	}

}
