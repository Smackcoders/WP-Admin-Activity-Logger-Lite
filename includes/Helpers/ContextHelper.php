<?php
namespace Smackcoders\AdminActivityLogger\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ContextHelper {

	public static function get_request_url() {
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return '';
		}
		$uri = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		$host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		$scheme = is_ssl() ? 'https' : 'http';
		return $host ? $scheme . '://' . $host . $uri : $uri;
	}

	public static function get_current_admin_screen_id() {
		if ( ! is_admin() ) {
			return '';
		}
		$screen = get_current_screen();
		return $screen ? $screen->id : '';
	}

	public static function get_referrer() {
		if ( empty( $_SERVER['HTTP_REFERER'] ) ) {
			return '';
		}
		return esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) );
	}

	public static function is_admin_request() {
		return is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX );
	}
}
