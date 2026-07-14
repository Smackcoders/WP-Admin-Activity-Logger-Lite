<?php
namespace Smackcoders\AdminActivityLogger\Core;

use Smackcoders\AdminActivityLogger\Logger\ActivityTable;
use Smackcoders\AdminActivityLogger\Cleanup\CleanupScheduler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Activator {

	public static function activate() {
		ActivityTable::create_table();
		self::set_default_settings();
		( new CleanupScheduler() )->schedule();
		flush_rewrite_rules();
	}

	private static function set_default_settings() {
		if ( false !== get_option( 'wpaal_settings' ) ) {
			return;
		}

		update_option( 'wpaal_settings', array(
			'enable_logging'          => '1',
			'log_posts'               => '1',
			'log_pages'               => '1',
			'log_plugins'             => '1',
			'log_users'               => '1',
			'log_settings'            => '1',
			'log_media'               => '1',
			'store_ip'                => '1',
			'anonymize_ip'            => '0',
			'store_user_agent'        => '0',
			'retention_days'          => '90',
			'auto_cleanup'            => '0',
			'show_dashboard_widget'   => '1',
			'delete_data_on_uninstall' => '0',
		) );
	}
}
