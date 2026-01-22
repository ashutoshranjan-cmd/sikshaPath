@extends('layouts.master')

@section('title')
    {{ __('assign') . ' ' . __('elective') . ' ' . __('subject') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('manage') . ' ' . __('elective') . ' ' . __('subject') }}
            </h3>
        </div>

        <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('list') . ' ' . __('elective') . ' ' . __('subject') }}
                        </h4>

                        <div class="row" id="toolbar">
                            <div class="form-group col-sm-12 col-md-3">
                                <label for="session_year_id" class="filter-menu">{{ __('session_years') }}</label>
                                <select name="session_year_id" id="filter-session-year-id" class="form-control select2">
                                    <option value="">{{ __('select') . ' ' . __('session_years') }}</option>
                                    @foreach ($session_years as $session_year)
                                        <option value={{ $session_year->id }}
                                            {{ $session_year->default == 1 ? 'selected' : '' }}>{{ $session_year->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-sm-12 col-md-3">
                                <label for="class_section_id" class="filter-menu">{{ __('Class Section') }}</label>
                                <select name="class_section_id" id="filter-class-section-id" class="form-control select2">
                                    <option value="">{{ __('select') . ' ' . __('class_section') }}</option>
                                    @foreach ($class_sections as $class_section)
                                        <option value={{ $class_section->id }}
                                            data-class-id="{{ $class_section->class_id }}">
                                            {{ $class_section->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-sm-12 col-md-3">
                                <label for="elective_subject_id"
                                    class="filter-menu">{{ __('elective') . ' ' . __('subject') }}</label>
                                <select name="elective_subject_id" id="filter-elective-subject-id"
                                    class="form-control select2">
                                    <option value="">{{ __('select') . ' ' . __('subject') }}</option>
                                    <option value="data-not-found">-- {{ __('no_data_found') }} --</option>
                                </select>
                            </div>

                            <div class="form-group col-sm-12 col-md-3">
                                <label for="status" class="filter-menu">{{ __('status') }}</label>
                                <select name="status" id="filter-status" class="form-control select2">
                                    <option value="">{{ __('select') . ' ' . __('status') }}</option>
                                    <option value="complete">{{ __('complete') }}</option>
                                    <option value="incomplete">{{ __('incomplete') }}</option>
                                    <option value="not_assigned">{{ __('not_assigned') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <table aria-describedby="mydesc" class='table' id='table_list' data-toggle="table"
                                    data-url="{{ route('assign.elective.subject.show') }}" data-click-to-select="true"
                                    data-side-pagination="server" data-pagination="true"
                                    data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-toolbar="#toolbar"
                                    data-show-columns="true" data-show-refresh="true" data-fixed-columns="true"
                                    data-fixed-number="1" data-fixed-right-number="1" data-trim-on-search="false"
                                    data-mobile-responsive="true" data-sort-name="id" data-sort-order="desc"
                                    data-maintain-selected="true" data-export-types='["txt","excel"]'
                                    data-export-options='{ "fileName": "elective-subject-list-<?= date('d-m-y') ?>"
                                    ,"ignoreColumn": ["operate"]}'
                                    data-query-params="assignElectiveSubjectQueryParams">
                                    <thead>
                                        <tr>
                                            {{-- <th scope="col" data-field="state" data-checkbox="true"></th> --}}
                                            <th scope="col" data-field="no" data-sortable="false" data-visible="false">
                                                {{ __('no.') }}</th>
                                            <th scope="col" data-field="id" data-sortable="true" data-visible="false">
                                                {{ __('id') }}</th>
                                            <th scope="col" data-field="user.full_name" data-sortable="true"
                                                data-formatter="StudentNameFormatter">
                                                {{ __('student') }}</th>
                                            <th scope="col" data-field="status"
                                                data-formatter="assignElectiveSubjectStatusFormatter">
                                                {{ __('status') }}</th>
                                            <th scope="col" data-field="elective_subjects"
                                                data-formatter="assignElectiveSubjectsFormatter">
                                                {{ __('selected_subjects') }}</th>
                                            <th scope="col" data-field="operate"
                                                data-events="assignElectiveSubjectEvents"
                                                data-formatter="actionColumnFormatter">
                                                {{ __('action') }}</th>
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

    <!-- Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="assignElectiveSubjectModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom pb-3">
                    <h5 class="modal-title font-weight-bold" id="assignElectiveSubjectModalLabel">
                        {{ __('Assign Elective Subjects') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body px-4 py-3">
                    <!-- Student Name and Class Section -->
                    <div class="mb-4">
                        <h6 class="font-weight-bold mb-0" id="modal-student-name">Student Name • Class Section</h6>
                    </div>

                    <form class="section-edit-form" id="edit-form" action="{{ url('assign.elective.subject.update') }}"
                        novalidate="novalidate">
                        <input type="hidden" name="student_id" id="edit-student-id" value="" />
                        <input type="hidden" name="user_id" id="edit-user-id" value="" />
                        <input type="hidden" name="session_year_id" id="edit-session-year-id" value="" />
                        <input type="hidden" name="class_section_id" id="edit-class-section-id" value="" />

                        <!-- Elective Subject Group Selection -->
                        <div class="form-group mb-4">
                            <label for="edit-elective-subject-group-id" class="form-label font-weight-bold mb-2">
                                {{ __('Select Elective Group') }}
                            </label>
                            <select name="elective_subject_group_id" id="edit-elective-subject-group-id"
                                class="form-control" required>
                                <option value="">
                                    {{ __('select') . ' ' . __('elective') . ' ' . __('subject') . ' ' . __('group') }}
                                </option>
                            </select>
                        </div>

                        <!-- Validation Message -->
                        <div class="alert alert-info d-flex align-items-center mb-4" id="validation-message"
                            style="display: none !important; visibility: hidden !important; opacity: 0 !important; height: 0 !important; padding: 0 !important; margin: 0 !important; overflow: hidden !important;">
                            <i class="fa fa-info-circle mr-2"></i>
                            <span id="validation-text"></span>
                        </div>

                        <!-- Available Subjects Section -->
                        <div class="form-group mb-0">
                            <label class="form-label font-weight-bold mb-3">{{ __('Available Subjects') }}</label>
                            <div id="available-subjects-list" class="border rounded p-3 bg-light">
                                <p class="text-muted mb-0 text-center">
                                    {{ __('Please select an elective subject group first') }}</p>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top pt-3">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-success"
                        id="save-assignment-btn">{{ __('submit') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <style>
        #validation-message.hidden,
        #validation-message[style*="display: none"],
        #validation-message[style*="visibility: hidden"] {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            height: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
            overflow: hidden !important;
            border: none !important;
            line-height: 0 !important;
        }

        #validation-message.hidden *,
        #validation-message[style*="display: none"] *,
        #validation-message[style*="visibility: hidden"] * {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
        }
    </style>
    <script>
        $(document).ready(function() {
            var $tableList = $('#table_list');
            // Make electiveSubjectGroups globally accessible for actionEvents.js
            window.electiveSubjectGroups = @json($electiveSubjectGroups);

            // Handle class section change to populate elective subjects filter
            $('#filter-class-section-id').on('change', function() {
                var classId = $(this).find(':selected').data('class-id');

                // Reset elective subject filter dropdown
                $('#filter-elective-subject-id').html(
                    '<option value="">{{ __('select') }} {{ __('subject') }}</option>');

                if (classId) {
                    // Find elective subject groups for the selected class
                    var classData = window.electiveSubjectGroups.find(function(cls) {
                        return cls.id == classId;
                    });

                    if (classData && classData.elective_subject_groups && classData.elective_subject_groups
                        .length > 0) {
                        // Collect all subjects from all groups
                        var allSubjects = [];
                        classData.elective_subject_groups.forEach(function(group) {
                            if (group.subjects && group.subjects.length > 0) {
                                group.subjects.forEach(function(subject) {
                                    // Try to get the class_subject_id from different possible locations
                                    var classSubjectId = null;

                                    if (subject.pivot && subject.pivot.class_subject_id) {
                                        classSubjectId = subject.pivot.class_subject_id;
                                    } else if (subject.class_subject_id) {
                                        classSubjectId = subject.class_subject_id;
                                    } else if (subject.id) {
                                        classSubjectId = subject.id;
                                    }

                                    if (classSubjectId) {
                                        var subjectName = subject.name;
                                        if (subject.type) {
                                            subjectName += ' (' + subject.type + ')';
                                        }

                                        // Check if subject already added (avoid duplicates)
                                        if (!allSubjects.find(function(s) {
                                                return s.id == classSubjectId;
                                            })) {
                                            allSubjects.push({
                                                id: classSubjectId,
                                                name: subjectName
                                            });
                                        }
                                    }
                                });
                            }
                        });

                        // Populate subjects filter dropdown
                        if (allSubjects.length > 0) {
                            allSubjects.forEach(function(subject) {
                                $('#filter-elective-subject-id').prop('disabled', false);
                                $('#filter-elective-subject-id').append('<option value="' + subject
                                    .id + '">' + subject.name + '</option>');
                            });
                        } else {
                            $('#filter-elective-subject-id').append(
                                '<option value="data-not-found">-- {{ __('no_data_found') }} --</option>'
                                );
                        }
                    } else {
                        $('#filter-elective-subject-id').append(
                            '<option value="data-not-found">-- {{ __('no_data_found') }} --</option>');
                    }
                }

                // Refresh table when class section changes
                $('#table_list').bootstrapTable('refresh');
            });

            // Handle elective subject filter change
            $('#filter-elective-subject-id').on('change', function() {
                $('#table_list').bootstrapTable('refresh');
            });

            // Handle session year change
            $('#filter-session-year-id').on('change', function() {
                $('#table_list').bootstrapTable('refresh');
            });

            // Handle status filter change
            $('#filter-status').on('change', function() {
                $('#table_list').bootstrapTable('refresh');
            });
        });

        // Global function to open and populate the modal (accessible from actionEvents.js)
        function openAssignElectiveSubjectModal(rowData, sessionYearId, classSectionId) {
            // Use provided sessionYearId and classSectionId, or get from filters
            sessionYearId = sessionYearId || $('#filter-session-year-id').val() || '';
            classSectionId = classSectionId || $('#filter-class-section-id').val() || '';

            // Set hidden fields
            $('#edit-student-id').val(rowData.id);
            $('#edit-user-id').val(rowData.user_id || rowData.user?.id || '');
            $('#edit-session-year-id').val(sessionYearId);
            $('#edit-class-section-id').val(classSectionId);

            // Set student name and class section
            var studentName = '';
            if (rowData.user) {
                if (rowData.user.first_name) {
                    studentName = (rowData.user.first_name || '') + ' ' + (rowData.user.last_name || '');
                    studentName = studentName.trim();
                }
            }

            if (!studentName) {
                studentName = 'Student #' + rowData.id;
            }

            // Get class section name
            var classSectionName = '';
            if (rowData.class_section) {
                classSectionName = rowData.class_section.class.full_name;
            } else {
                var selectedSection = $('#filter-class-section-id').find(':selected').text();
                classSectionName = selectedSection || '';
            }

            // Display: "John Smith • 10A"
            var displayText = studentName;
            if (classSectionName) {
                displayText += ' • ' + classSectionName;
            }
            $('#modal-student-name').text(displayText);

            // Get class ID from selected class section
            var classId = $('#filter-class-section-id').find(':selected').data('class-id');

            // Get elective subject groups from row data or from window.electiveSubjectGroups variable
            var groupsData = null;
            var classData = null;

            // First, try to get class data from window.electiveSubjectGroups (has full subject data)
            if (classId) {
                classData = window.electiveSubjectGroups.find(function(cls) {
                    return parseInt(cls.id) === parseInt(classId);
                });
            }

            if (classData && classData.elective_subject_groups) {
                // Use groups from window.electiveSubjectGroups (has full subject data)
                groupsData = classData.elective_subject_groups;
            } else if (rowData.class_section && rowData.class_section.class && rowData.class_section.class
                .elective_subject_groups) {
                // Fallback to groups from row data, but enrich with subjects if available
                groupsData = rowData.class_section.class.elective_subject_groups;

                // If groups don't have subjects, try to enrich them from window.electiveSubjectGroups
                if (classData && classData.elective_subject_groups) {
                    groupsData = groupsData.map(function(group) {
                        var enrichedGroup = classData.elective_subject_groups.find(function(g) {
                            return parseInt(g.id) === parseInt(group.id);
                        });
                        return enrichedGroup || group;
                    });
                }
            }

            // Get currently assigned subjects for this student
            var assignedSubjectIds = getAssignedSubjectIds(rowData.user_id, sessionYearId, classSectionId);

            // Populate elective subject groups dropdown
            populateElectiveSubjectGroups(groupsData, assignedSubjectIds);

            // Show modal
            $('#editModal').modal('show');
        }

        // Global function to get assigned subject IDs for a student
        function getAssignedSubjectIds(userId, sessionYearId, classSectionId) {
            var assignedIds = [];
            // Get from table row data if available
            var $tableList = $('#table_list');
            var allRows = $tableList.bootstrapTable('getData');
            var rowData = allRows.find(function(row) {
                return row.user_id == userId;
            });

            if (rowData && rowData.assigned_class_subject_ids && Array.isArray(rowData.assigned_class_subject_ids)) {
                assignedIds = rowData.assigned_class_subject_ids;
            }

            return assignedIds;
        }

        // Global function to populate elective subject groups dropdown
        function populateElectiveSubjectGroups(groupsData, assignedSubjectIds) {
            var $groupSelect = $('#edit-elective-subject-group-id');
            $groupSelect.empty().append(
                '<option value="">{{ __('select') }} {{ __('elective') }} {{ __('subject') }} {{ __('group') }}</option>'
                );

            // Hide validation message initially when populating groups
            $('#validation-message').addClass('hidden').hide().css({
                'display': 'none !important',
                'visibility': 'hidden !important',
                'opacity': '0 !important',
                'height': '0 !important',
                'padding': '0 !important',
                'margin': '0 !important',
                'overflow': 'hidden !important'
            });

            if (!groupsData || groupsData.length === 0) {
                $('#group-info-text').text('{{ __('No elective subject groups found') }}');
                return;
            }

            // Populate groups dropdown
            groupsData.forEach(function(group) {
                // Use group name if available, otherwise use ID
                var optionText = group.name || ('Group #' + group.id);
                if (group.total_subjects && group.total_selectable_subjects) {
                    optionText += ' (' + group.total_selectable_subjects + ' of ' + group.total_subjects +
                        ' selectable)';
                }
                // Store only group ID in data attribute - we'll fetch full data from window.electiveSubjectGroups
                // This ensures we always get the complete group data with subjects
                $groupSelect.append('<option value="' + group.id + '">' + optionText + '</option>');
            });

            // Reinitialize select2
            if ($groupSelect.hasClass('select2-hidden-accessible')) {
                $groupSelect.select2('destroy');
            }

            // Remove any existing event handlers
            $groupSelect.off('change select2:select');

            // Initialize select2
            $groupSelect.select2({
                dropdownParent: $('#editModal'),
                width: '100%'
            });

            // Handle group selection change - use both change and select2:select events
            $groupSelect.on('change select2:select', function(e) {
                var selectedGroupId = $(this).val();

                // Always hide validation message first, then show if needed
                $('#validation-message').addClass('hidden').hide().css({
                    'display': 'none !important',
                    'visibility': 'hidden !important',
                    'opacity': '0 !important',
                    'height': '0 !important',
                    'padding': '0 !important',
                    'margin': '0 !important',
                    'overflow': 'hidden !important'
                }).removeClass('alert-info alert-warning alert-success');
                $('#validation-text').text('');

                if (selectedGroupId && selectedGroupId !== 'data-not-found') {
                    // Always get group from window.electiveSubjectGroups (has full subject data)
                    var groupData = findGroupInWindowData(selectedGroupId);

                    if (groupData) {
                        if (groupData.subjects && groupData.subjects.length > 0) {
                            populateSubjectsForGroup(groupData, assignedSubjectIds);
                        } else {
                            $('#available-subjects-list').html(
                                '<p class="text-muted mb-0 text-center">{{ __('No subjects found in this group') }}</p>'
                                );
                            $('#validation-message').addClass('hidden').hide().css({
                                'display': 'none !important',
                                'visibility': 'hidden !important',
                                'opacity': '0 !important',
                                'height': '0 !important',
                                'padding': '0 !important',
                                'margin': '0 !important',
                                'overflow': 'hidden !important'
                            });
                        }
                    } else {
                        $('#available-subjects-list').html(
                            '<p class="text-muted mb-0 text-center">{{ __('Group not found') }}</p>');
                        $('#validation-message').addClass('hidden').hide().css({
                            'display': 'none !important',
                            'visibility': 'hidden !important',
                            'opacity': '0 !important',
                            'height': '0 !important',
                            'padding': '0 !important',
                            'margin': '0 !important',
                            'overflow': 'hidden !important'
                        });
                    }
                } else {
                    // No group selected - hide validation message and show placeholder
                    $('#available-subjects-list').html(
                        '<p class="text-muted mb-0 text-center">{{ __('Please select an elective subject group first') }}</p>'
                        );
                    $('#validation-message').addClass('hidden').hide().css({
                        'display': 'none !important',
                        'visibility': 'hidden !important',
                        'opacity': '0 !important',
                        'height': '0 !important',
                        'padding': '0 !important',
                        'margin': '0 !important',
                        'overflow': 'hidden !important'
                    }).removeClass('alert-info alert-warning alert-success');
                    $('#validation-text').text('');
                }
            });

            // Helper function to find group in window.electiveSubjectGroups
            function findGroupInWindowData(selectedGroupId) {
                var classId = $('#filter-class-section-id').find(':selected').data('class-id');

                if (classId) {
                    var classData = window.electiveSubjectGroups.find(function(cls) {
                        return parseInt(cls.id) === parseInt(classId);
                    });

                    if (classData && classData.elective_subject_groups) {
                        var foundGroup = classData.elective_subject_groups.find(function(g) {
                            return parseInt(g.id) === parseInt(selectedGroupId);
                        });
                        return foundGroup || null;
                    }
                }

                // Fallback: search all classes for the group
                for (var i = 0; i < window.electiveSubjectGroups.length; i++) {
                    var cls = window.electiveSubjectGroups[i];
                    if (cls.elective_subject_groups) {
                        var foundGroup = cls.elective_subject_groups.find(function(g) {
                            return parseInt(g.id) === parseInt(selectedGroupId);
                        });
                        if (foundGroup) {
                            return foundGroup;
                        }
                    }
                }

                return null;
            }

        }

        // Global function to populate subjects for selected group
        function populateSubjectsForGroup(group, assignedSubjectIds) {
            var $subjectsList = $('#available-subjects-list');
            $subjectsList.html('<p class="text-muted mb-0 text-center">{{ __('Loading subjects...') }}</p>');

            var totalSelectable = group.total_selectable_subjects || 0;
            var subjectsHtml = '';

            // Check if subjects are available in the group
            if (group.subjects && group.subjects.length > 0) {
                group.subjects.forEach(function(subject) {
                    // Try to get the class_subject_id from different possible locations
                    var classSubjectId = null;

                    if (subject.pivot && subject.pivot.class_subject_id) {
                        classSubjectId = subject.pivot.class_subject_id;
                    } else if (subject.class_subject_id) {
                        classSubjectId = subject.class_subject_id;
                    } else if (subject.id) {
                        classSubjectId = subject.id;
                    }

                    if (classSubjectId) {
                        // Check if subject is already assigned
                        var isAssigned = assignedSubjectIds.some(function(id) {
                            return String(id) === String(classSubjectId);
                        });

                        var checked = isAssigned ? 'checked' : '';
                        var subjectCode = subject.code ? subject.code : '';
                        var subjectName = subject.name_with_type || 'Unknown Subject';

                        subjectsHtml += '<div class="card mb-3 border shadow-sm" style="border-radius: 8px;">';
                        subjectsHtml += '<div class="card-body p-3">';
                        subjectsHtml += '<div class="form-check">';
                        subjectsHtml += '<label class="form-check-label d-flex align-items-center" for="subject_' +
                            classSubjectId + '">';
                        subjectsHtml += '<input class="form-check-input subject-checkbox" type="checkbox" value="' +
                            classSubjectId + '" id="subject_' + classSubjectId + '" ' + checked + '>';
                        subjectsHtml += '<i class="input-helper"></i>';
                        subjectsHtml += '<div class="d-flex flex-column ml-2">';
                        subjectsHtml += '<strong class="mb-1" style="font-size: 15px;">' + subjectName +
                        '</strong>';
                        if (subjectCode) {
                            subjectsHtml += '<span class="text-muted" style="font-size: 13px;">Code: ' +
                                subjectCode + '</span>';
                        }
                        subjectsHtml += '</div>';
                        subjectsHtml += '</label>';
                        subjectsHtml += '</div>';
                        subjectsHtml += '</div>';
                        subjectsHtml += '</div>';
                    }
                });
            } else {
                subjectsHtml = '<p class="text-muted mb-0 text-center">{{ __('No subjects found in this group') }}</p>';
            }

            $subjectsList.html(subjectsHtml);

            // Show validation message if total_selectable is set
            if (totalSelectable > 0) {
                $('#validation-text').text('{{ __('You must select') }} ' + totalSelectable + ' {{ __('subjects') }}.');
                $('#validation-message').removeClass('hidden').css({
                    'display': 'flex',
                    'visibility': 'visible',
                    'opacity': '1',
                    'height': 'auto',
                    'padding': '',
                    'margin': '',
                    'overflow': 'visible'
                }).show();
            } else {
                $('#validation-message').addClass('hidden').hide().css({
                    'display': 'none !important',
                    'visibility': 'hidden !important',
                    'opacity': '0 !important',
                    'height': '0 !important',
                    'padding': '0 !important',
                    'margin': '0 !important',
                    'overflow': 'hidden !important'
                });
            }

            // Update validation on checkbox change
            $('.subject-checkbox').off('change').on('change', function() {
                updateValidationMessage(totalSelectable);
            });

            // Initial validation check
            updateValidationMessage(totalSelectable);
        }

        // Global function to update validation message
        function updateValidationMessage(totalSelectable) {
            var checkedCount = $('.subject-checkbox:checked').length;

            if (totalSelectable > 0) {
                if (checkedCount < totalSelectable) {
                    $('#validation-text').text('{{ __('You must select') }} ' + totalSelectable +
                        ' {{ __('subjects') }}. {{ __('Currently selected') }}: ' + checkedCount);
                    $('#validation-message').removeClass('hidden alert-success alert-warning').addClass('alert-info').css({
                        'display': 'flex',
                        'visibility': 'visible',
                        'opacity': '1',
                        'height': 'auto',
                        'padding': '',
                        'margin': '',
                        'overflow': 'visible'
                    }).show();
                } else if (checkedCount > totalSelectable) {
                    $('#validation-text').text('{{ __('You can select maximum') }} ' + totalSelectable +
                        ' {{ __('subjects') }}. {{ __('Currently selected') }}: ' + checkedCount);
                    $('#validation-message').removeClass('hidden alert-success alert-info').addClass('alert-warning').css({
                        'display': 'flex',
                        'visibility': 'visible',
                        'opacity': '1',
                        'height': 'auto',
                        'padding': '',
                        'margin': '',
                        'overflow': 'visible'
                    }).show();
                } else {
                    $('#validation-text').text('{{ __('Perfect! You have selected') }} ' + checkedCount +
                        ' {{ __('subjects') }}.');
                    $('#validation-message').removeClass('hidden alert-info alert-warning').addClass('alert-success').css({
                        'display': 'flex',
                        'visibility': 'visible',
                        'opacity': '1',
                        'height': 'auto',
                        'padding': '',
                        'margin': '',
                        'overflow': 'visible'
                    }).show();
                }
            } else {
                $('#validation-message').addClass('hidden').hide().css({
                    'display': 'none !important',
                    'visibility': 'hidden !important',
                    'opacity': '0 !important',
                    'height': '0 !important',
                    'padding': '0 !important',
                    'margin': '0 !important',
                    'overflow': 'hidden !important'
                });
            }
        }

        // Handle save button click
        $(document).ready(function() {
            $('#save-assignment-btn').on('click', function(e) {
                e.preventDefault();

                // Get all checked subjects
                var selectedSubjects = [];
                $('.subject-checkbox:checked').each(function() {
                    selectedSubjects.push($(this).val());
                });

                if (selectedSubjects.length === 0) {
                    showErrorToast('Please select at least one elective subject.');
                    return;
                }

                // Show loading
                var $submitBtn = $('#save-assignment-btn');
                var originalText = $submitBtn.html();
                $submitBtn.prop('disabled', true).html(
                    '<i class="fa fa-spinner fa-spin"></i> {{ __('Saving') }}...');

                // Submit all subjects as an array in a single request
                var studentId = $('#edit-student-id').val();
                var classSectionId = $('#edit-class-section-id').val();
                var sessionYearId = $('#edit-session-year-id').val();

                var formData = {
                    student_ids: studentId,
                    class_subject_ids: selectedSubjects, // Send as array
                    class_section_id: classSectionId,
                    session_year_id: sessionYearId
                };

                $.ajax({
                    url: '{{ route('assign.elective.subject.store') }}',
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $submitBtn.prop('disabled', false).html(originalText);

                        if (!response.error) {
                            showSuccessToast(response.message ||
                                'Elective subjects assigned successfully.');
                            $('#editModal').modal('hide');
                            $('#table_list').bootstrapTable('refresh');
                        } else {
                            showErrorToast(response.message ||
                                'Failed to assign elective subjects.');
                        }
                    },
                    error: function(xhr) {
                        $submitBtn.prop('disabled', false).html(originalText);
                        var errorMessage = 'Failed to assign elective subjects.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        showErrorToast(errorMessage);
                    }
                });
            });

            // Reset form when modal is closed
            $('#editModal').on('hidden.bs.modal', function() {
                $('#edit-form')[0].reset();
                var $groupSelect = $('#edit-elective-subject-group-id');
                // Destroy select2 before clearing
                if ($groupSelect.hasClass('select2-hidden-accessible')) {
                    $groupSelect.select2('destroy');
                }
                $groupSelect.empty().append(
                    '<option value="">{{ __('select') }} {{ __('elective') }} {{ __('subject') }} {{ __('group') }}</option>'
                    );
                $('#available-subjects-list').html(
                    '<p class="text-muted mb-0 text-center">{{ __('Please select an elective subject group first') }}</p>'
                    );
                // Hide validation message when modal is closed
                $('#validation-message').addClass('hidden').hide().css({
                    'display': 'none !important',
                    'visibility': 'hidden !important',
                    'opacity': '0 !important',
                    'height': '0 !important',
                    'padding': '0 !important',
                    'margin': '0 !important',
                    'overflow': 'hidden !important'
                });
                $('#validation-text').text('');
                $('#group-info-text').text('');
            });

            // Ensure select2 is properly initialized after modal is shown
            $('#editModal').on('shown.bs.modal', function() {
                var $groupSelect = $('#edit-elective-subject-group-id');
                // Only reinitialize if select2 is not already initialized
                if (!$groupSelect.hasClass('select2-hidden-accessible')) {
                    $groupSelect.select2({
                        dropdownParent: $('#editModal'),
                        width: '100%'
                    });
                }
            });
        });

        $('#table_list').bootstrapTable({
            formatNoMatches: function () {
                return "No elective subjects found.";
            }
        });
    </script>
@endsection
