<?php
namespace Smackcoders\AdminActivityLogger\Admin;

if (!defined('ABSPATH')) {
	exit;
}

class HelpPage
{

	public function render()
	{
		?>
		<div class="wrap waal-wrap">
			<div class="waal-page-header" style="margin-bottom: 28px; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0;">
				<h2
					style="margin: 0; font-size: 22px; letter-spacing: -0.02em; font-weight: 800; line-height: 1.2; color: #0f172a;">
					<?php esc_html_e('Help & Support', 'admin-activity-logger-lite'); ?>
				</h2>
				<p style="margin: 6px 0 0 0; font-size: 14px; color: #64748b; font-weight: 500;">
					<?php esc_html_e('Need assistance? Check our documentation or reach out to our support team.', 'admin-activity-logger-lite'); ?>
				</p>
			</div>

			<div style="display: flex; gap: 24px; margin-top: 30px;">

				<!-- Documentation Box -->
				<div
					style="flex: 1; padding: 32px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
					<div style="margin-bottom: 16px; color: #3b82f6;">
						<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
							stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
							<path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"></path>
						</svg>
					</div>
					<h3 style="margin-top: 0; font-size: 18px; color: #0f172a; font-weight: 700;">
						<?php esc_html_e('Documentation', 'admin-activity-logger-lite'); ?>
					</h3>
					<p style="color: #64748b; font-size: 14px; margin-bottom: 24px; line-height: 1.5;">
						<?php esc_html_e('Learn how to monitor admin activity, configure tracking settings, and export your logs easily.', 'admin-activity-logger-lite'); ?>
					</p>
					<a href="https://www.smackcoders.com/documentation/wp-admin-activity-logger-lite/wordpress-admin-activity-log-plugin-guide"
						style="background: #1d4ed8 !important; color: #ffffff !important; border: none !important; border-radius: 6px !important; font-size: 13px !important; font-weight: 600 !important; padding: 6px 16px !important; min-height: 30px !important; line-height: normal !important; box-shadow: 0 4px 14px rgba(29, 78, 216, 0.3) !important; text-decoration: none !important; display: inline-flex !important; align-items: center !important; justify-content: center !important;">
						<?php esc_html_e('View Documentation', 'admin-activity-logger-lite'); ?>
					</a>
				</div>

				<!-- Contact Us Box -->
				<div
					style="flex: 1; padding: 32px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
					<div style="margin-bottom: 16px; color: #10b981;">
						<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
							stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
							<path
								d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
							</path>
						</svg>
					</div>
					<h3 style="margin-top: 0; font-size: 18px; color: #0f172a; font-weight: 700;">
						<?php esc_html_e('Contact Us', 'admin-activity-logger-lite'); ?>
					</h3>
					<p style="color: #64748b; font-size: 14px; margin-bottom: 24px; line-height: 1.5;">
						<?php esc_html_e('Have a question or found a bug? Reach out to our dedicated support team.', 'admin-activity-logger-lite'); ?>
					</p>
					<a href="https://www.smackcoders.com/contact-us.html/#form-field-name"
						style="background: #1d4ed8 !important; color: #ffffff !important; border: none !important; border-radius: 6px !important; font-size: 13px !important; font-weight: 600 !important; padding: 6px 16px !important; min-height: 30px !important; line-height: normal !important; box-shadow: 0 4px 14px rgba(29, 78, 216, 0.3) !important; text-decoration: none !important; display: inline-flex !important; align-items: center !important; justify-content: center !important;">
						<?php esc_html_e('Contact Us', 'admin-activity-logger-lite'); ?>
					</a>
				</div>

			</div>
		</div>
		<?php
	}
}
