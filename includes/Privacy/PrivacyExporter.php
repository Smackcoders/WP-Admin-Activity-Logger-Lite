<?php
namespace Smackcoders\AdminActivityLogger\Privacy;

use Smackcoders\AdminActivityLogger\Logger\ActivityRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PrivacyExporter {

	public function register() {
		add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'register_exporter' ) );
	}

	public function register_exporter( $exporters ) {
		$exporters['admin-activity-logger-lite'] = array(
			'exporter_friendly_name' => __( 'Admin Activity Logs', 'admin-activity-logger-lite' ),
			'callback'               => array( $this, 'export_user_data' ),
		);
		return $exporters;
	}

	public function export_user_data( $email, $page = 1 ) {
		$repository = new ActivityRepository();
		$logs       = $repository->get_logs_by_email( $email );
		$data       = array();

		foreach ( $logs as $log ) {
			$data[] = array(
				'group_id'    => 'activity-logs',
				'group_label' => __( 'Admin Activity Logs', 'admin-activity-logger-lite' ),
				'item_id'     => 'activity-log-' . $log['id'],
				'data'        => array(
					array( 'name' => __( 'Date/Time', 'admin-activity-logger-lite' ),  'value' => esc_html( $log['created_at'] ) ),
					array( 'name' => __( 'Event Type', 'admin-activity-logger-lite' ), 'value' => esc_html( $log['event_type'] ) ),
					array( 'name' => __( 'Object Type', 'admin-activity-logger-lite' ), 'value' => esc_html( $log['object_type'] ) ),
					array( 'name' => __( 'Action', 'admin-activity-logger-lite' ),     'value' => esc_html( $log['action'] ) ),
					array( 'name' => __( 'Message', 'admin-activity-logger-lite' ),    'value' => esc_html( $log['message'] ) ),
					array( 'name' => __( 'IP Address', 'admin-activity-logger-lite' ), 'value' => esc_html( $log['ip_address'] ?? '' ) ),
					array( 'name' => __( 'User Agent', 'admin-activity-logger-lite' ), 'value' => esc_html( $log['user_agent'] ?? '' ) ),
					array( 'name' => __( 'Request URL', 'admin-activity-logger-lite' ), 'value' => esc_url( $log['request_url'] ?? '' ) ),
				),
			);
		}

		return array(
			'data' => $data,
			'done' => true,
		);
	}
}
