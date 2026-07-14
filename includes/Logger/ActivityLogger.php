<?php
namespace Smackcoders\AdminActivityLogger\Logger;

use Smackcoders\AdminActivityLogger\Helpers\IpHelper;
use Smackcoders\AdminActivityLogger\Helpers\UserAgentHelper;
use Smackcoders\AdminActivityLogger\Helpers\ContextHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ActivityLogger {

	private $repository;
	private static $logging = false;
	private $pending_logs = array();

	public function __construct() {
		$this->repository = new ActivityRepository();
		add_action( 'shutdown', array( $this, 'flush_pending_logs' ), 20 );
	}

	public function log( array $data ) {
		if ( self::$logging ) {
			return false;
		}

		$settings = get_option( 'wpaal_settings', array() );
		if ( empty( $settings['enable_logging'] ) ) {
			return false;
		}

		if ( empty( $data['event_type'] ) || empty( $data['action'] ) || empty( $data['message'] ) ) {
			return false;
		}

		$should_log = apply_filters( 'wpaal_should_log_activity', true, $data['event_type'], $data );
		if ( ! $should_log ) {
			return false;
		}

		// List of event types that can be consolidated under bulk actions
		$bufferable_events = array(
			'post_created', 'post_updated', 'post_trashed', 'post_restored', 'post_deleted',
			'page_created', 'page_updated', 'page_trashed', 'page_restored', 'page_deleted',
			'plugin_activated', 'plugin_deactivated',
			'user_created', 'user_deleted',
			'media_uploaded', 'media_deleted'
		);

		if ( in_array( $data['event_type'], $bufferable_events, true ) ) {
			if ( empty( $this->pending_logs ) ) {
				add_action( 'shutdown', array( $this, 'flush_pending_logs' ), 20 );
			}
			$this->pending_logs[] = $data;
			return true;
		}

		return $this->insert_log_immediately( $data, $settings );
	}

	private function insert_log_immediately( array $data, $settings ) {
		self::$logging = true;

		$user_ctx    = $this->build_user_context();
		$request_ctx = $this->build_request_context( $settings );

		$record = array_merge( $user_ctx, $request_ctx, $data );
		$record = apply_filters( 'wpaal_log_data_before_insert', $record );

		do_action( 'wpaal_before_activity_logged', $record );

		$id = $this->repository->insert( $record );

		self::$logging = false;

		if ( $id ) {
			do_action( 'wpaal_activity_logged', $id, $record );
		}

		return $id;
	}

	public function flush_pending_logs() {
		if ( empty( $this->pending_logs ) ) {
			return;
		}

		$settings = get_option( 'wpaal_settings', array() );

		// Group pending logs by event_type
		$grouped = array();
		foreach ( $this->pending_logs as $log ) {
			$grouped[ $log['event_type'] ][] = $log;
		}

		foreach ( $grouped as $event_type => $logs ) {
			if ( count( $logs ) === 1 ) {
				$this->insert_log_immediately( $logs[0], $settings );
			} else {
				$count = count( $logs );
				$first_log = $logs[0];
				$object_type = $first_log['object_type'] ?? '';
				$action = $first_log['action'] ?? '';

				$titles = array();
				$old_values = array();
				$new_values = array();
				foreach ( $logs as $l ) {
					if ( ! empty( $l['object_title'] ) ) {
						$titles[] = $l['object_title'];
					}
					if ( isset( $l['old_value'] ) ) {
						$old_values[] = $l['old_value'];
					}
					if ( isset( $l['new_value'] ) ) {
						$new_values[] = $l['new_value'];
					}
				}

				$titles_list = implode( ', ', $titles );

				switch ( $action ) {
					case 'create':
						$action_text = __( 'Bulk created', 'admin-activity-logger-lite' );
						$action_field = 'bulk_create';
						break;
					case 'update':
						$action_text = __( 'Bulk updated', 'admin-activity-logger-lite' );
						$action_field = 'bulk_update';
						break;
					case 'trash':
						$action_text = __( 'Bulk moved to trash', 'admin-activity-logger-lite' );
						$action_field = 'bulk_trash';
						break;
					case 'restore':
						$action_text = __( 'Bulk restored from trash', 'admin-activity-logger-lite' );
						$action_field = 'bulk_restore';
						break;
					case 'delete':
						$action_text = __( 'Bulk permanently deleted', 'admin-activity-logger-lite' );
						$action_field = 'bulk_delete';
						break;
					case 'activate':
						$action_text = __( 'Bulk activated', 'admin-activity-logger-lite' );
						$action_field = 'bulk_activate';
						break;
					case 'deactivate':
						$action_text = __( 'Bulk deactivated', 'admin-activity-logger-lite' );
						$action_field = 'bulk_deactivate';
						break;
					case 'upload':
						$action_text = __( 'Bulk uploaded', 'admin-activity-logger-lite' );
						$action_field = 'bulk_upload';
						break;
					default:
						$action_text = __( 'Bulk action performed on', 'admin-activity-logger-lite' );
						$action_field = 'bulk_' . $action;
						break;
				}

				switch ( $object_type ) {
					case 'post':
						$object_plural = _n( 'post', 'posts', $count, 'admin-activity-logger-lite' );
						break;
					case 'page':
						$object_plural = _n( 'page', 'pages', $count, 'admin-activity-logger-lite' );
						break;
					case 'plugin':
						$object_plural = _n( 'plugin', 'plugins', $count, 'admin-activity-logger-lite' );
						break;
					case 'user':
						$object_plural = _n( 'user', 'users', $count, 'admin-activity-logger-lite' );
						break;
					case 'attachment':
						$object_plural = _n( 'media item', 'media items', $count, 'admin-activity-logger-lite' );
						break;
					default:
						$object_plural = $object_type;
						break;
				}

				/* translators: 1: action, 2: count, 3: object name plural, 4: list of titles */
				$msg = sprintf( __( '%1$s %2$d %3$s: %4$s.', 'admin-activity-logger-lite' ), $action_text, $count, $object_plural, $titles_list );

				$consolidated = array(
					'event_type'   => $event_type,
					'object_type'  => $object_type,
					'object_id'    => null,
					/* translators: %s: object name plural */
					'object_title' => sprintf( __( 'Multiple %s', 'admin-activity-logger-lite' ), ucfirst( $object_plural ) ),
					'action'       => $action_field,
					'message'      => $msg,
					'old_value'    => wp_json_encode( $old_values ),
					'new_value'    => wp_json_encode( $new_values ),
				);

				$this->insert_log_immediately( $consolidated, $settings );
			}
		}

		$this->pending_logs = array();
	}

	public function build_user_context() {
		$user = wp_get_current_user();

		if ( ! $user || ! $user->ID ) {
			return array( 'user_id' => null, 'user_login' => null, 'user_email' => null );
		}

		return array(
			'user_id'    => $user->ID,
			'user_login' => $user->user_login,
			'user_email' => $user->user_email,
		);
	}

	public function build_request_context( $settings = null ) {
		if ( null === $settings ) {
			$settings = get_option( 'wpaal_settings', array() );
		}

		$ctx = array(
			'ip_address'  => null,
			'user_agent'  => null,
			'request_url' => ContextHelper::get_request_url(),
		);

		if ( ! empty( $settings['store_ip'] ) ) {
			$ip = IpHelper::get_ip_address();
			if ( ! empty( $settings['anonymize_ip'] ) ) {
				$ip = IpHelper::anonymize_ip( $ip );
			}
			$ctx['ip_address'] = $ip;
		}

		if ( ! empty( $settings['store_user_agent'] ) ) {
			$ctx['user_agent'] = UserAgentHelper::get_user_agent();
		}

		return apply_filters( 'wpaal_request_context', $ctx );
	}

	public function should_log( $event_type, $data = array() ) {
		$settings = get_option( 'wpaal_settings', array() );

		if ( empty( $settings['enable_logging'] ) ) {
			return false;
		}

		$map = array(
			'post_created'       => 'log_posts',
			'post_updated'       => 'log_posts',
			'post_trashed'       => 'log_posts',
			'post_restored'      => 'log_posts',
			'post_deleted'       => 'log_posts',
			'page_created'       => 'log_pages',
			'page_updated'       => 'log_pages',
			'page_trashed'       => 'log_pages',
			'page_restored'      => 'log_pages',
			'page_deleted'       => 'log_pages',
			'plugin_activated'   => 'log_plugins',
			'plugin_deactivated' => 'log_plugins',
			'user_created'       => 'log_users',
			'user_deleted'       => 'log_users',
			'settings_updated'   => 'log_settings',
			'media_uploaded'     => 'log_media',
			'media_deleted'      => 'log_media',
		);

		$setting_key = $map[ $event_type ] ?? null;
		if ( $setting_key && empty( $settings[ $setting_key ] ) ) {
			return false;
		}

		return (bool) apply_filters( 'wpaal_should_log_activity', true, $event_type, $data );
	}
}
