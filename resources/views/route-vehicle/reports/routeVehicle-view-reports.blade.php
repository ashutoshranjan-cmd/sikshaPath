@extends('layouts.master')

@section('title')
    {{ __('routeVehicle_report') }}
@endsection

@section('css')
    <style>
        :dir(rtl) .monthlyAttendanceDateSection {
            direction: rtl;
            display: flex;
            flex-direction: row-reverse;
        }

        :dir(rtl) .ms-3 {
            margin-left: 0 !important;
            margin-right: 1rem !important;
        }

        :dir(rtl) .legend-item i {
            font-size: 10px;
            margin-left: 5px;
        }
    </style>
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('routeVehicle_report') }}
            </h3>
        </div>
        <div class="row">
            <!-- Left Profile Card -->
            <div class="col-md-4 grid-margin">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <!-- ROUTE VEHICLE SECTION -->
                        <h6 class="fw-bold text-center text-primary text-dark mb-3">{{ __('route_vehicle') }}</h6>
                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('pickup_trip_start_time') }}</span>
                                <span
                                    class="fw-semibold">{{ Carbon\Carbon::parse($routeVehicles->pickup_start_time)->format($originalTimeFormat) ?? '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('pickup_trip_end_time') }}</span>
                                <span
                                    class="fw-semibold">{{ Carbon\Carbon::parse($routeVehicles->pickup_end_time)->format($originalTimeFormat) ?? '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('drop_trip_start_time') }}</span>
                                <span
                                    class="fw-semibold">{{ Carbon\Carbon::parse($routeVehicles->drop_start_time)->format($originalTimeFormat) ?? '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('drop_trip_end_time') }}</span>
                                <span
                                    class="fw-semibold">{{ Carbon\Carbon::parse($routeVehicles->drop_end_time)->format($originalTimeFormat) ?? '-' }}</span>
                            </li>
                        </ul>

                        <!-- ROUTE SECTION -->
                        <h6 class="fw-bold text-center text-primary text-dark mb-3">{{ __('route') }}</h6>
                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('name') }}</span>
                                <span class="fw-semibold text-capitalize">{{ $routeVehicles->route->name ?? '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('shift') }}</span>
                                <span
                                    class="fw-semibold text-capitalize">{{ $routeVehicles->route->shift->name ?? '-' }}</span>
                            </li>
                        </ul>

                        <!-- VEHICLE SECTION -->
                        <h6 class="fw-bold text-center text-primary text-dark mb-3">{{ __('vehicle') }}</h6>
                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('name') }}</span>
                                <span class="fw-semibold text-capitalize">{{ $routeVehicles->vehicle->name ?? '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('number') }}</span>
                                <span
                                    class="fw-semibold text-capitalize">{{ $routeVehicles->vehicle->vehicle_number ?? '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('capacity') }}</span>
                                <span
                                    class="fw-semibold text-capitalize">{{ $routeVehicles->vehicle->capacity ?? '-' }}</span>
                            </li>
                        </ul>

                        <!-- DRIVER SECTION -->
                        <h6 class="fw-bold text-center text-primary text-dark mb-3">{{ __('driver') }}</h6>
                        @if ($routeVehicles->driver)
                            <div class="d-flex align-items-center p-2 border rounded mb-4 bg-light">
                                <a data-toggle="lightbox" href="{{ $routeVehicles->driver->image }}">
                                    <img src="{{ $routeVehicles->driver->image }}" class="rounded-circle border"
                                        style="width: 60px; height: 60px; object-fit: cover;" onerror="onErrorImage(event)" />
                                </a>
                                <div class="ms-3">
                                    <h6 class="mb-0 fw-semibold">{{ $routeVehicles->driver->full_name ?? '-' }}</h6>
                                    <small class="d-block text-muted">{{ $routeVehicles->driver->email ?? '-' }}</small>
                                    <small class="d-block text-muted">{{ $routeVehicles->driver->mobile ?? '-' }}</small>
                                </div>
                            </div>
                        @else
                            <div class="text-center text-muted py-4 mb-4">
                                <i class="bi bi-exclamation-circle fs-4 mb-2 d-block"></i>
                                {{ __('no_driver_assigned_for_this_vehicle') }}
                            </div>
                        @endif

                        <!-- HELPER SECTION -->
                        <h6 class="fw-bold text-center text-primary text-dark mb-3">{{ __('helper') }}</h6>
                        @if ($routeVehicles->helper)
                            <div class="d-flex align-items-center p-2 border rounded bg-light">
                                <a data-toggle="lightbox" href="{{ $routeVehicles->helper->image }}">
                                    <img src="{{ $routeVehicles->helper->image }}" class="rounded-circle border"
                                        style="width: 60px; height: 60px; object-fit: cover;" onerror="onErrorImage(event)" />
                                </a>
                                <div class="ms-3">
                                    <h6 class="mb-0 fw-semibold">{{ $routeVehicles->helper->full_name ?? '-' }}</h6>
                                    <small class="d-block text-muted">{{ $routeVehicles->helper->email ?? '-' }}</small>
                                    <small class="d-block text-muted">{{ $routeVehicles->helper->mobile ?? '-' }}</small>
                                </div>
                            </div>
                        @else
                            <div class="text-center text-muted py-4 mb-4">
                                <i class="bi bi-exclamation-circle fs-4 mb-2 d-block"></i>
                                {{ __('no_helper_assigned_for_this_vehicle') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <!-- Right Details Tabs -->
            <div class="col-md-8 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <!-- Tabs -->
                        <ul class="nav nav-tabs nav-tabs-line" id="studentTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="pickup-points-tab" data-toggle="tab" href="#pickup-points"
                                    role="tab">
                                    {{ __('pickup_points') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="student_attendance-tab" data-toggle="tab"
                                    href="#studentAttendanceSection" role="tab">
                                    {{ __('student_attendance') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="staff_attendance-tab" data-toggle="tab"
                                    href="#staffAttendanceSection" role="tab">
                                    {{ __('staff_attendance') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="trip_details-tab" data-toggle="tab" href="#tripDetailsSection"
                                    role="tab">
                                    {{ __('trip_details') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="trip_reports-tab" data-toggle="tab" href="#tripReportsSection"
                                    role="tab">
                                    {{ __('trip_reports') }}
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content border-0 px-0" id="studentTabContent">
                            <div class="tab-pane fade show active py-3" id="pickup-points" role="tabpanel">
                                <div class="col-md-8 grid-margin">
                                    <div class="card border-0 shadow-sm rounded-4">
                                        <div class="card-header bg-gradient-light py-3">
                                            <h5 class="mb-0 text-dark">
                                                <i class="bi bi-geo-alt-fill me-2"></i>{{ __('pickup_points') }}
                                            </h5>
                                        </div>

                                        <div class="card-body p-4">
                                            @if(isset($pickupPoints) && count($pickupPoints) > 0)
                                                <div class="list-group">
                                                    @foreach($pickupPoints as $routePickup)
                                                        @php
                                                            $pickup = $routePickup->pickupPoint;
                                                        @endphp
                                                        <div
                                                            class="list-group-item border-0 mb-3 p-3 rounded-3 shadow-sm hover-card">
                                                            <div class="d-flex align-items-center">
                                                                <div class="flex-grow-1">
                                                                    <h6 class="fw-semibold mb-1 text-capitalize">
                                                                        {{ $pickup->name ?? '-' }}
                                                                    </h6>
                                                                    <small class="d-block">
                                                                        <i class="bi bi-clock me-1"></i>
                                                                        {{ __('pickup_time') }}:
                                                                        {{ Carbon\Carbon::parse($routePickup->pickup_time)->format($originalTimeFormat) ?? '-' }}
                                                                        |
                                                                        {{ __('drop_time') }}:
                                                                        {{ Carbon\Carbon::parse($routePickup->drop_time)->format($originalTimeFormat) ?? '-' }}
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="text-center text-muted py-4">
                                                    <i class="bi bi-exclamation-circle fs-4 mb-2 d-block"></i>
                                                    {{ __('No pickup points added for this route.') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Other tab panes can be filled as needed -->
                            <div class="tab-pane fade py-3" id="studentAttendanceSection" role="tabpanel">
                                @include('route-vehicle.reports.student-attendance-report', ['students' => $students, 'session_year_id' => $session_year_id])
                            </div>
                            <div class="tab-pane fade py-3" id="staffAttendanceSection" role="tabpanel">
                                @include('route-vehicle.reports.staff-attendance-report', ['staff' => $staffs, 'session_year_id' => $session_year_id])
                            </div>
                            <div class="tab-pane fade py-3" id="tripDetailsSection" role="tabpanel">
                                @include('route-vehicle.reports.trip-details-section', ['id' => $routeVehicles->id])
                            </div>
                            <div class="tab-pane fade py-3" id="tripReportsSection" role="tabpanel">
                                @include('route-vehicle.reports.trip-reports-section', ['id' => $routeVehicles->id])
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            // Handle tab navigation
            $('#studentTab a').on('click', function (e) {
                e.preventDefault();
                $(this).tab('show');
            });

            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@endpush