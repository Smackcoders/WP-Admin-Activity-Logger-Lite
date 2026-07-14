<?php
use Smackcoders\AdminActivityLogger\Helpers\IpHelper;

class IpHelperTest extends WP_UnitTestCase {

	public function test_valid_ip_detection() {
		$this->assertTrue( IpHelper::is_valid_ip( '192.168.1.1' ) );
		$this->assertTrue( IpHelper::is_valid_ip( '2001:db8::1' ) );
		$this->assertFalse( IpHelper::is_valid_ip( 'not-an-ip' ) );
		$this->assertFalse( IpHelper::is_valid_ip( '' ) );
	}

	public function test_ipv4_anonymization() {
		$anon = IpHelper::anonymize_ip( '192.168.1.100' );
		$this->assertEquals( '192.168.1.0', $anon );
	}

	public function test_ipv6_anonymization() {
		$anon = IpHelper::anonymize_ip( '2001:db8:85a3:0:0:8a2e:370:7334' );
		$this->assertStringEndsWith( '::', $anon );
	}

	public function test_normalize_strips_invalid() {
		$this->assertEquals( '', IpHelper::normalize_ip( 'bad' ) );
		$this->assertEquals( '10.0.0.1', IpHelper::normalize_ip( '10.0.0.1' ) );
	}
}
