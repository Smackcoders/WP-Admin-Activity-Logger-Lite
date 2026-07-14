<?php
namespace Smackcoders\AdminActivityLogger\Watchers;

use Smackcoders\AdminActivityLogger\Logger\ActivityLogger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MediaActivityWatcher {

	private $logger;

	public function __construct( ActivityLogger $logger ) {
		$this->logger = $logger;
	}

	public function register() {
		add_action( 'add_attachment', array( $this, 'on_uploaded' ) );
		add_action( 'delete_attachment', array( $this, 'on_deleted' ), 10, 2 );
	}

	public function on_uploaded( $attachment_id ) {
		if ( ! $this->logger->should_log( 'media_uploaded' ) ) {
			return;
		}

		$post = get_post( $attachment_id );
		$url  = wp_get_attachment_url( $attachment_id );
		$mime = get_post_mime_type( $attachment_id );

		$this->logger->log( array(
			'event_type'   => 'media_uploaded',
			'object_type'  => 'attachment',
			'object_id'    => $attachment_id,
			'object_title' => $post ? $post->post_title : (string) $attachment_id,
			'action'       => 'upload',
			'message'      => sprintf(
				/* translators: 1: Media title, 2: MIME type. */
				__( 'Media "%1$s" (%2$s) was uploaded.', 'admin-activity-logger-lite' ),
				$post ? $post->post_title : $attachment_id,
				$mime
			),
			'new_value'    => $url ?: '',
		) );
	}

	public function on_deleted( $attachment_id, $post ) {
		if ( ! $this->logger->should_log( 'media_deleted' ) ) {
			return;
		}

		if ( ! $post instanceof \WP_Post ) {
			$post = get_post( $attachment_id );
		}

		$mime  = get_post_mime_type( $attachment_id );
		$title = $post ? $post->post_title : (string) $attachment_id;

		$this->logger->log( array(
			'event_type'   => 'media_deleted',
			'object_type'  => 'attachment',
			'object_id'    => $attachment_id,
			'object_title' => $title,
			'action'       => 'delete',
			'message'      => sprintf(
				/* translators: 1: Media title, 2: MIME type. */
				__( 'Media "%1$s" (%2$s) was deleted.', 'admin-activity-logger-lite' ),
				$title,
				$mime
			),
		) );
	}
}
