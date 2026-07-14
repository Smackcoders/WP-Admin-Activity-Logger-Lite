<?php
use Smackcoders\AdminActivityLogger\Logger\ActivityTable;
use Smackcoders\AdminActivityLogger\Logger\ActivityRepository;

class ActivityRepositoryTest extends WP_UnitTestCase {

	private $repo;

	public function setUp(): void {
		parent::setUp();
		ActivityTable::create_table();
		$this->repo = new ActivityRepository();
	}

	private function insert( $overrides = array() ) {
		return $this->repo->insert( array_merge( array(
			'event_type'   => 'post_created',
			'object_type'  => 'post',
			'object_title' => 'Test Post',
			'action'       => 'create',
			'message'      => 'Post "Test Post" was created.',
			'ip_address'   => '127.0.0.1',
		), $overrides ) );
	}

	public function test_insert_and_fetch() {
		$id  = $this->insert();
		$this->assertGreaterThan( 0, $id );
		$row = $this->repo->get( $id );
		$this->assertEquals( 'post_created', $row['event_type'] );
		$this->assertEquals( '127.0.0.1', $row['ip_address'] );
	}

	public function test_count_by_event_type() {
		$this->insert( array( 'event_type' => 'post_created' ) );
		$this->insert( array( 'event_type' => 'post_deleted' ) );
		$this->insert( array( 'event_type' => 'post_deleted' ) );

		$this->assertEquals( 1, $this->repo->count_logs( array( 'event_type' => 'post_created' ) ) );
		$this->assertEquals( 2, $this->repo->count_logs( array( 'event_type' => 'post_deleted' ) ) );
	}

	public function test_get_logs_with_search() {
		$this->insert( array( 'object_title' => 'findme post' ) );
		$this->insert( array( 'object_title' => 'other post' ) );
		$results = $this->repo->get_logs( array( 'search' => 'findme' ) );
		$this->assertCount( 1, $results );
		$this->assertEquals( 'findme post', $results[0]['object_title'] );
	}

	public function test_delete_older_than() {
		$this->insert();
		$deleted = $this->repo->delete_older_than( 0 );
		$this->assertGreaterThan( 0, $deleted );
		$this->assertEquals( 0, $this->repo->count_logs() );
	}

	public function test_delete_all() {
		$this->insert();
		$this->insert();
		$this->repo->delete_all();
		$this->assertEquals( 0, $this->repo->count_logs() );
	}

	public function test_anonymize_by_email() {
		$this->insert( array( 'user_email' => 'test@example.com' ) );
		$this->repo->anonymize_by_email( 'test@example.com' );
		$logs = $this->repo->get_logs_by_email( 'test@example.com' );
		foreach ( $logs as $log ) {
			$this->assertEmpty( $log['user_email'] );
		}
	}
}
