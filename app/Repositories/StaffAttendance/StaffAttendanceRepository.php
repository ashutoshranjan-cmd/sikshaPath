<?php

namespace App\Repositories\StaffAttendance;

use App\Models\StaffAttendance;
use App\Repositories\Saas\SaaSRepository;

class StaffAttendanceRepository extends SaaSRepository implements StaffAttendanceInterface {

    public function __construct(StaffAttendance $model) {
        parent::__construct($model);
    }
} 