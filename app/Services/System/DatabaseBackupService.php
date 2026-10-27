<?php

namespace App\Services\System;

use App\Models\Platform\SystemActivity;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class DatabaseBackupService
{
    /**
     * Get the directory where backups are stored.
     */
    public function getBackupDirectory(): string
    {
        $dir = storage_path('app/backups');
        if (! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true, true);
        }

        return $dir;
    }

    /**
     * Create an automated backup of the active database.
     */
    public function createBackup(?string $connection = null): array
    {
        $connection = $connection ?? config('database.default');
        $driver = config("database.connections.{$connection}.driver");
        $databaseName = config("database.connections.{$connection}.database");

        $dir = $this->getBackupDirectory();
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $suffix = substr(md5(uniqid((string) mt_rand(), true)), 0, 6);
        $safeDbName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', basename($databaseName ?? 'db'));

        if ($driver === 'sqlite') {
            $filename = "backup-{$connection}-{$safeDbName}-{$timestamp}-{$suffix}.sqlite";
            $backupPath = $dir . DIRECTORY_SEPARATOR . $filename;
            if (File::exists($backupPath)) {
                File::delete($backupPath);
            }
            $this->backupSqlite($connection, $backupPath);
        } else {
            $filename = "backup-{$connection}-{$safeDbName}-{$timestamp}-{$suffix}.sql";
            $backupPath = $dir . DIRECTORY_SEPARATOR . $filename;
            if (File::exists($backupPath)) {
                File::delete($backupPath);
            }
            $this->backupMysql($connection, $backupPath);
        }

        if (! File::exists($backupPath) || File::size($backupPath) === 0) {
            throw new Exception("Backup creation failed. Target file '{$filename}' is missing or empty.");
        }

        $size = File::size($backupPath);
        $checksum = hash_file('sha256', $backupPath);

        $metadata = [
            'filename' => $filename,
            'path' => $backupPath,
            'relative_path' => 'backups/' . $filename,
            'size_bytes' => $size,
            'size_formatted' => $this->formatBytes($size),
            'checksum' => $checksum,
            'driver' => $driver,
            'connection' => $connection,
            'created_at' => Carbon::now()->toIso8601String(),
        ];

        $this->recordManifest($metadata);

        // Audit Trail logging
        SystemActivity::log(
            'system.backup_created',
            "Database backup created: {$filename} ({$metadata['size_formatted']})",
            null,
            auth()->id(),
            [
                'filename' => $filename,
                'checksum' => $checksum,
                'size_bytes' => $size,
                'driver' => $driver,
            ]
        );

        Log::info("Database backup created successfully: {$filename}", $metadata);

        return $metadata;
    }

    /**
     * Backup SQLite database using VACUUM INTO, with disk copy fallback.
     */
    protected function backupSqlite(string $connection, string $backupPath): void
    {
        $normalizedPath = str_replace('\\', '/', $backupPath);

        try {
            // SQLite 3.27+ native online backup snapshot
            DB::connection($connection)->statement("VACUUM INTO '{$normalizedPath}'");
            return;
        } catch (Exception $e) {
            Log::warning("SQLite VACUUM INTO failed, attempting direct file copy or table dump fallback: " . $e->getMessage());
        }

        // Fallback 1: Direct file copy if database is a disk file
        $dbPath = config("database.connections.{$connection}.database");
        if ($dbPath && File::exists($dbPath) && $dbPath !== ':memory:') {
            File::copy($dbPath, $backupPath);
            return;
        }

        // Fallback 2: Pure SQL table dump into sqlite file
        $this->dumpSqliteTablesViaPdo($connection, $backupPath);
    }

    /**
     * Dump SQLite schema and data into a target SQLite database file.
     */
    protected function dumpSqliteTablesViaPdo(string $connection, string $backupPath): void
    {
        $targetPdo = new \PDO("sqlite:{$backupPath}");
        $targetPdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $sourcePdo = DB::connection($connection)->getPdo();

        $tablesStmt = $sourcePdo->query("SELECT name, sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
        $tables = $tablesStmt->fetchAll(\PDO::FETCH_ASSOC);

        $targetPdo->exec("BEGIN TRANSACTION;");

        foreach ($tables as $t) {
            $tableName = $t['name'];
            $sql = $t['sql'];
            if (! $sql) {
                continue;
            }

            $targetPdo->exec($sql);

            $rowsStmt = $sourcePdo->query("SELECT * FROM \"{$tableName}\"");
            $rows = $rowsStmt->fetchAll(\PDO::FETCH_ASSOC);

            if (! empty($rows)) {
                $columns = array_keys($rows[0]);
                $colList = implode(', ', array_map(fn($c) => "\"{$c}\"", $columns));
                $placeholders = implode(', ', array_fill(0, count($columns), '?'));
                $insertSql = "INSERT INTO \"{$tableName}\" ({$colList}) VALUES ({$placeholders})";
                $insertStmt = $targetPdo->prepare($insertSql);

                foreach ($rows as $row) {
                    $insertStmt->execute(array_values($row));
                }
            }
        }

        $targetPdo->exec("COMMIT;");
    }

    /**
     * Backup MySQL / MariaDB using mysqldump or PDO table exporter.
     */
    protected function backupMysql(string $connection, string $backupPath): void
    {
        $config = config("database.connections.{$connection}");
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? '3306';
        $database = $config['database'];
        $username = $config['username'] ?? 'root';
        $password = $config['password'] ?? '';

        // Check if mysqldump CLI is available
        if ($this->isMysqldumpAvailable()) {
            $cmd = sprintf(
                'mysqldump --host=%s --port=%s --user=%s %s --single-transaction --quick --skip-lock-tables %s > "%s"',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                $password ? '--password=' . escapeshellarg($password) : '',
                escapeshellarg($database),
                $backupPath
            );

            @exec($cmd, $output, $returnCode);

            if ($returnCode === 0 && File::exists($backupPath) && File::size($backupPath) > 0) {
                return;
            }
        }

        // Pure PHP PDO MySQL Exporter Fallback
        $this->dumpMysqlTablesViaPdo($connection, $backupPath);
    }

    /**
     * Check if mysqldump is installed and accessible.
     */
    protected function isMysqldumpAvailable(): bool
    {
        $testCmd = PHP_OS_FAMILY === 'Windows' ? 'where mysqldump 2>NUL' : 'which mysqldump 2>/dev/null';
        @exec($testCmd, $output, $code);
        return $code === 0;
    }

    /**
     * Export MySQL tables into a valid SQL dump file using PDO.
     */
    protected function dumpMysqlTablesViaPdo(string $connection, string $backupPath): void
    {
        $pdo = DB::connection($connection)->getPdo();
        $handle = fopen($backupPath, 'w');

        fwrite($handle, "-- Automated POS System Database Dump\n");
        fwrite($handle, "-- Generated: " . Carbon::now()->toIso8601String() . "\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($handle, "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n\n");

        $tablesStmt = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
        $tables = $tablesStmt->fetchAll(\PDO::FETCH_NUM);

        foreach ($tables as $tableRow) {
            $tableName = $tableRow[0];

            fwrite($handle, "-- --------------------------------------------------------\n");
            fwrite($handle, "-- Table structure for `{$tableName}`\n");
            fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n");

            $createStmt = $pdo->query("SHOW CREATE TABLE `{$tableName}`");
            $createRow = $createStmt->fetch(\PDO::FETCH_ASSOC);
            $createSql = $createRow['Create Table'] ?? $createRow['create table'] ?? '';

            fwrite($handle, $createSql . ";\n\n");

            // Dump data
            $rowsStmt = $pdo->query("SELECT * FROM `{$tableName}`");
            while ($row = $rowsStmt->fetch(\PDO::FETCH_ASSOC)) {
                $cols = array_map(fn($c) => "`{$c}`", array_keys($row));
                $vals = array_map(function ($val) use ($pdo) {
                    if ($val === null) {
                        return 'NULL';
                    }
                    return $pdo->quote((string) $val);
                }, array_values($row));

                $insertSql = sprintf(
                    "INSERT INTO `%s` (%s) VALUES (%s);\n",
                    $tableName,
                    implode(', ', $cols),
                    implode(', ', $vals)
                );
                fwrite($handle, $insertSql);
            }

            fwrite($handle, "\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);
    }

    /**
     * Prune backups older than the specified retention days.
     */
    public function pruneOldBackups(int $daysToKeep = 7): int
    {
        $dir = $this->getBackupDirectory();
        $cutoff = Carbon::now()->subDays($daysToKeep);
        $deletedCount = 0;

        $files = File::files($dir);
        foreach ($files as $file) {
            $ext = strtolower($file->getExtension());
            if (! in_array($ext, ['sqlite', 'sql'])) {
                continue;
            }

            $modified = Carbon::createFromTimestamp($file->getMTime());
            if ($modified->lessThan($cutoff)) {
                File::delete($file->getPathname());
                $deletedCount++;
            }
        }

        if ($deletedCount > 0) {
            $this->cleanManifest();

            SystemActivity::log(
                'system.backup_pruned',
                "Pruned {$deletedCount} database backup(s) older than {$daysToKeep} days.",
                null,
                auth()->id(),
                ['deleted_count' => $deletedCount, 'retention_days' => $daysToKeep]
            );

            Log::info("Pruned {$deletedCount} old backups older than {$daysToKeep} days.");
        }

        return $deletedCount;
    }

    /**
     * Retrieve the most recent backup file.
     */
    public function getLatestBackup(): ?array
    {
        $dir = $this->getBackupDirectory();
        $files = File::files($dir);

        $backupFiles = array_filter($files, function ($file) {
            return in_array(strtolower($file->getExtension()), ['sqlite', 'sql']);
        });

        if (empty($backupFiles)) {
            return null;
        }

        // Sort by mtime descending
        usort($backupFiles, fn($a, $b) => $b->getMTime() <=> $a->getMTime());
        $latest = reset($backupFiles);

        $size = $latest->getSize();
        $filename = $latest->getFilename();

        return [
            'filename' => $filename,
            'path' => $latest->getPathname(),
            'size_bytes' => $size,
            'size_formatted' => $this->formatBytes($size),
            'checksum' => hash_file('sha256', $latest->getPathname()),
            'modified_at' => Carbon::createFromTimestamp($latest->getMTime())->toIso8601String(),
        ];
    }

    /**
     * Record backup entry in manifest JSON.
     */
    protected function recordManifest(array $metadata): void
    {
        $manifestPath = $this->getBackupDirectory() . DIRECTORY_SEPARATOR . 'manifest.json';
        $manifest = [];

        if (File::exists($manifestPath)) {
            $content = File::get($manifestPath);
            $manifest = json_decode($content, true) ?: [];
        }

        array_unshift($manifest, $metadata);
        $manifest = array_slice($manifest, 0, 50); // Keep last 50 metadata entries

        File::put($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT));
    }

    /**
     * Synchronize manifest with files actually remaining on disk.
     */
    protected function cleanManifest(): void
    {
        $dir = $this->getBackupDirectory();
        $manifestPath = $dir . DIRECTORY_SEPARATOR . 'manifest.json';
        if (! File::exists($manifestPath)) {
            return;
        }

        $manifest = json_decode(File::get($manifestPath), true) ?: [];
        $cleaned = array_filter($manifest, function ($entry) use ($dir) {
            $path = $entry['path'] ?? ($dir . DIRECTORY_SEPARATOR . ($entry['filename'] ?? ''));
            return File::exists($path);
        });

        File::put($manifestPath, json_encode(array_values($cleaned), JSON_PRETTY_PRINT));
    }

    /**
     * Format byte sizes into human-readable strings.
     */
    public function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
