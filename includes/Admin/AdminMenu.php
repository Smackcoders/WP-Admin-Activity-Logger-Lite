<?php
namespace Smackcoders\AdminActivityLogger\Admin;

if (!defined('ABSPATH')) {
	exit;
}

class AdminMenu
{
	private $logs_hook;
	private $settings_hook;

	public function register()
	{
		add_action('admin_menu', array($this, 'add_menu'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
		add_action('admin_notices', array($this, 'logging_disabled_notice'));
		add_action('admin_init', array($this, 'handle_cleanup_action'));
		add_action('admin_init', array($this, 'handle_export_action'));
		add_action('admin_init', array($this, 'handle_delete_log_action'));
		add_action('admin_init', array($this, 'handle_bulk_actions'));
		add_action('wp_ajax_wpaal_submit_pro_request', array($this, 'handle_pro_request'));

		(new SettingsPage())->register();
	}

	public function add_menu()
	{
		$view_cap = $this->get_view_cap();

		$this->logs_hook = add_menu_page(
			__('Activity Logger', 'admin-activity-logger-lite'),
			__('Activity Logger', 'admin-activity-logger-lite'),
			$view_cap,
			'wpaal-logs',
			array($this, 'render_logs_page'),
			'dashicons-list-view',
			80
		);

		add_submenu_page(
			'wpaal-logs',
			__('Admin Activity Logs', 'admin-activity-logger-lite'),
			__('Activity Logs', 'admin-activity-logger-lite'),
			$view_cap,
			'wpaal-logs',
			array($this, 'render_logs_page')
		);

		$this->settings_hook = add_submenu_page(
			'wpaal-logs',
			__('Admin Activity Logger Settings', 'admin-activity-logger-lite'),
			__('Settings', 'admin-activity-logger-lite'),
			'manage_options',
			'wpaal-settings',
			array($this, 'render_settings_page')
		);

		add_submenu_page(
			'wpaal-logs',
			__('Help & Support', 'admin-activity-logger-lite'),
			__('Help & Support', 'admin-activity-logger-lite'),
			'manage_options',
			'wpaal-help',
			array($this, 'render_help_page')
		);
	}

	public function render_logs_page()
	{
		$view_cap = $this->get_view_cap();
		if (!current_user_can($view_cap)) {
			wp_die(esc_html__('Permission denied.', 'admin-activity-logger-lite'));
		}
		$page = new ActivityLogsPage();
		$page->render();
	}

	private function get_view_cap() {
		$settings = get_option('wpaal_settings', array());
		return ! empty($settings['view_logs_cap']) ? sanitize_key($settings['view_logs_cap']) : 'manage_options';
	}

	private function get_delete_cap() {
		$settings = get_option('wpaal_settings', array());
		return ! empty($settings['delete_logs_cap']) ? sanitize_key($settings['delete_logs_cap']) : 'manage_options';
	}

	public function render_settings_page()
	{
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Permission denied.', 'admin-activity-logger-lite'));
		}
		$page = new SettingsPage();
		$page->render();
	}

	public function render_help_page()
	{
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('Permission denied.', 'admin-activity-logger-lite'));
		}
		$page = new HelpPage();
		$page->render();
	}

	public function enqueue_assets($hook)
	{
		if ($hook !== $this->logs_hook && $hook !== $this->settings_hook && strpos($hook, 'wpaal-help') === false) {
			return;
		}

		wp_enqueue_style('wpaal-admin', WPAAL_PLUGIN_URL . 'assets/css/admin.css', array(), filemtime(WPAAL_PLUGIN_DIR . 'assets/css/admin.css'));
		wp_enqueue_script('wpaal-admin', WPAAL_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), filemtime(WPAAL_PLUGIN_DIR . 'assets/js/admin.js'), true);

		wp_localize_script('wpaal-admin', 'wpaal_admin', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('wpaal_admin_action'),
			'confirm_clear' => __('Delete logs? This cannot be undone.', 'admin-activity-logger-lite'),
			'confirm_clear_all' => __('This will delete ALL logs permanently. Are you sure?', 'admin-activity-logger-lite'),
			'confirm_title_single' => __('Confirm Deletion', 'admin-activity-logger-lite'),
			'confirm_msg_single' => __('Are you sure you want to delete this log entry? This action cannot be undone.', 'admin-activity-logger-lite'),
			'confirm_title_clear' => __('Clear Logs', 'admin-activity-logger-lite'),
			'select_alert' => __('Please select at least one log entry.', 'admin-activity-logger-lite'),
			/* translators: %d: number of log entries */
			'confirm_msg_bulk' => __('Are you sure you want to delete the selected %d log entries?', 'admin-activity-logger-lite'),
		));
	}

	public function handle_cleanup_action()
	{
		if (empty($_POST['wpaal_cleanup_action'])) {
			return;
		}

		if (!current_user_can('manage_options')) {
			return;
		}

		if (!isset($_POST['wpaal_cleanup_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wpaal_cleanup_nonce'])), 'wpaal_cleanup')) {
			wp_die(esc_html__('Security check failed.', 'admin-activity-logger-lite'));
		}

		$manager = new \Smackcoders\AdminActivityLogger\Cleanup\CleanupManager();
		$action = sanitize_text_field(wp_unslash($_POST['wpaal_cleanup_action']));

		if ('clear_all' === $action) {
			$count = $manager->clear_all();
		} else {
			$days = absint($action);
			$count = $days > 0 ? $manager->cleanup_older_than($days) : 0;
		}

		$redirect = wp_nonce_url(add_query_arg(array(
			'page' => 'wpaal-logs',
			'wpaal_msg' => 'cleared',
			'wpaal_count' => $count,
		), admin_url('admin.php')), 'wpaal_logs_notice', 'wpaal_notice_nonce');

		wp_safe_redirect($redirect);
		exit;
	}

	public function handle_export_action()
	{
		$page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
		if (empty($_GET['wpaal_export_csv']) || 'wpaal-logs' !== $page) {
			return;
		}

		if (!current_user_can('manage_options')) {
			return;
		}

		if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'wpaal_export_csv')) {
			wp_die(esc_html__('Security check failed.', 'admin-activity-logger-lite'));
		}

		$filters = array(
			'user_id' => isset($_GET['user_id']) ? absint(wp_unslash($_GET['user_id'])) : 0,
			'event_type' => isset($_GET['event_type']) ? sanitize_text_field(wp_unslash($_GET['event_type'])) : '',
			'search' => isset($_GET['search']) ? sanitize_text_field(wp_unslash($_GET['search'])) : '',
			'date_from' => isset($_GET['date_from']) ? sanitize_text_field(wp_unslash($_GET['date_from'])) : '',
			'date_to' => isset($_GET['date_to']) ? sanitize_text_field(wp_unslash($_GET['date_to'])) : '',
		);

		(new \Smackcoders\AdminActivityLogger\Export\CsvExporter())->export($filters);
	}

	public function handle_delete_log_action()
	{
		if (empty($_GET['wpaal_delete_log'])) {
			return;
		}

		if (!current_user_can($this->get_delete_cap())) {
			return;
		}

		$log_id = absint($_GET['wpaal_delete_log']);

		if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'wpaal_delete_log_' . $log_id)) {
			wp_die(esc_html__('Security check failed.', 'admin-activity-logger-lite'));
		}

		$repo = new \Smackcoders\AdminActivityLogger\Logger\ActivityRepository();
		$repo->delete($log_id);

		$redirect = wp_nonce_url(add_query_arg(array(
			'page' => 'wpaal-logs',
			'wpaal_msg' => 'deleted',
			'wpaal_count' => 1,
		), admin_url('admin.php')), 'wpaal_logs_notice', 'wpaal_notice_nonce');

		wp_safe_redirect($redirect);
		exit;
	}

	public function handle_bulk_actions()
	{
		if (empty($_POST['action']) || 'delete' !== $_POST['action']) {
			return;
		}

		$page = isset($_REQUEST['page']) ? sanitize_text_field(wp_unslash($_REQUEST['page'])) : '';
		if ('wpaal-logs' !== $page) {
			return;
		}

		if (!current_user_can($this->get_delete_cap())) {
			return;
		}

		if (!isset($_POST['wpaal_bulk_action_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wpaal_bulk_action_nonce'])), 'wpaal_bulk_action')) {
			wp_die(esc_html__('Security check failed.', 'admin-activity-logger-lite'));
		}

		if (empty($_POST['wpaal_bulk_ids']) || !is_array($_POST['wpaal_bulk_ids'])) {
			return;
		}

		$ids = array_map('absint', $_POST['wpaal_bulk_ids']);
		$repo = new \Smackcoders\AdminActivityLogger\Logger\ActivityRepository();

		$count = 0;
		foreach ($ids as $id) {
			if ($id > 0) {
				$repo->delete($id);
				$count++;
			}
		}

		$redirect = wp_nonce_url(add_query_arg(array(
			'page' => 'wpaal-logs',
			'wpaal_msg' => 'deleted',
			'wpaal_count' => $count,
		), admin_url('admin.php')), 'wpaal_logs_notice', 'wpaal_notice_nonce');

		wp_safe_redirect($redirect);
		exit;
	}

	public function handle_pro_request()
	{
		check_ajax_referer('wpaal_admin_action', 'nonce');
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => 'Unauthorized'));
		}

		$name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
		$email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
		$message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';
		$feature = isset($_POST['feature']) ? sanitize_text_field(wp_unslash($_POST['feature'])) : '';

		if (empty($name) || empty($email) || empty($message)) {
			wp_send_json_error(array('message' => 'Missing fields'));
		}

		$to      = 'dummy@example.com';
		$subject = 'Admin Activity Logger Pro Request: ' . $feature;
		$body    = "Name: $name\nEmail: $email\nFeature: $feature\n\nMessage:\n$message";
		$headers = array('Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $email);

		wp_mail($to, $subject, $body, $headers);

		wp_send_json_success(array('message' => 'Request submitted successfully!'));
	}

	public function logging_disabled_notice()
	{
		$settings = get_option('wpaal_settings', array());
		if (!empty($settings['enable_logging'])) {
			return;
		}

		$screen = get_current_screen();
		if ($screen && false !== strpos($screen->id, 'wpaal-settings')) {
			return;
		}
		?>
		<div class="notice notice-warning">
			<p><?php
			printf(
				/* translators: %s: settings link */
				esc_html__('Activity logging is currently disabled. %s', 'admin-activity-logger-lite'),
				'<a href="' . esc_url(admin_url('admin.php?page=wpaal-settings')) . '">' . esc_html__('Enable in Settings', 'admin-activity-logger-lite') . '</a>'
			);
			?></p>
		</div>
		<?php
	}
}
