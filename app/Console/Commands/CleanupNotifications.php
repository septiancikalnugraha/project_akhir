<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class CleanupNotifications extends Command
{
    protected $signature = 'notifications:cleanup {--days=30 : Number of days to keep notifications}';
    protected $description = 'Clean up old notifications';

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    public function handle()
    {
        $days = $this->option('days');
        $count = $this->notificationService->deleteOldNotifications($days);
        
        $this->info("Successfully deleted {$count} old notifications.");
    }
} 