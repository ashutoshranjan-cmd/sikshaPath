<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\TransportationPayment;
use App\Models\Students;
use App\Models\Staff;

class TransportationExpiryReminder extends Command
{
    protected $signature = 'transport:expiry-reminder';
    protected $description = 'Send reminder 7 days before transportation plan expiry';

    public function handle()
    {
        $today = Carbon::today();
        $targetDate = $today->copy()->addDays(7)->format('Y-m-d');

        $expiringPlans = TransportationPayment::with(['user'])
            ->where('expiry_date', $targetDate)
            ->where('status', 'paid')
            ->get();

        if ($expiringPlans->isEmpty()) {
            return Command::SUCCESS;
        }

        foreach ($expiringPlans as $plan) {

            // -----------------------------------------
            // 🔍 Check if this is Student OR Staff user
            // -----------------------------------------
            $student = Students::with('user')
                ->where('user_id', $plan->user_id)
                ->first();

            $staff = Staff::with('user')
                ->where('user_id', $plan->user_id)
                ->first();

            $expiryFormatted = Carbon::parse($plan->expiry_date)->format('F jS, Y');
            $title = "Transportation Plan Expiring Soon";
            $body = "Your transportation plan expires on {$expiryFormatted}";

            // =====================================================
            // 1️⃣ CASE: STUDENT
            // =====================================================
            if ($student) {
                $childId = $student->id;
                $studentUserId = $student->user_id;
                $guardianId = $student->guardian_id;
                $childName = $student->user->full_name ?? "Student #$childId";

                // ---------------------------
                // Send to Student
                // ---------------------------
                send_notification(
                    [$studentUserId],
                    $title,
                    $body,
                    'Transportation',
                    [
                        'user_id' => $studentUserId
                    ]
                );

                // ---------------------------
                // Send to Guardian (with child_id)
                // ---------------------------
                send_notification(
                    [$guardianId],
                    $title,
                    "Your child {$childName}'s transportation plan expires on {$expiryFormatted}.",
                    'Transportation',
                    [
                        'guardian_id' => $guardianId,
                        'child_id'    => $childId
                    ]
                );
            }

            // =====================================================
            // 2️⃣ CASE: STAFF / TEACHER
            // =====================================================
            if ($staff) {
                send_notification(
                    [$staff->user_id],
                    $title,
                    $body,
                    'Transportation',
                    [
                        'user_id' => $staff->user_id
                    ]
                );
            }            
        }

        return Command::SUCCESS;
    }
}