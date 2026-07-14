<?php
use Smackcoders\AdminActivityLogger\Logger\LogFormatter;

class LogFormatterTest extends WP_UnitTestCase {

	public function test_event_type_label_known() {
		$this->assertEquals( 'Post Created',      LogFormatter::event_type_label( 'post_created' ) );
		$this->assertEquals( 'Plugin Activated',  LogFormatter::event_type_label( 'plugin_activated' ) );
		$this->assertEquals( 'Media Uploaded',    LogFormatter::event_type_label( 'media_uploaded' ) );
		$this->assertEquals( 'Settings Updated',  LogFormatter::event_type_label( 'settings_updated' ) );
		$this->assertEquals( 'User Deleted',      LogFormatter::event_type_label( 'user_deleted' ) );
	}

	public function test_event_type_label_unknown_returns_raw() {
		$this->assertEquals( 'custom_event', LogFormatter::event_type_label( 'custom_event' ) );
	}

	public function test_badge_class_created() {
		$this->assertStringContainsString( 'green', LogFormatter::event_type_badge_class( 'post_created' ) );
		$this->assertStringContainsString( 'green', LogFormatter::event_type_badge_class( 'plugin_activated' ) );
		$this->assertStringContainsString( 'green', LogFormatter::event_type_badge_class( 'media_uploaded' ) );
	}

	public function test_badge_class_deleted() {
		$this->assertStringContainsString( 'red', LogFormatter::event_type_badge_class( 'post_deleted' ) );
		$this->assertStringContainsString( 'red', LogFormatter::event_type_badge_class( 'plugin_deactivated' ) );
		$this->assertStringContainsString( 'red', LogFormatter::event_type_badge_class( 'post_trashed' ) );
	}

	public function test_badge_class_updated() {
		$this->assertStringContainsString( 'blue', LogFormatter::event_type_badge_class( 'settings_updated' ) );
		$this->assertStringContainsString( 'blue', LogFormatter::event_type_badge_class( 'post_updated' ) );
	}

	public function test_truncate_short_string() {
		$this->assertEquals( 'hello', LogFormatter::truncate( 'hello' ) );
	}

	public function test_truncate_long_string() {
		$long     = str_repeat( 'a', 200 );
		$result   = LogFormatter::truncate( $long, 120 );
		$this->assertLessThanOrEqual( 124, mb_strlen( $result ) ); // 120 + ellipsis
		$this->assertStringEndsWith( '…', $result );
	}

	public function test_relative_time_just_now() {
		$result = LogFormatter::relative_time( current_time( 'mysql' ) );
		$this->assertEquals( 'Just now', $result );
	}

	public function test_relative_time_invalid() {
		$result = LogFormatter::relative_time( 'not-a-date' );
		$this->assertEquals( 'not-a-date', $result );
	}
}
