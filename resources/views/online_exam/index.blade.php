{{-- @dd($subjectTeachers) --}}
@extends('layouts.master')

@section('title')
{{ __('manage_online_exam') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('manage_online_exam') }}
            </h3>
        </div>
        <div class="row">
            @can('online-exam-create')
                <div class="col-md-12 grid-margin stretch-card search-container">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">
                                {{ __('create_online_exam') }}
                            </h4>
                            <form class="pt-3 mt-6" id="create-form" method="POST" action="{{ route('online-exam.store') }}">
                                {{-- {!! Form::hidden('user_id', Auth::user()->id, ['id' => 'user_id']) !!} --}}
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>{{ __('Class') }} <span class="text-danger">*</span></label>
                                        <select name="class_id" required id="class-id" class="form-control">
                                            <option value="">-- {{ __('Select Class') }} --</option>
                                            @foreach ($classes as $class)
                                                <option value="{{ $class['id'] }}">{{ $class['full_name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>{{ __('Class Section') }}</label>
                                        <select name="class_section_id[]" id="class-section-id" class="form-control select2" style="width:100%;" multiple disabled>
                                            <option value="">-- {{ __('Select Class First') }} --</option>
                                        </select>
                                        <div class="form-check w-fit-content mt-2">
                                            <label class="form-check-label user-select-none">
                                                <input type="checkbox" class="form-check-input" id="select-all" value="1">{{__("Select All")}}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>{{ __('Subject') }} <span class="text-danger">*</span></label>
                                        <select required name="subject_id" id="subject-id" class="form-control" disabled>
                                            <option value="">-- {{ __('Select Section First') }} --</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>{{ __('title') }} <span class="text-danger">*</span></label>
                                        {!! Form::text('title', "", ['required','id' => "title","placeholder" => trans('title'),"class" => "form-control" ]) !!}
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>{{ __('exam_key') }} <span class="text-danger">*</span></label>
                                        {!! Form::number('exam_key', "", ['required','id' => "key","placeholder" => trans('exam_key'),"class" => "form-control","min" => 1,'readonly' => true]) !!}
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>{{ __('duration') }} <span class="text-danger">*</span> <span class="text-info small">( {{__('in_minutes')}} )</span></label>
                                        {!! Form::number('duration', "", ['required','id' => "duration","placeholder" => trans('duration'),"class" => "form-control","min" => 1]) !!}
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>{{ __('start_date')}} <span class="text-danger">*</span></label>
                                        {!! Form::datetimeLocal('start_date', "", ['required','id' => "start-date timepicker-example","placeholder" => trans('start_date'),"class" => "form-control"]) !!}
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>{{ __('end_date') }} <span class="text-danger">*</span></label>
                                        {!! Form::datetimeLocal('end_date', "", ['required','id' => "end-date","placeholder" => trans('end_date'),"class" => "form-control"]) !!}
                                    </div>
                                </div>

                                {{-- <input class="btn btn-theme" id="add-online-exam-btn" type="submit" value={{ __('submit') }}> --}}
                                <input class="btn btn-theme float-right ml-3" id="create-btn" type="submit" value={{ __('submit') }}>
                                <input class="btn btn-secondary float-right" type="reset" value={{ __('reset') }}>
                            </form>
                        </div>
                    </div>
                </div>
            @endcan

            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('list') . ' ' . __('exams') }}
                        </h4>
                        <div class="row" id="">
                            <div class="form-group col-12 col-sm-12 col-md-2">
                                <label for="filter-session-year-id" class="filter-menu">{{__("session_year")}}</label>
                                {!! Form::select('session_year_id', $sessionYear, $defaultSessionYear->id, ['class' => 'form-control', 'id' => 'filter_session_year_id']) !!}
                            </div>

                            <div class="form-group col-sm-12 col-md-3">
                                <label for="filter-class-id" class="filter-menu">{{__("Class")}}</label>
                                <select name="class_id" id="filter-class-id" class="form-control">
                                    <option value="">{{ __('all') }}</option>
                                    @foreach ($classes as $class)
                                        <option value="{{ $class['id'] }}">{{ $class['full_name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-sm-12 col-md-2">
                                <label for="filter-class-section-id" class="filter-menu">{{__("Class Section")}}</label>
                                <select name="class_section_id" id="filter-class-section-id" class="form-control" disabled>
                                    <option value="">{{ __('all') }}</option>
                                </select>
                            </div>
                            <div class="form-group col-sm-12 col-md-3">
                                <label for="filter-subject-id" class="filter-menu">{{ __('subject') }}</label>
                                <select name="subject_id" id="filter-subject-id" class="form-control" disabled>
                                    <option value="">{{ __('all') }}</option>
                                </select>
                            </div>
                            <div class="form-group col-sm-12 col-md-2">
                                <label for="filter-exam-status" class="filter-menu">{{ __('status') }}</label>
                                <select name="exam_status" id="filter-exam-status" class="form-control">
                                    <option value="">{{ __('all') }}</option>
                                    <option value="Upcoming">{{ __('Upcoming') }}</option>
                                    <option value="On Going">{{ __('On Going') }}</option>
                                    <option value="Completed">{{ __('Completed') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-block">
                            <div class="">
                                <div class="col-12 text-right d-flex justify-content-end text-right align-items-end">
                                    <b><a href="#" class="table-list-type active mr-2" data-id="0">{{ __('all') }}</a></b> | <a href="#" class="ml-2 table-list-type" data-id="1">{{ __('Trashed') }}</a>
                                </div>
                            </div>
                        </div>
                      
                        <table aria-describedby="mydesc" class='table' id='table_list'
                               data-toggle="table" data-url="{{ route('online-exam.show', 1) }}" data-click-to-select="true"
                               data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]"
                               data-search="true" data-toolbar="#toolbar" data-show-columns="true"
                               data-show-refresh="true" data-fixed-columns="false" data-fixed-right-number="1"
                               data-trim-on-search="false" data-mobile-responsive="true" data-sort-name="id"
                               data-sort-order="desc" data-maintain-selected="true" data-export-data-type='all'
                               data-export-options='{ "fileName": "{{__('online').' '.__('exam')}}-<?= date(' d-m-y') ?>" ,"ignoreColumn":["operate"]}'
                               data-show-export="true" data-query-params="onlineExamQueryParams" data-escape="true" data-escape-title="false"
                               data-row-style="onlineExamRowStyle">
                            <thead>
                            <tr>
                                <th scope="col" data-field="id" data-sortable="true" data-visible="false">{{ __('id') }}</th>
                                <th scope="col" data-field="no">{{ __('no.') }}</th>
                                <th scope="col" data-field="class_section_with_medium" data-formatter="classSectionFormatter">{{ __('class_section') }}</th>
                                <th scope="col" data-field="title">{{ __('exam_name') }}</th>
                                <th scope="col" data-field="subject_name">{{ __('subject') }}</th>
                              
                                <th scope="col" data-field="exam_key">{{ __('exam_key')}}</th>
                                <th scope="col" data-field="total_questions" data-align="center">{{ __('total').' '.__('questions') }}</th>
                                <th scope="col" data-field="start_date_db"  data-sortable="true">{{ __('start_date') }}</th>
                                <th scope="col" data-field="end_date_db"  data-sortable="true">{{ __('end_date') }}</th>
                                <th scope="col" data-field="duration" data-align="center">{{ __('duration')}} <span class="text-info small">( {{__('in_minutes')}} )</span></th>
                                <th scope="col" data-field="participants" data-align="center">{{ __('participants') }}</th>
                                <th scope="col" data-field="exam_status_name" data-formatter="examStatusFormatter">{{ __('status') }}</th>
                                <th scope="col" data-field="created_at"  data-sortable="true" data-visible="false">{{ __('created_at') }}</th>
                                <th scope="col" data-field="updated_at"  data-sortable="true" data-visible="false">{{ __('updated_at') }}</th>
                                <th scope="col" data-field="operate" data-formatter="actionColumnFormatter" data-events="onlineExamEvents" data-escape="false">{{ __('action') }}</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>


            {{-- model --}}
            <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">{{__('edit')}} {{__('online')}} {{__('exam')}}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true"><i class="fa fa-close"></i></span>
                            </button>
                        </div>
                        <form id="edit-form" class="pt-3 edit-form" action="{{ url('online-exam') }}">
                            <input type="hidden" name="edit_id" id="edit_id">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>{{ __('title') }} <span class="text-danger">*</span></label>
                                    <input type="text" id="edit-online-exam-title" required name="edit_title" placeholder="{{ __('title') }}" class="form-control"/>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>{{ __('exam') }} {{__('key')}} <span class="text-danger">*</span></label>
                                        <input type="number" id="edit-online-exam-key" required name="edit_exam_key" placeholder="{{ __('exam_key') }}" class="form-control" readonly/>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>{{ __('duration') }} <span class="text-danger">*</span></label><span class="text-info small">( {{__('in_minutes')}} )</span>
                                        <input type="number" id="edit-online-exam-duration" required name="edit_duration" placeholder="{{ __('duration') }}" class="form-control"/>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>{{ __('start_date')}} <span class="text-danger">*</span></label>
                                        <input type="datetime-local" id="edit-online-exam-start-date" required name="edit_start_date" placeholder="{{__('start_date')}}" class='form-control'>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>{{ __('end_date') }} <span class="text-danger">*</span></label>
                                        <input type="datetime-local" id="edit-online-exam-end-date" required name="edit_end_date" placeholder="{{ __('end_date')}}" class='form-control'>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('close') }}</button>
                                <input class="btn btn-theme" type="submit" value={{ __('submit') }} />
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
        $(document).ready(function () {

            function random_exam_key() { 
                return Math.floor(100000 + Math.random() * 900000);
            }

            // Initialize the exam key if not set
            if ($("#key").val() === "") {
                let rndInt = random_exam_key();
                $('#key').attr("value", rndInt);
            }

            // Generate a new exam key when the form is submitted
            $("form").submit(function(event) {
                event.preventDefault();
                let rndInt = random_exam_key();
                $('#key').attr("value", rndInt);
            });

            // Cascading dropdown functionality
            $('#class-id').on('change', function() {
                var classId = $(this).val();
                var classSectionSelect = $('#class-section-id');
                var subjectSelect = $('#subject-id');
                
                // Reset dependent dropdowns
                classSectionSelect.empty().append('<option value="">-- {{ __('Select Class First') }} --</option>').prop('disabled', true);
                subjectSelect.empty().append('<option value="">-- {{ __('Select Section First') }} --</option>').prop('disabled', true);
                
                if (classId) {
                    // Enable class section dropdown
                    classSectionSelect.prop('disabled', false);
                    subjectSelect.prop('disabled', false);
                    // Load sections for the selected class
                    $.ajax({
                        url: '{{ route("online-exam.get-sections-by-class") }}',
                        type: 'GET',
                        data: { class_id: classId },
                        success: function(response) {
                            classSectionSelect.empty().append('<option value="placeholder">-- {{ __('Select Section') }} --</option>');
                            
                            if (response.length > 0) {
                                $.each(response, function(index, section) {
                                    classSectionSelect.append('<option value="' + section.id + '">' + section.full_name + '</option>');
                                });
                            } else {
                                classSectionSelect.append('<option value="data-not-found">-- {{ __('no_data_found') }} --</option>');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error loading sections:', error);
                            classSectionSelect.append('<option value="error">-- {{ __('Error loading sections') }} --</option>');
                        }
                    });
                }
            });

            $('#class-section-id').on('change', function() {
                var classSectionIds = $(this).val();
                var subjectSelect = $('#subject-id');
                
                // Reset subject dropdown
                subjectSelect.empty().append('<option value="">-- {{ __('Select Section First') }} --</option>').prop('disabled', true);
                
                if (classSectionIds && classSectionIds.length > 0 && !classSectionIds.includes('')) {
                    // Enable subject dropdown
                    subjectSelect.prop('disabled', false);
                    
                    // Load subjects for the selected sections
                    $.ajax({
                        url: '{{ route("online-exam.get-subjects-by-class-section") }}',
                        type: 'GET',
                        data: { class_section_id: classSectionIds },
                        success: function(response) {
                            subjectSelect.empty().append('<option value="">-- {{ __('Select Subject') }} --</option>');
                            
                            if (response.length > 0) {
                                $.each(response, function(index, subject) {
                                    subjectSelect.append('<option value="' + subject.subject_id + '" data-class-subject-id="' + subject.id + '">' + subject.subject_with_name + '</option>');
                                });
                            } else {
                                subjectSelect.append('<option value="data-not-found">-- {{ __('no_data_found') }} --</option>');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error loading subjects:', error);
                            subjectSelect.append('<option value="error">-- {{ __('Error loading subjects') }} --</option>');
                        }
                    });
                }
            });

            // Select All functionality for class sections
            $('#select-all').on('change', function() {
                var classSectionSelect = $('#class-section-id');
                var isChecked = $(this).is(':checked');
                
                if (isChecked) {
                    // Clear all selections first
                    classSectionSelect.val(null).trigger('change');
                    
                    // Get all valid option values (excluding placeholder and error options)
                    var validValues = [];
                    classSectionSelect.find('option').each(function() {
                        var $option = $(this);
                        var value = $option.val();
                        var text = $option.text().trim();
                        
                        // Only include options that have a value and are not placeholder/error options
                        if (value && 
                            value !== '' && 
                            value !== 'placeholder' &&
                            value !== 'data-not-found' && 
                            value !== 'error' &&
                            !text.includes('-- Select') &&
                            !text.includes('-- no_data_found') &&
                            !text.includes('-- Error')) {
                            validValues.push(value);
                        }
                    });
                    
                    // Select only valid values
                    if (validValues.length > 0) {
                        classSectionSelect.val(validValues).trigger('change');
                    }
                } else {
                    // Deselect all options
                    classSectionSelect.val(null).trigger('change');
                }
            });

            // Initialize Select2 for class sections dropdown
            $('#class-section-id').select2({
                placeholder: '-- {{ __('Select Section') }} --',
                allowClear: true
            });

            // Filter dropdown functionality
            $('#filter-class-id').on('change', function() {
                var classId = $(this).val();
                var classSectionSelect = $('#filter-class-section-id');
                var subjectSelect = $('#filter-subject-id');
                
                // Reset dependent dropdowns
                classSectionSelect.empty().append('<option value="">{{ __('all') }}</option>').prop('disabled', true);
                subjectSelect.empty().append('<option value="">{{ __('all') }}</option>').prop('disabled', true);
                
                if (classId) {
                    // Enable class section dropdown
                    classSectionSelect.prop('disabled', false);
                    
                    // Load sections for the selected class
                    $.ajax({
                        url: '{{ route("online-exam.get-sections-by-class") }}',
                        type: 'GET',
                        data: { class_id: classId },
                        success: function(response) {
                            classSectionSelect.empty().append('<option value="">{{ __('all') }}</option>');
                            
                            if (response.length > 0) {
                                $.each(response, function(index, section) {
                                    classSectionSelect.append('<option value="' + section.id + '">' + section.full_name + '</option>');
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error loading sections:', error);
                        }
                    });
                }
            });

            $('#filter-class-section-id').on('change', function() {
                var classSectionIds = $(this).val();
                var subjectSelect = $('#filter-subject-id');
                
                // Reset subject dropdown
                subjectSelect.empty().append('<option value="">{{ __('all') }}</option>').prop('disabled', true);
                
                if (classSectionIds && classSectionIds !== '') {
                    // Enable subject dropdown
                    subjectSelect.prop('disabled', false);
                    
                    // Load subjects for the selected sections
                    $.ajax({
                        url: '{{ route("online-exam.get-subjects-by-class-section") }}',
                        type: 'GET',
                        data: { class_section_id: [classSectionIds] },
                        success: function(response) {
                            subjectSelect.empty().append('<option value="">{{ __('all') }}</option>');
                            
                            if (response.length > 0) {
                                $.each(response, function(index, subject) {
                                    // Use class_subject_id (subject.id) as the value, not subject_id
                                    // The API returns 'id' as class_subject_id
                                    var classSubjectId = subject.id;
                                    subjectSelect.append('<option value="' + classSubjectId + '">' + subject.subject_with_name + '</option>');
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error loading subjects:', error);
                        }
                    });
                }
            });

            // Status filter change event - refresh table
            $('#filter-exam-status, #filter_session_year_id,#filter-class-id,#filter-class-section-id,#filter-subject-id').on('change', function() {
                $('#table_list').bootstrapTable('refresh');
            });

            // Row style function for linked exams with dynamic colors
            window.onlineExamRowStyle = function(row, index) {
                if (row.is_linked === 1 || row.is_linked === '1') {
                    return {
                        classes: 'linked-exam-row'
                    };
                }
                return {};
            };
            
            // Function to apply colors to linked rows
            function applyLinkedRowColors() {
                try {
                    var tableData = $('#table_list').bootstrapTable('getData');
                    if (!tableData || tableData.length === 0) {
                        return;
                    }
                    
                    $('#table_list tbody tr').each(function(index) {
                        var $row = $(this);
                        if (index >= tableData.length) {
                            return;
                        }
                        
                        var rowData = tableData[index];
                        
                        // Check for linked_color instead of is_linked, so all linked rows get colored border
                        if (rowData && rowData.linked_color) {
                            var color = rowData.linked_color;
                            
                            // Remove any existing border-left from cells
                            $row.find('td').css('border-left', 'none');
                            
                            // Apply the specific color for this linked exam to the row
                            $row.css({
                                'border-left': '4px solid ' + color,
                                'box-sizing': 'border-box'
                            });
                            
                            // Also add data attribute for reference
                            $row.attr('data-linked-color', color);
                        } else {
                            // Remove border if not linked
                            $row.css('border-left', '');
                            $row.removeAttr('data-linked-color');
                        }
                    });
                } catch (e) {
                    console.error('Error applying linked row colors:', e);
                }
            }
            
            // Apply styling after table loads
            $('#table_list').on('load-success.bs.table post-body.bs.table refresh.bs.table', function(e, data) {
                setTimeout(applyLinkedRowColors, 150);
            });
            
            // Also apply on initial load if table is already loaded
            setTimeout(applyLinkedRowColors, 500);

        });
    </script>
@endsection