<?php
use Smackcoders\AdminActivityLogger\Logger\ActivityLogger;
use Smackcoders\AdminActivityLogger\Logger\ActivityTable;
use Smackcoders\AdminActivityLogger\Watchers\SettingsActivityWatcher;

class SettingsActivityWatcherTest extends WP_UnitTestCase {

    private $logger;
    private $watcher;

    public function setUp(): void {
        parent::setUp();
        ActivityTable::create_table();
        update_option( 'wpaal_settings', array(
            'enable_logging' => '1',
            'log_settings'   => '1',
            'store_ip'       => '0',
            'store_user_agent' => '0',
        ) );
        $this->logger  = new ActivityLogger();
        $this->watcher = new SettingsActivityWatcher( $this->logger );
    }

    public function test_logs_tracked_option_change() {
        $this->watcher->on_updated_option( 'blogname', 'Old Site', 'New Site' );

        $repo = new \Smackcoders\AdminActivityLogger\Logger\ActivityRepository();
        $logs = $repo->get_logs( array( 'event_type' => 'settings_updated' ) );
        $found = false;
        foreach ( $logs as $log ) {
            if ( 'blogname' === $log['object_title'] ) {
                $found = true;
                break;
            }
        }
        $this->assertTrue( $found );
    }

    public function test_ignores_untracked_option() {
        $repo_before = new \Smackcoders\AdminActivityLogger\Logger\ActivityRepository();
        $before = $repo_before->count_logs();

        $this->watcher->on_updated_option( 'some_random_plugin_option', 'x', 'y' );

        $repo_after = new \Smackcoders\AdminActivityLogger\Logger\ActivityRepository();
        $this->assertEquals( $before, $repo_after->count_logs() );
    }

    public function test_ignores_unchanged_values() {
        $repo = new \Smackcoders\AdminActivityLogger\Logger\ActivityRepository();
        $before = $repo->count_logs();
        $this->watcher->on_updated_option( 'blogname', 'Same', 'Same' );
        $this->assertEquals( $before, $repo->count_logs() );
    }
}
