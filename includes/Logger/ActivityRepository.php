<?php
namespace Smackcoders\AdminActivityLogger\Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ActivityRepository {

	private $table;

	public function __construct() {
		$this->table = ActivityTable::get_table_name();
	}

	public function insert( array $data ) {
		global $wpdb;
		$db = $wpdb;

		$now     = current_time( 'mysql' );
		$now_gmt = current_time( 'mysql', true );

		$row = array(
			'user_id'       => isset( $data['user_id'] ) ? (int) $data['user_id'] : null,
			'user_login'    => isset( $data['user_login'] ) ? sanitize_text_field( $data['user_login'] ) : null,
			'user_email'    => isset( $data['user_email'] ) ? sanitize_email( $data['user_email'] ) : null,
			'event_type'    => sanitize_text_field( $data['event_type'] ?? '' ),
			'object_type'   => isset( $data['object_type'] ) ? sanitize_text_field( $data['object_type'] ) : null,
			'object_id'     => isset( $data['object_id'] ) ? absint( $data['object_id'] ) : null,
			'object_title'  => isset( $data['object_title'] ) ? sanitize_text_field( $data['object_title'] ) : null,
			'action'        => sanitize_text_field( $data['action'] ?? '' ),
			'message'       => sanitize_textarea_field( $data['message'] ?? '' ),
			'old_value'     => isset( $data['old_value'] ) ? wp_kses_post( (string) $data['old_value'] ) : null,
			'new_value'     => isset( $data['new_value'] ) ? wp_kses_post( (string) $data['new_value'] ) : null,
			'ip_address'    => isset( $data['ip_address'] ) ? sanitize_text_field( $data['ip_address'] ) : null,
			'user_agent'    => isset( $data['user_agent'] ) ? sanitize_textarea_field( $data['user_agent'] ) : null,
			'request_url'   => isset( $data['request_url'] ) ? esc_url_raw( $data['request_url'] ) : null,
			'created_at'    => $now,
			'created_at_gmt' => $now_gmt,
		);

		$result = $db->insert( $this->table, $row );

		if ( $result ) {
			wp_cache_flush();
		}

		return $result ? (int) $db->insert_id : false;
	}

	public function get( $id ) {
		global $wpdb;
		$db        = $wpdb;
		$cache_key = 'wpaal_log_' . $id;
		$cached    = wp_cache_get( $cache_key, 'wpaal' );
		if ( false !== $cached ) {
			return $cached;
		}
		$row = $db->get_row(
			$db->prepare( "SELECT * FROM {$wpdb->prefix}wpaal_activity_logs WHERE id = %d", (int) $id ),
			ARRAY_A
		);
		if ( $row ) {
			wp_cache_set( $cache_key, $row, 'wpaal', 3600 );
		}
		return $row;
	}

	public function get_logs( array $args = array() ) {
		global $wpdb;
		$db = $wpdb;

		$defaults = array(
			'user_id'     => 0,
			'event_type'  => '',
			'object_type' => '',
			'action'      => '',
			'search'      => '',
			'date_from'   => '',
			'date_to'     => '',
			'limit'       => 20,
			'offset'      => 0,
			'orderby'     => 'created_at_gmt',
			'order'       => 'DESC',
		);

		$args = wp_parse_args( $args, $defaults );

		$cache_key = 'wpaal_logs_' . md5( serialize( $args ) );
		$cached    = wp_cache_get( $cache_key, 'wpaal' );
		if ( false !== $cached ) {
			return $cached;
		}

		$allowed_orderby = array( 'id', 'event_type', 'user_login', 'action', 'created_at_gmt', 'object_type' );
		$orderby         = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'created_at_gmt';
		$order           = 'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC';

		$search_val = ! empty( $args['search'] ) ? $args['search'] : '';
		$like       = '%' . $db->esc_like( sanitize_text_field( $search_val ) ) . '%';

		$user_id_val     = ! empty( $args['user_id'] ) ? (int) $args['user_id'] : 0;
		$event_type_val  = ! empty( $args['event_type'] ) ? sanitize_text_field( $args['event_type'] ) : '';
		$object_type_val = ! empty( $args['object_type'] ) ? sanitize_text_field( $args['object_type'] ) : '';
		$action_val      = ! empty( $args['action'] ) ? sanitize_text_field( $args['action'] ) : '';
		$date_from_val   = ! empty( $args['date_from'] ) ? sanitize_text_field( $args['date_from'] ) . ' 00:00:00' : '';
		$date_to_val     = ! empty( $args['date_to'] ) ? sanitize_text_field( $args['date_to'] ) . ' 23:59:59' : '';

		$limit  = max( 1, (int) $args['limit'] );
		$offset = max( 0, (int) $args['offset'] );

		$where  = array();
		$params = array();

		if ( ! empty( $user_id_val ) ) {
			$where[]  = 'user_id = %d';
			$params[] = $user_id_val;
		}
		if ( ! empty( $event_type_val ) ) {
			$where[]  = 'event_type = %s';
			$params[] = $event_type_val;
		}
		if ( ! empty( $object_type_val ) ) {
			$where[]  = 'object_type = %s';
			$params[] = $object_type_val;
		}
		if ( ! empty( $action_val ) ) {
			$where[]  = 'action = %s';
			$params[] = $action_val;
		}
		if ( ! empty( $search_val ) ) {
			$where[]  = '(message LIKE %s OR object_title LIKE %s OR user_login LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}
		if ( ! empty( $args['date_from'] ) ) {
			$where[]  = 'created_at_gmt >= %s';
			$params[] = $date_from_val;
		}
		if ( ! empty( $args['date_to'] ) ) {
			$where[]  = 'created_at_gmt <= %s';
			$params[] = $date_to_val;
		}

		$where_sql = '';
		if ( ! empty( $where ) ) {
			$where_sql = 'WHERE ' . implode( ' AND ', $where );
		}

		if ( 'action' === $orderby ) {
			$orderby_clause = '`action`';
		} else {
			$orderby_clause = sanitize_key( $orderby );
		}

		$query    = "SELECT * FROM {$this->table} {$where_sql} ORDER BY {$orderby_clause} {$order} LIMIT %d OFFSET %d";
		$params[] = $limit;
		$params[] = $offset;

		$results = $db->get_results( $db->prepare( $query, ...$params ), ARRAY_A );

		wp_cache_set( $cache_key, $results, 'wpaal', 300 );
		return $results;
	}

	public function count_logs( array $args = array() ) {
		global $wpdb;
		$db = $wpdb;

		$defaults = array( 'user_id' => 0, 'event_type' => '', 'object_type' => '', 'action' => '', 'search' => '', 'date_from' => '', 'date_to' => '' );
		$args     = wp_parse_args( $args, $defaults );

		$cache_key = 'wpaal_count_' . md5( serialize( $args ) );
		$cached    = wp_cache_get( $cache_key, 'wpaal' );
		if ( false !== $cached ) {
			return (int) $cached;
		}

		$search_val = ! empty( $args['search'] ) ? $args['search'] : '';
		$like       = '%' . $db->esc_like( sanitize_text_field( $search_val ) ) . '%';

		$user_id_val     = ! empty( $args['user_id'] ) ? (int) $args['user_id'] : 0;
		$event_type_val  = ! empty( $args['event_type'] ) ? sanitize_text_field( $args['event_type'] ) : '';
		$object_type_val = ! empty( $args['object_type'] ) ? sanitize_text_field( $args['object_type'] ) : '';
		$action_val      = ! empty( $args['action'] ) ? sanitize_text_field( $args['action'] ) : '';
		$date_from_val   = ! empty( $args['date_from'] ) ? sanitize_text_field( $args['date_from'] ) . ' 00:00:00' : '';
		$date_to_val     = ! empty( $args['date_to'] ) ? sanitize_text_field( $args['date_to'] ) . ' 23:59:59' : '';

		$where  = array();
		$params = array();

		if ( ! empty( $user_id_val ) ) {
			$where[]  = 'user_id = %d';
			$params[] = $user_id_val;
		}
		if ( ! empty( $event_type_val ) ) {
			$where[]  = 'event_type = %s';
			$params[] = $event_type_val;
		}
		if ( ! empty( $object_type_val ) ) {
			$where[]  = 'object_type = %s';
			$params[] = $object_type_val;
		}
		if ( ! empty( $action_val ) ) {
			$where[]  = 'action = %s';
			$params[] = $action_val;
		}
		if ( ! empty( $search_val ) ) {
			$where[]  = '(message LIKE %s OR object_title LIKE %s OR user_login LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}
		if ( ! empty( $args['date_from'] ) ) {
			$where[]  = 'created_at_gmt >= %s';
			$params[] = $date_from_val;
		}
		if ( ! empty( $args['date_to'] ) ) {
			$where[]  = 'created_at_gmt <= %s';
			$params[] = $date_to_val;
		}

		$where_sql = '';
		if ( ! empty( $where ) ) {
			$where_sql = 'WHERE ' . implode( ' AND ', $where );
		}

		$query = "SELECT COUNT(*) FROM {$this->table} {$where_sql}";

		if ( ! empty( $params ) ) {
			$count = (int) $db->get_var( $db->prepare( $query, ...$params ) );
		} else {
			$count = (int) $db->get_var( $query );
		}

		wp_cache_set( $cache_key, $count, 'wpaal', 300 );
		return $count;
	}

	public function delete_older_than( $days ) {
		global $wpdb;
		$db   = $wpdb;
		$days = max( 1, (int) $days );
		wp_cache_delete( 'wpaal_logs_cache', 'wpaal' );
		return (int) $db->query(
			$db->prepare( "DELETE FROM {$wpdb->prefix}wpaal_activity_logs WHERE created_at_gmt < DATE_SUB(UTC_TIMESTAMP(), INTERVAL %d DAY)", $days )
		);
	}

	public function delete_all() {
		global $wpdb;
		$db = $wpdb;
		wp_cache_delete( 'wpaal_logs_cache', 'wpaal' );
		return (int) $db->query( "DELETE FROM {$wpdb->prefix}wpaal_activity_logs" );
	}

	public function get_logs_by_email( $email ) {
		global $wpdb;
		$db        = $wpdb;
		$cache_key = 'wpaal_logs_email_' . md5( $email );
		$cached    = wp_cache_get( $cache_key, 'wpaal' );
		if ( false !== $cached ) {
			return $cached;
		}
		$results = $db->get_results(
			$db->prepare( "SELECT * FROM {$wpdb->prefix}wpaal_activity_logs WHERE user_email = %s ORDER BY created_at_gmt DESC", sanitize_email( $email ) ),
			ARRAY_A
		) ?: array();
		wp_cache_set( $cache_key, $results, 'wpaal', 300 );
		return $results;
	}

	public function anonymize_by_email( $email ) {
		global $wpdb;
		$db = $wpdb;
		wp_cache_delete( 'wpaal_logs_cache', 'wpaal' );
		return $db->update(
			$this->table,
			array( 'user_email' => '', 'user_login' => __( '[anonymized]', 'admin-activity-logger-lite' ), 'ip_address' => '', 'user_agent' => '' ),
			array( 'user_email' => sanitize_email( $email ) )
		);
	}

	public function get_summary_today() {
		global $wpdb;
		$db        = $wpdb;
		$today     = current_time( 'Y-m-d' );
		$cache_key = 'wpaal_summary_' . $today;
		$cached    = wp_cache_get( $cache_key, 'wpaal' );
		if ( false !== $cached ) {
			return $cached;
		}
		$results = $db->get_results(
			$db->prepare(
				"SELECT object_type, COUNT(*) as count FROM {$wpdb->prefix}wpaal_activity_logs WHERE DATE(created_at_gmt) = %s GROUP BY object_type",
				$today
			),
			ARRAY_A
		) ?: array();
		wp_cache_set( $cache_key, $results, 'wpaal', 300 );
		return $results;
	}

	public function count_today() {
		global $wpdb;
		$db        = $wpdb;
		$today     = current_time( 'Y-m-d' );
		$cache_key = 'wpaal_count_today_' . $today;
		$cached    = wp_cache_get( $cache_key, 'wpaal' );
		if ( false !== $cached ) {
			return (int) $cached;
		}
		$count = (int) $db->get_var(
			$db->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}wpaal_activity_logs WHERE DATE(created_at_gmt) = %s", $today )
		);
		wp_cache_set( $cache_key, $count, 'wpaal', 300 );
		return $count;
	}

	public function delete( $id ) {
		global $wpdb;
		$db = $wpdb;
		$id = absint( $id );
		wp_cache_delete( 'wpaal_log_' . $id, 'wpaal' );
		wp_cache_delete( 'wpaal_logs_cache', 'wpaal' );
		wp_cache_flush();
		return $db->delete( $this->table, array( 'id' => $id ), array( '%d' ) );
	}
}


