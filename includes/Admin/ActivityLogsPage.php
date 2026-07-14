<?php
namespace Smackcoders\AdminActivityLogger\Admin;

use Smackcoders\AdminActivityLogger\Logger\ActivityRepository;
use Smackcoders\AdminActivityLogger\Logger\LogFormatter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ActivityLogsPage {

	private $repository;

	public function __construct() {
		$this->repository = new ActivityRepository();
	}

	public function render() {
		$settings = get_option( 'wpaal_settings', array() );
		$view_cap = ! empty( $settings['view_logs_cap'] ) ? sanitize_key( $settings['view_logs_cap'] ) : 'manage_options';
		if ( ! current_user_can( $view_cap ) ) {
			return;
		}

		$filter_nonce = isset( $_GET['wpaal_filter_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['wpaal_filter_nonce'] ) ) : '';
		$has_filters  = isset( $_GET['user_id'] ) || isset( $_GET['event_type'] ) || isset( $_GET['object_type'] ) || isset( $_GET['search'] ) || isset( $_GET['date_from'] ) || isset( $_GET['date_to'] );
		$use_filters  = ! $has_filters || wp_verify_nonce( $filter_nonce, 'wpaal_filter_logs' );
		$user_id      = ( $use_filters && isset( $_GET['user_id'] ) ) ? absint( wp_unslash( $_GET['user_id'] ) ) : 0;
		$event_type   = ( $use_filters && isset( $_GET['event_type'] ) ) ? sanitize_text_field( wp_unslash( $_GET['event_type'] ) ) : '';
		$object_type  = ( $use_filters && isset( $_GET['object_type'] ) ) ? sanitize_text_field( wp_unslash( $_GET['object_type'] ) ) : '';
		$search       = ( $use_filters && isset( $_GET['search'] ) ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '';
		$date_from    = ( $use_filters && isset( $_GET['date_from'] ) ) ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) : '';
		$date_to      = ( $use_filters && isset( $_GET['date_to'] ) ) ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) ) : '';
		$paged        = isset( $_GET['paged'] ) ? max( 1, absint( wp_unslash( $_GET['paged'] ) ) ) : 1;
		$per_page    = 20;

		$filters = compact( 'user_id', 'event_type', 'object_type', 'search', 'date_from', 'date_to' );
		$total   = $this->repository->count_logs( $filters );
		$logs    = $this->repository->get_logs( array_merge( $filters, array(
			'limit'  => $per_page,
			'offset' => ( $paged - 1 ) * $per_page,
		) ) );

		$total_pages = $total > 0 ? ceil( $total / $per_page ) : 1;

		$export_url = wp_nonce_url(
			add_query_arg( array_merge( array( 'page' => 'wpaal-logs', 'wpaal_export_csv' => '1' ), $filters ), admin_url( 'admin.php' ) ),
			'wpaal_export_csv'
		);

		$detail_nonce = isset( $_GET['wpaal_detail_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['wpaal_detail_nonce'] ) ) : '';
		$detail_id    = ( wp_verify_nonce( $detail_nonce, 'wpaal_view_log_detail' ) && isset( $_GET['detail'] ) ) ? absint( wp_unslash( $_GET['detail'] ) ) : 0;
		$detail_log   = $detail_id ? $this->repository->get( $detail_id ) : null;

		$notice_nonce = isset( $_GET['wpaal_notice_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['wpaal_notice_nonce'] ) ) : '';
		$show_notice  = wp_verify_nonce( $notice_nonce, 'wpaal_logs_notice' );
		$wpaal_msg    = ( $show_notice && isset( $_GET['wpaal_msg'] ) ) ? sanitize_text_field( wp_unslash( $_GET['wpaal_msg'] ) ) : '';
		$wpaal_count  = ( $show_notice && isset( $_GET['wpaal_count'] ) ) ? absint( wp_unslash( $_GET['wpaal_count'] ) ) : 0;

		$users = get_users( array( 'fields' => array( 'ID', 'user_login' ), 'number' => 100 ) );

		$event_types = array(
			'post_created', 'post_updated', 'post_trashed', 'post_restored', 'post_deleted',
			'page_created', 'page_updated', 'page_trashed', 'page_restored', 'page_deleted',
			'plugin_activated', 'plugin_deactivated',
			'user_created', 'user_deleted',
			'settings_updated',
			'media_uploaded', 'media_deleted',
		);
		?>
		<div class="wrap wpaal-logs-wrap">
			<h1><?php esc_html_e( 'Admin Activity Logs', 'admin-activity-logger-lite' ); ?></h1>

			<?php if ( 'cleared' === $wpaal_msg || 'deleted' === $wpaal_msg ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php printf(
						/* translators: %d: count */
						esc_html( _n( '%d log deleted.', '%d logs deleted.', $wpaal_count, 'admin-activity-logger-lite' ) ),
						(int) $wpaal_count
					); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( $detail_log ) : ?>
				<?php $this->render_detail( $detail_log ); ?>
				<p><a href="<?php echo esc_url( remove_query_arg( 'detail' ) ); ?>" class="wpaal-back-btn">&larr; <?php esc_html_e( 'Back to Logs', 'admin-activity-logger-lite' ); ?></a></p>
			<?php else : ?>

			<form method="get" action="" class="wpaal-toolbar">
				<div class="wpaal-filter-inputs">
					<input type="hidden" name="page" value="wpaal-logs">
					<?php wp_nonce_field( 'wpaal_filter_logs', 'wpaal_filter_nonce' ); ?>

					<select name="user_id" class="wpaal-filter-select">
						<option value=""><?php esc_html_e( 'All Users', 'admin-activity-logger-lite' ); ?></option>
						<?php foreach ( $users as $u ) : ?>
							<option value="<?php echo esc_attr( $u->ID ); ?>" <?php selected( $user_id, $u->ID ); ?>><?php echo esc_html( $u->user_login ); ?></option>
						<?php endforeach; ?>
					</select>

					<select name="event_type" class="wpaal-filter-select">
						<option value=""><?php esc_html_e( 'All Events', 'admin-activity-logger-lite' ); ?></option>
						<?php foreach ( $event_types as $et ) : ?>
							<option value="<?php echo esc_attr( $et ); ?>" <?php selected( $event_type, $et ); ?>><?php echo esc_html( LogFormatter::event_type_label( $et ) ); ?></option>
						<?php endforeach; ?>
					</select>

					<select name="object_type" class="wpaal-filter-select">
						<option value=""><?php esc_html_e( 'All Objects', 'admin-activity-logger-lite' ); ?></option>
						<?php foreach ( array( 'post', 'page', 'plugin', 'user', 'option', 'attachment' ) as $ot ) : ?>
							<?php
							if ( 'option' === $ot ) {
								$label = __( 'Settings', 'admin-activity-logger-lite' );
							} elseif ( 'attachment' === $ot ) {
								$label = __( 'Media Item', 'admin-activity-logger-lite' );
							} elseif ( 'post' === $ot ) {
								$label = __( 'Post', 'admin-activity-logger-lite' );
							} elseif ( 'page' === $ot ) {
								$label = __( 'Page', 'admin-activity-logger-lite' );
							} elseif ( 'plugin' === $ot ) {
								$label = __( 'Plugin', 'admin-activity-logger-lite' );
							} elseif ( 'user' === $ot ) {
								$label = __( 'User', 'admin-activity-logger-lite' );
							} else {
								$label = ucfirst( $ot );
							}
							?>
							<option value="<?php echo esc_attr( $ot ); ?>" <?php selected( $object_type, $ot ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>

					<input type="search" name="search" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search...', 'admin-activity-logger-lite' ); ?>">
					<input type="date" name="date_from" value="<?php echo esc_attr( $date_from ); ?>">
					<input type="date" name="date_to" value="<?php echo esc_attr( $date_to ); ?>">

					<input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Filter', 'admin-activity-logger-lite' ); ?>">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpaal-logs' ) ); ?>" class="button"><?php esc_html_e( 'Reset', 'admin-activity-logger-lite' ); ?></a>
					<a href="<?php echo esc_url( $export_url ); ?>" class="button button-secondary"><?php esc_html_e( 'Export CSV', 'admin-activity-logger-lite' ); ?></a>
					<button type="button" class="button button-secondary" id="wpaal-toggle-cleanup"><?php esc_html_e( 'Clear Logs', 'admin-activity-logger-lite' ); ?></button>
				</div>
			</form>

			<div id="wpaal-cleanup-panel" style="display:none;" class="wpaal-cleanup-panel">
				<form method="post" action="">
					<?php wp_nonce_field( 'wpaal_cleanup', 'wpaal_cleanup_nonce' ); ?>
					<label for="wpaal_cleanup_action_select"><?php esc_html_e( 'Delete logs older than:', 'admin-activity-logger-lite' ); ?></label>
					<select name="wpaal_cleanup_action" id="wpaal_cleanup_action_select">
						<option value="7"><?php esc_html_e( '7 days', 'admin-activity-logger-lite' ); ?></option>
						<option value="30"><?php esc_html_e( '30 days', 'admin-activity-logger-lite' ); ?></option>
						<option value="90" selected><?php esc_html_e( '90 days', 'admin-activity-logger-lite' ); ?></option>
						<option value="180"><?php esc_html_e( '180 days', 'admin-activity-logger-lite' ); ?></option>
						<option value="365"><?php esc_html_e( '365 days', 'admin-activity-logger-lite' ); ?></option>
						<option value="clear_all"><?php esc_html_e( 'ALL logs', 'admin-activity-logger-lite' ); ?></option>
					</select>
					<input type="submit" class="button button-primary wpaal-confirm-clear" value="<?php esc_attr_e( 'Delete', 'admin-activity-logger-lite' ); ?>">
				</form>
			</div>

			<?php if ( empty( $logs ) ) : ?>
				<div class="wpaal-empty-state"><p><?php esc_html_e( 'No activity logs found.', 'admin-activity-logger-lite' ); ?></p></div>
			<?php else : ?>

				<p class="wpaal-count"><?php printf(
					/* translators: %d: total */
					esc_html( _n( '%d log found', '%d logs found', $total, 'admin-activity-logger-lite' ) ),
					(int) $total
				); ?></p>

				<form method="post" action="" id="wpaal-bulk-actions-form">
					<?php wp_nonce_field( 'wpaal_bulk_action', 'wpaal_bulk_action_nonce' ); ?>
					
					<div class="tablenav top" style="margin: 10px 0 5px 0; display: flex; align-items: center;">
						<div class="alignleft actions bulkactions" style="display: flex; gap: 5px;">
							<select name="action" id="bulk-action-selector-top">
								<option value="-1"><?php esc_html_e( 'Bulk actions', 'admin-activity-logger-lite' ); ?></option>
								<option value="delete"><?php esc_html_e( 'Delete', 'admin-activity-logger-lite' ); ?></option>
							</select>
							<input type="submit" id="doaction" class="button action" value="<?php esc_attr_e( 'Apply', 'admin-activity-logger-lite' ); ?>">
						</div>
					</div>

				<table class="wp-list-table widefat fixed striped wpaal-logs-table">
					<thead>
						<tr>
							<td id="cb" class="manage-column column-cb check-column" style="width: 2.2em; padding: 8px 10px;"><input id="cb-select-all-1" type="checkbox"></td>
							<th width="140"><?php esc_html_e( 'Date', 'admin-activity-logger-lite' ); ?></th>
							<th width="110"><?php esc_html_e( 'User', 'admin-activity-logger-lite' ); ?></th>
							<th width="150"><?php esc_html_e( 'Event', 'admin-activity-logger-lite' ); ?></th>
							<th width="80"><?php esc_html_e( 'Object', 'admin-activity-logger-lite' ); ?></th>
							<th class="column-message"><?php esc_html_e( 'Message', 'admin-activity-logger-lite' ); ?></th>
							<th width="100"><?php esc_html_e( 'IP', 'admin-activity-logger-lite' ); ?></th>
							<th width="120"><?php esc_html_e( 'Actions', 'admin-activity-logger-lite' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $logs as $log ) : ?>
							<tr>
								<th scope="row" class="check-column" style="padding: 8px 10px;"><input id="cb-select-<?php echo (int) $log['id']; ?>" type="checkbox" name="wpaal_bulk_ids[]" value="<?php echo (int) $log['id']; ?>"></th>
								<td title="<?php echo esc_attr( $log['created_at'] ); ?>"><?php echo esc_html( LogFormatter::relative_time( $log['created_at'] ) ); ?></td>
								<td><?php echo esc_html( $log['user_login'] ?? __( '(system)', 'admin-activity-logger-lite' ) ); ?></td>
								<td><span class="<?php echo esc_attr( LogFormatter::event_type_badge_class( $log['event_type'] ) ); ?>"><?php echo esc_html( LogFormatter::event_type_label( $log['event_type'] ) ); ?></span></td>
								<td><?php
									$obj_type = $log['object_type'] ?? '';
									if ( 'option' === $obj_type ) {
										echo esc_html( __( 'Settings', 'admin-activity-logger-lite' ) );
									} elseif ( 'attachment' === $obj_type ) {
										echo esc_html( __( 'Media Item', 'admin-activity-logger-lite' ) );
									} elseif ( 'post' === $obj_type ) {
										echo esc_html( __( 'Post', 'admin-activity-logger-lite' ) );
									} elseif ( 'page' === $obj_type ) {
										echo esc_html( __( 'Page', 'admin-activity-logger-lite' ) );
									} elseif ( 'plugin' === $obj_type ) {
										echo esc_html( __( 'Plugin', 'admin-activity-logger-lite' ) );
									} elseif ( 'user' === $obj_type ) {
										echo esc_html( __( 'User', 'admin-activity-logger-lite' ) );
									} else {
										echo esc_html( ucfirst( $obj_type ) );
									}
								?></td>
								<td class="column-message"><?php echo esc_html( LogFormatter::truncate( $log['message'] ) ); ?></td>
								<td><?php echo esc_html( $log['ip_address'] ?? '' ); ?></td>
								<td>
									<div style="display: flex; gap: 16px; align-items: center; justify-content: flex-start;">
										<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'detail', $log['id'] ), 'wpaal_view_log_detail', 'wpaal_detail_nonce' ) ); ?>" class="wpaal-action-icon" title="<?php esc_attr_e( 'View', 'admin-activity-logger-lite' ); ?>">
											<span class="dashicons dashicons-visibility"></span>
										</a>
										<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wpaal_delete_log', $log['id'] ), 'wpaal_delete_log_' . $log['id'] ) ); ?>" class="submitdelete wpaal-delete-log-btn wpaal-action-icon wpaal-action-icon-delete" title="<?php esc_attr_e( 'Delete', 'admin-activity-logger-lite' ); ?>">
											<span class="dashicons dashicons-trash"></span>
										</a>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				</form>

				<?php if ( $total_pages > 1 ) : ?>
					<div class="tablenav bottom">
						<div class="tablenav-pages">
							<?php echo wp_kses_post( paginate_links( array(
								'base'    => add_query_arg( 'paged', '%#%' ),
								'format'  => '',
								'current' => $paged,
								'total'   => $total_pages,
							) ) ); ?>
						</div>
					</div>
				<?php endif; ?>

			<?php endif; ?>
			<?php endif; ?>

			<!-- Elegant Deletion Confirmation Modal -->
			<div id="wpaal-confirm-modal" class="wpaal-confirm-modal">
				<div class="wpaal-confirm-modal-backdrop"></div>
				<div class="wpaal-confirm-modal-container">
					<div class="wpaal-confirm-modal-icon">⚠️</div>
					<div class="wpaal-confirm-modal-header">
						<h3 id="wpaal-confirm-title"><?php esc_html_e( 'Confirm Deletion', 'admin-activity-logger-lite' ); ?></h3>
					</div>
					<div class="wpaal-confirm-modal-body">
						<p id="wpaal-confirm-message"><?php esc_html_e( 'Are you sure you want to delete this log entry? This action cannot be undone.', 'admin-activity-logger-lite' ); ?></p>
					</div>
					<div class="wpaal-confirm-modal-footer">
						<button type="button" id="wpaal-confirm-cancel" class="wpaal-confirm-btn-cancel"><?php esc_html_e( 'Cancel', 'admin-activity-logger-lite' ); ?></button>
						<button type="button" id="wpaal-confirm-ok" class="wpaal-confirm-btn-delete"><?php esc_html_e( 'Delete', 'admin-activity-logger-lite' ); ?></button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	private function render_detail( $log ) {
		$sensitive = array( 'password', 'secret', 'token', 'api_key', 'auth' );
		$is_sensitive = false;
		foreach ( $sensitive as $s ) {
			if ( false !== stripos( $log['object_title'] ?? '', $s ) ) {
				$is_sensitive = true;
				break;
			}
		}
		?>
		<div class="wpaal-detail-card">
			<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
				<h2 style="margin: 0;"><?php
					/* translators: %d: Log ID. */
					printf( esc_html__( 'Log #%d Detail', 'admin-activity-logger-lite' ), (int) $log['id'] );
				?></h2>
				<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wpaal_delete_log', $log['id'] ), 'wpaal_delete_log_' . $log['id'] ) ); ?>" class="button button-link-delete wpaal-delete-log-btn" style="color: #b32d2e; text-decoration: none; border: 1px solid #b32d2e; padding: 4px 8px; border-radius: 3px;"><?php esc_html_e( 'Delete this Log Entry', 'admin-activity-logger-lite' ); ?></a>
			</div>
			<table class="form-table wpaal-detail-table" role="presentation">
				<tr><th><?php esc_html_e( 'Date/Time', 'admin-activity-logger-lite' ); ?></th><td><?php echo esc_html( $log['created_at'] ); ?></td></tr>
				<tr><th><?php esc_html_e( 'User', 'admin-activity-logger-lite' ); ?></th><td><?php echo esc_html( $log['user_login'] ?? '—' ); ?> (<?php echo esc_html( $log['user_email'] ?? '' ); ?>)</td></tr>
				<tr><th><?php esc_html_e( 'Event Type', 'admin-activity-logger-lite' ); ?></th><td><span class="<?php echo esc_attr( LogFormatter::event_type_badge_class( $log['event_type'] ) ); ?>"><?php echo esc_html( LogFormatter::event_type_label( $log['event_type'] ) ); ?></span></td></tr>
				<tr><th><?php esc_html_e( 'Object Type', 'admin-activity-logger-lite' ); ?></th><td><?php echo esc_html( $log['object_type'] ?? '—' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Object ID', 'admin-activity-logger-lite' ); ?></th><td><?php echo esc_html( $log['object_id'] ?? '—' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Object Title', 'admin-activity-logger-lite' ); ?></th><td><?php echo esc_html( $log['object_title'] ?? '—' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Action', 'admin-activity-logger-lite' ); ?></th><td><?php echo esc_html( $log['action'] ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Message', 'admin-activity-logger-lite' ); ?></th><td><?php echo esc_html( $log['message'] ); ?></td></tr>
				<?php
				// For creation events, show a creation details panel instead of before/after
				if ( false !== strpos( $log['event_type'] ?? '', '_created' ) ) {
					$this->render_creation_detail( $log['new_value'] ?? '' );
				} else {
					$this->render_before_after( $log['old_value'] ?? '', $log['new_value'] ?? '', $is_sensitive, $log['event_type'] ?? '' );
				}
				?>
				<tr><th><?php esc_html_e( 'IP Address', 'admin-activity-logger-lite' ); ?></th><td><?php echo esc_html( $log['ip_address'] ?? '—' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'User Agent', 'admin-activity-logger-lite' ); ?></th><td><?php echo esc_html( $log['user_agent'] ?? '—' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Request URL', 'admin-activity-logger-lite' ); ?></th><td><?php echo esc_html( $log['request_url'] ?? '—' ); ?></td></tr>
			</table>
		</div>
		<?php
	}

	private function render_creation_detail( $new_val ) {
		if ( empty( $new_val ) ) {
			return;
		}
		$data = json_decode( $new_val, true );
		if ( ! is_array( $data ) || empty( $data ) ) {
			return;
		}
		$label_map = array(
			'status'        => __( 'Status', 'admin-activity-logger-lite' ),
			'content'       => __( 'Content (excerpt)', 'admin-activity-logger-lite' ),
			'featured_image'=> __( 'Featured Image', 'admin-activity-logger-lite' ),
			'categories'    => __( 'Categories', 'admin-activity-logger-lite' ),
			'tags'          => __( 'Tags', 'admin-activity-logger-lite' ),
		);
		?>
		<tr>
			<th><?php esc_html_e( 'Creation Details', 'admin-activity-logger-lite' ); ?></th>
			<td>
				<table style="width:100%;border-collapse:collapse;border:1px solid #ccd0d4;">
					<thead>
						<tr style="background:#f6f7f7;border-bottom:1px solid #ccd0d4;">
							<th style="padding:8px;text-align:left;border-right:1px solid #ccd0d4;width:180px;"><?php esc_html_e( 'Field', 'admin-activity-logger-lite' ); ?></th>
							<th style="padding:8px;text-align:left;"><?php esc_html_e( 'Value', 'admin-activity-logger-lite' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $data as $key => $val ) : ?>
							<tr style="border-bottom:1px solid #ccd0d4;">
								<td style="padding:8px;border-right:1px solid #ccd0d4;font-weight:600;background:#fbfbfc;"><?php echo esc_html( $label_map[ $key ] ?? ucfirst( str_replace( '_', ' ', $key ) ) ); ?></td>
								<td style="padding:8px;"><?php echo nl2br( esc_html( is_array( $val ) ? implode( ', ', $val ) : (string) $val ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</td>
		</tr>
		<?php
	}

	private function render_before_after( $old_val, $new_val, $is_sensitive, $event_type = '' ) {
		if ( $is_sensitive ) {
			?>
			<tr><th><?php esc_html_e( 'Old Value', 'admin-activity-logger-lite' ); ?></th><td><code>[hidden]</code></td></tr>
			<tr><th><?php esc_html_e( 'New Value', 'admin-activity-logger-lite' ); ?></th><td><code>[hidden]</code></td></tr>
			<?php
			return;
		}

		if ( empty( $old_val ) && empty( $new_val ) ) {
			return;
		}

		// Issue 8: Gate settings_updated diffs — show upgrade notice instead of values
		if ( 'settings_updated' === $event_type ) {
			?>
			<tr>
				<th><?php esc_html_e( 'Changes Detail', 'admin-activity-logger-lite' ); ?></th>
				<td><span style="background:#fffbeb;padding:8px 12px;display:inline-block;border-radius:4px;color:#92400e;">&#128274; <?php esc_html_e( 'Settings changed. To view details, upgrade to Pro.', 'admin-activity-logger-lite' ); ?></span></td>
			</tr>
			<?php
			return;
		}

		$old_data = json_decode( $old_val, true );
		$new_data = json_decode( $new_val, true );

		// If at least one side is a JSON object, render comparison table
		if ( is_array( $old_data ) || is_array( $new_data ) ) {
			// Normalise both sides to arrays so we can iterate
			if ( ! is_array( $old_data ) ) {
				$old_data = array( 'value' => $old_val );
			}
			if ( ! is_array( $new_data ) ) {
				$new_data = array( 'value' => $new_val );
			}

			// Build unified field list (all keys from both sides)
			$all_fields = array_unique( array_merge( array_keys( $old_data ), array_keys( $new_data ) ) );
			?>
			<tr>
				<th><?php esc_html_e( 'Changes Detail', 'admin-activity-logger-lite' ); ?></th>
				<td>
					<table class="wpaal-comparison-table" style="width: 100%; border-collapse: collapse; margin-top: 5px; border: 1px solid #ccd0d4;">
						<thead>
							<tr style="background: #f6f7f7; border-bottom: 1px solid #ccd0d4;">
								<th style="padding: 8px; text-align: left; border-right: 1px solid #ccd0d4; font-weight: 600;"><?php esc_html_e( 'Field', 'admin-activity-logger-lite' ); ?></th>
								<th style="padding: 8px; text-align: left; border-right: 1px solid #ccd0d4; font-weight: 600; color: #b32d2e;"><?php esc_html_e( 'Before', 'admin-activity-logger-lite' ); ?></th>
								<th style="padding: 8px; text-align: left; font-weight: 600; color: #10b981;"><?php esc_html_e( 'After', 'admin-activity-logger-lite' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $all_fields as $field ) : ?>
								<?php
								$before_val = isset( $old_data[ $field ] ) ? $old_data[ $field ] : '';
								$after_val  = isset( $new_data[ $field ] ) ? $new_data[ $field ] : '';
								if ( is_array( $before_val ) ) {
									$before_val = wp_json_encode( $before_val );
								}
								if ( is_array( $after_val ) ) {
									$after_val = wp_json_encode( $after_val );
								}
								// Issue 8: Gate content field — show Pro upgrade notice
								if ( 'content' === $field ) {
									?>
									<tr style="border-bottom: 1px solid #ccd0d4;">
										<td style="padding: 8px; border-right: 1px solid #ccd0d4; font-weight: 600; background: #fbfbfc;"><?php esc_html_e( 'Content', 'admin-activity-logger-lite' ); ?></td>
										<td colspan="2" style="padding: 8px; background: #fffbeb;"><span style="color:#92400e;">&#128274; <?php esc_html_e( 'Post content is updated. To view details, upgrade to Pro.', 'admin-activity-logger-lite' ); ?></span></td>
									</tr>
									<?php
									continue;
								}
								?>
								<tr style="border-bottom: 1px solid #ccd0d4;">
									<td style="padding: 8px; border-right: 1px solid #ccd0d4; font-weight: 600; background: #fbfbfc;"><?php echo esc_html( ucfirst( str_replace( '_', ' ', $field ) ) ); ?></td>
									<td style="padding: 8px; border-right: 1px solid #ccd0d4; background: #fdf2f2; color: #b32d2e;"><?php echo nl2br( esc_html( (string) $before_val ) ); ?></td>
									<td style="padding: 8px; background: #f2fdf5; color: #10b981;"><?php echo nl2br( esc_html( (string) $after_val ) ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</td>
			</tr>
			<?php
		} else {
			// Plain string values (e.g. simple status strings, or legacy logs with raw HTML).
			// Strip HTML tags so Gutenberg block markup does not pollute the display.
			$old_display_val = trim( wp_strip_all_tags( $old_val ) );
			$new_display_val = trim( wp_strip_all_tags( $new_val ) );
			// If both stripped values are empty (only HTML, no readable text) show nothing.
			if ( '' === $old_display_val && '' === $new_display_val ) {
				return;
			}
			?>
			<tr>
				<th><?php esc_html_e( 'Changes Detail', 'admin-activity-logger-lite' ); ?></th>
				<td>
					<table class="wpaal-comparison-table" style="width: 100%; border-collapse: collapse; margin-top: 5px; border: 1px solid #ccd0d4;">
						<thead>
							<tr style="background: #f6f7f7; border-bottom: 1px solid #ccd0d4;">
								<th style="padding: 8px; text-align: left; border-right: 1px solid #ccd0d4; font-weight: 600; color: #b32d2e;"><?php esc_html_e( 'Before', 'admin-activity-logger-lite' ); ?></th>
								<th style="padding: 8px; text-align: left; font-weight: 600; color: #10b981;"><?php esc_html_e( 'After', 'admin-activity-logger-lite' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td style="padding: 8px; border-right: 1px solid #ccd0d4; background: #fdf2f2; color: #b32d2e;"><?php echo nl2br( esc_html( $old_display_val ) ); ?></td>
								<td style="padding: 8px; background: #f2fdf5; color: #10b981;"><?php echo nl2br( esc_html( $new_display_val ) ); ?></td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
			<?php
		}
	}
}
