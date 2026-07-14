<?php
namespace Smackcoders\AdminActivityLogger\Cleanup;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CleanupScheduler {

	const HOOK = 'wpaal_cleanup_old_activity_logs';

	public function register() {
		add_action( self::HOOK, array( $this, 'run' ) );
	}

	public function schedule() {
		if ( ! wp_next_scheduled( self::HOOK ) ) {
			wp_schedule_event( time(), 'daily', self::HOOK );
		}
	}

	public function unschedule() {
		$timestamp = wp_next_scheduled( self::HOOK );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::HOOK );
		}
	}

	public function run() {
		$manager = new CleanupManager();
		$manager->auto_cleanup();
	}
}
