<?php

namespace App\Services\System;

use App\Models\Platform\SystemActivity;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class DatabaseRestoreService
{
    public function __construct(
        protected DatabaseBackupService $backupService
    ) {}

    /**
     * Run an isolated integrity verification smoke test on a backup file
     * without modifying or corrupting the live database.
     */
    public function runSmokeTest(?string $fileOrPath = null): array
    {
        $backupPath = $this->resolveBackupPath($fileOrPath);

        if (! File::exists($backupPath)) {
            throw new Exception("Backup file not found at: {$backupPath}");
        }

        $size = File::size($backupPath);
        if ($size === 0) {
            throw new Exception("Backup file is empty (0 bytes): {$backupPath}");
        }

        $filename = basename($backupPath);
        $checksum = hash_file('sha256', $backupPath);
        $ext = strtolower(pathinfo($backupPath, PATHINFO_EXTENSION));

        $tablesCount = 0;
        $integrityMessage = 'OK';

        if ($ext === 'sqlite') {
            $tablesCount = $this->verifySqliteIntegrity($backupPath);
        } elseif ($ext === 'sql') {
            $tablesCount = $this->verifySqlDumpIntegrity($backupPath);
        } else {
            throw new Exception("Unsupported backup format: .{$ext}. Expected .sqlite or .sql");
        }

        $result = [
            'status' => 'passed',
            'file' => $filename,
            'path' => $backupPath,
            'size_bytes' => $size,
            'size_formatted' => $this->backupService->formatBytes($size),
            'checksum' => $checksum,
            'tables_verified' => $tablesCount,
            'integrity_status' => $integrityMessage,
            'verified_at' => Carbon::now()->toIso8601String(),
        ];

        // Audit Trail logging
        SystemActivity::log(
            'system.restore_smoke_test',
            "Restore smoke test passed for backup {$filename}. Integrity verification: OK ({$tablesCount} tables checked).",
            null,
            auth()->id(),
            [
                'status' => 'passed',
                'file' => $filename,
                'checksum' => $checksum,
                'tables_verified' => $tablesCount,
            ]
        );

        Log::info("Backup restore smoke test passed for {$filename}", $result);

        return $result;
    }

    /**
     * Verify SQLite database integrity using PRAGMA integrity_check and table introspection.
     */
    protected function verifySqliteIntegrity(string $path): int
    {
        $pdo = new \PDO("sqlite:{$path}");
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        // Run SQLite PRAGMA integrity check
        $stmt = $pdo->query('PRAGMA integrity_check;');
        $check = $stmt->fetchColumn();

        if (strtolower((string) $check) !== 'ok') {
            throw new Exception("SQLite integrity check failed for {$path}: {$check}");
        }

        // Count user tables
        $tablesStmt = $pdo->query("SELECT count(*) FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
        $count = (int) $tablesStmt->fetchColumn();

        return $count;
    }

    /**
     * Verify SQL dump integrity by inspecting schema and table definitions.
     */
    protected function verifySqlDumpIntegrity(string $path): int
    {
        $handle = fopen($path, 'r');
        if (! $handle) {
            throw new Exception("Unable to read SQL dump file: {$path}");
        }

        $createTableCount = 0;
        $hasDataInserts = false;

        while (($line = fgets($handle)) !== false) {
            if (preg_match('/CREATE TABLE/i', $line)) {
                $createTableCount++;
            }
            if (preg_match('/INSERT INTO/i', $line)) {
                $hasDataInserts = true;
            }
        }
        fclose($handle);

        if ($createTableCount === 0) {
            throw new Exception("SQL dump verification failed: No valid CREATE TABLE statements found.");
        }

        return $createTableCount;
    }

    /**
     * Resolve the target backup path from input or find the latest backup.
     */
    public function resolveBackupPath(?string $fileOrPath = null): string
    {
        if (empty($fileOrPath) || $fileOrPath === 'latest') {
            $latest = $this->backupService->getLatestBackup();
            if (! $latest) {
                throw new Exception("No backup archives exist in storage/app/backups.");
            }
            return $latest['path'];
        }

        if (File::exists($fileOrPath)) {
            return $fileOrPath;
        }

        $inDir = $this->backupService->getBackupDirectory() . DIRECTORY_SEPARATOR . $fileOrPath;
        if (File::exists($inDir)) {
            return $inDir;
        }

        throw new Exception("Backup archive '{$fileOrPath}' does not exist.");
    }
}

