<?php

Class QQ_Utils {

	/**
	 * qq_warn is an interface to PHP's trigger_error function; it will be used to
	 * provide users will debugging information about how QQ is functioning
	 *
	 * @param  string $warning_code a string index that corresponds to warning messages
	 *                              in $qq_warnings
	 */
	public static function warn( $warning_code ) {
		$qq_warnings = array(
			'EMPTY_SET' => 'QQuery has received data that will return an empty set',
			'ARR_CONVERSION' => 'QQuery is attempting to convert an unusual object to an array',
			'EMPTY_ARR' => 'QQuery has found or generated an empty array',
			'NON_OBJECT_RETURNED' => 'WP_Post has been requested, but WP did not return one'
		);

		trigger_error( $qq_warnings[$warning_code], E_USER_WARNING);
	}

	/**
	 * Utility method
	 * @param  string $tag_slug a term slug (ex: 'government')
	 * @return object           PHP object with one or more term id, name and slug
	 */
	public static function get_data_for_term( $term_slug ) {
		global $wpdb;

		$query = 'SELECT wp_terms.term_id, wp_terms.name, wp_terms.slug, taxonomy FROM wp_terms LEFT JOIN wp_term_taxonomy ON wp_terms.`term_id` = wp_term_taxonomy.term_id WHERE wp_terms.`slug` = "' . $term_slug . '"';
		return $wpdb->get_results( $query, OBJECT );
	}

}
