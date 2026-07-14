<?php
namespace Smackcoders\AdminActivityLogger\Cli;

use Smackcoders\AdminActivityLogger\Cleanup\CleanupManager;
use Smackcoders\AdminActivityLogger\Export\CsvExporter;
use Smackcoders\AdminActivityLogger\Logger\ActivityRepository;
use Smackcoders\AdminActivityLogger\Logger\LogFormatter;
use WP_CLI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CliCommands {

	/**
	 * List recent activity logs.
	 *
	 * ## OPTIONS
	 * [--limit=<n>]
	 * : Number of logs to show. Default 20.
	 *
	 * ## EXAMPLES
	 *     wp wpaal list
	 *     wp wpaal list --limit=50
	 */
	public function list( $args, $assoc_args ) {
		$repo  = new ActivityRepository();
		$limit = absint( $assoc_args['limit'] ?? 20 );
		$logs  = $repo->get_logs( array( 'limit' => $limit ) );

		if ( empty( $logs ) ) {
			WP_CLI::line( 'No logs found.' );
			return;
		}

		foreach ( $logs as $log ) {
			WP_CLI::line( sprintf(
				'[%d] %s | %s | %s | %s',
				$log['id'],
				$log['created_at'],
				$log['user_login'] ?? 'system',
				LogFormatter::event_type_label( $log['event_type'] ),
				LogFormatter::truncate( $log['message'], 80 )
			) );
		}
	}

	/**
	 * Show a summary of today's activity.
	 *
	 * ## EXAMPLES
	 *     wp wpaal summary
	 */
	public function summary( $args, $assoc_args ) {
		$repo  = new ActivityRepository();
		$today = $repo->count_today();
		$breakdown = $repo->get_summary_today();

		WP_CLI::line( "Total events today: {$today}" );

		foreach ( $breakdown as $row ) {
			WP_CLI::line( sprintf( '  %s: %d', ucfirst( $row['object_type'] ), (int) $row['count'] ) );
		}
	}

	/**
	 * Export activity logs to CSV.
	 *
	 * ## OPTIONS
	 * [--file=<path>]
	 * : Output file path. Defaults to activity-logs-YYYY-MM-DD.csv in current dir.
	 *
	 * ## EXAMPLES
	 *     wp wpaal export --file=/tmp/logs.csv
	 */
	public function export( $args, $assoc_args ) {
		$repo  = new ActivityRepository();
		$rows  = $repo->get_logs( array( 'limit' => 99999 ) );

		if ( empty( $rows ) ) {
			WP_CLI::warning( 'No logs to export.' );
			return;
		}

		$file = $assoc_args['file'] ?? ( getcwd() . '/activity-logs-' . gmdate( 'Y-m-d' ) . '.csv' );

		$csv_lines   = array();
		$csv_lines[] = '"ID","Date","User","Event","Object","Message","IP"';
		foreach ( $rows as $row ) {
			$csv_lines[] = sprintf(
				'"%s","%s","%s","%s","%s","%s","%s"',
				str_replace( '"', '""', $row['id'] ),
				str_replace( '"', '""', $row['created_at'] ),
				str_replace( '"', '""', $row['user_login'] ?? '' ),
				str_replace( '"', '""', $row['event_type'] ),
				str_replace( '"', '""', $row['object_title'] ?? '' ),
				str_replace( '"', '""', $row['message'] ),
				str_replace( '"', '""', $row['ip_address'] ?? '' )
			);
		}
		$csv_content = implode( "\n", $csv_lines );

		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		if ( ! $wp_filesystem->put_contents( $file, $csv_content, FS_CHMOD_FILE ) ) {
			WP_CLI::error( "Cannot write to: {$file}" );
		}

		WP_CLI::success( 'Exported ' . count( $rows ) . " logs to {$file}" );
	}

	/**
	 * Clear logs older than N days.
	 *
	 * ## OPTIONS
	 * [--older-than=<days>]
	 * : Number of days. Default 90.
	 *
	 * [--yes]
	 * : Skip confirmation.
	 *
	 * ## EXAMPLES
	 *     wp wpaal clear --older-than=30 --yes
	 */
	public function clear( $args, $assoc_args ) {
		$days = absint( $assoc_args['older-than'] ?? 90 );
		WP_CLI::confirm( "Delete logs older than {$days} days?", $assoc_args );
		$count = ( new CleanupManager() )->cleanup_older_than( $days );
		WP_CLI::success( "Deleted {$count} logs." );
	}

	/**
	 * Clear ALL activity logs.
	 *
	 * ## OPTIONS
	 * [--yes]
	 * : Skip confirmation.
	 *
	 * ## EXAMPLES
	 *     wp wpaal clear-all --yes
	 */
	public function clear_all( $args, $assoc_args ) {
		WP_CLI::confirm( 'Delete ALL activity logs? This cannot be undone.', $assoc_args );
		$count = ( new CleanupManager() )->clear_all();
		WP_CLI::success( "Deleted {$count} logs." );
	}
}
