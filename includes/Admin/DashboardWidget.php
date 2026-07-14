<?php
namespace Smackcoders\AdminActivityLogger\Admin;

use Smackcoders\AdminActivityLogger\Logger\ActivityRepository;
use Smackcoders\AdminActivityLogger\Logger\LogFormatter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DashboardWidget {

	public function register() {
		add_action( 'wp_dashboard_setup', array( $this, 'add_widget' ) );
	}

	public function add_widget() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = get_option( 'wpaal_settings', array() );
		if ( empty( $settings['show_dashboard_widget'] ) ) {
			return;
		}

		wp_add_dashboard_widget(
			'wpaal_dashboard_widget',
			__( 'Recent Admin Activity', 'admin-activity-logger-lite' ),
			array( $this, 'render' )
		);
	}

	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$repo    = new ActivityRepository();
		$logs    = $repo->get_logs( array( 'limit' => 10 ) );
		$total_today = $repo->count_today();
		$summary = $repo->get_summary_today();

		$by_type = array();
		foreach ( $summary as $row ) {
			$by_type[ $row['object_type'] ] = (int) $row['count'];
		}
		?>
		<div class="wpaal-widget">
			<div class="wpaal-widget-summary">
				<strong><?php printf(
					/* translators: %d: count */
					esc_html( _n( '%d event today', '%d events today', $total_today, 'admin-activity-logger-lite' ) ),
					(int) $total_today
				); ?></strong>
				<?php foreach ( $by_type as $type => $count ) : ?>
					<span class="wpaal-widget-type-count"><?php echo esc_html( ucfirst( $type ) ); ?>: <?php echo (int) $count; ?></span>
				<?php endforeach; ?>
			</div>

			<?php if ( empty( $logs ) ) : ?>
				<p class="wpaal-empty"><?php esc_html_e( 'No activity logged yet.', 'admin-activity-logger-lite' ); ?></p>
			<?php else : ?>
				<ul class="wpaal-widget-list">
					<?php foreach ( $logs as $log ) : ?>
						<li>
							<strong><?php echo esc_html( LogFormatter::event_type_label( $log['event_type'] ) ); ?>:</strong> 
							<?php echo esc_html( LogFormatter::truncate( $log['object_title'], 80 ) ); ?>
							<span class="wpaal-time" style="color: #646970; font-size: 12px; margin-left: 5px;">(<?php echo esc_html( LogFormatter::relative_time( $log['created_at'] ) ); ?>)</span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<p class="wpaal-widget-footer">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpaal-logs' ) ); ?>"><?php esc_html_e( 'View All Activity Logs', 'admin-activity-logger-lite' ); ?> &rarr;</a>
			</p>
		</div>
		<?php
	}
}
