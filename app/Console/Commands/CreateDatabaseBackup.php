<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BackupService;

class CreateDatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new database backup';

    /**
     * The backup service instance.
     *
     * @var \App\Services\BackupService
     */
    protected $backupService;

    /**
     * Create a new command instance.
     *
     * @param  \App\Services\BackupService  $backupService
     * @return void
     */
    public function __construct(BackupService $backupService)
    {
        parent::__construct();
        $this->backupService = $backupService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $backup = $this->backupService->createBackup();
            $this->info('Backup created successfully: ' . $backup['filename']);
            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to create backup: ' . $e->getMessage());
            return 1;
        }
    }
} 