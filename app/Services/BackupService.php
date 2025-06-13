<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class BackupService
{
    protected $backupPath;
    protected $backupDisk;

    public function __construct()
    {
        $this->backupPath = 'backups';
        $this->backupDisk = 'local';
    }

    public function createBackup()
    {
        $filename = 'backup-' . Carbon::now()->format('Y-m-d-H-i-s') . '.sql';
        $path = storage_path('app/' . $this->backupPath . '/' . $filename);

        // Create backup directory if it doesn't exist
        if (!File::exists(storage_path('app/' . $this->backupPath))) {
            File::makeDirectory(storage_path('app/' . $this->backupPath), 0755, true);
        }

        // Get database configuration
        $host = Config::get('database.connections.mysql.host');
        $database = Config::get('database.connections.mysql.database');
        $username = Config::get('database.connections.mysql.username');
        $password = Config::get('database.connections.mysql.password');

        // Create backup using mysqldump
        $command = sprintf(
            'mysqldump --host=%s --user=%s --password=%s %s > %s',
            $host,
            $username,
            $password,
            $database,
            $path
        );

        exec($command);

        // Compress the backup file
        $zip = new \ZipArchive();
        $zipPath = $path . '.zip';
        
        if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
            $zip->addFile($path, $filename);
            $zip->close();
            
            // Delete the original SQL file
            File::delete($path);
            
            return [
                'filename' => basename($zipPath),
                'path' => $zipPath,
                'size' => File::size($zipPath),
                'created_at' => Carbon::now()
            ];
        }

        return false;
    }

    public function restoreBackup($filename)
    {
        $path = storage_path('app/' . $this->backupPath . '/' . $filename);
        
        if (!File::exists($path)) {
            return false;
        }

        // Extract the zip file
        $zip = new \ZipArchive();
        if ($zip->open($path) === TRUE) {
            $zip->extractTo(storage_path('app/' . $this->backupPath));
            $zip->close();
            
            // Get the SQL file name
            $sqlFile = storage_path('app/' . $this->backupPath . '/' . str_replace('.zip', '', $filename));
            
            // Get database configuration
            $host = Config::get('database.connections.mysql.host');
            $database = Config::get('database.connections.mysql.database');
            $username = Config::get('database.connections.mysql.username');
            $password = Config::get('database.connections.mysql.password');

            // Restore the database
            $command = sprintf(
                'mysql --host=%s --user=%s --password=%s %s < %s',
                $host,
                $username,
                $password,
                $database,
                $sqlFile
            );

            exec($command);

            // Delete the extracted SQL file
            File::delete($sqlFile);

            return true;
        }

        return false;
    }

    public function deleteBackup($filename)
    {
        $path = storage_path('app/' . $this->backupPath . '/' . $filename);
        
        if (File::exists($path)) {
            return File::delete($path);
        }

        return false;
    }

    public function getBackups()
    {
        $files = File::files(storage_path('app/' . $this->backupPath));
        $backups = [];

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                $backups[] = [
                    'filename' => basename($file),
                    'size' => File::size($file),
                    'created_at' => Carbon::createFromTimestamp(File::lastModified($file))
                ];
            }
        }

        // Sort by created_at desc
        usort($backups, function($a, $b) {
            return $b['created_at']->timestamp - $a['created_at']->timestamp;
        });

        return $backups;
    }

    public function downloadBackup($filename)
    {
        $path = storage_path('app/' . $this->backupPath . '/' . $filename);
        
        if (File::exists($path)) {
            return response()->download($path);
        }

        return false;
    }

    public function cleanupOldBackups($days = 7)
    {
        $files = File::files(storage_path('app/' . $this->backupPath));
        $count = 0;

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                $lastModified = Carbon::createFromTimestamp(File::lastModified($file));
                
                if ($lastModified->diffInDays(Carbon::now()) > $days) {
                    File::delete($file);
                    $count++;
                }
            }
        }

        return $count;
    }
} 