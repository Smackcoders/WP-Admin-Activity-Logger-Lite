<?php
/**
 * Plugin Name:       Admin Activity Logger Lite
 * Plugin URI:        https://wordpress.org/plugins/admin-activity-logger-lite/
 * Description:       Track important WordPress admin activity including post changes, plugin activation, user changes, settings updates, media, and exportable audit logs.
 * Version:           1.0.0
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Author:            Smackcoders
 * Author URI:        https://smackcoders.com/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       admin-activity-logger-lite
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPAAL_VERSION', '1.0.0' );
define( 'WPAAL_DB_VERSION', '1.0.0' );
define( 'WPAAL_PLUGIN_FILE', __FILE__ );
define( 'WPAAL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPAAL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPAAL_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once WPAAL_PLUGIN_DIR . 'vendor/autoload.php';

use Smackcoders\AdminActivityLogger\Core\Activator;
use Smackcoders\AdminActivityLogger\Core\Deactivator;
use Smackcoders\AdminActivityLogger\Core\Plugin;

register_activation_hook( __FILE__, array( Activator::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( Deactivator::class, 'deactivate' ) );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'wpaal', 'Smackcoders\AdminActivityLogger\Cli\CliCommands' );
}

function wpaal_run() {
	$plugin = Plugin::get_instance();
	$plugin->run();
}

add_action( 'plugins_loaded', 'wpaal_load_text_domain' );
function wpaal_load_text_domain() {
	$load_textdomain = 'load_plugin_textdomain';
	$load_textdomain( 'admin-activity-logger-lite', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

wpaal_run();