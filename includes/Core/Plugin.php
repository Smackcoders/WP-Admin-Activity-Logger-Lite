<?php
namespace Smackcoders\AdminActivityLogger\Core;

use Smackcoders\AdminActivityLogger\Admin\AdminMenu;
use Smackcoders\AdminActivityLogger\Admin\DashboardWidget;
use Smackcoders\AdminActivityLogger\Cleanup\CleanupScheduler;
use Smackcoders\AdminActivityLogger\Logger\ActivityLogger;
use Smackcoders\AdminActivityLogger\Privacy\PrivacyEraser;
use Smackcoders\AdminActivityLogger\Privacy\PrivacyExporter;
use Smackcoders\AdminActivityLogger\Watchers\MediaActivityWatcher;
use Smackcoders\AdminActivityLogger\Watchers\PluginActivityWatcher;
use Smackcoders\AdminActivityLogger\Watchers\PostActivityWatcher;
use Smackcoders\AdminActivityLogger\Watchers\SettingsActivityWatcher;
use Smackcoders\AdminActivityLogger\Watchers\UserActivityWatcher;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Plugin {

	private static $instance = null;

	private function __construct() {}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function run() {
		$logger = new ActivityLogger();

		( new PostActivityWatcher( $logger ) )->register();
		( new PluginActivityWatcher( $logger ) )->register();
		( new UserActivityWatcher( $logger ) )->register();
		( new SettingsActivityWatcher( $logger ) )->register();
		( new MediaActivityWatcher( $logger ) )->register();

		( new CleanupScheduler() )->register();

		( new PrivacyExporter() )->register();
		( new PrivacyEraser() )->register();

		if ( is_admin() ) {
			( new AdminMenu() )->register();
			( new DashboardWidget() )->register();
		}
	}

}
