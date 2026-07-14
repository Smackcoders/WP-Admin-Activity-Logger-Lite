<?php
class PluginLoadTest extends WP_UnitTestCase {

	public function test_constants() {
		$this->assertTrue( defined( 'WPAAL_VERSION' ) );
		$this->assertTrue( defined( 'WPAAL_PLUGIN_DIR' ) );
		$this->assertTrue( defined( 'WPAAL_DB_VERSION' ) );
	}

	public function test_table_created_on_activation() {
		global $wpdb;
		\Smackcoders\AdminActivityLogger\Core\Activator::activate();
		$table  = $wpdb->prefix . 'wpaal_activity_logs';
		$db     = $wpdb;
		$exists = $db->get_var( $db->prepare( 'SHOW TABLES LIKE %s', $table ) );
		$this->assertEquals( $table, $exists );
	}

	public function test_default_settings() {
		\Smackcoders\AdminActivityLogger\Core\Activator::activate();
		$s = get_option( 'wpaal_settings' );
		$this->assertIsArray( $s );
		$this->assertEquals( '1', $s['enable_logging'] );
		$this->assertEquals( '1', $s['log_posts'] );
		$this->assertEquals( '1', $s['log_plugins'] );
		$this->assertEquals( '90', $s['retention_days'] );
	}
}
