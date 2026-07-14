<?php
use Smackcoders\AdminActivityLogger\Export\CsvExporter;

class CsvExporterTest extends WP_UnitTestCase {

	public function test_formula_injection_protection() {
		$exporter   = new CsvExporter();
		$reflection = new ReflectionClass( $exporter );
		$method     = $reflection->getMethod( 'sanitize_csv_value' );
		$method->setAccessible( true );

		foreach ( array( '=SUM(A1)', '+bad', '-evil', '@foo' ) as $val ) {
			$result = $method->invoke( $exporter, $val );
			$this->assertStringStartsWith( "'", $result );
		}

		$safe = $method->invoke( $exporter, 'safe value' );
		$this->assertEquals( 'safe value', $safe );
	}

	public function test_quote_escaping() {
		$exporter   = new CsvExporter();
		$reflection = new ReflectionClass( $exporter );
		$method     = $reflection->getMethod( 'sanitize_csv_value' );
		$method->setAccessible( true );

		$result = $method->invoke( $exporter, 'say "hello"' );
		$this->assertStringContainsString( '""', $result );
	}
}
