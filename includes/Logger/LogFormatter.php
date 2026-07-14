<?php
namespace Smackcoders\AdminActivityLogger\Logger;

if (!defined('ABSPATH')) {
	exit;
}

class LogFormatter
{

	public static function event_type_label($event_type)
	{
		$labels = array(
			'post_created' => __('Post Created', 'admin-activity-logger-lite'),
			'post_updated' => __('Post Updated', 'admin-activity-logger-lite'),
			'post_trashed' => __('Post Trashed', 'admin-activity-logger-lite'),
			'post_restored' => __('Post Restored', 'admin-activity-logger-lite'),
			'post_deleted' => __('Post Deleted', 'admin-activity-logger-lite'),
			'page_created' => __('Page Created', 'admin-activity-logger-lite'),
			'page_updated' => __('Page Updated', 'admin-activity-logger-lite'),
			'page_trashed' => __('Page Trashed', 'admin-activity-logger-lite'),
			'page_restored' => __('Page Restored', 'admin-activity-logger-lite'),
			'page_deleted' => __('Page Deleted', 'admin-activity-logger-lite'),
			'plugin_activated' => __('Plugin Activated', 'admin-activity-logger-lite'),
			'plugin_deactivated' => __('Plugin Deactivated', 'admin-activity-logger-lite'),
			'user_created' => __('User Created', 'admin-activity-logger-lite'),
			'user_deleted' => __('User Deleted', 'admin-activity-logger-lite'),
			'settings_updated' => __('Settings Updated', 'admin-activity-logger-lite'),
			'media_uploaded' => __('Media Uploaded', 'admin-activity-logger-lite'),
			'media_deleted' => __('Media Deleted', 'admin-activity-logger-lite'),
		);

		return $labels[$event_type] ?? esc_html($event_type);
	}

	public static function event_type_badge_class($event_type)
	{
		if (strpos($event_type, 'created') !== false || strpos($event_type, 'uploaded') !== false || strpos($event_type, 'restored') !== false || strpos($event_type, 'activated') !== false) {
			return 'wpaal-badge wpaal-badge-green';
		}
		if (strpos($event_type, 'deleted') !== false || strpos($event_type, 'trashed') !== false || strpos($event_type, 'deactivated') !== false) {
			return 'wpaal-badge wpaal-badge-red';
		}
		if (strpos($event_type, 'updated') !== false) {
			return 'wpaal-badge wpaal-badge-blue';
		}
		return 'wpaal-badge wpaal-badge-grey';
	}

	public static function relative_time($datetime)
	{
		$timestamp = strtotime($datetime);
		if (!$timestamp) {
			return esc_html($datetime);
		}
		$diff = current_time('timestamp') - $timestamp;

		if ($diff < 60) {
			return __('Just now', 'admin-activity-logger-lite');
		} else {
			$time_diff = human_time_diff($timestamp, current_time('timestamp'));
			/* translators: %s: Human-readable time difference. */
			return sprintf(__('%s ago', 'admin-activity-logger-lite'), $time_diff);
		}
	}

	public static function truncate($value, $length = 120)
	{
		$value = (string) $value;
		if (mb_strlen($value) > $length) {
			$half_length = (int) ($length / 2) - 2;
			$first_half = trim(mb_substr($value, 0, $half_length));
			$last_half = trim(mb_substr($value, -$half_length));
			
			// Make sure we don't cut words in half if possible
			$first_space = mb_strrpos($first_half, ' ');
			if ($first_space !== false && $first_space > ($half_length * 0.5)) {
				$first_half = mb_substr($first_half, 0, $first_space);
			}
			
			$last_space = mb_strpos($last_half, ' ');
			if ($last_space !== false && $last_space < ($half_length * 0.5)) {
				$last_half = mb_substr($last_half, $last_space + 1);
			}
			
			return trim($first_half) . ' ... ' . trim($last_half);
		}
		return $value;
	}
}
