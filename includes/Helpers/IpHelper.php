<?php
namespace Smackcoders\AdminActivityLogger\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IpHelper {

	public static function get_ip_address() {
		$ip = '';

		// Only trust REMOTE_ADDR by default — proxy headers can be spoofed.
		if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		$ip = self::normalize_ip( $ip );

		return apply_filters( 'wpaal_detected_ip_address', $ip );
	}

	public static function normalize_ip( $ip ) {
		$ip = trim( (string) $ip );
		if ( ! self::is_valid_ip( $ip ) ) {
			return '';
		}
		return $ip;
	}

	public static function is_valid_ip( $ip ) {
		return (bool) filter_var( $ip, FILTER_VALIDATE_IP );
	}

	public static function anonymize_ip( $ip ) {
		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
			$parts    = explode( '.', $ip );
			$parts[3] = '0';
			return implode( '.', $parts );
		}

		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
			if ( $ip === '::1' || $ip === '::' ) {
				return '::1'; // Localhost
			}
			$packed = inet_pton( $ip );
			if ( $packed ) {
				for ( $i = 8; $i < 16; $i++ ) {
					$packed[ $i ] = "\0";
				}
				return inet_ntop( $packed );
			}
		}

		return '';
	}
}
