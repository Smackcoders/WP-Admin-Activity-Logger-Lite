<?php
namespace Smackcoders\AdminActivityLogger\Privacy;

use Smackcoders\AdminActivityLogger\Logger\ActivityRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PrivacyEraser {

	public function register() {
		add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'register_eraser' ) );
	}

	public function register_eraser( $erasers ) {
		$erasers['admin-activity-logger-lite'] = array(
			'eraser_friendly_name' => __( 'Admin Activity Logs', 'admin-activity-logger-lite' ),
			'callback'             => array( $this, 'erase_user_data' ),
		);
		return $erasers;
	}

	public function erase_user_data( $email, $page = 1 ) {
		$repository = new ActivityRepository();
		$count      = count( $repository->get_logs_by_email( $email ) );

		$repository->anonymize_by_email( $email );

		return array(
			'items_removed'  => $count,
			'items_retained' => 0,
			'messages'       => array(
				sprintf(
					/* translators: %d: count */
					_n( '%d activity log anonymized.', '%d activity logs anonymized.', $count, 'admin-activity-logger-lite' ),
					$count
				),
			),
			'done'           => true,
		);
	}
}
