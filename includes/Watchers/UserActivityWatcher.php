<?php
namespace Smackcoders\AdminActivityLogger\Watchers;

use Smackcoders\AdminActivityLogger\Logger\ActivityLogger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UserActivityWatcher {

	private $logger;

	public function __construct( ActivityLogger $logger ) {
		$this->logger = $logger;
	}

	public function register() {
		add_action( 'user_register', array( $this, 'on_user_created' ), 10, 1 );
		add_action( 'delete_user', array( $this, 'on_user_deleted' ), 10, 3 );
	}

	public function on_user_created( $user_id ) {
		if ( ! $this->logger->should_log( 'user_created' ) ) {
			return;
		}

		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return;
		}

		$this->logger->log( array(
			'event_type'   => 'user_created',
			'object_type'  => 'user',
			'object_id'    => $user_id,
			'object_title' => $user->user_login,
			'action'       => 'create',
			'message'      => sprintf(
				/* translators: 1: User login, 2: User email. */
				__( 'User "%1$s" (%2$s) was created.', 'admin-activity-logger-lite' ),
				$user->user_login,
				$user->user_email
			),
			'new_value'    => wp_json_encode( array(
				'user_id'    => $user_id,
				'user_login' => $user->user_login,
				'user_email' => $user->user_email,
				'roles'      => $user->roles,
			) ),
		) );
	}

	public function on_user_deleted( $user_id, $reassign, $user ) {
		if ( ! $this->logger->should_log( 'user_deleted' ) ) {
			return;
		}

		if ( ! $user instanceof \WP_User ) {
			$user = get_userdata( $user_id );
		}

		$login = $user ? $user->user_login : (string) $user_id;
		$email = $user ? $user->user_email : '';

		$this->logger->log( array(
			'event_type'   => 'user_deleted',
			'object_type'  => 'user',
			'object_id'    => $user_id,
			'object_title' => $login,
			'action'       => 'delete',
			'message'      => sprintf(
				/* translators: 1: User login, 2: User email. */
				__( 'User "%1$s" (%2$s) was deleted.', 'admin-activity-logger-lite' ),
				$login,
				$email
			),
			'old_value'    => wp_json_encode( array(
				'user_id'    => $user_id,
				'user_login' => $login,
				'user_email' => $email,
			) ),
		) );
	}
}
