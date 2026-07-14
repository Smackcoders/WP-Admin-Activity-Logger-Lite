<?php
namespace Smackcoders\AdminActivityLogger\Core;

use Smackcoders\AdminActivityLogger\Cleanup\CleanupScheduler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Deactivator {

	public static function deactivate() {
		( new CleanupScheduler() )->unschedule();
		flush_rewrite_rules();
	}
}
