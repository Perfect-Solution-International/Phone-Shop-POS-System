<?php

namespace App\Console\Commands;

use App\Services\System\DatabaseBackupService;
use Exception;
use Illuminate\Console\Command;

class BackupDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database {--prune=7 : Number of days of old backups to retain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an automated database backup and prune backups older than specified retention days.';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseBackupService $service): int
    {
        $this->info('Starting automated database backup...');

        try {
            $metadata = $service->createBackup();

            $this->info("Backup created successfully: {$metadata['filename']}");
            $this->table(
                ['Property', 'Value'],
                [
                    ['File Name', $metadata['filename']],
                    ['File Size', $metadata['size_formatted']],
                    ['Checksum (SHA256)', substr($metadata['checksum'], 0, 16) . '...'],
                    ['Driver', $metadata['driver']],
                    ['Location', $metadata['path']],
                ]
            );

            // Pruning
            $pruneDays = (int) $this->option('prune');
            if ($pruneDays > 0) {
                $prunedCount = $service->pruneOldBackups($pruneDays);
                if ($prunedCount > 0) {
                    $this->comment("Pruned {$prunedCount} backup(s) older than {$pruneDays} days.");
                }
            }

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error("Database backup failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

