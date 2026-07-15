# Admin Activity Logger Lite

A lightweight WordPress audit log for admin changes, with filtering, CSV export, and WP-CLI.

![License](https://img.shields.io/badge/license-GPLv2%2B-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-21759b.svg)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4.svg)

## Table of Contents

- [Overview](#overview)
- [Key Features](#key-features)
- [Use Cases](#use-cases)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Supported Integrations](#supported-integrations)
- [Screenshots](#screenshots)
- [Documentation](#documentation)
- [FAQ](#faq)
- [Roadmap](#roadmap)
- [Changelog](#changelog)
- [Security](#security)
- [Contributing](#contributing)
- [Support](#support)
- [License](#license)
- [Disclaimer](#disclaimer)
- [Author](#author)

## Overview

Admin Activity Logger Lite answers a question every multi-admin WordPress site runs into sooner or later: who changed what, and when? It records admin-side activity — post and page edits, plugin activation, user accounts, settings updates, and media — into a searchable audit trail you can filter, inspect, and export.

The plugin deliberately stays narrow. It is not a firewall or a full security suite; it is a focused activity log that captures the events worth keeping a record of and gets out of the way. Each entry stores the user, the event, the object involved, the before-and-after values, and the request context (IP address, user agent, and URL), so an unexpected change is easy to trace back to its source. For teams currently weighing a heavier audit-log tool, this is the smaller, faster option that runs comfortably on client sites and shared hosting.

Everything runs on a single custom database table with a WP-Cron cleanup routine, so the log never quietly grows into a maintenance problem.

## Key Features

- **Post and page tracking** — records create, update, trash, restore, and permanent-delete events across posts and pages.
- **Plugin activation tracking** — logs activation and deactivation, resolving each plugin's display name so entries are readable at a glance.
- **User account tracking** — captures user creation and deletion, preserving the user's data before a delete removes it.
- **Settings change log** — tracks a defined whitelist of options (site title, admin email, permalink structure, and more) while skipping sensitive values.
- **Media tracking** — logs attachment uploads and deletions.
- **Filterable log table** — a clean admin screen with filters for user, event type, object type, date range, and keyword search.
- **Detailed entry view** — open any row to see old and new values, IP address, user agent, and request URL.
- **CSV export** — export the filtered result set, with formula-injection protection applied to every field.
- **Manual cleanup** — delete logs older than 7, 30, 90, 180, or 365 days, or clear the table entirely.
- **Scheduled cleanup** — a WP-Cron job prunes old logs automatically at your chosen retention period.
- **Dashboard widget** — today's event count and the 10 most recent entries, shown on the WordPress dashboard.
- **Privacy Tools integration** — plugs into WordPress personal-data export and erasure for GDPR data requests.
- **IP capture with anonymization** — records IPv4 and IPv6 addresses, ignores spoofable proxy headers, and can anonymize or disable capture entirely.
- **WP-CLI commands** — list, summarize, export, and clear logs from the terminal.
- **Developer hooks** — filters and actions for tuning what is logged, how it is stored, and how it is exported.
- **Translation-ready** — ships with the `admin-activity-logger-lite` text domain and language files.
- **Security-hardened** — nonces, capability checks, prepared SQL statements, and output escaping throughout.

## Use Cases

- **Client-site accountability** — agencies running several admins on one install can see exactly who edited a post, changed a setting, or deactivated a plugin.
- **Troubleshooting unexpected changes** — when something breaks, trace it to the person and the action that caused it instead of guessing.
- **Compliance and audit reporting** — export the log as CSV for internal review or a client-facing audit.
- **Membership and multi-author sites** — keep a record of user creation and deletion where many people have back-end access.
- **Hands-off log hygiene** — set a retention period and let scheduled cleanup keep the table trimmed.
- **CLI and DevOps workflows** — script exports and cleanup across many sites with the WP-CLI commands.

## Requirements

| Requirement | Version |
| --- | --- |
| WordPress | 5.0 or higher |
| PHP | 7.4 or higher |
| WP-CLI | Optional — only needed for the command-line tools |

No external API, account, or third-party service is required.

## Installation

### Install from WordPress

1. Go to **WordPress Admin → Plugins → Add New**.
2. Search for "Admin Activity Logger Lite", or click **Upload Plugin** and choose the plugin ZIP file.
3. Click **Install Now**, then **Activate**.

### Manual Installation

1. Download or clone this repository.
2. Upload the `admin-activity-logger-lite` folder to `/wp-content/plugins/`.
3. Activate the plugin from **WordPress Admin → Plugins**.

## Configuration

1. After activation, open **Settings → Activity Logger**.
2. Configure the plugin across its tabs:
   - **Logging** — turn logging on or off globally, and pick which events to track.
   - **Privacy & IP** — enable or disable IP capture, and switch on anonymization if you need it.
   - **Retention** — choose a manual or scheduled cleanup window (7, 30, 90, 180, or 365 days).
   - **Display** — control how the log table and dashboard widget are presented.
3. Open **Tools → Activity Logs** to view, filter, search, and export recorded activity.

## Usage

1. Perform any tracked action — edit a post, activate a plugin, change a setting, upload media, or create or delete a user.
2. Open **Tools → Activity Logs**. The event appears in the log table with its user, event type, object type, and timestamp.
3. Click a row to open the detail view and inspect old and new values, IP address, user agent, and request URL.
4. Narrow the list with the filters (user, event type, object type, date range, keyword), then export the filtered set to CSV.
5. Check the dashboard widget for today's activity and the 10 most recent entries without leaving the dashboard.

### WP-CLI Commands

```bash
wp wpaal list                          # List recent logs (--limit=<n>, default 20)
wp wpaal summary                       # Show today's activity summary
wp wpaal export --file=/path/logs.csv  # Export logs to CSV
wp wpaal clear --older-than=90         # Clear logs older than 90 days
wp wpaal clear-all                     # Clear all logs (asks for confirmation)
```

### Developer Hooks

| Hook | Purpose |
| --- | --- |
| `wpaal_should_log_activity` | Skip specific events before they are logged |
| `wpaal_log_data_before_insert` | Modify log data before it is saved |
| `wpaal_tracked_options` | Change which options are tracked |
| `wpaal_sensitive_option_patterns` | Add option patterns treated as sensitive |
| `wpaal_supported_post_types` | Change which post types are tracked |
| `wpaal_csv_export_columns` | Customize the CSV export columns |
| `wpaal_csv_export_filename` | Customize the export filename |
| `wpaal_log_retention_days` | Override the retention period |
| `wpaal_detected_ip_address` | Override IP detection |
| `wpaal_request_context` | Modify captured request-context data |
| `wpaal_activity_logged` | Fires after an entry is saved |
| `wpaal_before_logs_cleared` | Fires before cleanup runs |
| `wpaal_after_logs_cleared` | Fires after cleanup runs |

## Supported Integrations

- **WordPress Privacy Tools** — personal-data exporter and eraser for GDPR data requests.
- **WP-Cron** — drives scheduled cleanup of old logs.
- **WP-CLI** — command-line log management (list, summary, export, clear, clear-all).

## Screenshots

This repository does not bundle screenshot images. The plugin's interface covers:

1. The activity logs page with event badges, filters, and an export button.
2. The log detail view showing old and new values, IP, user agent, and request URL.
3. The settings page for logging configuration.
4. The settings page for privacy and IP options.
5. The settings page for retention and scheduled cleanup.
6. The dashboard widget with today's activity summary.

Full screenshots are available on the [WordPress.org plugin listing](https://wordpress.org/plugins/admin-activity-logger-lite/).

## Documentation

For setup notes, WP-CLI usage, and the full hook reference, see the `readme.txt` bundled with this plugin and the [WordPress.org plugin page](https://wordpress.org/plugins/admin-activity-logger-lite/).

## FAQ

### Does it log plugin activation and deactivation?

Yes. Both are tracked, and each entry resolves the plugin's display name so you can tell at a glance which plugin it refers to.

### Can I export logs to CSV?

Yes. Export the current filtered result set as CSV straight from the activity logs page. Formula-injection protection is applied to every field automatically.

### Does it track media uploads and deletions?

Yes. Attachment uploads and deletions are logged as media events.

### How long are logs kept?

By default they are kept indefinitely. You can delete logs older than 7, 30, 90, 180, or 365 days manually, or set a retention period and let scheduled cleanup handle it.

### Does it support WP-CLI?

Yes. The `wp wpaal list`, `summary`, `export`, `clear`, and `clear-all` commands manage logs from the terminal.

### Is there a dashboard widget?

Yes. It shows today's event count and the 10 most recent entries on the WordPress dashboard.

### Does this plugin block suspicious activity?

No. The free version logs admin activity for accountability and troubleshooting. Real-time alerts and blocking are planned for a Pro version.

### Does the plugin store passwords or API keys?

No. Passwords, API keys, and OAuth tokens are never logged. Tracked settings use a whitelist, and values matching sensitive patterns are excluded.

### Who can view the activity logs?

Users with the `manage_options` capability.

### Does it work with WordPress privacy tools?

Yes. It hooks into WordPress personal-data export and erasure to support GDPR-related requests.

## Roadmap

The following are being explored for a future Pro version and are not part of the current release. These are directions under consideration, not commitments to dates.

- Real-time alerts (email, Slack, Telegram, Google Chat)
- WooCommerce order activity logs
- Role-based log visibility
- External log storage (S3, Elasticsearch, Google Sheets)
- GitLab issue creation for critical admin events
- Multisite network-wide activity logs
- Advanced diff view for content and settings changes
- AI-assisted suspicious-activity summaries

## Changelog

### 1.0.0 (2026-06-04)

- Initial release.
- Plugin scaffold with PSR-4 autoloader, constants, and activation/deactivation hooks.
- Custom database table (`wpaal_activity_logs`) built with dbDelta and a full index set.
- `ActivityRepository` with insert, fetch, count, filter, delete, and anonymize methods.
- `ActivityLogger` central service with user context, request context, and a recursive-loop guard.
- `PostActivityWatcher` for post and page create, update, trash, restore, and permanent delete.
- `PluginActivityWatcher` for plugin activation and deactivation with plugin name resolution.
- `UserActivityWatcher` for user creation and deletion, capturing data before delete.
- `SettingsActivityWatcher` with whitelist-based option tracking and sensitive-value protection.
- `MediaActivityWatcher` for attachment upload and deletion.
- `IpHelper` for safe IP detection, normalization, and IPv4/IPv6 anonymization.
- `UserAgentHelper` and `ContextHelper` for request-context capture.
- Admin logs page: filterable, searchable, paginated table with event badges.
- Log detail view with old and new values, IP, user agent, and request URL, with sensitive masking.
- CSV exporter with formula-injection protection and filter preservation.
- Manual cleanup: delete by age or clear all, with nonce and confirmation.
- Scheduled cleanup via WP-Cron with configurable retention.
- Settings page with a tabbed UI for logging, privacy/IP, retention, and display.
- Dashboard widget: today's summary, latest 10 logs, and a breakdown by object type.
- WordPress Privacy Tools integration: personal-data exporter and eraser.
- Admin notice shown when logging is globally disabled.
- WP-CLI commands: list, summary, export, clear, clear-all.
- Developer hooks and filters throughout.
- PHPUnit test setup covering the repository, settings watcher, CSV exporter, and cleanup.
- Translation-ready with the `admin-activity-logger-lite` text domain.

## Security

The plugin follows WordPress security practice: nonces on every state-changing action, `manage_options` capability checks before any log data is shown or modified, prepared SQL statements, output escaping, and formula-injection protection on CSV exports. IP detection trusts only `REMOTE_ADDR` rather than proxy headers that can be forged. Passwords, API keys, and tokens are never recorded.

If you find a security vulnerability, please do not open a public GitHub issue. Report it privately to the Smackcoders team at https://smackcoders.com/ so it can be investigated and patched before disclosure.

## Contributing

Contributions are welcome. To propose a change:

1. Fork this repository and create a feature branch.
2. Make your changes, following the existing code style (PSR-4 autoloading, WordPress coding standards).
3. Add or update PHPUnit tests where relevant.
4. Open a pull request describing the change and the reason for it.

Bug reports and feature suggestions are also welcome through GitHub Issues.

## Support

- **GitHub Issues** — report bugs or request features on the [repository issue tracker](https://github.com/Smackcoders/WP-Admin-Activity-Logger-Lite/issues).
- **WordPress.org support forum** — https://wordpress.org/plugins/admin-activity-logger-lite/
- **Official support** — https://smackcoders.com/

## License

Licensed under the **GPLv2 or later**. See https://www.gnu.org/licenses/gpl-2.0.html for the full license text.

Copyright © Smackcoders.

## Disclaimer

Admin Activity Logger Lite is an independent product developed by Smackcoders and is not affiliated with, sponsored by, or endorsed by Automattic Inc. or the WordPress project. "WordPress" is a trademark of the WordPress Foundation. Any reference to third-party plugins is for comparison only.

## Author

Developed and maintained by [Smackcoders](https://smackcoders.com/).
