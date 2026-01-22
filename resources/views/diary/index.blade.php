@extends('layouts.master')

@section('title')
    {{ __('diary') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('manage_diaries') }}
            </h3>
        </div>

        <div class="row">
            <div class="col-lg-5 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('create_diary') }}
                        </h4>

                        <form class="create-form" data-success-function="formSuccessFunction" action="{{ route('diary.store') }}" method="POST"
                            novalidate="novalidate">
                            @csrf
                            <div class="row">
                                <div class="form-group col-sm-12 col-md-12">
                                    <label>{{ __('title') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="title" id="title" class="form-control" placeholder="{{ __('title') }}" required>
                                </div>

                                <div class="form-group col-sm-12 col-md-12">
                                    <label>{{ __('diary_category') }} <span class="text-danger">*</span></label>
                                    <select name="diary_category_id" id="diary_category_id" class="form-control" required>
                                        <option value="" disabled selected>{{ __('select_category') }}</option>
                                        @foreach ($diaryCategories as $diaryCategory)
                                        <option value="{{ $diaryCategory->id }}">{{ $diaryCategory->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {!! Form::hidden('user_id', Auth::user()->id, ['id' => 'user_id']) !!}
                                <div class="form-group col-sm-12 col-md-12">
                                    <label>{{ __('class_section') }} <span class="text-danger">*</span></label>
                                    <select name="filter_class_section_id" id="filter_class_section_id"
                                        class="form-control" required>
                                        <option value="">{{ __('select_class') }}</option>
                                        @foreach ($class_sections as $class_section)
                                            <option value="{{ $class_section->id }}">{{ $class_section->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-sm-12 col-md-12">
                                    <label>{{ __('subject') }} </label>
                                    <select name="subject_id" id="subject_id" class="form-control" disabled>
                                        <option value="">-- {{ __('select_subject') }} --</option>
                                    </select>
                                </div>

                                <div class="form-group col-sm-12 col-md-12">
                                    <label>{{ __('date') }}</label>
                                    <input type="text" name="date" id="date"
                                        class="datepicker-popup-no-future form-control">
                                </div>
                                <div class="form-group col-sm-12 col-md-12">
                                    <label>{{ __('description') }}<span class="text-danger">*</span></label>
                                    <textarea name="description" id="description" class="form-control" placeholder="{{ __('Write Something...') }}" required></textarea>
                                </div>

                                <input type="hidden" name="student_class_section_map" id="student_class_section_map">

                            </div>

                            <input class="btn btn-theme float-right ml-3" id="create-btn" type="submit"
                                value={{ __('submit') }}>
                            <input class="btn btn-secondary float-right" id="reset" type="reset"
                                value={{ __('reset') }}>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-7 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('list') . ' ' . __('students') }}
                        </h4>

                        <div class="row">
                            <div class="col-12">
                                <table aria-describedby="mydesc" class='table' id='student_table_list' data-toggle="table"
                                    data-url="{{ route('diary.showStudents') }}" data-click-to-select="true"
                                    data-side-pagination="server" data-pagination="true"
                                    data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                                    data-toolbar="#toolbarStudents" data-show-columns="true" data-show-refresh="true"
                                    data-trim-on-search="false" data-mobile-responsive="true" data-sort-name="id"
                                    data-sort-order="desc" data-maintain-selected="true" data-export-data-type='all'
                                    data-query-params="diaryStudentQueryParams" data-escape="true">
                                    <thead>
                                        <tr>
                                            <th data-field="state" data-checkbox="true"></th>
                                            <th scope="col" data-field="id" data-sortable="true" data-visible="false"> {{ __('id') }}</th>
                                            <th scope="col" data-field="user_id" data-sortable="true" data-visible="false"> {{ __('user_id') }}</th>
                                            <th scope="col" data-field="no">{{ __('no.') }}</th>
                                            <th scope="col" data-field="full_name">{{ __('student_name') }}</th>
                                            <th scope="col" data-field="roll_number">{{ __('roll_number') }}</th>
                                            <th scope="col" data-field="class_section_id" data-visible="false">{{ __('class_section') }}</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('list_diaries') }}
                        </h4>
                        <div class="row" id="toolbar">
                            <div class="form-group col-md-4 col-sm-12">
                                <label class="filter-menu">{{ __('class_section') }}</label>
                                <select name="diary_filter_class_section_id" id="diary_filter_class_section_id"
                                    class="form-control">
                                    <option value="">{{ __('select_class') }}</option>
                                    @foreach ($class_sections as $class_section)
                                        <option value="{{ $class_section->id }}">{{ $class_section->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-12">
                                <label class="filter-menu">{{ __('diary_type') }} </label>
                                <select name="filter_diary_type" id="filter_diary_type" class="form-control">
                                    <option value="">{{ __('select_type') }}</option>
                                    <option value="positive">{{ __('positive') }}</option>
                                    <option value="negative">{{ __('negative') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <table aria-describedby="mydesc" class='table' id='table_list' data-toggle="table"
                                    data-url="{{ route('diary.show', [1]) }}" data-click-to-select="true"
                                    data-side-pagination="server" data-pagination="true"
                                    data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-toolbar="#toolbar"
                                    data-show-columns="true" data-show-refresh="true" data-trim-on-search="false"
                                    data-mobile-responsive="true" data-sort-name="id" data-sort-order="desc"
                                    data-maintain-selected="true" data-export-data-type='all' data-show-export="true"
                                    data-export-options='{ "fileName": "diary-list-<?= date('d-m-y') ?>"
                                    ,"ignoreColumn":["operate"]}'
                                    data-query-params="diaryQueryParams" data-escape="true">
                                    <thead>
                                        <tr>
                                            <th scope="col" data-field="id" data-sortable="true"
                                                data-visible="false">
                                                {{ __('id') }}</th>
                                            <th scope="col" data-field="no">{{ __('no.') }}</th>
                                            {{-- <th scope="col" data-field="session_year.name">{{ __('session_year') }}
                                            </th> --}}

                                            <th scope="col" data-field="student" data-events="tableDescriptionEvents"
                                                data-formatter="descriptionFormatter" data-sortable="false">
                                                {{ __('student') }}</th>

                                            <th scope="col" data-field="diary_category.name">
                                                {{ __('diary_category') }}
                                            </th>
                                            <th scope="col" data-field="title">{{ __('title') }}</th>
                                            {{-- <th scope="col" data-field="subject.name">{{ __('Class Section') }}</th> --}}
                                            <th scope="col" data-field="subject.name">{{ __('subject') }}</th>
                                            <th scope="col" data-field="description">{{ __('description') }}</th>
                                            @role('School Admin')
                                                <th scope="col" data-field="user.full_name">{{ __('added_by') }}</th>
                                            @endrole
                                            <th scope="col" data-formatter="diaryTypeFormatter"
                                                data-field="diary_category.type">{{ __('type') }}</th>
                                            <th data-events="" scope="col" data-formatter="actionColumnFormatter"
                                                data-field="operate" data-escape="false">{{ __('action') }}</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $('#date').datepicker({
            format: "dd-mm-yyyy",
            rtl: isRTL()
        }).datepicker("setDate", 'now');

        $(document).ready(function() {
            $('#filter_class_section_id, #filter_session_year_id').on('change', function() {
                $('#student_table_list').bootstrapTable('refresh');
            });
        });

        let classSections = @json($class_sections);
        let currentSemesterId = @json($currentSemesterId);

        $(document).on('change', '#filter_class_section_id', function () {
            let classSectionId = $(this).val();
            let subjectSelect = $('#subject_id');
            let current_user = "{{ $current_user }}";

            // Reset dropdown
            subjectSelect.empty().append('<option value="">-- ' + window.trans["Select Subject"] + ' --</option>');

            // Find the selected section
            let selectedSection = classSections.find(cs => cs.id == classSectionId);
            if (!selectedSection) {
                subjectSelect.prop('disabled', true);
                return;
            }

            // Check if class has semesters
            let classHasSemesters = selectedSection.class?.include_semesters || false;
            
            // Filter subjects based on current user
            let filteredSubjects = [];

            if (current_user) {
                const isClassTeacher = Array.isArray(selectedSection.class?.class_teachers) &&
                selectedSection.class.class_teachers.some(
                    ct => ct.teacher_id == current_user && ct.class_section_id == selectedSection.id
                );
                if (isClassTeacher) {
                    // ✅ User is class teacher → show all subjects
                    filteredSubjects = selectedSection.subjects || [];
                } else {
                    // ✅ Otherwise → show only subjects assigned to this teacher
                    filteredSubjects = (selectedSection.subjects || []).filter(sub =>
                        Array.isArray(selectedSection.subject_teachers) &&
                        selectedSection.subject_teachers.some(
                            t => t.teacher_id == current_user && t.subject_id == sub.id
                        )
                    );
                }
            } else {
                // No current_user (admin, etc.) → show all
                filteredSubjects = selectedSection.subjects;
            }
            
            // If class has semesters, filter by current semester
            if (classHasSemesters && currentSemesterId) {
                filteredSubjects = filteredSubjects.filter(sub => {
                    // Include subjects with current semester_id or null semester_id
                    let semesterId = sub.pivot?.semester_id || null;
                    return semesterId === null || semesterId == currentSemesterId;
                });
            }
            
            // Populate select options
            if (filteredSubjects.length > 0) {
                filteredSubjects.forEach(item => {
                    subjectSelect.append(
                        `<option value="${item.id}">${item.name_with_type}</option>`
                    );
                });
                subjectSelect.prop('disabled', false);
            } else {
                subjectSelect.prop('disabled', true);
            }
        });


        // $(document).ready(function() {
        //     $('#filter_class_section_id').on('change', function() {
        //         let classSectionId = $(this).val();

        //         if (classSectionId) {
        //             $.ajax({
        //                 url: '/diary/change-subjects-by-class-section', // 👈 update to your actual route
        //                 type: 'GET',
        //                 data: {
        //                     class_section_id: classSectionId
        //                 },
        //                 success: function(response) {
        //                     // console.log(response);

        //                     // Example: populate a select element
        //                     let options = '<option value="">Select Subject</option>';
        //                     response.forEach(function(subject) {
        //                         options +=
        //                             `<option value="${subject.subject_id}">${subject.subject_with_name}</option>`;
        //                     });
        //                     $('#subject_id').html(options);
        //                 },
        //                 error: function() {
        //                     alert('Failed to fetch subject subjects.');
        //                 }
        //             });
        //         }

        //     });
        // });

        $(document).ready(function() {
            $('.user-list').hide(500);
            $('.type').trigger('change');
        });

        function formSuccessFunction(response) {
            setTimeout(() => {
                // Reset selections
                selections = [];
                user_list = [];
                $('.type').trigger('change');
                $('#table_list').bootstrapTable('refresh');

                // reset form fields
                $('.form-control').val('');
                $('input#student_class_section_map').val('');
                $('#student_table_list').bootstrapTable('refresh');
            }, 500);
        }

        $('#reset').click(function(e) {
            // e.preventDefault();
            $('.default-all').prop('checked', true);
            $('.type').trigger('change');
            $('#table_list').bootstrapTable('refresh');
            $('input#student_class_section_map').val('');
        });


        $('.type').change(function(e) {
            var selectedType = $('input[name="type"]:checked').val();
            e.preventDefault();
            $('.student_class_section_map').val('').trigger('change');

            $('#student_table_list').bootstrapTable('uncheckAll');

        });


        $('.type').change(function(e) {
            e.preventDefault();
            $('#student_table_list').bootstrapTable('refresh');

        });

        var $tableList = $('#student_table_list')
        var selections = []
        var user_list = [];

        function responseHandler(res) {
            $.each(res.rows, function(i, row) {
                row.state = $.inArray(row.id, selections) !== -1
            })
            return res
        }

        $(function() {
            $tableList.on('check.bs.table check-all.bs.table uncheck.bs.table uncheck-all.bs.table',
                function(e, rowsAfter, rowsBefore) {
                    user_list = [];
                    var rows = rowsAfter
                    if (e.type === 'uncheck-all') {
                        rows = rowsBefore
                    }
                    var students = $.map(!$.isArray(rows) ? [rows] : rows, function(row) {
                        return {
                            id: row.user_id,
                            class_section_id: row.class_section_id
                        };
                    });

                    // Update selections
                    var func = $.inArray(e.type, ['check', 'check-all']) > -1 ? 'unionBy' : 'differenceBy';
                    selections = window._[func](selections.concat(students), students, 'id');

                    // Build mapping object
                    let studentMap = {};
                    selections.forEach(s => {
                        studentMap[s.id] = s.class_section_id;
                    });

                    // Store JSON in hidden input
                    $('#student_class_section_map').val(JSON.stringify(studentMap));

                })
        })
    </script>
@endsection
