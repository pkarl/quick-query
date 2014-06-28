<?php

$_tests_dir = getenv('WP_TESTS_DIR');
if ( !$_tests_dir ) $_tests_dir = '/tmp/wordpress';

require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {
	require dirname( __FILE__ ) . '/../QuickQuery.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';

// Activates this plugin in WordPress so it can be tested.
// $GLOBALS['wp_tests_options'] = array(
// 	'active_plugins' => array( 'quick-query/QuickQuery.php' ),
// );
