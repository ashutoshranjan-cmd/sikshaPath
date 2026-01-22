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

    /* Responsive driver/helper cards */
    .driver-helper-card {
        display: flex;
        align-items: center;
        padding: 0.75rem;
        border: 1px solid #e0e0e0;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }

    .driver-helper-card img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        flex-shrink: 0;
    }

    .driver-helper-info {
        margin-left: 1rem;
        flex: 1;
        min-width: 0;
    }

    .driver-helper-info h6 {
        margin-bottom: 0.25rem;
        font-weight: 600;
        word-wrap: break-word;
    }

    .driver-helper-info small {
        display: block;
        color: #6c757d;
        font-size: 0.875rem;
        word-wrap: break-word;
    }

    @media (max-width: 576px) {
        .driver-helper-card {
            flex-direction: column;
            align-items: flex-start;
            text-align: left;
        }

        .driver-helper-info {
            margin-left: 0;
            margin-top: 0.75rem;
            width: 100%;
        }

        .driver-helper-card img {
            align-self: center;
        }
    }

    @media (max-width: 768px) {
        .legend-item {
            margin-left: 0;
            margin-right: 0.75rem;
            margin-bottom: 0.5rem;
        }

        .attendance-legends {
            display: flex;
            flex-wrap: wrap;
        }
    }
</style>

@if ($transportation)
    <div class="col-md-12 grid-margin">
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h6 class="fw-bold text-center text-primary text-dark mb-3">{{ __('plan_details') }}</h6>
                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('plan_status') }}</span>
                                <span class="fw-semibold">{{ $transportation['plan']['plan_status'] ?? '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('vehicle_assignment') }}</span>
                                <span class="fw-semibold">{{ $transportation['plan']['vehicle_assignment'] ?? '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('expiry_date') }}</span>
                                <span class="fw-semibold">{{ $transportation['plan']['expiry_date'] ?? '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('paid_amount') }}</span>
                                <span class="fw-semibold">{{ $transportation['plan']['paid_amount'] ?? '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('payment_mode') }}</span>
                                <span class="fw-semibold">{{ $transportation['plan']['payment_mode'] ?? '-' }}</span>
                            </li>
                        </ul>

                        <h6 class="fw-bold text-center text-primary text-dark mb-3">{{ __('shift_details') }}</h6>
                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('name') }}</span>
                                <span class="fw-semibold">{{ $transportation['shift']['name'] ?? '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('start_time') }}</span>
                                <span class="fw-semibold">{{ $transportation['shift']['start_time'] ?? '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('end_time') }}</span>
                                <span class="fw-semibold">{{ $transportation['shift']['end_time'] ?? '-' }}</span>
                            </li>
                        </ul>

                        <h6 class="fw-bold text-center text-primary text-dark mb-3">{{ __('fee_details') }}</h6>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('duration') }}</span>
                                <span class="fw-semibold">{{ $transportation['fee']['duration'] ?? '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('amount') }}</span>
                                <span class="fw-semibold">{{ $transportation['fee']['amount'] ?? '-' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h6 class="fw-bold text-center text-primary text-dark mb-3">{{ __('route_details') }}</h6>
                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('route_name') }}</span>
                                <span class="fw-semibold">{{ $transportation['route']['route_name'] ?? '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('pickup_point_name') }}</span>
                                <span class="fw-semibold">{{ $transportation['route']['pickup_point_name'] ?? '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('pickup_time') }}</span>
                                <span class="fw-semibold">{{ $transportation['route']['pickup_time'] ?? '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('drop_time') }}</span>
                                <span class="fw-semibold">{{ $transportation['route']['drop_time'] ?? '-' }}</span>
                            </li>
                        </ul>

                        <h6 class="fw-bold text-center text-primary text-dark mb-3">{{ __('vehicle_details') }}</h6>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('name') }}</span>
                                <span class="fw-semibold">{{ $transportation['vehicle']['name'] ?? '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('number') }}</span>
                                <span class="fw-semibold">{{ $transportation['vehicle']['number'] ?? '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('capacity') }}</span>
                                <span class="fw-semibold">{{ $transportation['vehicle']['capacity'] ?? '-' }}</span>
                            </li>
                            <li class="list-group-item">
                                <span class="text-muted d-block mb-2">{{ __('driver') }}</span>
                                @if (!isset($transportation['vehicle']['driver']))
                                    <div class="text-muted">{{ __('No driver assigned') }}</div>
                                @else
                                <div class="driver-helper-card">
                                    <a data-toggle="lightbox" href="{{ $transportation['vehicle']['driver']['image'] }}">
                                        <img src="{{ $transportation['vehicle']['driver']['image'] }}" 
                                             class="rounded-circle border"
                                             alt="Driver"
                                             onerror="onErrorImage(event)" />
                                    </a>
                                    <div class="driver-helper-info">
                                        <h6 class="mb-0">{{ $transportation['vehicle']['driver']['full_name'] ?? '-' }}</h6>
                                        <small>{{ $transportation['vehicle']['driver']['email'] ?? '-' }}</small>
                                        <small>{{ $transportation['vehicle']['driver']['mobile'] ?? '-' }}</small>
                                    </div>
                                </div>
                                @endif
                            </li>
                            <li class="list-group-item">
                                <span class="text-muted d-block mb-2">{{ __('helper') }}</span>
                                @if (!isset($transportation['vehicle']['helper']))
                                    <div class="text-muted">{{ __('No helper assigned') }}</div>
                                @else
                                <div class="driver-helper-card">
                                    <a data-toggle="lightbox" href="{{ $transportation['vehicle']['helper']['image'] }}">
                                        <img src="{{ $transportation['vehicle']['helper']['image'] }}" 
                                             class="rounded-circle border"
                                             alt="Helper"
                                             onerror="onErrorImage(event)" />
                                    </a>
                                    <div class="driver-helper-info">
                                        <h6 class="mb-0">{{ $transportation['vehicle']['helper']['full_name'] ?? '-' }}</h6>
                                        <small>{{ $transportation['vehicle']['helper']['email'] ?? '-' }}</small>
                                        <small>{{ $transportation['vehicle']['helper']['mobile'] ?? '-' }}</small>
                                    </div>
                                </div>
                                @endif
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex align-items-center justify-content-end flex-wrap gap-3 mb-4" id="monthNavigator">
        <div class="d-flex align-items-center border rounded mb-sm-2 mb-md-0 p-1">
            <button id="prevMonth" class="btn btn-sm border-0 bg-transparent prev-month me-2">
                <i class="fa fa-angle-left"></i>
            </button>

            <h5 id="currentMonth" class="mb-0 fw-bold text-dark current-month">-</h5>

            <button id="nextMonth" class="btn btn-sm border-0 bg-transparent next-month ms-2">
                <i class="fa fa-angle-right"></i>
            </button>
        </div>
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
@else
    <div class="col-md-12 text-center py-5">
        <div class="alert alert-info shadow-sm border-0 d-inline-block px-4 py-3">
            {{ __('Did not opt for transportation service') }}
        </div>
    </div>
@endif

<script>
    document.addEventListener('DOMContentLoaded', () => {
        let staffIds = @json([$teacher->id]);
        initAttendanceSection({
            sectionId: 'transportation',
            fetchUrl: '{{ route("route-vehicle.user.attendance.report") }}',
            userIds: staffIds,
            sessionYearId: '{{ $sessionYear->id }}',
            isStaff: true
        });
    });
</script>