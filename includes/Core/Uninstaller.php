<?php
namespace Smackcoders\AdminActivityLogger\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Uninstaller {

	public static function uninstall() {
		$settings = get_option( 'wpaal_settings', array() );

		if ( empty( $settings['delete_data_on_uninstall'] ) ) {
			return;
		}

		global $wpdb;

		delete_option( 'wpaal_settings' );
		delete_option( 'wpaal_db_version' );
		delete_option( 'wpaal_last_cleanup' );

		$db = $wpdb;
		$db->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}wpaal_activity_logs`" );
	}
}
