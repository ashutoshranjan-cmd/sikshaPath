<!-- Staff Attendance Report Tab -->
<div class="container-fluid">
    <div class="attendance-summary card">
        <div class="card-body px-0 py-0">
            <h5 class="card-title">{{ __('Attendance Summary') }}</h5>
        </div>
    </div>

    <div class="d-flex align-items-center justify-content-end flex-wrap gap-3 mb-4" id="monthNavigator">
        <!-- Month navigation -->
        <div class="d-flex monthlyAttendanceDateSection align-items-center border rounded mb-sm-2 mb-md-0 p-1">
            <button id="prevMonth" class="btn btn-sm border-0 bg-transparent prev-month me-2">
                <i class="fa fa-angle-left"></i>
            </button>

            <h5 id="currentMonth" class="mb-0 fw-bold text-dark current-month">-</h5>

            <button id="nextMonth" class="btn btn-sm border-0 bg-transparent next-month ms-2">
                <i class="fa fa-angle-right"></i>
            </button>
        </div>

        <!-- Daily / Monthly buttons -->
        <!-- <div class="border rounded p-1">
            <div class="btn-group" role="group" aria-label="View Mode">
                <button type="button" class="btn btn-outline-successed px-3 py-2 active" id="dailyView">Daily</button>
                <button type="button" class="btn btn-outline-successed px-3 py-2" id="monthlyView">Monthly</button>
            </div>
        </div> -->
    </div>

    <div class="card">
        <div class="d-flex mr-2 align-items-center border p-4 bg-gradient-light mb-4">
            <span class="mb-0 small mr-2">{{ __('Attendance Summary') }} : </span>
            <div class="attendance-legends">
                <span class="legend-item mx-2 mb-sm-2 mb-md-0"><i class="fa fa-circle text-success"></i>
                    {{ __('Present') }}
                </span>
                <span class="legend-item mx-2 mb-sm-2 mb-md-0"><i class="fa fa-circle text-danger"></i>
                    {{ __('Absent') }}</span>
                <span class="legend-item mx-2 mb-sm-2 mb-md-0"><i class="fa fa-circle text-info"></i>
                    {{ __('Holiday') }}</span>
                <span class="legend-item mx-2 mb-sm-2 mb-md-0"><i class="fa fa-circle text-secondary"></i>
                    {{ __('Not Marked') }}</span>
            </div>
        </div>
        <div class="d-flex align-items-center justify-content-start flex-wrap gap-3 mb-4" id="monthNavigator">
            <!-- Month navigation -->
            <!-- <div class="d-flex align-items-center border rounded mr-2 mb-sm-2 mb-md-0 p-1">
                <button id="prevMonth" class="btn btn-sm border-0 bg-transparent me-2">
                    <i class="fa fa-angle-left"></i>
                </button>

                <h5 id="currentMonth" class="mb-0 fw-bold text-dark">March 2028</h5>

                <button id="nextMonth" class="btn btn-sm border-0 bg-transparent ms-2">
                    <i class="fa fa-angle-right"></i>
                </button>
            </div> -->

            <!-- Pickup / Drop buttons -->
            <div class="border rounded p-1">
                <div class="btn-group" role="group" aria-label="View Mode">
                    <button type="button" class="btn btn-outline-successed pickup-btn px-3 py-2 active"
                        id="pickupView">{{ __('pickup') }}</button>
                    <button type="button" class="btn btn-outline-successed drop-btn px-3 py-2"
                        id="dropView">{{ __('drop') }}</button>
                </div>
            </div>
        </div>
        <div class="px-0">
            <div class="table-responsive">
                <table class="table table-bordered border table-hover attendance-table table-sm">
                    <thead>
                        <tr>
                            <th>{{ __('staff_details') }}</th>
                            @for($i = 1; $i <= 31; $i++)
                                <th>{{ $i }}</th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody id="attendance_data" class="attendance-data">
                        <!-- Attendance data will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        initAttendanceSection({
            sectionId: 'staffAttendanceSection',
            fetchUrl: '{{ route("route-vehicle.user.attendance.report") }}',
            userIds: JSON.parse('{{ $staffs ?? "[]" }}'),
            sessionYearId: '{{ $session_year_id }}',
            isStaff: true
        });
    });
</script>

<style>
    .btn-outline-successed {
        color: #3e4b5b;
        border-color: transparent !important;
    }


    .btn-outline-successed.active {
        background-color: #37C978 !important;
        color: #fff !important;
        border-color: transparent !important;
    }

    .attendance-legends {
        font-size: 0.8rem;
    }

    .legend-item {
        display: inline-flex;
        align-items: center;
        margin-left: 12px;
    }

    .legend-item i {
        font-size: 10px;
        margin-right: 5px;
    }

    .attendance-table th {
        text-align: center;
        font-size: 0.9rem;
        padding: 8px 4px;
    }

    .attendance-table td {
        padding: 4px;
        vertical-align: middle;
    }
</style>