=== Admin Activity Logger Lite ===
Contributors: smackcoders
Donate link: https://smackcoders.com/
Tags: activity log, admin audit, user activity, plugin activity, post changes
Requires at least: 5.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Track important WordPress admin activity including post changes, plugin activation, user changes, settings updates, and exportable audit logs.

== Description ==

**Admin Activity Logger Lite** tracks important admin-side changes in WordPress for accountability, troubleshooting, and client-site maintenance.

The plugin provides a lightweight audit trail showing who changed what, when it happened, and what action was performed.

= What It Logs =

* Post create, update, trash, restore, and permanent delete
* Page create, update, trash, restore, and permanent delete
* Plugin activation and deactivation
* User creation and deletion
* WordPress settings changes (site title, admin email, permalink structure, and more)
* Media file uploads and deletions

= Key Features =

* Clean admin activity log page with filters: user, event type, object type, date range, and keyword search
* Detailed log view showing old/new values, IP address, user agent, and request URL
* Export logs as CSV with formula injection protection
* Manual log cleanup: delete logs older than 7/30/90/180/365 days, or clear all
* Scheduled auto cleanup via WP-Cron (configurable retention period)
* Dashboard widget showing today's activity summary and latest 10 events
* Disabling notice if logging is globally paused
* WordPress Privacy Tools integration: personal data exporter and eraser
* IP address storage with optional anonymization (IPv4 and IPv6)
* WP-CLI commands for log management
* Developer hooks and filters for full customisation
* Translation-ready
* Security hardened: nonces, capability checks, prepared SQL, CSV formula injection protection
* Sensitive values (passwords, tokens, API keys) are never logged

= Who Is This For? =

* Multi-admin WordPress websites
* Agencies managing client sites
* Membership platforms
* Website maintenance teams
* Security-conscious site managers
* WordPress support teams

= Pro Features (Coming Soon) =

* Real-time alerts (email/Slack/Telegram/Google Chat)
* WooCommerce order activity logs
* Role-based log visibility
* External log storage (S3, Elasticsearch, Google Sheets)
* GitLab issue creation for critical events
* Multisite network-wide activity logs
* Advanced diff view for content and settings changes
* AI-based suspicious activity summary

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/admin-activity-logger-lite/`, or install through the WordPress plugins screen.
2. Activate the plugin through the **Plugins** screen.
3. Go to **Tools > Activity Logs** to view logs.
4. Go to **Settings > Activity Logger** to configure the plugin.

== Frequently Asked Questions ==

= Does this plugin block suspicious activity? =
No. The free version logs admin activity for accountability and troubleshooting. Blocking and real-time alerts are planned for the Pro version.

= Can I export activity logs? =
Yes. Admins can export filtered logs as CSV from the activity logs page.

= Does the plugin store passwords or API keys? =
No. The plugin never stores passwords, API keys, OAuth tokens, or any sensitive credentials. Settings tracked via a whitelist approach.

= Can I clear old logs? =
Yes. You can manually delete logs older than 7, 30, 90, 180, or 365 days, or enable auto cleanup with a configurable retention period.

= Who can view the activity logs? =
In the free version, users with `manage_options` capability can view logs.

= Does it support WooCommerce logs? =
WooCommerce order activity logging is planned for the Pro version.

= Does it work with WordPress privacy tools? =
Yes. The plugin integrates with WordPress personal data export and erasure tools (GDPR Tools).

= Are WP-CLI commands available? =
Yes:
- `wp wpaal list` — List recent logs
- `wp wpaal summary` — Show today's activity summary
- `wp wpaal export --file=/path/logs.csv` — Export to CSV
- `wp wpaal clear --older-than=90` — Clear old logs
- `wp wpaal clear-all` — Clear all logs (with confirmation)

= Can developers extend the plugin? =
Yes. Available hooks and filters:
- `wpaal_should_log_activity` — Disable specific log events
- `wpaal_log_data_before_insert` — Modify log data before saving
- `wpaal_tracked_options` — Customize which options are tracked
- `wpaal_sensitive_option_patterns` — Add custom sensitive option patterns
- `wpaal_supported_post_types` — Change tracked post types
- `wpaal_csv_export_columns` — Customize CSV export columns
- `wpaal_csv_export_filename` — Customize export filename
- `wpaal_log_retention_days` — Override retention period
- `wpaal_detected_ip_address` — Override IP detection
- `wpaal_request_context` — Modify request context data
- `wpaal_activity_logged` — Fires after a log is saved
- `wpaal_before_logs_cleared` — Fires before cleanup
- `wpaal_after_logs_cleared` — Fires after cleanup

== Screenshots ==

1. Activity logs page with event badges, filters, and export button.
2. Log detail view showing old/new values, IP, user agent, and request URL.
3. Settings page — Logging configuration.
4. Settings page — Privacy and IP options.
5. Settings page — Retention and auto cleanup.
6. WordPress dashboard widget with today's activity summary.
7. CSV export downloaded to spreadsheet.

== Privacy Policy ==

This plugin records WordPress admin activity for security, troubleshooting, and accountability. The recorded data may include:

- User identity (login name, email)
- IP address (optional, can be anonymized or disabled)
- Browser and user agent (optional, can be disabled)
- Action performed, related object, and request URL
- Date and time of the activity

Data is stored in the WordPress database and is accessible only to administrators. It is retained based on the configured retention period. The plugin integrates with WordPress Privacy Tools for personal data export and anonymization requests.

Site owners should update their privacy policy to inform users about activity logging.

== Changelog ==

= 1.0.0 (2026-06-04) =
* Initial release.
* Plugin scaffold with PSR-4 autoloader, constants, activation/deactivation hooks.
* Custom database table (wpaal_activity_logs) with dbDelta and full index set.
* ActivityRepository with insert, fetch, count, filter, delete, anonymize methods.
* ActivityLogger central service with user context, request context, and recursive loop guard.
* PostActivityWatcher: post/page create/update/trash/restore/permanent delete.
* PluginActivityWatcher: plugin activation and deactivation with plugin name resolution.
* UserActivityWatcher: user creation and deletion (captures data before deletion).
* SettingsActivityWatcher: whitelist-based option tracking with sensitive value protection.
* MediaActivityWatcher: attachment upload and deletion.
* IpHelper: safe IP detection, normalization, and IPv4/IPv6 anonymization.
* UserAgentHelper and ContextHelper for request context capture.
* Admin logs page: filterable, searchable, paginated table with event badges.
* Log detail view: old/new values, IP, user agent, request URL with sensitive masking.
* CSV exporter with formula injection protection and filter preservation.
* Manual cleanup: delete by age or clear all with nonce and confirmation.
* Scheduled auto cleanup via WP-Cron with configurable retention.
* Settings page: tabbed UI for logging, privacy/IP, retention, display.
* Dashboard widget: today's summary, latest 10 logs, breakdown by object type.
* WordPress Privacy Tools integration: personal data exporter and eraser.
* Admin notice when logging is globally disabled.
* WP-CLI commands: list, summary, export, clear, clear-all.
* Developer hooks and filters throughout.
* PHPUnit test setup with tests for repository, settings watcher, CSV exporter, cleanup.
* Translation-ready with admin-activity-logger-lite text domain.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
