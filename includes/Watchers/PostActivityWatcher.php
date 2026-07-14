<?php
namespace Smackcoders\AdminActivityLogger\Watchers;

use Smackcoders\AdminActivityLogger\Logger\ActivityLogger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PostActivityWatcher {

	private $logger;
	private $logged_transitions = array();
	private $temp_post_changes = array();
	private $temp_post_creates = array();
	private $skip_post_changes = array();

	public function __construct( ActivityLogger $logger ) {
		$this->logger = $logger;
	}

	public function register() {
		add_action( 'transition_post_status', array( $this, 'on_transition' ), 10, 3 );
		add_action( 'before_delete_post', array( $this, 'on_before_delete' ), 10, 2 );

		// Capture old/new meta values (featured image)
		add_filter( 'add_post_metadata', array( $this, 'on_add_post_metadata' ), 10, 5 );
		add_filter( 'update_post_metadata', array( $this, 'on_update_post_metadata' ), 10, 5 );
		add_filter( 'delete_post_metadata', array( $this, 'on_delete_post_metadata' ), 10, 5 );
		// Capture old/new tags and categories
		add_action( 'set_object_terms', array( $this, 'on_set_object_terms' ), 10, 6 );
		// Detailed post updates hook
		add_action( 'post_updated', array( $this, 'on_post_updated' ), 10, 3 );
		// Flush all accumulated changes during shutdown
		add_action( 'shutdown', array( $this, 'flush_temp_post_changes' ) );
		add_action( 'shutdown', array( $this, 'flush_temp_post_creates' ) );
	}

	public function on_add_post_metadata( $check, $object_id, $meta_key, $meta_value, $unique = false ) {
		if ( '_thumbnail_id' === $meta_key ) {
			if ( ! isset( $this->temp_post_changes[ $object_id ]['featured_image']['before'] ) ) {
				$this->temp_post_changes[ $object_id ]['featured_image']['before'] = '';
			}
			$this->temp_post_changes[ $object_id ]['featured_image']['after'] = $meta_value;
		}
		return $check;
	}

	public function on_update_post_metadata( $check, $object_id, $meta_key, $meta_value, $prev_value = '' ) {
		if ( '_thumbnail_id' === $meta_key ) {
			$old_val = get_post_meta( $object_id, '_thumbnail_id', true );
			if ( (string) $old_val !== (string) $meta_value ) {
				if ( ! isset( $this->temp_post_changes[ $object_id ]['featured_image']['before'] ) ) {
					$this->temp_post_changes[ $object_id ]['featured_image']['before'] = $old_val;
				}
				$this->temp_post_changes[ $object_id ]['featured_image']['after'] = $meta_value;
			}
		}
		return $check;
	}

	public function on_delete_post_metadata( $check, $object_id, $meta_key, $meta_value, $delete_all = false ) {
		if ( '_thumbnail_id' === $meta_key ) {
			$old_val = get_post_meta( $object_id, '_thumbnail_id', true );
			if ( $old_val ) {
				if ( ! isset( $this->temp_post_changes[ $object_id ]['featured_image']['before'] ) ) {
					$this->temp_post_changes[ $object_id ]['featured_image']['before'] = $old_val;
				}
				$this->temp_post_changes[ $object_id ]['featured_image']['after'] = '';
			}
		}
		return $check;
	}

	public function on_set_object_terms( $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids ) {
		if ( ! in_array( $taxonomy, array( 'category', 'post_tag' ), true ) ) {
			return;
		}

		$old_names = array();
		if ( ! empty( $old_tt_ids ) ) {
			foreach ( $old_tt_ids as $tt_id ) {
				$term = get_term_by( 'term_taxonomy_id', $tt_id );
				if ( $term && ! is_wp_error( $term ) ) {
					$old_names[] = $term->name;
				}
			}
		}

		$new_names = array();
		if ( ! empty( $tt_ids ) ) {
			foreach ( $tt_ids as $tt_id ) {
				$term = get_term_by( 'term_taxonomy_id', $tt_id );
				if ( $term && ! is_wp_error( $term ) ) {
					$new_names[] = $term->name;
				}
			}
		}

		sort( $old_names );
		sort( $new_names );

		if ( $old_names !== $new_names ) {
			$tax_label = ( 'category' === $taxonomy ) ? 'Categories' : 'Tags';
			$before_val = implode( ', ', $old_names );
			$after_val = implode( ', ', $new_names );

			if ( ! isset( $this->temp_post_changes[ $object_id ][ $tax_label ]['before'] ) ) {
				$this->temp_post_changes[ $object_id ][ $tax_label ]['before'] = $before_val;
			}
			$this->temp_post_changes[ $object_id ][ $tax_label ]['after'] = $after_val;
		}
	}

	public function on_transition( $new_status, $old_status, $post ) {
		if ( ! $post instanceof \WP_Post ) {
			return;
		}

		$supported_types = apply_filters( 'wpaal_supported_post_types', array( 'post', 'page' ) );
		if ( ! in_array( $post->post_type, $supported_types, true ) ) {
			return;
		}

		if ( in_array( $post->post_status, array( 'auto-draft', 'inherit' ), true ) ) {
			return;
		}

		if ( $old_status === $new_status ) {
			return;
		}

		$key = $post->ID . '_' . $new_status . '_' . $old_status;
		if ( isset( $this->logged_transitions[ $key ] ) ) {
			return;
		}
		$this->logged_transitions[ $key ] = true;

		$type = $post->post_type;

		$is_create = false;

		if ( ( 'new' === $old_status || 'auto-draft' === $old_status ) && in_array( $new_status, array( 'publish', 'draft', 'pending', 'private' ), true ) ) {
			$event     = $type . '_created';
			$action    = 'create';
			$is_create = true;
			/* translators: 1: Post type, 2: Post title. */
			$msg = sprintf( __( '%1$s "%2$s" was created.', 'admin-activity-logger-lite' ), ucfirst( $type ), $post->post_title );
		} elseif ( 'trash' === $new_status ) {
			$event  = $type . '_trashed';
			$action = 'trash';
			/* translators: 1: Post type, 2: Post title. */
			$msg = sprintf( __( '%1$s "%2$s" was moved to trash.', 'admin-activity-logger-lite' ), ucfirst( $type ), $post->post_title );
		} elseif ( 'trash' === $old_status && 'trash' !== $new_status ) {
			$event  = $type . '_restored';
			$action = 'restore';
			/* translators: 1: Post type, 2: Post title. */
			$msg = sprintf( __( '%1$s "%2$s" was restored from trash.', 'admin-activity-logger-lite' ), ucfirst( $type ), $post->post_title );
		} else {
			// Standard status transitions (e.g. publish→pending) are handled
			// entirely by on_post_updated which has access to before/after post
			// objects. Do NOT store anything here to avoid producing a
			// status-only log when Gutenberg fires a dedicated status REST call.
			return;
		}

		// Mark this post to skip consolidated updates in this request
		$this->skip_post_changes[ $post->ID ] = true;

		if ( ! $this->logger->should_log( $event ) ) {
			return;
		}

		// Defer creation logs to shutdown so categories/tags/featured image are set
		if ( $is_create ) {
			if ( ! isset( $this->temp_post_creates[ $post->ID ] ) ) {
				$this->temp_post_creates[ $post->ID ] = array(
					'event'  => $event,
					'action' => $action,
					'msg'    => $msg,
					'type'   => $type,
				);
			}
			return;
		}

		$this->logger->log( array(
			'event_type'   => $event,
			'object_type'  => $type,
			'object_id'    => $post->ID,
			'object_title' => $post->post_title,
			'action'       => $action,
			'message'      => $msg,
			'old_value'    => $old_status,
			'new_value'    => $new_status,
		) );
	}

	public function on_post_updated( $post_id, $post_after, $post_before ) {
		if ( ! $post_after instanceof \WP_Post || ! $post_before instanceof \WP_Post ) {
			return;
		}

		$supported_types = apply_filters( 'wpaal_supported_post_types', array( 'post', 'page' ) );
		if ( ! in_array( $post_after->post_type, $supported_types, true ) ) {
			return;
		}

		if ( in_array( $post_after->post_status, array( 'auto-draft', 'inherit' ), true ) ) {
			return;
		}

		if ( 'revision' === $post_after->post_type ) {
			return;
		}

		$old_status = $post_before->post_status;
		$new_status = $post_after->post_status;

		if ( ( 'new' === $old_status || 'auto-draft' === $old_status ) && in_array( $new_status, array( 'publish', 'draft', 'pending', 'private' ), true ) ) {
			return;
		}
		if ( 'trash' === $new_status ) {
			return;
		}
		if ( 'trash' === $old_status && 'trash' !== $new_status ) {
			return;
		}

		// Accumulate post title and content changes
		if ( $post_before->post_title !== $post_after->post_title ) {
			if ( ! isset( $this->temp_post_changes[ $post_id ]['title']['before'] ) ) {
				$this->temp_post_changes[ $post_id ]['title']['before'] = $post_before->post_title;
			}
			$this->temp_post_changes[ $post_id ]['title']['after'] = $post_after->post_title;
		}

		if ( $post_before->post_content !== $post_after->post_content ) {
			if ( ! isset( $this->temp_post_changes[ $post_id ]['content']['before'] ) ) {
				$this->temp_post_changes[ $post_id ]['content']['before'] = $post_before->post_content;
			}
			$this->temp_post_changes[ $post_id ]['content']['after'] = $post_after->post_content;
		}

		// Accumulate status change fallback
		if ( $old_status !== $new_status ) {
			if ( ! isset( $this->temp_post_changes[ $post_id ]['status']['before'] ) ) {
				$this->temp_post_changes[ $post_id ]['status']['before'] = $old_status;
			}
			$this->temp_post_changes[ $post_id ]['status']['after'] = $new_status;
		}
	}

	public function flush_temp_post_changes() {
		if ( empty( $this->temp_post_changes ) ) {
			return;
		}

		foreach ( $this->temp_post_changes as $post_id => $fields ) {
			if ( ! empty( $this->skip_post_changes[ $post_id ] ) ) {
				continue;
			}

			$post = get_post( $post_id );
			if ( ! $post ) {
				continue;
			}

			$type  = $post->post_type;
			$event = $type . '_updated';

			if ( ! $this->logger->should_log( $event ) ) {
				continue;
			}

			$changes = array();

			// 1. Title
			if ( isset( $fields['title'] ) ) {
				$changes['title'] = array(
					'before' => $fields['title']['before'],
					'after'  => $fields['title']['after'],
				);
			}

			// 2. Content — do paragraph-level diff and store only changed paragraphs
			if ( isset( $fields['content'] ) ) {
				$raw_before_html = $fields['content']['before'];
				$raw_after_html  = $fields['content']['after'];

				// Extract paragraphs from raw HTML before stripping tags
				preg_match_all( '/<p[^>]*>(.*?)<\/p>/is', $raw_before_html, $b_p_matches );
				preg_match_all( '/<p[^>]*>(.*?)<\/p>/is', $raw_after_html, $a_p_matches );

				if ( ! empty( $b_p_matches[1] ) || ! empty( $a_p_matches[1] ) ) {
					// Use <p>-tag split when HTML paragraphs are available
					$b_paras = array_values( array_filter( array_map( function( $p ) { return trim( wp_strip_all_tags( $p ) ); }, $b_p_matches[1] ) ) );
					$a_paras = array_values( array_filter( array_map( function( $p ) { return trim( wp_strip_all_tags( $p ) ); }, $a_p_matches[1] ) ) );
				} else {
					// Fallback: split plain stripped text by double newline
					$b_paras = array_values( array_filter( array_map( 'trim', preg_split( '/\n{2,}/', trim( wp_strip_all_tags( $raw_before_html ) ) ) ) ) );
					$a_paras = array_values( array_filter( array_map( 'trim', preg_split( '/\n{2,}/', trim( wp_strip_all_tags( $raw_after_html ) ) ) ) ) );
				}

				// Find paragraphs that changed
				$max_p        = max( count( $b_paras ), count( $a_paras ) );
				$changed_before = array();
				$changed_after  = array();
				for ( $pi = 0; $pi < $max_p; $pi++ ) {
					$b = isset( $b_paras[ $pi ] ) ? $b_paras[ $pi ] : '';
					$a = isset( $a_paras[ $pi ] ) ? $a_paras[ $pi ] : '';
					if ( $b !== $a ) {
						$changed_before[] = $b;
						$changed_after[]  = $a;
					}
				}

				if ( ! empty( $changed_before ) || ! empty( $changed_after ) ) {
					$changes['content'] = array(
						'before' => implode( "\n\n", $changed_before ),
						'after'  => implode( "\n\n", $changed_after ),
					);
				}
			}

			// 3. Status
			if ( isset( $fields['status'] ) ) {
				$changes['status'] = array(
					'before' => $fields['status']['before'],
					'after'  => $fields['status']['after'],
				);
			}

			// 4. Featured Image
			if ( isset( $fields['featured_image'] ) ) {
				$before_val = $fields['featured_image']['before'];
				$after_val  = $fields['featured_image']['after'];

				if ( (string) $before_val !== (string) $after_val ) {
					$before_title = $before_val ? get_the_title( $before_val ) : '';
					$after_title  = $after_val ? get_the_title( $after_val ) : '';

					$changes['featured_image'] = array(
						'before' => $before_title ? sprintf( '%s (ID: %d)', $before_title, $before_val ) : __( 'None', 'admin-activity-logger-lite' ),
						'after'  => $after_title ? sprintf( '%s (ID: %d)', $after_title, $after_val ) : __( 'None', 'admin-activity-logger-lite' ),
					);
				}
			}

			// 5. Terms (Categories/Tags)
			foreach ( array( 'Categories', 'Tags' ) as $tax_label ) {
				if ( isset( $fields[ $tax_label ] ) ) {
					$changes[ $tax_label ] = array(
						'before' => $fields[ $tax_label ]['before'] ?: __( 'None', 'admin-activity-logger-lite' ),
						'after'  => $fields[ $tax_label ]['after'] ?: __( 'None', 'admin-activity-logger-lite' ),
					);
				}
			}

			if ( empty( $changes ) ) {
				continue;
			}

			$old_display = array();
			$new_display = array();
			foreach ( $changes as $field => $change ) {
				$old_display[ $field ] = $change['before'];
				$new_display[ $field ] = $change['after'];
			}

			$msg = sprintf(
				/* translators: 1: Post type, 2: Post title. */
				__( '%1$s "%2$s" was updated.', 'admin-activity-logger-lite' ),
				ucfirst( $type ),
				$post->post_title
			);

			$this->logger->log( array(
				'event_type'   => $event,
				'object_type'  => $type,
				'object_id'    => $post_id,
				'object_title' => $post->post_title,
				'action'       => 'update',
				'message'      => $msg,
				'old_value'    => wp_json_encode( $old_display ),
				'new_value'    => wp_json_encode( $new_display ),
			) );
		}

		$this->temp_post_changes = array();
	}

	public function flush_temp_post_creates() {
		if ( empty( $this->temp_post_creates ) ) {
			return;
		}

		foreach ( $this->temp_post_creates as $post_id => $data ) {
			$post = get_post( $post_id );
			if ( ! $post ) {
				continue;
			}

			$event  = $data['event'];
			$action = $data['action'];
			$msg    = $data['msg'];
			$type   = $data['type'];

			// Build creation details
			$details = array(
				'status' => $post->post_status,
			);

			$content_plain = trim( wp_strip_all_tags( $post->post_content ) );
			if ( ! empty( $content_plain ) ) {
				$details['content'] = wp_trim_words( $content_plain, 80, '...' );
			}

			$thumbnail_id = get_post_thumbnail_id( $post_id );
			if ( $thumbnail_id ) {
				$details['featured_image'] = get_the_title( $thumbnail_id );
			}

			if ( 'post' === $type ) {
				$cats = wp_get_post_categories( $post_id, array( 'fields' => 'names' ) );
				if ( ! empty( $cats ) && is_array( $cats ) ) {
					$details['categories'] = implode( ', ', $cats );
				}
				$tags_list = wp_get_post_tags( $post_id, array( 'fields' => 'names' ) );
				if ( ! empty( $tags_list ) && is_array( $tags_list ) ) {
					$details['tags'] = implode( ', ', $tags_list );
				}
			}

			$this->logger->log( array(
				'event_type'   => $event,
				'object_type'  => $type,
				'object_id'    => $post_id,
				'object_title' => $post->post_title,
				'action'       => $action,
				'message'      => $msg,
				'old_value'    => '',
				'new_value'    => wp_json_encode( $details ),
			) );
		}

		$this->temp_post_creates = array();
	}

	public function on_before_delete( $post_id, $post ) {
		if ( ! $post instanceof \WP_Post ) {
			return;
		}

		$supported_types = apply_filters( 'wpaal_supported_post_types', array( 'post', 'page' ) );
		if ( ! in_array( $post->post_type, $supported_types, true ) ) {
			return;
		}

		$type  = $post->post_type;
		$event = $type . '_deleted';

		if ( ! $this->logger->should_log( $event ) ) {
			return;
		}

		$this->logger->log( array(
			'event_type'   => $event,
			'object_type'  => $type,
			'object_id'    => $post->ID,
			'object_title' => $post->post_title,
			'action'       => 'delete',
			'message'      => sprintf(
				/* translators: 1: Post type, 2: Post title. */
				__( '%1$s "%2$s" was permanently deleted.', 'admin-activity-logger-lite' ),
				ucfirst( $type ),
				$post->post_title
			),
		) );
	}
}
