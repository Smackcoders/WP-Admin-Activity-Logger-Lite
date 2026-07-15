# Admin Activity Logger Lite

A lightweight WordPress activity log plugin that tracks admin-side changes — post edits, plugin activation, user changes, settings updates, and media activity — with exportable audit logs.

## Overview

Admin Activity Logger Lite is a free WordPress audit log plugin built for accountability, troubleshooting, and client-site maintenance. It gives site owners and agencies a lightweight audit trail that answers the question every multi-admin site eventually asks: who changed what, when, and how.

Unlike heavier security suites, this plugin focuses purely on **admin activity tracking** — a wordpress admin change tracker that stays fast and unobtrusive while still capturing the events that matter for compliance tracking and forensic logging. It is well suited as a lightweight activity log plugin alternative for teams looking for a wp activity log alternative or a simple history alternative that is easier to run on smaller sites.

**Who should use it:**

- Multi-admin WordPress websites
- Agencies managing client sites
- Membership platforms
- WordPress maintenance and support teams
- Security-conscious site managers who need ongoing security monitoring and admin oversight

## Key Features

- **Post & page activity watchers** — post page tracking for create, update, trash, restore, and permanent delete events
- **Plugin activation tracking** — logs plugin activation and deactivation with plugin name resolution
- **User creation & deletion tracking** — captures user data before deletion for a reliable wordpress user deletion log
- **Settings whitelist tracking** — a wordpress settings change log for site title, admin email, permalink structure, and more, without ever logging sensitive values
- **Media upload & deletion tracking** — a dedicated wordpress media activity log for attachment uploads and deletions
- **Activity logger admin page** — a clean, filterable log table with filters for user, event type, object type, date range, and keyword search
- **Detailed log view** — see old/new values, IP address, user agent, and request URL for every entry
- **CSV export** — export filtered logs with built-in formula injection protection, making it a safe wordpress csv export log plugin
- **Manual log cleanup** — delete logs older than 7/30/90/180/365 days, or clear all
- **Scheduled auto cleanup** — a wp-cron based wordpress log cleanup plugin feature with configurable retention period
- **Dashboard widget** — today's activity summary and the latest 10 events, right on the WordPress dashboard
- **Privacy Tools integration** — works with WordPress's personal data exporter and eraser for wordpress gdpr logging plugin compliance
- **IP address capture with anonymization** — supports IPv4 and IPv6, with ip spoofing prevention baked into IP detection
- **WP-CLI commands** — full wp-cli activity log support for listing, exporting, summarizing, and clearing logs from the terminal
- **Developer hooks and filters** — extend or customize almost every part of the logging behaviour
- **Translation-ready strings** — ready for localization out of the box
- **Security hardened** — nonces, capability checks, prepared SQL statements, and CSV formula injection protection throughout

## Use Cases

- **Client-site accountability** — agencies managing several admins on a client's WordPress install can see exactly who edited or deleted a post, changed a setting, or deactivated a plugin
- **Troubleshooting after unexpected changes** — quickly answer "how to see who edited a post wordpress" or "how to see who deleted a user wordpress" questions when something breaks
- **Compliance and audit reporting** — export a wordpress compliance logging plugin trail as CSV for internal reviews or client audits
- **User accountability on membership sites** — track user creation and deletion events on platforms with multiple contributors or subscribers
- **Automated log hygiene** — schedule automatic cleanup so a growing wordpress activity log plugin table never becomes a maintenance burden
- **DevOps / CLI workflows** — agencies that manage many sites can use the wp-cli activity log commands to script log exports and cleanup across servers

## Requirements

- **WordPress:** 5.0 or higher
- **PHP:** 7.4 or higher
- **Other requirements:** None — no external API account or third-party service is required. WP-CLI is optional and only needed if you want to use the command-line tools.

## Installation

### Install from WordPress

1. Download the plugin ZIP file (or install it directly from the WordPress.org plugin directory).
2. Go to **WordPress Admin → Plugins → Add New**.
3. Search for "Admin Activity Logger Lite", or click **Upload Plugin** and select the ZIP file.
4. Install and **Activate** the plugin.

### Manual Installation

1. Download or clone this repository.
2. Upload the `admin-activity-logger-lite` folder to `/wp-content/plugins/`.
3. Activate the plugin from **WordPress Admin → Plugins**.

## Configuration / Setup

1. After activation, go to **Settings → Activity Logger** to configure the plugin.
2. Choose which settings tab to configure:
   - **Logging** — enable/disable logging globally and choose which events to track
   - **Privacy & IP** — enable or disable IP address capture, and turn on IP anonymization if required
   - **Retention** — set a manual or scheduled auto-cleanup retention period (7/30/90/180/365 days)
   - **Display** — control how the log table and dashboard widget are presented
3. Go to **Tools → Activity Logs** to view, filter, search, and export recorded activity.

## Usage

1. Perform any tracked action in WordPress — edit a post, activate a plugin, change a setting, upload media, or create/delete a user.
2. Open **Tools → Activity Logs** to see the event appear in the filterable log table, complete with user, event type, object type, and timestamp.
3. Click a row to open the detail view and inspect old/new values, IP address, user agent, and the request URL.
4. Use the filters (user, event type, object type, date range, keyword search) to narrow down results, then export the filtered set to CSV.
5. Check the WordPress dashboard widget for a quick summary of today's activity and the latest 10 events without leaving the dashboard.
6. Optionally, manage logs from the command line with WP-CLI (see below).

### WP-CLI Commands

```bash
wp wpaal list                          # List recent logs
wp wpaal summary                       # Show today's activity summary
wp wpaal export --file=/path/logs.csv  # Export logs to CSV
wp wpaal clear --older-than=90         # Clear logs older than 90 days
wp wpaal clear-all                     # Clear all logs (with confirmation)
```

### Developer Hooks

| Hook | Purpose |
|---|---|
| `wpaal_should_log_activity` | Disable specific log events |
| `wpaal_log_data_before_insert` | Modify log data before saving |
| `wpaal_tracked_options` | Customize which options are tracked |
| `wpaal_sensitive_option_patterns` | Add custom sensitive option patterns |
| `wpaal_supported_post_types` | Change tracked post types |
| `wpaal_csv_export_columns` | Customize CSV export columns |
| `wpaal_csv_export_filename` | Customize export filename |
| `wpaal_log_retention_days` | Override retention period |
| `wpaal_detected_ip_address` | Override IP detection |
| `wpaal_request_context` | Modify request context data |
| `wpaal_activity_logged` | Fires after a log is saved |
| `wpaal_before_logs_cleared` | Fires before cleanup |
| `wpaal_after_logs_cleared` | Fires after cleanup |

## Supported Integrations

- **WordPress Privacy Tools** — personal data exporter and eraser (GDPR data requests)
- **WP-Cron** — powers scheduled auto cleanup of old logs
- **WP-CLI** — command-line log management (list, summary, export, clear, clear-all)

## Screenshots / Demo

The plugin interface includes:

1. Activity logs page with event badges, filters, and export button
2. Log detail view showing old/new values, IP, user agent, and request URL
3. Settings page — logging configuration
4. Settings page — privacy and IP options
5. Settings page — retention and auto cleanup
6. WordPress dashboard widget with today's activity summary
7. CSV export downloaded to a spreadsheet

Full screenshots are available on the [WordPress.org plugin listing](https://wordpress.org/plugins/admin-activity-logger-lite/).

## Documentation

For setup details, WP-CLI usage, and the full list of developer hooks, see the WordPress.org plugin page and the `readme.txt` file bundled with this plugin: https://wordpress.org/plugins/admin-activity-logger-lite/

## Frequently Asked Questions

### Does it log plugin activation and deactivation?
Yes. Plugin activation and deactivation events are tracked, including resolution of the plugin's display name, so you always know which plugin activation log entry corresponds to which plugin.

### Can I export logs to CSV?
Yes. Admins can export filtered logs as CSV directly from the activity logs page, with formula injection protection applied automatically.

### Does it track media uploads and deletions?
Yes. Media file uploads and deletions are logged as part of the plugin's media activity tracking.

### How long are logs kept?
By default logs are kept indefinitely. You can manually delete logs older than 7, 30, 90, 180, or 365 days, or enable scheduled auto cleanup with a configurable retention period.

### Does it support WP-CLI?
Yes. `wp wpaal list`, `summary`, `export`, `clear`, and `clear-all` commands are available for managing logs from the terminal.

### Is there a dashboard widget?
Yes. The WordPress dashboard widget shows today's event count and the 10 most recent activity log entries.

### Does this plugin block suspicious activity?
No. The free version focuses on logging admin activity for accountability and troubleshooting. Real-time alerts and blocking are planned for the Pro version.

### Does the plugin store passwords or API keys?
No. Sensitive values such as passwords, API keys, and OAuth tokens are never logged. Tracked settings use a whitelist approach.

### Who can view the activity logs?
In the free version, users with the `manage_options` capability can view logs.

### Does it work with WordPress privacy tools?
Yes. The plugin integrates with WordPress's built-in personal data export and erasure tools to support GDPR-related requests.

## Roadmap

The following are being explored for a future Pro version and are not part of the current release:

- Real-time alerts (email/Slack/Telegram/Google Chat)
- WooCommerce order activity logs
- Role-based log visibility
- External log storage (S3, Elasticsearch, Google Sheets)
- GitLab issue creation for critical admin events
- Multisite network-wide activity logs
- Advanced diff view for content and settings changes
- AI-based suspicious activity summary

## Changelog

### 1.0.0 (2026-06-04)
- Initial release
- Plugin scaffold with PSR-4 autoloader, constants, and activation/deactivation hooks
- Custom database table (`wpaal_activity_logs`) with dbDelta and a full index set
- `ActivityRepository` with insert, fetch, count, filter, delete, and anonymize methods
- `ActivityLogger` central service with user context, request context, and recursive loop guard
- `PostActivityWatcher` for post/page create, update, trash, restore, and permanent delete
- `PluginActivityWatcher` for plugin activation and deactivation with plugin name resolution
- `UserActivityWatcher` for user creation and deletion (captures data before deletion)
- `SettingsActivityWatcher` with whitelist-based option tracking and sensitive value protection
- `MediaActivityWatcher` for attachment upload and deletion
- `IpHelper` for safe IP detection, normalization, and IPv4/IPv6 anonymization
- `UserAgentHelper` and `ContextHelper` for request context capture
- Admin logs page: filterable, searchable, paginated table with event badges
- Log detail view with old/new values, IP, user agent, and request URL, with sensitive masking
- CSV exporter with formula injection protection and filter preservation
- Manual cleanup: delete by age or clear all, with nonce and confirmation
- Scheduled auto cleanup via WP-Cron with configurable retention
- Settings page with tabbed UI for logging, privacy/IP, retention, and display
- Dashboard widget: today's summary, latest 10 logs, breakdown by object type
- WordPress Privacy Tools integration: personal data exporter and eraser
- Admin notice when logging is globally disabled
- WP-CLI commands: list, summary, export, clear, clear-all
- Developer hooks and filters throughout
- PHPUnit test setup with tests for repository, settings watcher, CSV exporter, and cleanup
- Translation-ready with the `admin-activity-logger-lite` text domain

## Security

Admin Activity Logger Lite is built with WordPress security best practices: nonces on all state-changing actions, capability checks (`manage_options`) before any log data is displayed or modified, prepared SQL statements, output escaping, and CSV formula injection protection on exports. Sensitive values such as passwords, API keys, and tokens are never recorded.

If you discover a security vulnerability, please **do not** disclose it in a public GitHub issue. Instead, report it privately to the Smackcoders team at https://smackcoders.com/ so it can be investigated and patched responsibly before public disclosure.

## Contributing

Contributions are welcome. If you'd like to help improve this WordPress activity log plugin:

1. Fork this repository and create a feature branch.
2. Make your changes, following the existing code style (PSR-4 autoloading, WordPress coding standards).
3. Add or update PHPUnit tests where relevant.
4. Submit a pull request describing the change and the motivation behind it.

Bug reports and feature suggestions are also welcome via GitHub Issues.

## Support

- **GitHub Issues:** Use this repository's Issues tab to report bugs or request features.
- **WordPress.org support forum:** https://wordpress.org/plugins/admin-activity-logger-lite/
- **Official support page:** https://smackcoders.com/

## License

This plugin is licensed under the **GPLv2 or later**.
See https://www.gnu.org/licenses/gpl-2.0.html for full license text.

Copyright © Smackcoders.

## Disclaimer

Admin Activity Logger Lite is an independent product developed by Smackcoders and is not affiliated with, sponsored by, or endorsed by Automattic Inc. or the official WordPress project. "WordPress" is a trademark of the WordPress Foundation. Any references to third-party plugins (e.g. WP Activity Log, Simple History) are for comparison purposes only.

## Author / Maintainer

Developed and maintained by **[Smackcoders](https://smackcoders.com/)**.
