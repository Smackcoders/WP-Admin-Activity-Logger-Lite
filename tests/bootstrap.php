<?php
if ( ! defined( 'ABSPATH' ) ) {
	if ( ! getenv( 'WP_TESTS_DIR' ) ) {
		exit;
	}
}

$wpaal_tests_dir = getenv( 'WP_TESTS_DIR' ) ?: rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
if ( ! file_exists( $wpaal_tests_dir . '/includes/functions.php' ) ) {
	echo esc_html( "WP test suite not found at: {$wpaal_tests_dir}\n" ); exit( 1 );
}
require_once $wpaal_tests_dir . '/includes/functions.php';
function wpaal_load_plugin() { require dirname( __DIR__ ) . '/admin-activity-logger-lite.php'; }
tests_add_filter( 'muplugins_loaded', 'wpaal_load_plugin' );
require $wpaal_tests_dir . '/includes/bootstrap.php';
