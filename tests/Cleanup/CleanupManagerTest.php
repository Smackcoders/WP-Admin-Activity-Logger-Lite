<?php
use Smackcoders\AdminActivityLogger\Cleanup\CleanupManager;
use Smackcoders\AdminActivityLogger\Logger\ActivityRepository;
use Smackcoders\AdminActivityLogger\Logger\ActivityTable;

class CleanupManagerTest extends WP_UnitTestCase {

    public function setUp(): void {
        parent::setUp();
        ActivityTable::create_table();
        update_option( 'wpaal_settings', array( 'enable_logging' => '1', 'auto_cleanup' => '1', 'retention_days' => '90' ) );
    }

    public function test_clear_all() {
        $repo = new ActivityRepository();
        $repo->insert( array( 'event_type' => 'post_created', 'action' => 'create', 'message' => 'x' ) );

        $manager = new CleanupManager();
        $manager->clear_all();

        $this->assertEquals( 0, $repo->count_logs() );
    }

    public function test_auto_cleanup_respects_setting() {
        update_option( 'wpaal_settings', array( 'auto_cleanup' => '0' ) );
        $manager = new CleanupManager();
        $count   = $manager->auto_cleanup();
        $this->assertEquals( 0, $count );
    }
}
