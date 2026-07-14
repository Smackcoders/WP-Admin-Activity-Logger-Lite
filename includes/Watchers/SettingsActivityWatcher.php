<?php
namespace Smackcoders\AdminActivityLogger\Watchers;

use Smackcoders\AdminActivityLogger\Logger\ActivityLogger;

if (!defined('ABSPATH')) {
	exit;
}

class SettingsActivityWatcher
{

	private $logger;

	private $tracked_options = array(
		// General
		'blogname',
		'blogdescription',
		'siteurl',
		'home',
		'admin_email',
		'users_can_register',
		'default_role',
		'timezone_string',
		'date_format',
		'time_format',
		'start_of_week',
		'WPLANG',
		'site_icon',
		// Writing
		'default_category',
		'default_post_format',
		'mailserver_url',
		'mailserver_login',
		'mailserver_pass',
		'default_email_category',
		// Reading
		'show_on_front',
		'page_on_front',
		'page_for_posts',
		'posts_per_page',
		'posts_per_rss',
		'rss_use_excerpt',
		'blog_public',
		// Discussion
		'default_pingback_status',
		'default_ping_status',
		'default_comment_status',
		'require_name_email',
		'comment_registration',
		'close_comments_for_old_posts',
		'close_comments_days_old',
		'thread_comments',
		'thread_comments_depth',
		'page_comments',
		'comments_per_page',
		'default_comments_page',
		'comment_order',
		'comments_notify',
		'moderation_notify',
		'comment_moderation',
		'comment_previously_approved',
		'comment_max_links',
		'moderation_keys',
		'disallowed_keys',
		'show_avatars',
		'avatar_rating',
		'avatar_default',
		// Media
		'thumbnail_size_w',
		'thumbnail_size_h',
		'thumbnail_crop',
		'medium_size_w',
		'medium_size_h',
		'large_size_w',
		'large_size_h',
		'uploads_use_yearmonth_folders',
		// Permalinks
		'permalink_structure',
		'category_base',
		'tag_base',
		// Privacy
		'wp_page_for_privacy_policy',
		// AI configurations
		'smartseo_gemini_api_key',
		'smartseo_mistral_api_key',
		'smartseo_gdpr_consent',
		'gsc_client_id',
		'gsc_client_secret',
		'gsc_site_url',
		'ga4_property_id',
	);

	private $sensitive_patterns = array(
		'password',
		'secret',
		'api_key',
		'token',
		'auth',
		'license',
		'smtp_pass',
		'private_key',
		'client_secret',
		'oauth',
	);

	private $pending_updates = array();

	public function __construct(ActivityLogger $logger)
	{
		$this->logger = $logger;
	}

	public function register()
	{
		add_action('updated_option', array($this, 'on_updated_option'), 10, 3);
		add_action('added_option', array($this, 'on_added_option'), 10, 2);
	}

	public function on_updated_option($option, $old_value, $new_value)
	{
		if (!$this->logger->should_log('settings_updated')) {
			return;
		}

		$tracked = apply_filters('wpaal_tracked_options', $this->tracked_options);
		$is_tracked = in_array($option, $tracked, true) || (0 === strpos($option, 'connectors_ai_'));
		if (!$is_tracked) {
			return;
		}

		if ($old_value === $new_value) {
			return;
		}

		// Check if sensitive
		$is_sensitive = false;
		$sensitive_patterns = apply_filters('wpaal_sensitive_option_patterns', $this->sensitive_patterns);
		$option_lower = strtolower($option);
		foreach ($sensitive_patterns as $pattern) {
			if (false !== strpos($option_lower, strtolower($pattern))) {
				$is_sensitive = true;
				break;
			}
		}

		if ($is_sensitive) {
			$old_value = '[hidden]';
			$new_value = '[hidden]';
		}

		if (empty($this->pending_updates)) {
			add_action('shutdown', array($this, 'save_pending_updates'));
		}

		$this->pending_updates[$option] = array(
			'old' => $old_value,
			'new' => $new_value,
			'type' => 'updated',
		);
	}

	public function on_added_option($option, $value)
	{
		if (!$this->logger->should_log('settings_updated')) {
			return;
		}

		$tracked = apply_filters('wpaal_tracked_options', $this->tracked_options);
		$is_tracked = in_array($option, $tracked, true) || (0 === strpos($option, 'connectors_ai_'));
		if (!$is_tracked) {
			return;
		}

		// Check if sensitive
		$is_sensitive = false;
		$sensitive_patterns = apply_filters('wpaal_sensitive_option_patterns', $this->sensitive_patterns);
		$option_lower = strtolower($option);
		foreach ($sensitive_patterns as $pattern) {
			if (false !== strpos($option_lower, strtolower($pattern))) {
				$is_sensitive = true;
				break;
			}
		}

		if ($is_sensitive) {
			$value = '[hidden]';
		}

		if (empty($this->pending_updates)) {
			add_action('shutdown', array($this, 'save_pending_updates'));
		}

		$this->pending_updates[$option] = array(
			'old' => null,
			'new' => $value,
			'type' => 'added',
		);
	}

	public function save_pending_updates()
	{
		if (empty($this->pending_updates)) {
			return;
		}

		if (count($this->pending_updates) === 1) {
			$option = key($this->pending_updates);
			$data = current($this->pending_updates);

			$old_display = is_scalar($data['old']) ? (string) $data['old'] : wp_json_encode($data['old']);
			$new_display = is_scalar($data['new']) ? (string) $data['new'] : wp_json_encode($data['new']);

			if ('updated' === $data['type']) {
				$this->logger->log(array(
					'event_type' => 'settings_updated',
					'object_type' => 'option',
					'object_title' => $option,
					'action' => 'update',
					'message' => sprintf(
						/* translators: %s: Option name. */
						__('Option "%s" was changed.', 'admin-activity-logger-lite'),
						$option
					),
					'old_value' => $old_display,
					'new_value' => $new_display,
				));
			} else {
				$this->logger->log(array(
					'event_type' => 'settings_updated',
					'object_type' => 'option',
					'object_title' => $option,
					'action' => 'add',
					'message' => sprintf(
						/* translators: %s: Option name. */
						__('Option "%s" was set.', 'admin-activity-logger-lite'),
						$option
					),
					'new_value' => $new_display,
				));
			}
		} else {
			$options_list = array_keys($this->pending_updates);
			$object_title = implode(', ', $options_list);

			$old_values = array();
			$new_values = array();
			foreach ($this->pending_updates as $opt => $data) {
				$old_values[$opt] = $data['old'];
				$new_values[$opt] = $data['new'];
			}

			$this->logger->log(array(
				'event_type' => 'settings_updated',
				'object_type' => 'option',
				'object_title' => $object_title,
				'action' => 'update',
				'message' => sprintf(
					/* translators: %s: Comma-separated option names. */
					__('Multiple options were changed: %s.', 'admin-activity-logger-lite'),
					$object_title
				),
				'old_value' => wp_json_encode($old_values),
				'new_value' => wp_json_encode($new_values),
			));
		}

		$this->pending_updates = array();
	}
}
