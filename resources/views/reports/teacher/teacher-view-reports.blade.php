@extends('layouts.master')

@section('title')
    {{ __('teacher_profile') }} - {{ $teacher->first_name }} {{ $teacher->last_name }}
@endsection

@section('content')
<div class="content-wrapper">
    <div class="page-header">
        <h3 class="page-title">
           {{ __('teacher_profile') }}
        </h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('reports.teacher.teacher-reports') }}">{{ __('teacher_reports') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('profile') }}</li>
            </ol>
        </nav>
    </div>
    <div class="row">
        <!-- Left Profile Card -->
        <div class="col-md-4 grid-margin">
            <div class="card">
                <div class="card-body text-center">
                    <img src="{{ $teacher->image ?? asset('images/default-user.png') }}" 
                         class="rounded-circle mb-3 shadow" 
                         style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #eaeaea;"
                         alt="{{ $teacher->first_name }}'s Photo">
                    <h4 class="mb-1">{{ $teacher->first_name }} {{ $teacher->last_name }}</h4>
                    <p class="text-muted mb-2">{{ __('teacher') }}</p>
                    <hr>
                    <ul class="list-group list-group-flush text-left">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><b>{{ __('qualification') }}:</b></span>
                            <span class="text-capitalize font-weight-medium">{{ ucfirst($teacher->staff->qualification ?? '-') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><b>{{ __('salary') }}:</b></span>
                            <span class="text-capitalize font-weight-medium">{{ ucfirst($teacher->staff->salary ?? '-') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><b>{{ __('gender') }}:</b></span>
                            <span class="text-capitalize font-weight-medium">{{ ucfirst($teacher->gender) }}</span>
                        </li>
                    </ul>
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
                            <a class="nav-link active" id="profile-tab" data-toggle="tab" href="#profile" role="tab">
                                {{ __('profile') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="assigned_class_and_subject-tab" data-toggle="tab" href="#assigned_class_and_subject" role="tab">
                                {{ __('assigned_class_and_subject') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="attendance-tab" data-toggle="tab" href="#attendance" role="tab">
                                {{ __('attendance') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="timetable-tab" data-toggle="tab" href="#timetable" role="tab">
                                {{ __('timetable') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="salary-structure-tab" data-toggle="tab" href="#salary-structure" role="tab">
                                {{ __('salary_structure') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="leaves-tab" data-toggle="tab" href="#leaves" role="tab">
                                {{ __('leaves') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="transportation-tab" data-toggle="tab" href="#transportation" role="tab">
                                {{ __('transportation') }}
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content border-0 px-0" id="studentTabContent">
                        <div class="tab-pane fade show active py-3" id="profile" role="tabpanel">
                            <div class="card">
                                <div class="card-header bg-gradient-light p-2">
                                    <h5 class="mb-0 text-theme">{{ __('basic_information') }}</h5>
                                </div>
                                <div class="card-body p-2">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>{{ __('joining_date') }}:</strong> {{ explode(' ', $teacher->staff->joining_date)[0] ?? '-' }}</p>
                                            <p><strong>{{ __('dob') }}:</strong> {{ $teacher->dob ?? '-' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>{{ __('mobile_number') }}:</strong> {{ $teacher->mobile ?? '-' }}</p>
                                            <p><strong>{{ __('email') }}:</strong> {{ $teacher->email ?? '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                                <div class="card">
                                <div class="card-header bg-gradient-light p-2">
                                    <h5 class="mb-0 text-theme">{{ __('address_information') }}</h5>
                                </div>
                                <div class="card-body p-2">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-muted">{{ __('current_address') }}</h6>
                                            <p>{{ $teacher->current_address ?: '-' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-muted">{{ __('permanent_address') }}</h6>
                                            <p>{{ $teacher->permanent_address ?: '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Other tab panes can be filled as needed -->
                        <div class="tab-pane fade py-3" id="assigned_class_and_subject" role="tabpanel">
                            @include('reports.teacher.assigned-class-subject-report-tab', ['teacher' => $teacher])
                        </div>
                        <div class="tab-pane fade py-3" id="attendance" role="tabpanel">
                            @include('reports.teacher.attendance-report-tab', ['teacher' => $teacher])
                        </div>
                        <div class="tab-pane fade py-3" id="timetable" role="tabpanel">
                            @include('reports.teacher.timetable-tab', ['timetables' => $timetables, 'timetableSettingsData' => $timetableSettingsData])
                        </div>
                        <div class="tab-pane fade py-3" id="salary-structure" role="tabpanel">
                            @include('reports.teacher.salary-structure-tab', ['salary_structure' => $salary_structure])
                        </div>
                        <div class="tab-pane fade py-3" id="leaves" role="tabpanel">
                            @include('reports.teacher.leaves-tab', ['teacher' => $teacher])
                        </div>
                        <div class="tab-pane fade py-3" id="transportation" role="tabpanel">
                            @include('reports.teacher.transportation-report-tab', ['transportation' => $transportation, 'sessionYear' => $sessionYear])
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

