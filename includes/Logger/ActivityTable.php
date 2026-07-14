<?php
namespace Smackcoders\AdminActivityLogger\Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ActivityTable {

	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'wpaal_activity_logs';
	}

	public static function create_table() {
		global $wpdb;

		$table        = self::get_table_name();
		$charset_coll = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned DEFAULT NULL,
			user_login varchar(191) DEFAULT NULL,
			user_email varchar(191) DEFAULT NULL,
			event_type varchar(100) NOT NULL,
			object_type varchar(100) DEFAULT NULL,
			object_id bigint(20) unsigned DEFAULT NULL,
			object_title text DEFAULT NULL,
			action varchar(100) NOT NULL,
			message text NOT NULL,
			old_value longtext DEFAULT NULL,
			new_value longtext DEFAULT NULL,
			ip_address varchar(100) DEFAULT NULL,
			user_agent text DEFAULT NULL,
			request_url text DEFAULT NULL,
			created_at datetime NOT NULL,
			created_at_gmt datetime NOT NULL,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY event_type (event_type),
			KEY object_type (object_type),
			KEY object_id (object_id),
			KEY action (action(50)),
			KEY created_at_gmt (created_at_gmt)
		) {$charset_coll};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'wpaal_db_version', WPAAL_DB_VERSION );
	}
}
