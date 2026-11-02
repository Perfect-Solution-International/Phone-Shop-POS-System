<?php

namespace App\Console\Commands;

use App\Services\System\DatabaseRestoreService;
use Exception;
use Illuminate\Console\Command;

class RestoreDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:restore 
                            {file? : Backup file path or "latest"} 
                            {--smoke-test : Perform integrity verification on an isolated temporary copy without modifying live database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore database from an archive or execute an isolated smoke test integrity check.';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseRestoreService $service): int
    {
        $file = $this->argument('file');
        $isSmokeTest = (bool) $this->option('smoke-test');

        if ($isSmokeTest) {
            $this->info('Running isolated restore smoke test...');

            try {
                $result = $service->runSmokeTest($file);

                $this->info("Restore smoke test PASSED for: {$result['file']}");
                $this->table(
                    ['Verification Metric', 'Status / Value'],
                    [
                        ['Target Backup Archive', $result['file']],
                        ['File Size', $result['size_formatted']],
                        ['SHA256 Checksum', substr($result['checksum'], 0, 16) . '...'],
                        ['Integrity Check', 'OK (PRAGMA integrity passed)'],
                        ['Verified Tables Count', $result['tables_verified']],
                        ['Verified At', $result['verified_at']],
                    ]
                );

                $this->info('Database archive verified healthy and ready for disaster recovery.');
                return Command::SUCCESS;
            } catch (Exception $e) {
                $this->error("Restore smoke test FAILED: " . $e->getMessage());
                return Command::FAILURE;
            }
        }

        // Live restore requires explicit confirmation in production
        $this->warn('Live restoration of database will overwrite existing tables.');
        if (! $this->confirm('Are you absolutely sure you want to proceed with live database restore?')) {
            $this->info('Database restoration cancelled.');
            return Command::SUCCESS;
        }

        // In non-smoke-test mode, verify integrity first
        try {
            $smokeTest = $service->runSmokeTest($file);
            $this->info("Archive pre-restore integrity verified ({$smokeTest['file']}).");
            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error("Restore aborted: Archive failed integrity check: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

