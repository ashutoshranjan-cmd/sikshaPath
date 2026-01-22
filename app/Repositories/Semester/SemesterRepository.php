<?php

namespace App\Repositories\Semester;

use App\Models\Semester;
use App\Repositories\Saas\SaaSRepository;
use App\Services\CachingService;
use Carbon\Carbon;

class SemesterRepository extends SaaSRepository implements SemesterInterface {

    public function __construct(Semester $model) {
        parent::__construct($model);
    }

    public function default($schoolId = null) {
        // Get the default session year
        $defaultSessionYear = app(CachingService::class)->getDefaultSessionYear($schoolId);
        $schoolSettings = app(CachingService::class)->getSchoolSettings();
        
        // Get current date
        $currentDate = Carbon::now()->format('Y-m-d');
        
        // If no default session year exists, return null
        if (!$defaultSessionYear) {
            return null;
        }
        
        // Check if current date falls within the session year's date range
        $sessionStartDate = Carbon::createFromFormat($schoolSettings['date_format'] ?? 'Y-m-d', $defaultSessionYear->start_date);
        $sessionEndDate = Carbon::createFromFormat($schoolSettings['date_format'] ?? 'Y-m-d', $defaultSessionYear->end_date);
        $currentCarbon = Carbon::now();
        
        // If current date is not within session year range, return null
        if (!$currentCarbon->between($sessionStartDate, $sessionEndDate)) {
            return null;
        }
        
        // Build query with school_id filter if provided
        $query = $this->defaultModel()->withTrashed();
        
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        $currentSemester = $query->whereDate('start_date', '<=', $currentDate)->whereDate('end_date', '>=', $currentDate)
        ->whereDate('start_date', '>=', $defaultSessionYear->start_date)->whereDate('end_date', '<=', $defaultSessionYear->end_date)->orderBy('start_date', 'desc')->first();
            
        return $currentSemester;
    }
}