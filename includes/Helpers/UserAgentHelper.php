<?php
namespace Smackcoders\AdminActivityLogger\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UserAgentHelper {

	public static function get_user_agent() {
		if ( empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return '';
		}
		return sanitize_textarea_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );
	}
}
