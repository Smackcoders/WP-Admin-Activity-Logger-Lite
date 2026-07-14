<?php
namespace Smackcoders\AdminActivityLogger\Cleanup;

use Smackcoders\AdminActivityLogger\Logger\ActivityRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CleanupManager {

	private $repository;

	public function __construct() {
		$this->repository = new ActivityRepository();
	}

	public function cleanup_older_than( $days ) {
		$days = max( 1, (int) $days );
		$days = (int) apply_filters( 'wpaal_log_retention_days', $days );

		do_action( 'wpaal_before_logs_cleared', $days );

		$deleted = $this->repository->delete_older_than( $days );
		update_option( 'wpaal_last_cleanup', current_time( 'mysql', true ) );

		do_action( 'wpaal_after_logs_cleared', $deleted );

		return $deleted;
	}

	public function clear_all() {
		do_action( 'wpaal_before_logs_cleared', 0 );

		$deleted = $this->repository->delete_all();
		update_option( 'wpaal_last_cleanup', current_time( 'mysql', true ) );

		do_action( 'wpaal_after_logs_cleared', $deleted );

		return $deleted;
	}

	public function auto_cleanup() {
		$settings = get_option( 'wpaal_settings', array() );

		if ( empty( $settings['auto_cleanup'] ) ) {
			return 0;
		}

		$days = max( 1, (int) ( $settings['retention_days'] ?? 90 ) );
		return $this->cleanup_older_than( $days );
	}
}
