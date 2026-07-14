<?php
namespace Smackcoders\AdminActivityLogger\Export;

use Smackcoders\AdminActivityLogger\Logger\ActivityRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CsvExporter {

	private $repository;

	public function __construct() {
		$this->repository = new ActivityRepository();
	}

	public function export( array $filters = array() ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to export logs.', 'admin-activity-logger-lite' ) );
		}

		$filters['limit']  = 99999;
		$filters['offset'] = 0;
		$rows = $this->repository->get_logs( $filters );

		$columns = apply_filters( 'wpaal_csv_export_columns', array(
			'id'            => __( 'ID', 'admin-activity-logger-lite' ),
			'created_at'    => __( 'Date/Time', 'admin-activity-logger-lite' ),
			'created_at_gmt' => __( 'Date/Time GMT', 'admin-activity-logger-lite' ),
			'user_id'       => __( 'User ID', 'admin-activity-logger-lite' ),
			'user_login'    => __( 'User Login', 'admin-activity-logger-lite' ),
			'user_email'    => __( 'User Email', 'admin-activity-logger-lite' ),
			'event_type'    => __( 'Event Type', 'admin-activity-logger-lite' ),
			'object_type'   => __( 'Object Type', 'admin-activity-logger-lite' ),
			'object_id'     => __( 'Object ID', 'admin-activity-logger-lite' ),
			'object_title'  => __( 'Object Title', 'admin-activity-logger-lite' ),
			'action'        => __( 'Action', 'admin-activity-logger-lite' ),
			'message'       => __( 'Message', 'admin-activity-logger-lite' ),
			'old_value'     => __( 'Old Value', 'admin-activity-logger-lite' ),
			'new_value'     => __( 'New Value', 'admin-activity-logger-lite' ),
			'ip_address'    => __( 'IP Address', 'admin-activity-logger-lite' ),
			'user_agent'    => __( 'User Agent', 'admin-activity-logger-lite' ),
			'request_url'   => __( 'Request URL', 'admin-activity-logger-lite' ),
		) );

		$date     = gmdate( 'Y-m-d' );
		$filename = apply_filters( 'wpaal_csv_export_filename', "wp-admin-activity-logs-{$date}.csv" );

		do_action( 'wpaal_logs_export_started', $filters );

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $filename ) . '"' );
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Pragma: no-cache' );

		$output = fopen( 'php://output', 'w' );

		fputcsv( $output, array_values( $columns ) );

		foreach ( $rows as $row ) {
			$line = array();
			foreach ( array_keys( $columns ) as $col ) {
				$line[] = $this->sanitize_csv_value( (string) ( $row[ $col ] ?? '' ) );
			}
			fputcsv( $output, $line );
		}

		do_action( 'wpaal_logs_export_completed', count( $rows ) );

		exit;
	}

	private function sanitize_csv_value( $value ) {
		$value = str_replace( '"', '""', $value );
		if ( in_array( substr( $value, 0, 1 ), array( '=', '+', '-', '@' ), true ) ) {
			$value = "'" . $value;
		}
		return $value;
	}
}
