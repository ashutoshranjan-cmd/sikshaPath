@extends('layouts.master')

@section('title')
    {{ __('Staff Attendance') }}
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/staff-attendance.css') }}">
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">{{ __('manage_staff_attendance') }}</h3>
        </div>

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{{ __('staff_attendance_management') }}</h4>
                        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                            <div
                                class="padding-none d-flex align-items-center justify-content-start flex-wrap gap-3 col-md-6">
                                <div class="padding-none col-sm-12 col-md-6">
                                    <input type="text" class="form-control" placeholder="Search" id="search" name="search">
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-end flex-wrap gap-3" id="monthNavigator">
                                <div class="form-check dailyAttendanceSection">
                                    <label class="form-check-label">
                                        <input type="checkbox" class="form-check-input" name="holiday" id="holiday"
                                            value="0" aria-label="Mark this date as holiday">
                                        {{ __('holiday') }}
                                        <i class="input-helper"></i>
                                    </label>
                                </div>
                                <div id="dateDisplayContainer"
                                    class="d-flex dailyAttendanceDateSection align-items-center border rounded mb-sm-2 mb-md-0 p-2 me-2"
                                    role="button" tabindex="0" aria-label="Select attendance date"
                                    aria-describedby="currentDate">
                                    <i class="fa fa-calendar-o me-2" aria-hidden="true"></i>
                                    <h5 id="currentDate" class="mb-0 fw-bold text-dark current-date">
                                        {{ now()->format('F jS, Y') }}
                                    </h5>
                                    <input type="text" id="hiddenDatePicker" class="datepicker-anchor-input">
                                </div>

                                <div
                                    class="d-none monthlyAttendanceDateSection align-items-center border rounded mb-sm-2 mb-md-0 p-1">
                                    <button id="prevMonth" class="btn btn-sm border-0 bg-transparent prev-month me-2">
                                        <i class="fa fa-angle-left"></i>
                                    </button>

                                    <h5 id="currentMonth" class="mb-0 fw-bold text-dark current-month">March 2028</h5>

                                    <button id="nextMonth" class="btn btn-sm border-0 bg-transparent next-month ms-2">
                                        <i class="fa fa-angle-right"></i>
                                    </button>
                                </div>

                                <input type="hidden" id="selectedDate" name="selected_date"
                                    value="{{ now()->format('Y-m-d') }}">
                                <input type="hidden" id="displayDate" name="display_date"
                                    value="{{ now()->format('F jS, Y') }}">

                                <div class="border rounded p-1">
                                    <div class="btn-group" role="group" aria-label="View mode selection">
                                        <button type="button" class="btn btn-outline-successed px-3 py-2 active"
                                            id="dailyView" aria-pressed="true">{{ __('daily') }}</button>
                                        <button type="button" class="btn btn-outline-successed px-3 py-2" id="monthlyView"
                                            aria-pressed="false">{{ __('monthly') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>


                        {{-- Staff Table --}}
                        <div class="dailyAttendanceSection">
                            <div class="show_staff_list loading-overlay" id="staffListContainer">
                                <table class="table staff_table" id="table_list" data-toggle="table"
                                    data-url="{{ route('staff-attendance.show', [1]) }}" data-click-to-select="true"
                                    data-side-pagination="server" data-pagination="false" data-search="false"
                                    data-show-refresh="false" data-toolbar="#toolbar" data-show-columns="false"
                                    data-mobile-responsive="true" data-sort-name="id" data-sort-order="asc"
                                    data-maintain-selected="true" data-query-params="staffAttendanceQueryParams"
                                    data-escape="true">
                                    <thead>
                                        <tr>
                                            @if (Auth::user()->can('staff-attendance-edit'))
                                                <th data-field="state" data-checkbox="true"></th>
                                            @endif
                                            <th data-field="id" data-visible="false">{{ __('id') }}</th>
                                            <th data-field="no" data-visible="false">{{ __('no.') }}</th>
                                            <th data-field="staff_id" data-visible="false"
                                                data-formatter="addStaffIdInputAttendance">{{ __('staff_id') }}</th>
                                            <th data-field="user.staff.user_id" data-visible="false">{{ __('user_id') }}
                                            </th>
                                            <th data-field="user.full_name" data-formatter="staffAttendanceUserFormatter">
                                                {{ __('name') }}
                                            </th>
                                            <th data-field="status" data-click-to-select="false"
                                                data-formatter="staffAttendanceStatus">
                                                {{ __('status') }}
                                            </th>
                                            @if (Auth::user()->can('staff-attendance-edit'))
                                                <th data-field="type" data-click-to-select="false"
                                                    data-formatter="addStaffInputAttendance">
                                                    {{ __('action') }}
                                                </th>
                                            @endif
                                        </tr>
                                    </thead>
                                </table>
                                <button type="button" id="saveAttendanceBtn" class="btn btn-dark btn-sm px-4 d-none ms-2">
                                    {{ __('mark_as_holiday') }}
                                </button>
                                <!-- Floating Multi-Select Bar -->
                                <div id="multiSelectBar" class="multi-select-floating d-none">
                                    <div class="d-flex align-items-center justify-content-between w-100 px-4 py-2">
                                        <div class="d-flex align-items-center">
                                            <div id="selectedStaffAvatars" class="d-flex align-items-center mr-3"></div>
                                            <span id="selectedCount" class="fw-semibold text-dark mr-2"></span>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <button type="button" id="markSelected"
                                                class="btn btn-dark btn-sm px-4 mr-2">{{ __('mark') }}</button>
                                            <button type="button" id="clearSelected"
                                                class="btn btn-outline-secondary btn-sm px-4">{{ __('clear') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="monthlyAttendanceSection d-none">
                            <div class="card">
                                <div class="d-flex flex-wrap flex-md-nowrap align-items-center border p-4 bg-gradient-light mb-4 mt-4">
                                    <span class="mb-2 mb-md-0 small mr-md-2">{{ __('Attendance Summary') }} :</span>

                                    <div class="attendance-legends d-flex flex-wrap">
                                        <span class="legend-item d-flex align-items-center mx-2 mb-2">
                                            <i class="fa fa-circle text-present mr-1"></i> {{ __('Present') }}
                                        </span>
                                        <span class="legend-item d-flex align-items-center mx-2 mb-2">
                                            <i class="fa fa-circle text-absent mr-1"></i> {{ __('Absent') }}
                                        </span>
                                        <span class="legend-item d-flex align-items-center mx-2 mb-2">
                                            <i class="fa fa-circle text-half-day mr-1"></i> {{ __('Half Day') }}
                                        </span>
                                        <span class="legend-item d-flex align-items-center mx-2 mb-2">
                                            <i class="fa fa-circle text-holiday mr-1"></i> {{ __('Holiday') }}
                                        </span>
                                        <span class="legend-item d-flex align-items-center mx-2 mb-2">
                                            <i class="fa fa-circle text-leave mr-1"></i> {{ __('Leave') }}
                                        </span>
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
                                            <tbody id="staff_month_attendance_data" class="attendance-data">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('staff-attendance.attendance-modal')
@endsection

@section('script')
    <script>
        window.staffAttendanceDataUrl = "{{ url('staff-attendance/getAttendanceData') }}";
    </script>
    <script src="{{ asset('assets/js/custom/staff-attendance.js') }}"></script>
@endsection