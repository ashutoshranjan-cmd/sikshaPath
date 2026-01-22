@extends('layouts.master')

@section('title')
    {{ __('manage') . ' ' . __('assignment') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('manage') . ' ' . __('assignment_submission') }}
            </h3>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('list') . ' ' . __('assignment_submission') }}
                        </h4>

                        <div class="row" id="toolbar">
                            <div class="form-group col-12 col-sm-12 col-md-3 col-lg-4">
                                <label for="filter-class-section-id" class="filter-menu">{{__("class_section")}}</label>
                                <select name="class_section_id" id="filter-class-section-id" class="form-control">
                                    <option value="">{{ __('all') }}</option>
                                    @foreach ($classSections as $data)
                                        <option value="{{ $data->id }}">
                                            {{ $data->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @if($semesters->count() > 0)
                                <div class="form-group col-sm-12 col-md-3" id="semester-filter-group" style="display: none;">
                                    <label for="filter-semester-id" class="filter-menu">{{ __('Semester') }} <span class="text-danger">*</span></label>
                                    <select name="filter-semester-id" id="filter-semester-id" class="form-control">
                                        <option value="">{{ __('select_semester') }}</option>
                                        @foreach ($semesters as $semester)
                                            <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="form-group col-12 col-sm-12 col-md-3 col-lg-4">
                                <label for="filter-subject-id" class="filter-menu">{{__("subject")}}</label>
                                <select name="class_subject_id" id="filter-subject-id" class="form-control select2">
                                    <option value="">-- {{ __('Select Subject') }} --</option>
                                    {{-- <option value="data-not-found">-- {{ __('no_data_found') }} --</option> --}}
                                    @foreach ($subjectTeachers as $item)
                                        <option value="{{ $item->class_subject_id }}" 
                                                data-class-section="{{ $item->class_section_id }}"
                                                data-semester-id="{{ isset($item->class_subject) && $item->class_subject ? ($item->class_subject->semester_id ?? '') : '' }}">
                                            {{ $item->subject_with_name}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                          
                            

                        </div>
                        <table aria-describedby="mydesc" class='table' id='table_list' data-toggle="table"
                               data-url="{{ route('assignment.submission.list') }}" data-click-to-select="true"
                               data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]"
                               data-search="true" data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true"
                               data-fixed-columns="false" data-fixed-number="2" data-fixed-right-number="1"
                               data-trim-on-search="false" data-mobile-responsive="true" data-sort-name="id"
                               data-query-params="AssignmentSubmissionQueryParams" data-sort-order="desc"
                               data-maintain-selected="true" data-export-data-type='all'
                               data-export-options='{ "fileName": "assignment-submission-list-<?= date('d-m-y') ?>","ignoreColumn": ["operate"]}'
                               data-show-export="true" data-escape="true">
                            <thead>
                            <tr>
                                <th scope="col" data-field="id" data-sortable="false" data-visible="false">{{ __('id') }}</th>
                                <th scope="col" data-field="no">{{ __('no.') }}</th>
                                <th scope="col" data-field="assignment.name" data-sortable="false">{{ __('assignment_name') }}</th>
                                <th scope="col" data-field="assignment.class_section.full_name" data-sortable="false">{{ __('class_section') }}</th>
                                <th scope="col" data-field="assignment.class_subject.subject.name_with_type" data-sortable="false">{{ __('subject') }}</th>
                                <th scope="col" data-field="student" data-formatter="AssignmentSubmissionStudentNameFormatter" data-sortable="false">{{ __('student_name') }}</th>
                                <th scope="col" data-field="file" data-sortable="false" data-formatter="fileFormatter">{{ __('files') }}</th>
                                <th scope="col" data-field="status" data-sortable="false" data-formatter="assignmentSubmissionStatusFormatter">{{ __('status') }}</th>
                                <th scope="col" data-field="points" data-sortable="false">{{ __('points') }}</th>
                                <th scope="col" data-field="feedback" data-sortable="false">{{ __('feedback') }}</th>
                                <th scope="col" data-field="session_year.name" data-sortable="false" data-visible="false">{{ __('Session Year') }}</th>
                                <th scope="col" data-field="created_at"  data-sortable="false" data-visible="false">{{ __('created_at') }}</th>
                                <th scope="col" data-field="updated_at"  data-sortable="false" data-visible="false">{{ __('updated_at') }}</th>
                                <th scope="col" data-field="operate" data-events="assignmentSubmissionEvents" data-escape="false">{{ __('action') }}</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                 aria-hidden="true">
                <div class="modal-dialog modal-xl" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">
                                {{ __('edit') . ' ' . __('assignment_submission') }}
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form class="pt-3 class-edit-form" id="edit-form" action="{{ url('assignment-submission') }}" novalidate="novalidate">
                            <input type="hidden" name="edit_id" id="edit_id" value=""/>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="form-group col-sm-12 col-md-4">
                                        <label for="assignment_name">{{ __('assignment_name') }}</label>
                                        <input type="text" name="" id="assignment_name" class="form-control" disabled>
                                    </div>

                                    <div class="form-group col-sm-12 col-md-4">
                                        <label for="subject">{{ __('subject') }}</label>
                                        <input type="text" name="" id="subject" class="form-control" disabled>
                                    </div>

                                    <div class="form-group col-sm-12 col-md-4">
                                        <label for="student_name">{{ __('student_name') }}</label>
                                        <input type="text" name="" id="student_name" class="form-control" disabled>
                                    </div>

                                    <div class="form-group col-sm-12 col-md-12">
                                        <label>{{ __('files') }}</label>
                                        <div id="files"></div>
                                    </div>

                                    <div class="form-group col-sm-12 col-md-12">
                                        <label>{{ __('status') }} <span class="text-danger">*</span></label>
                                        <div class="d-flex">
                                            <div class="form-check form-check-inline">
                                                <label class="form-check-label">
                                                    <input type="radio" class="form-check-input edit-status" name="status" id="status_accept" value="1">{{ __('accept') }}
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <label class="form-check-label">
                                                    <input type="radio" class="form-check-input edit-status" name="status" id="status_reject" value="2">{{ __('reject') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-sm-12 col-md-12" id="points_div">
                                        <label for="points">{{ __('points') }} <span id="assignment_points"></span></label>
                                        <input type="number" name="points" placeholder="{{ __('points') }}" id="points" class="form-control" min="0">
                                    </div>

                                    <div class="form-group col-sm-12 col-md-12">
                                        <label for="feedback">{{ __('feedback') }}</label>
                                        <textarea name="feedback" id="feedback" placeholder="{{ __('feedback') }}" class="form-control"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('close') }}</button>
                                <input class="btn btn-theme" type="submit" value={{ __('submit') }}>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $('.edit-status').change(function (e) { 
            e.preventDefault();
            var status = $('input[name="status"]:checked').val();
            if (status == 1) {
                $('#points').attr('disabled', false);
            } else {
                $('#points').val(null);
                $('#points').attr('disabled', true);
            }
        });

        let classSections = @json($classSections);
        let currentSemesterId = @json($currentSemesterId);

        $(document).on('change', '#filter-class-section-id', function() {
            let classSectionId = $(this).val();
            let subjectSelect = $('#filter-subject-id');
            let semesterSelect = $('#filter-semester-id');
            let semesterGroup = $('#semester-filter-group');

            // Always reset semester dropdown first when class section changes
            semesterSelect.val('').prop('required', false).trigger('change');
            
            // Reset subject dropdown
            subjectSelect.val('').trigger('change');
            
            if (!classSectionId) {
                // Show all subjects and hide semester filter
                semesterGroup.hide();
                subjectSelect.find('option').show();
                return;
            }

            // Convert to number for comparison
            classSectionId = parseInt(classSectionId);

            // Find the selected class section
            let selectedSection = classSections.find(cs => parseInt(cs.id) == classSectionId);
            if (!selectedSection) {
                semesterGroup.hide();
                subjectSelect.find('option').show();
                return;
            }

            // Check if class has semesters
            let classHasSemesters = selectedSection.class?.include_semesters || false;

            if (classHasSemesters) {
                // Show semester dropdown and make it required
                semesterGroup.show();
                semesterSelect.prop('required', true);
                
                // Hide all subjects until semester is selected (don't show subjects with null semester_id)
                subjectSelect.find('option:not(:first)').hide();
            } else {
                // Hide semester dropdown and show all subjects for this class section
                semesterGroup.hide();
                
                subjectSelect.find('option').each(function() {
                    let $option = $(this);
                    let optionClassSection = parseInt($option.data('class-section')) || 0;
                    if (optionClassSection == classSectionId) {
                        $option.show();
                    } else {
                        $option.hide();
                    }
                });
            }
        });

        // Handle semester change to filter subjects
        $(document).on('change', '#filter-semester-id', function() {
            let semesterId = $(this).val();
            let classSectionId = $('#filter-class-section-id').val();
            let subjectSelect = $('#filter-subject-id');

            if (!classSectionId) {
                return;
            }

            // Convert to number for comparison
            classSectionId = parseInt(classSectionId);

            // Reset subject dropdown
            subjectSelect.val('').trigger('change');

            if (!semesterId) {
                // Show all subjects for this class section
                subjectSelect.find('option').each(function() {
                    let $option = $(this);
                    let optionClassSection = parseInt($option.data('class-section')) || 0;
                    if (optionClassSection == classSectionId) {
                        $option.show();
                    } else {
                        $option.hide();
                    }
                });
                return;
            }

            // Filter subjects by class section and semester
            // Only show subjects that have the selected semester_id (exclude null semester_id)
            subjectSelect.find('option').each(function() {
                let $option = $(this);
                let optionClassSection = parseInt($option.data('class-section')) || 0;
                let optionSemesterId = $option.data('semester-id');
                
                // Show subject if:
                // 1. It belongs to the selected class section AND
                // 2. It has the selected semester_id (exclude null/empty semester_id)
                if (optionClassSection == classSectionId) {
                    // Convert both to strings for comparison to handle type mismatches
                    let optionSemesterIdStr = String(optionSemesterId || '');
                    let semesterIdStr = String(semesterId || '');
                    
                    // Only show if semester_id matches exactly (exclude null, empty, or undefined)
                    if (optionSemesterIdStr && optionSemesterIdStr != '' && optionSemesterIdStr == semesterIdStr) {
                        $option.show();
                    } else {
                        $option.hide();
                    }
                } else {
                    $option.hide();
                }
            });
        });

        // Initialize on page load
        $(document).ready(function() {
            // If class section is already selected, trigger change to filter subjects
            if ($('#filter-class-section-id').val()) {
                $('#filter-class-section-id').trigger('change');
            } else {
                // If no class section selected, show all subjects
                $('#filter-subject-id').find('option').show();
            }
        });
    </script>
@endsection
