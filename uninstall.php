<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

use Smackcoders\AdminActivityLogger\Core\Uninstaller;

Uninstaller::uninstall();
