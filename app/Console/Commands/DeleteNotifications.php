<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\School;
use App\Models\SessionYearsTracking;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Command\Command as CommandAlias;

class DeleteNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete notifications from all school databases';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            // Calculate the date 1 month ago
            $oneMonthAgo = Carbon::now()->subMonth();

            // Get all schools from main database
            $schools = School::on('mysql')->get();

            $totalDeletedCount = 0;
            $totalSessionTrackingDeletedCount = 0;
            $schoolsProcessed = 0;

            foreach ($schools as $school) {
                if (!$school->database_name) {
                    continue; // Skip schools without database
                }

                try {
                    // Switch to school database
                    DB::setDefaultConnection('school');
                    Config::set('database.connections.school.database', $school->database_name);
                    DB::purge('school');
                    DB::connection('school')->reconnect();
                    DB::setDefaultConnection('school');

                    // Get notifications older than 1 month from this school's database
                    $oldNotifications = Notification::where('created_at', '<', $oneMonthAgo)->get();

                    $deletedCount = 0;
                    $sessionTrackingDeletedCount = 0;

                    foreach ($oldNotifications as $notification) {
                        // Delete related session_years_trackings (no cascade delete, so manual deletion)
                        $sessionTrackings = SessionYearsTracking::where('modal_type', 'App\Models\Notification')
                            ->where('modal_id', $notification->id)
                            ->delete();
                        
                        $sessionTrackingDeletedCount += $sessionTrackings;

                        // Delete the notification (this will cascade delete user_notifications and handle image deletion via boot method)
                        $notification->delete();
                        $deletedCount++;
                    }

                    if ($deletedCount > 0) {
                        $totalDeletedCount += $deletedCount;
                        $totalSessionTrackingDeletedCount += $sessionTrackingDeletedCount;
                        $schoolsProcessed++;
                        
                        Log::info("School ID {$school->id} ({$school->database_name}): {$deletedCount} notifications deleted, {$sessionTrackingDeletedCount} session tracking records deleted");
                    }
                } catch (\Throwable $e) {
                    // Log error for this school but continue with others
                    Log::error("Error deleting old notifications for school ID {$school->id} ({$school->database_name}): " . $e->getMessage());
                    $this->warn("Error processing school ID {$school->id}: " . $e->getMessage());
                }
            }

            // Switch back to main database
            DB::purge('school');
            DB::connection('mysql')->reconnect();
            DB::setDefaultConnection('mysql');

            // Log the results
            Log::info("Notification cleanup completed across all schools: {$totalDeletedCount} notifications deleted, {$totalSessionTrackingDeletedCount} session tracking records deleted from {$schoolsProcessed} schools");

            $this->info("Successfully deleted {$totalDeletedCount} notifications older than 1 month from {$schoolsProcessed} schools.");
            $this->info("Deleted {$totalSessionTrackingDeletedCount} related session tracking records.");

            return CommandAlias::SUCCESS;
        } catch (\Throwable $e) {
            // Ensure we're back on main database even if error occurs
            try {
                DB::purge('school');
                DB::connection('mysql')->reconnect();
                DB::setDefaultConnection('mysql');
            } catch (\Throwable $dbError) {
                // Ignore database switch errors during cleanup
            }
            
            Log::error("Error deleting old notifications: " . $e->getMessage());
            $this->error("Error deleting old notifications: " . $e->getMessage());
            return CommandAlias::FAILURE;
        }
    }
}

