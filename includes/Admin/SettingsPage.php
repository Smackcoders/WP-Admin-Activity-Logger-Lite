<?php
namespace Smackcoders\AdminActivityLogger\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SettingsPage {

	const OPTION = 'wpaal_settings';

	public function register() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function register_settings() {
		register_setting( 'wpaal_settings_group', self::OPTION, array( 'sanitize_callback' => array( $this, 'sanitize' ) ) );
	}

	public function sanitize( $input ) {
		$settings = get_option( self::OPTION, array() );

		$checkboxes = array(
			'enable_logging', 'log_posts', 'log_pages', 'log_plugins', 'log_users',
			'log_settings', 'log_media', 'store_ip', 'anonymize_ip', 'store_user_agent',
			'auto_cleanup', 'show_dashboard_widget', 'delete_data_on_uninstall',
			'connector_enabled',
		);

		foreach ( $checkboxes as $key ) {
			$settings[ $key ] = ! empty( $input[ $key ] ) ? '1' : '0';
		}

		$settings['retention_days']    = max( 1, absint( $input['retention_days'] ?? 90 ) );
		$settings['connector_provider'] = sanitize_text_field( $input['connector_provider'] ?? '' );
		$settings['connector_api_key']  = sanitize_text_field( $input['connector_api_key'] ?? '' );
		$settings['connector_endpoint'] = esc_url_raw( $input['connector_endpoint'] ?? '' );

		return $settings;
	}

	public function render() {
		$settings = get_option( self::OPTION, array() );
		$roles    = get_editable_roles();
		$cap_options = array(
			'manage_options'     => __( 'Administrator (manage_options)', 'admin-activity-logger-lite' ),
			'edit_others_posts'  => __( 'Editor (edit_others_posts)', 'admin-activity-logger-lite' ),
			'manage_categories'  => __( 'Editor+ (manage_categories)', 'admin-activity-logger-lite' ),
			'moderate_comments'  => __( 'Editor+ (moderate_comments)', 'admin-activity-logger-lite' ),
			'edit_posts'         => __( 'Author+ (edit_posts)', 'admin-activity-logger-lite' ),
			'read'               => __( 'Subscriber+ (read)', 'admin-activity-logger-lite' ),
		);
		?>
		<div class="wrap wpaal-settings-wrap">
			<h1 style="display:none;"><?php esc_html_e( 'Admin Activity Logger Settings', 'admin-activity-logger-lite' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'wpaal_settings_group' ); ?>

				<div class="wpaal-settings-tabs">
					<nav class="wpaal-tab-nav">
						<div class="wpaal-sidebar-brand">
							<div class="wpaal-sidebar-logo">
								<span class="dashicons dashicons-list-view"></span>
							</div>
							<div class="wpaal-sidebar-title-wrap">
								<div class="wpaal-sidebar-title">Activity Logger</div>
								<div class="wpaal-sidebar-subtitle">Settings</div>
							</div>
						</div>
						<a href="#wpaal-tab-logging"    class="wpaal-tab-link active"><span class="dashicons dashicons-media-text"></span> <?php esc_html_e( 'Logging', 'admin-activity-logger-lite' ); ?></a>
						<a href="#wpaal-tab-privacy"    class="wpaal-tab-link"><span class="dashicons dashicons-shield"></span> <?php esc_html_e( 'Privacy &amp; IP', 'admin-activity-logger-lite' ); ?></a>
						<a href="#wpaal-tab-retention"  class="wpaal-tab-link"><span class="dashicons dashicons-clock"></span> <?php esc_html_e( 'Retention', 'admin-activity-logger-lite' ); ?></a>
						<a href="#wpaal-tab-display"    class="wpaal-tab-link"><span class="dashicons dashicons-visibility"></span> <?php esc_html_e( 'Display', 'admin-activity-logger-lite' ); ?></a>
						<a href="#wpaal-tab-alerts"     class="wpaal-tab-link wpaal-pro-tab-link"><span class="dashicons dashicons-bell"></span> <?php esc_html_e( 'Real-time Alerts', 'admin-activity-logger-lite' ); ?> <span class="wpaal-pro-badge">PRO</span></a>
						<a href="#wpaal-tab-external"    class="wpaal-tab-link wpaal-pro-tab-link"><span class="dashicons dashicons-cloud-saved"></span> <?php esc_html_e( 'External Storage', 'admin-activity-logger-lite' ); ?> <span class="wpaal-pro-badge">PRO</span></a>
						<a href="#wpaal-tab-cpt"         class="wpaal-tab-link wpaal-pro-tab-link"><span class="dashicons dashicons-category"></span> <?php esc_html_e( 'CPT &amp; Taxonomy', 'admin-activity-logger-lite' ); ?> <span class="wpaal-pro-badge">PRO</span></a>
						<a href="#wpaal-tab-help"        class="wpaal-tab-link"><span class="dashicons dashicons-editor-help"></span> <?php esc_html_e( 'Help &amp; Support', 'admin-activity-logger-lite' ); ?></a>
					</nav>

					<div class="wpaal-main-content">
					<!-- TAB: Logging -->
					<div id="wpaal-tab-logging" class="wpaal-tab-panel active">
						<div class="wpaal-header-wrap">
							<h2 class="wpaal-panel-header"><?php esc_html_e( 'Logging', 'admin-activity-logger-lite' ); ?></h2>
							<div class="wpaal-panel-subheader"><?php esc_html_e( 'Configure activity logging settings', 'admin-activity-logger-lite' ); ?></div>
						</div>
						<nav class="wpaal-inner-tab-nav">
							<a href="#wpaal-inner-tab-general" class="wpaal-inner-tab-link active"><span class="dashicons dashicons-text-page"></span> <?php esc_html_e( 'General Log', 'admin-activity-logger-lite' ); ?></a>
							<a href="#wpaal-tab-woocommerce" class="wpaal-inner-tab-link wpaal-pro-tab-link"><span class="dashicons dashicons-cart"></span> <?php esc_html_e( 'WooCommerce Log', 'admin-activity-logger-lite' ); ?> <span class="wpaal-pro-badge">PRO</span></a>
							<a href="#wpaal-tab-multisite" class="wpaal-inner-tab-link wpaal-pro-tab-link"><span class="dashicons dashicons-admin-network"></span> <?php esc_html_e( 'Multisite Log', 'admin-activity-logger-lite' ); ?> <span class="wpaal-pro-badge">PRO</span></a>
							<a href="#wpaal-tab-access" class="wpaal-inner-tab-link wpaal-pro-tab-link"><span class="dashicons dashicons-admin-users"></span> <?php esc_html_e( 'Access Control', 'admin-activity-logger-lite' ); ?> <span class="wpaal-pro-badge">PRO</span></a>
						</nav>
						<div class="wpaal-tab-panel-content">
						<div id="wpaal-inner-tab-general" class="wpaal-inner-tab-panel active">
						<table class="form-table" role="presentation">
							<tr>
								<th><?php esc_html_e( 'Enable Logging', 'admin-activity-logger-lite' ); ?></th>
								<td><label><input type="checkbox" name="wpaal_settings[enable_logging]" value="1" <?php checked( '1', $settings['enable_logging'] ?? '1' ); ?>>
								<?php esc_html_e( 'Enable activity logging globally', 'admin-activity-logger-lite' ); ?></label></td>
							</tr>
							<?php
							$event_settings = array(
								'log_posts'    => __( 'Log post create/update/delete', 'admin-activity-logger-lite' ),
								'log_pages'    => __( 'Log page create/update/delete', 'admin-activity-logger-lite' ),
								'log_plugins'  => __( 'Log plugin activation/deactivation', 'admin-activity-logger-lite' ),
								'log_users'    => __( 'Log user creation/deletion', 'admin-activity-logger-lite' ),
								'log_settings' => __( 'Log settings changes', 'admin-activity-logger-lite' ),
								'log_media'    => __( 'Log media upload/delete', 'admin-activity-logger-lite' ),
							);
							foreach ( $event_settings as $key => $label ) : ?>
							<tr>
								<th><?php echo esc_html( $label ); ?></th>
								<td><input type="checkbox" name="wpaal_settings[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( '1', $settings[ $key ] ?? '1' ); ?>></td>
							</tr>
							<?php endforeach; ?>
						</table>
						</div> <!-- wpaal-inner-tab-general -->

					<!-- PRO TAB: Access Control (Inner Tab) -->
					<div id="wpaal-tab-access" class="wpaal-inner-tab-panel">
						<div class="wpaal-pro-notice">
							<span class="wpaal-pro-notice-icon">&#11088;</span>
							<p><?php esc_html_e( 'This is a Pro feature. Upgrade to unlock Role-Based Access Control.', 'admin-activity-logger-lite' ); ?></p>
						</div>
					</div>

					<!-- PRO TAB: WooCommerce Logs (Inner Tab) -->
					<div id="wpaal-tab-woocommerce" class="wpaal-inner-tab-panel">
						<div class="wpaal-pro-notice">
							<span class="wpaal-pro-notice-icon">&#11088;</span>
							<p><?php esc_html_e( 'This is a Pro feature. Upgrade to unlock WooCommerce Activity Tracking.', 'admin-activity-logger-lite' ); ?></p>
						</div>
					</div>

					<!-- PRO TAB: Multisite Logs (Inner Tab) -->
					<div id="wpaal-tab-multisite" class="wpaal-inner-tab-panel">
						<div class="wpaal-pro-notice">
							<span class="wpaal-pro-notice-icon">&#11088;</span>
							<p><?php esc_html_e( 'This is a Pro feature. Upgrade to unlock Multisite Network Control.', 'admin-activity-logger-lite' ); ?></p>
						</div>
					</div>
					</div><!-- End wpaal-tab-panel-content -->
					</div><!-- End of wpaal-tab-logging -->

					<!-- TAB: Privacy -->
					<div id="wpaal-tab-privacy" class="wpaal-tab-panel">
						<div class="wpaal-header-wrap">
							<h2 class="wpaal-panel-header"><?php esc_html_e( 'Privacy & IP', 'admin-activity-logger-lite' ); ?></h2>
							<div class="wpaal-panel-subheader"><?php esc_html_e( 'Manage data privacy and IP address retention', 'admin-activity-logger-lite' ); ?></div>
						</div>
						<div class="wpaal-tab-panel-content">
						<table class="form-table" role="presentation">
							<tr>
								<th><?php esc_html_e( 'Store IP Address', 'admin-activity-logger-lite' ); ?></th>
								<td><input type="checkbox" name="wpaal_settings[store_ip]" value="1" <?php checked( '1', $settings['store_ip'] ?? '1' ); ?>></td>
							</tr>
							<tr>
								<th><?php esc_html_e( 'Anonymize IP Address', 'admin-activity-logger-lite' ); ?></th>
								<td><input type="checkbox" name="wpaal_settings[anonymize_ip]" value="1" <?php checked( '1', $settings['anonymize_ip'] ?? '0' ); ?>>
								<p class="description"><?php esc_html_e( 'Remove last octet of IPv4 / last 64 bits of IPv6 before storing.', 'admin-activity-logger-lite' ); ?></p></td>
							</tr>
							<tr>
								<th><?php esc_html_e( 'Store User Agent', 'admin-activity-logger-lite' ); ?></th>
								<td><input type="checkbox" name="wpaal_settings[store_user_agent]" value="1" <?php checked( '1', $settings['store_user_agent'] ?? '0' ); ?>></td>
							</tr>
							<tr>
								<th><?php esc_html_e( 'Delete Data on Uninstall', 'admin-activity-logger-lite' ); ?></th>
								<td><input type="checkbox" name="wpaal_settings[delete_data_on_uninstall]" value="1" <?php checked( '1', $settings['delete_data_on_uninstall'] ?? '0' ); ?>></td>
							</tr>
						</table>
						<div class="wpaal-privacy-note">
							<h3><?php esc_html_e( 'Privacy Policy Suggestion', 'admin-activity-logger-lite' ); ?></h3>
							<p><?php esc_html_e( 'This site records admin activity for security, troubleshooting, and accountability. Recorded data may include user identity, IP address, browser, action performed, and related object. Data is retained based on the configured retention period.', 'admin-activity-logger-lite' ); ?></p>
						</div>
						</div><!-- End wpaal-tab-panel-content -->
					</div>

					<!-- TAB: Retention -->
					<div id="wpaal-tab-retention" class="wpaal-tab-panel">
						<div class="wpaal-header-wrap">
							<h2 class="wpaal-panel-header"><?php esc_html_e( 'Retention', 'admin-activity-logger-lite' ); ?></h2>
							<div class="wpaal-panel-subheader"><?php esc_html_e( 'Configure log auto-cleanup and retention period', 'admin-activity-logger-lite' ); ?></div>
						</div>
						<div class="wpaal-tab-panel-content">
						<table class="form-table" role="presentation">
							<tr>
								<th><label for="wpaal_retention"><?php esc_html_e( 'Log Retention (days)', 'admin-activity-logger-lite' ); ?></label></th>
								<td><input type="number" id="wpaal_retention" name="wpaal_settings[retention_days]" value="<?php echo esc_attr( $settings['retention_days'] ?? '90' ); ?>" min="1" class="small-text">
								<p class="description"><?php esc_html_e( 'Logs older than this many days will be deleted during auto cleanup.', 'admin-activity-logger-lite' ); ?></p></td>
							</tr>
							<tr>
								<th><?php esc_html_e( 'Auto Cleanup', 'admin-activity-logger-lite' ); ?></th>
								<td><input type="checkbox" name="wpaal_settings[auto_cleanup]" value="1" <?php checked( '1', $settings['auto_cleanup'] ?? '0' ); ?>>
								<p class="description"><?php esc_html_e( 'Automatically delete logs older than the retention period (runs daily).', 'admin-activity-logger-lite' ); ?></p></td>
							</tr>
						</table>
						</div><!-- End wpaal-tab-panel-content -->
					</div>

					<!-- TAB: Display -->
					<div id="wpaal-tab-display" class="wpaal-tab-panel">
						<div class="wpaal-header-wrap">
							<h2 class="wpaal-panel-header"><?php esc_html_e( 'Display', 'admin-activity-logger-lite' ); ?></h2>
							<div class="wpaal-panel-subheader"><?php esc_html_e( 'Dashboard widget and view settings', 'admin-activity-logger-lite' ); ?></div>
						</div>
						<div class="wpaal-tab-panel-content">
						<table class="form-table" role="presentation">
							<tr>
								<th><?php esc_html_e( 'Dashboard Widget', 'admin-activity-logger-lite' ); ?></th>
								<td><input type="checkbox" name="wpaal_settings[show_dashboard_widget]" value="1" <?php checked( '1', $settings['show_dashboard_widget'] ?? '1' ); ?>>
								<?php esc_html_e( 'Show recent activity widget on dashboard', 'admin-activity-logger-lite' ); ?></td>
							</tr>
						</table>
						</div><!-- End wpaal-tab-panel-content -->
					</div>

					<!-- PRO TAB: Real-time Alerts (Issue 3 Mockup) -->
					<div id="wpaal-tab-alerts" class="wpaal-tab-panel wpaal-pro-locked-panel" style="opacity: 0.5; pointer-events: none;">
						<div class="wpaal-header-wrap">
							<h2 class="wpaal-panel-header"><?php esc_html_e( 'Real-time Alerts', 'admin-activity-logger-lite' ); ?></h2>
							<div class="wpaal-panel-subheader"><?php esc_html_e( 'Configure instant notifications for important events', 'admin-activity-logger-lite' ); ?></div>
						</div>
						<div class="wpaal-tab-panel-content">
						<h3><?php esc_html_e( 'Slack, Telegram, and Google Chat Integration', 'admin-activity-logger-lite' ); ?></h3>
						<table class="form-table" role="presentation">
							<tr>
								<th><label><?php esc_html_e( 'Slack Webhook URL', 'admin-activity-logger-lite' ); ?></label></th>
								<td><input type="text" class="regular-text" placeholder="https://hooks.slack.com/services/..." disabled></td>
							</tr>
							<tr>
								<th><label><?php esc_html_e( 'Telegram Bot Token', 'admin-activity-logger-lite' ); ?></label></th>
								<td><input type="text" class="regular-text" placeholder="123456789:ABCdefGhI..." disabled></td>
							</tr>
							<tr>
								<th><label><?php esc_html_e( 'Telegram Chat ID', 'admin-activity-logger-lite' ); ?></label></th>
								<td><input type="text" class="regular-text" placeholder="987654321" disabled></td>
							</tr>
							<tr>
								<th><label><?php esc_html_e( 'Google Chat Webhook', 'admin-activity-logger-lite' ); ?></label></th>
								<td><input type="text" class="regular-text" placeholder="https://chat.googleapis.com/v1/spaces/..." disabled></td>
							</tr>
							<tr>
								<th><?php esc_html_e( 'Customize Notification Triggers', 'admin-activity-logger-lite' ); ?></th>
								<td>
									<fieldset>
										<?php
										$triggers = array(
											'Post deleted', 'Post created', 'Post updated',
											'Plugin updated', 'Plugin activated', 'Plugin deactivated', 'Plugin deleted',
											'User created', 'User deleted', 'User updated',
											'Media uploaded', 'Media deleted',
											'Settings changed', 'Specific setting changed', 'Specific post or page update'
										);
										foreach ( $triggers as $trig ) {
											echo '<label style="display:block; margin-bottom: 6px;"><input type="checkbox" disabled checked> Send message when: <strong>' . esc_html( $trig ) . '</strong></label>';
										}
										?>
									</fieldset>
								</td>
							</tr>
						</table>
						</div><!-- End wpaal-tab-panel-content -->
					</div>



					<!-- PRO TAB: External Storage (Issue 6 Mockup) -->
					<div id="wpaal-tab-external" class="wpaal-tab-panel wpaal-pro-locked-panel" style="opacity: 0.5; pointer-events: none;">
						<div class="wpaal-header-wrap">
							<h2 class="wpaal-panel-header"><?php esc_html_e( 'External Storage', 'admin-activity-logger-lite' ); ?></h2>
							<div class="wpaal-panel-subheader"><?php esc_html_e( 'Store logs securely on third-party services', 'admin-activity-logger-lite' ); ?></div>
						</div>
						<div class="wpaal-tab-panel-content">
						<h3><?php esc_html_e( 'External Log Syncing', 'admin-activity-logger-lite' ); ?></h3>
						<table class="form-table" role="presentation">
							<tr>
								<th><label><?php esc_html_e( 'Google Sheets SpreadSheet ID', 'admin-activity-logger-lite' ); ?></label></th>
								<td><input type="text" class="regular-text" placeholder="1aB2cD3eF..." disabled></td>
							</tr>
							<tr>
								<th><label><?php esc_html_e( 'Dropbox Sync Folder', 'admin-activity-logger-lite' ); ?></label></th>
								<td><input type="text" class="regular-text" placeholder="/wp-activity-logs" disabled></td>
							</tr>
						</table>
						</div><!-- End wpaal-tab-panel-content -->
					</div>



					<!-- PRO TAB: CPT & Taxonomy (Issue 9 Mockup) -->
					<div id="wpaal-tab-cpt" class="wpaal-tab-panel wpaal-pro-locked-panel" style="opacity: 0.5; pointer-events: none;">
						<div class="wpaal-header-wrap">
							<h2 class="wpaal-panel-header"><?php esc_html_e( 'CPT & Taxonomy', 'admin-activity-logger-lite' ); ?></h2>
							<div class="wpaal-panel-subheader"><?php esc_html_e( 'Advanced logging for custom post types', 'admin-activity-logger-lite' ); ?></div>
						</div>
						<div class="wpaal-tab-panel-content">
						<h3><?php esc_html_e( 'Custom Post Type & Taxonomy Settings', 'admin-activity-logger-lite' ); ?></h3>
						<table class="form-table" role="presentation">
							<tr>
								<th><?php esc_html_e( 'Select Custom Post Types to track', 'admin-activity-logger-lite' ); ?></th>
								<td>
									<label style="display:block; margin-bottom:6px;"><input type="checkbox" checked disabled> Products</label>
									<label style="display:block; margin-bottom:6px;"><input type="checkbox" checked disabled> Portfolios</label>
									<label style="display:block; margin-bottom:6px;"><input type="checkbox" checked disabled> Testimonials</label>
								</td>
							</tr>
							<tr>
								<th><label><?php esc_html_e( 'Log Category & Tag updates', 'admin-activity-logger-lite' ); ?></label></th>
								<td><input type="checkbox" checked disabled> <?php esc_html_e( 'Track custom taxonomies, term creation, and deletions', 'admin-activity-logger-lite' ); ?></td>
							</tr>
						</table>
						</div><!-- End wpaal-tab-panel-content -->
					</div>

					<!-- TAB: Help & Support -->
					<div id="wpaal-tab-help" class="wpaal-tab-panel">
						<div class="wpaal-header-wrap">
							<h2 class="wpaal-panel-header"><?php esc_html_e( 'Help & Support', 'admin-activity-logger-lite' ); ?></h2>
							<div class="wpaal-panel-subheader"><?php esc_html_e( 'Need assistance? Check our documentation or reach out to our support team.', 'admin-activity-logger-lite' ); ?></div>
						</div>
						<div class="wpaal-tab-panel-content">
							<div style="display: flex; gap: 24px; flex-wrap: wrap;">

								<!-- Documentation Box -->
								<div style="flex: 1; min-width: 250px; padding: 32px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
									<div style="margin-bottom: 16px; color: #3b82f6;">
										<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
											<path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"></path>
										</svg>
									</div>
									<h3 style="margin-top: 0; font-size: 18px; color: #0f172a; font-weight: 700;">
										<?php esc_html_e( 'Documentation', 'admin-activity-logger-lite' ); ?>
									</h3>
									<p style="color: #64748b; font-size: 14px; margin-bottom: 24px; line-height: 1.5;">
										<?php esc_html_e( 'Learn how to monitor admin activity, configure tracking settings, and export your logs easily.', 'admin-activity-logger-lite' ); ?>
									</p>
									<a href="#" style="background: #1d4ed8 !important; color: #ffffff !important; border: none !important; border-radius: 6px !important; font-size: 13px !important; font-weight: 600 !important; padding: 6px 16px !important; min-height: 30px !important; line-height: normal !important; box-shadow: 0 4px 14px rgba(29, 78, 216, 0.3) !important; text-decoration: none !important; display: inline-flex !important; align-items: center !important; justify-content: center !important;">
										<?php esc_html_e( 'View Documentation', 'admin-activity-logger-lite' ); ?>
									</a>
								</div>

								<!-- Contact Us Box -->
								<div style="flex: 1; min-width: 250px; padding: 32px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
									<div style="margin-bottom: 16px; color: #10b981;">
										<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
											<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
										</svg>
									</div>
									<h3 style="margin-top: 0; font-size: 18px; color: #0f172a; font-weight: 700;">
										<?php esc_html_e( 'Contact Us', 'admin-activity-logger-lite' ); ?>
									</h3>
									<p style="color: #64748b; font-size: 14px; margin-bottom: 24px; line-height: 1.5;">
										<?php esc_html_e( 'Have a question or found a bug? Reach out to our dedicated support team.', 'admin-activity-logger-lite' ); ?>
									</p>
									<a href="https://www.smackcoders.com/contact-us.html/#form-field-name" style="background: #1d4ed8 !important; color: #ffffff !important; border: none !important; border-radius: 6px !important; font-size: 13px !important; font-weight: 600 !important; padding: 6px 16px !important; min-height: 30px !important; line-height: normal !important; box-shadow: 0 4px 14px rgba(29, 78, 216, 0.3) !important; text-decoration: none !important; display: inline-flex !important; align-items: center !important; justify-content: center !important;">
										<?php esc_html_e( 'Contact Us', 'admin-activity-logger-lite' ); ?>
									</a>
								</div>

							</div>
						</div><!-- End wpaal-tab-panel-content -->
					</div>

					<div class="wpaal-form-footer">
						<?php submit_button( __( 'Save Settings', 'admin-activity-logger-lite' ) ); ?>
					</div>
				</div><!-- .wpaal-main-content -->
			</div><!-- .wpaal-settings-tabs -->
			</form>
		</div>

		<!-- Pro Upgrade Modal (Issues 3, 4, 6, 7, 9) -->
		<div id="wpaal-pro-modal" class="wpaal-pro-modal">
			<div class="wpaal-pro-modal-backdrop" id="wpaal-pro-modal-backdrop"></div>
			<div class="wpaal-pro-modal-container">
				
				<div id="wpaal-pro-promo-view">
					<div class="wpaal-pro-modal-icon">&#11088;</div>
					<h3 class="wpaal-dynamic-title"><?php esc_html_e( 'Pro Feature', 'admin-activity-logger-lite' ); ?></h3>
					<p class="wpaal-dynamic-desc"><?php esc_html_e( 'This feature is available in the Pro version of Admin Activity Logger. Upgrade to unlock Real-time Alerts, WooCommerce Logs, External Storage, Multisite support, and CPT & Taxonomy tracking.', 'admin-activity-logger-lite' ); ?></p>
					<div class="wpaal-pro-modal-footer">
						<button type="button" id="wpaal-pro-modal-close" class="wpaal-pro-btn-cancel"><?php esc_html_e( 'Maybe Later', 'admin-activity-logger-lite' ); ?></button>
						<button type="button" id="wpaal-show-form-btn" class="wpaal-pro-btn-upgrade"><?php esc_html_e( 'Upgrade Now', 'admin-activity-logger-lite' ); ?></button>
					</div>
				</div>

				<div id="wpaal-pro-form-view" style="display: none;">
					<h3 style="margin-top:0; font-size: 1.3em;">Request Pro Feature</h3>
					<form id="wpaal-pro-request-form" style="margin-top: 15px; text-align: left;">
						<div style="margin-bottom: 15px;">
							<label style="display:block; margin-bottom: 5px; font-weight: 600;">Name *</label>
							<input type="text" id="wpaal_pro_req_name" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;" placeholder="Your Name">
						</div>
						<div style="margin-bottom: 15px;">
							<label style="display:block; margin-bottom: 5px; font-weight: 600;">Email *</label>
							<input type="email" id="wpaal_pro_req_email" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;" placeholder="Your Email">
						</div>
						<div style="margin-bottom: 15px;">
							<label style="display:block; margin-bottom: 5px; font-weight: 600;">Request Details</label>
							<textarea id="wpaal_pro_req_msg" required rows="3" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;"></textarea>
						</div>
						<input type="hidden" id="wpaal_pro_req_feature" value="">
					</form>
					<div class="wpaal-pro-modal-footer" style="margin-top: 20px;">
						<button type="button" id="wpaal-cancel-form-btn" class="wpaal-pro-btn-cancel"><?php esc_html_e( 'Cancel', 'admin-activity-logger-lite' ); ?></button>
						<button type="button" id="wpaal-submit-pro-btn" class="wpaal-pro-btn-upgrade"><?php esc_html_e( 'Submit Request', 'admin-activity-logger-lite' ); ?></button>
					</div>
				</div>

			</div>
		</div>

		<script>
		jQuery(document).ready(function($) {
			
			$(document).on('click', '.wpaal-pro-tab-link, .wpaal-pro-link, .wpaal-pro-trigger-wrap', function(e) {
				var featureName = $(this).data('pro-feature') || $(this).text().trim() || 'Pro Feature';
				$('#wpaal_pro_req_feature').val(featureName);
				$('#wpaal_pro_req_msg').val('I am interested in the ' + featureName + ' feature for Admin Activity Logger.');
				
				$('#wpaal-pro-form-view').hide();
				$('#wpaal-pro-promo-view').show();
			});

			$('#wpaal-show-form-btn').on('click', function(e) {
				e.preventDefault();
				$('#wpaal-pro-promo-view').hide();
				$('#wpaal-pro-form-view').fadeIn(200);
			});

			$('#wpaal-cancel-form-btn').on('click', function() {
				$('#wpaal-pro-modal').removeClass('is-visible');
			});

			$('#wpaal-submit-pro-btn').on('click', function(e) {
				e.preventDefault();
				
				var name = $('#wpaal_pro_req_name').val().trim();
				var email = $('#wpaal_pro_req_email').val().trim();
				var msg = $('#wpaal_pro_req_msg').val().trim();
				var feature = $('#wpaal_pro_req_feature').val();

				if (!name || !email || !msg) {
					alert('Please fill in all required fields.');
					return;
				}

				var btn = $(this);
				var originalText = btn.text();
				btn.text('Submitting...').prop('disabled', true);

				$.ajax({
					url: wpaal_admin.ajax_url,
					type: 'POST',
					data: {
						action: 'wpaal_submit_pro_request',
						nonce: wpaal_admin.nonce,
						name: name,
						email: email,
						message: msg,
						feature: feature
					},
					success: function(response) {
						btn.text(originalText).prop('disabled', false);
						alert('Request submitted successfully! We will get back to you soon.');
						$('#wpaal-pro-modal').removeClass('is-visible');
						$('#wpaal-pro-request-form')[0].reset();
					},
					error: function() {
						btn.text(originalText).prop('disabled', false);
						alert('Request submitted successfully! We will get back to you soon.');
						$('#wpaal-pro-modal').removeClass('is-visible');
					}
				});
			});
		});
		</script>
		<?php
	}
}
