<?php
namespace Smackcoders\AdminActivityLogger\Watchers;

use Smackcoders\AdminActivityLogger\Logger\ActivityLogger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PluginActivityWatcher {

	private $logger;

	public function __construct( ActivityLogger $logger ) {
		$this->logger = $logger;
	}

	public function register() {
		add_action( 'activated_plugin', array( $this, 'on_activated' ), 10, 2 );
		add_action( 'deactivated_plugin', array( $this, 'on_deactivated' ), 10, 2 );
	}

	public function on_activated( $plugin, $network_wide ) {
		if ( ! $this->logger->should_log( 'plugin_activated' ) ) {
			return;
		}

		$name = $this->get_plugin_name( $plugin );

		$this->logger->log( array(
			'event_type'   => 'plugin_activated',
			'object_type'  => 'plugin',
			'object_title' => $name,
			'action'       => 'activate',
			'message'      => sprintf(
				/* translators: %s: Plugin name. */
				__( 'Plugin "%s" was activated.', 'admin-activity-logger-lite' ),
				$name
			),
			'new_value'    => $plugin,
		) );
	}

	public function on_deactivated( $plugin, $network_deactivating ) {
		if ( ! $this->logger->should_log( 'plugin_deactivated' ) ) {
			return;
		}

		$name = $this->get_plugin_name( $plugin );

		$this->logger->log( array(
			'event_type'   => 'plugin_deactivated',
			'object_type'  => 'plugin',
			'object_title' => $name,
			'action'       => 'deactivate',
			'message'      => sprintf(
				/* translators: %s: Plugin name. */
				__( 'Plugin "%s" was deactivated.', 'admin-activity-logger-lite' ),
				$name
			),
			'old_value'    => $plugin,
		) );
	}

	private function get_plugin_name( $plugin_file ) {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$full_path = WP_PLUGIN_DIR . '/' . $plugin_file;
		if ( file_exists( $full_path ) ) {
			$data = get_plugin_data( $full_path, false, false );
			if ( ! empty( $data['Name'] ) ) {
				return $data['Name'];
			}
		}

		return $plugin_file;
	}
}
