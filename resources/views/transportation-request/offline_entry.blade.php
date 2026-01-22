@extends('layouts.master')

@section('title')
    {{ __('transportation_offline_request_entry') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('transportation_offline_request_entry') }}
            </h3>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="card-title mb-4">
                                    {{ __('offline_request_entry') }}
                                </h4>
                            </div>
                            <div class="col-md-6 d-flex justify-content-end">
                                <a class="btn btn-sm btn-theme" style="height: 35px;"
                                    href="{{ route('transportation-requests.index') }}">{{ __('back') }}</a>
                            </div>
                        </div>
                        <form class="pt-3 create-form form-validation" method="post"
                            action="{{ route('transportation-requests.offline-entry.store') }}"
                            data-success-function="successFunction" novalidate="novalidate">
                            <input type="hidden" name="amount" id="amount">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="role_select">{{ __('role') }} <span class="text-danger">*</span></label>
                                    <select name="role_select" id="role_select"
                                        class="form-control select2-dropdown select2-hidden-accessible"
                                        data-live-search="true" required>
                                        <option value="" selected>{{ __('select_role') }}</option>
                                        <option value="Student">{{ __('student') }}</option>
                                        <option value="Teacher">{{ __('teacher') }}</option>
                                        <option value="Staff">{{ __('staff') }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6 d-none" id="class-section">
                                    <label for="classSectionId">{{ __('class_section') }} <span
                                            class="text-danger">*</span></label>
                                    <select name="class_section_id" id="classSectionId"
                                        class="form-control select2-dropdown select2-hidden-accessible"
                                        data-live-search="true" disabled>
                                        <option value="" selected>{{ __('select_class_section') }}</option>
                                        @foreach($class_sections as $class_section)
                                            <option value="{{ $class_section->id }}">
                                                {{ $class_section->full_name}}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6 d-none" id="student">
                                    <label for="student_id">{{ __('student') }} <span class="text-danger">*</span></label>
                                    <select name="user_id" id="student_id"
                                        class="form-control select2-dropdown select2-hidden-accessible"
                                        data-live-search="true" disabled>
                                        <option value="" selected>{{ __('select_student') }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6 d-none" id="teacher">
                                    <label for="teacher_id">{{ __('teacher') }} <span class="text-danger">*</span></label>
                                    <select name="user_id" id="teacher_id"
                                        class="form-control select2-dropdown select2-hidden-accessible"
                                        data-live-search="true" disabled>
                                        <option value="" selected>{{ __('select_teacher') }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6 d-none" id="staff">
                                    <label for="staff_id">{{ __('staff') }} <span class="text-danger">*</span></label>
                                    <select name="user_id" id="staff_id"
                                        class="form-control select2-dropdown select2-hidden-accessible"
                                        data-live-search="true" disabled>
                                        <option value="" selected>{{ __('select_staff') }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6" id="pickup_point">
                                    <label for="pickup_point_id">{{ __('pickup_point') }} <span
                                            class="text-danger">*</span></label>
                                    <select name="pickup_point_id" id="pickup_point_id"
                                        class="form-control select2-dropdown select2-hidden-accessible"
                                        data-live-search="true" disabled>
                                        <option value="" selected>{{ __('select_pickup_point') }}</option>
                                        @foreach($pickupPoints as $pickupPoint)
                                            <option value="{{ $pickupPoint->id }}">
                                                {{ $pickupPoint->name}}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6" id="fee">
                                    <label for="fee_id">{{ __('transportation_fees') }} <span
                                            class="text-danger">*</span></label>
                                    <select name="fee_id" id="fee_id"
                                        class="form-control select2-dropdown select2-hidden-accessible"
                                        data-live-search="true" disabled>
                                        <option value="">{{ __('select_transportation_fee') }}</option>
                                    </select>
                                </div>
                                {{-- @if($shifts)
                                <div class="form-group col-md-6" id="shift">
                                    <label for="shift_id">{{ __('Shift') }}</label>
                                    <select name="shift_id" id="shift_id"
                                        class="form-control select2-dropdown select2-hidden-accessible"
                                        data-live-search="true" disabled>
                                        <option value="">{{ __('Select Shift') }}</option>
                                        @foreach ($shifts as $shift)
                                        <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif --}}
                                
                                <div class="form-group col-md-6" id="vehicle_route">
                                    <label for="route_vehicle_id">{{ __('vehicle_route') }} <span
                                            class="text-danger">*</span></label>
                                    <select name="route_vehicle_id" id="route_vehicle_id"
                                        class="form-control select2-dropdown select2-hidden-accessible"
                                        data-live-search="true" disabled>
                                        <option value="">{{ __('select_vehicle/route') }}</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-6 d-none" id="amount_include">
                                    <label for="">{{ __('include_amount') }} <span class="text-danger">*</span></label>
                                    <div class="d-flex">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input type="radio" class="form-check-input include_amount"
                                                    id="include_amount_yes" name="include_amount" value="1"
                                                    checked>{{ __('Yes') }} </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input type="radio" class="form-check-input include_amount"
                                                    id="include_amount_no" name="include_amount" value="0">{{ __('no') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mode-container d-none" id="mode">
                                <div class="form-group col-sm-12 col-md-12">
                                    <label>{{ __('Mode') }} <span class="text-danger">*</span></label>
                                    <div class="d-flex">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input type="radio" name="mode" class="cash-compulsory-mode  mode" value="1"
                                                    checked>
                                                {{ __('cash') }}
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input type="radio" name="mode" class="cheque-compulsory-mode mode"
                                                    value="2">
                                                {{ __('cheque') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group cheque-no-container" style="display: none">
                                <label for="cheque_no">{{ __('cheque_no') }} <span class="text-danger">*</span></label>
                                <input type="number" id="cheque_no" name="cheque_no" placeholder="{{ __('cheque_no') }}"
                                    class="form-control cheque-no" required />
                            </div>
                            <input class="btn btn-theme float-right" type="submit" value={{ __('submit') }} />
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('js')
    <script>
        function resetForm() {
            // Reset all select fields
            $('#classSectionId, #student_id, #teacher_id, #staff_id, #pickup_point_id, #fee_id, #shift_id, #route_vehicle_id')
                .prop({ disabled: true, required: false }).val('').trigger('change');

            // Clear user lists
            $('#student_id').html('<option value="">{{ __("select_student") }}</option>');
            $('#teacher_id').html('<option value="">{{ __("select_teacher") }}</option>');
            $('#staff_id').html('<option value="">{{ __("select_staff") }}</option>');

            // Clear fee and vehicle route
            $('#fee_id').html('<option value="">{{ __("select_transportation_fee") }}</option>');
            $('#route_vehicle_id').html('<option value="">{{ __("select_vehicle/route") }}</option>');

            // Reset amount
            $('#amount').val('');
        }

        function showTeacherFields() {
            // Show only teacher field
            $('#class-section').addClass('d-none');
            $('#student').addClass('d-none');
            $('#teacher').removeClass('d-none');
            $('#amount_include').removeClass('d-none');
            $('#staff').addClass('d-none');
        }

        function showStaffFields() {
            // Show only staff field
            $('#class-section').addClass('d-none');
            $('#student').addClass('d-none');
            $('#teacher').addClass('d-none');
            $('#staff').removeClass('d-none');
            $('#amount_include').removeClass('d-none');
        }

        function showStudentFields() {
            // Show only student-related fields
            $('#class-section').removeClass('d-none');
            $('#student').removeClass('d-none');
            $('#teacher').addClass('d-none');
            $('#staff').addClass('d-none');
            $('#fee').removeClass('d-none');
            $('#amount_include').addClass('d-none');
        }

        const selectVehicleRouteText = @json(__('select_vehicle/route'));
        const selectStudentText = @json(__('select_student'));
        const selectTeacherText = @json(__('select_teacher'));
        const selectStaffText = @json(__('select_staff'));
        const feeText = @json(__('select_transportation_fee'));

        $('#role_select').on('select2:select', function () {
            const role = this.value;

            // Reset form first
            resetForm();

            if (role === 'Student') {
                // Show only student-related fields
                showStudentFields();

                // Enable class section selection
                $('#classSectionId').prop({ disabled: false, required: true });

                // Enable pickup point
                $('#pickup_point_id').prop('disabled', false);

                // Enable fee selection
                $('#fee_id').prop({ disabled: false, required: true });

                // Disable include_amount selection
                $('#include_amount_yes').prop({ disabled: true, required: false });
                $('#include_amount_no').prop({ disabled: true, required: false });

                // Enable shift selection
                $('#shift_id').prop('disabled', false);

                // Enable vehicle route selection
                $('#route_vehicle_id').prop('disabled', false);

                // Enable mode selection
                $('#mode').removeClass('d-none');

            } else if (role === 'Teacher') {
                // Show only teacher field
                showTeacherFields();

                // Enable teacher selection
                $('#teacher_id').prop({ disabled: false, required: true });

                // Enable pickup point
                $('#pickup_point_id').prop('disabled', false);

                // Enable shift selection
                $('#shift_id').prop('disabled', false);

                // Enable fee selection
                $('#fee_id').prop({ disabled: false, required: true });

                // Enable include_amount selection
                $('#include_amount_yes').prop({ disabled: false, required: false });
                $('#include_amount_no').prop({ disabled: false, required: false });

                // Enable vehicle route selection
                $('#route_vehicle_id').prop('disabled', false);

                // Enable mode selection
                $('#mode').addClass('d-none');

                // Populate teacher list
                fetch(`/transportation-requests/get-teachers`)
                    .then(response => response.json())
                    .then(result => {
                        const TeacherSelect = document.getElementById('teacher_id');
                        TeacherSelect.innerHTML = '<option value="">{{ __("select_teacher") }}</option>';
                        if (Array.isArray(result.data)) {
                            result.data.forEach(item => {
                                let option = document.createElement('option');
                                option.value = item.id;
                                option.text = `${item.full_name}`;
                                TeacherSelect.appendChild(option);
                            });
                        }
                    })
                    .catch(error => console.error('Error fetching teacher data:', error));

            } else if (role === 'Staff') {
                // Show only staff field
                showStaffFields();

                // Enable staff selection
                $('#staff_id').prop({ disabled: false, required: true });

                // Enable pickup point
                $('#pickup_point_id').prop('disabled', false);

                // Enable shift selection
                $('#shift_id').prop('disabled', false);

                // Enable fee selection
                $('#fee_id').prop({ disabled: false, required: true });

                // Enable include_amount selection
                $('#include_amount_yes').prop({ disabled: false, required: false });
                $('#include_amount_no').prop({ disabled: false, required: false });

                // Enable vehicle route selection
                $('#route_vehicle_id').prop('disabled', false);

                // Enable mode selection
                $('#mode').addClass('d-none');

                // Populate staff list
                fetch(`/transportation-requests/get-staff`)
                    .then(response => response.json())
                    .then(result => {
                        const StaffSelect = document.getElementById('staff_id');
                        StaffSelect.innerHTML = '<option value="">{{ __("select_staff") }}</option>';
                        if (Array.isArray(result.data)) {
                            result.data.forEach(item => {
                                let option = document.createElement('option');
                                option.value = item.id;
                                option.text = `${item.full_name}`;
                                StaffSelect.appendChild(option);
                            });
                        }
                    })
                    .catch(error => console.error('Error fetching staff data:', error));
            } else {
                $('#mode').addClass('d-none');
            }
        });

        // Class section change handler for students
        $('#classSectionId').on('select2:select', function () {
            const ClassSection_Id = this.value;
            const StudentSelect = document.getElementById('student_id');

            StudentSelect.innerHTML = `<option value="">${selectStudentText}</option>`;

            if (ClassSection_Id) {
                fetch(`/transportation-requests/get-students/${ClassSection_Id}`)
                    .then(response => response.json())
                    .then(result => {
                        const StudentSelect = document.getElementById('student_id');
                        StudentSelect.innerHTML = '<option value="">{{ __("select_student") }}</option>';
                        if (Array.isArray(result.data)) {
                            result.data.forEach(item => {
                                let option = document.createElement('option');
                                option.value = item.id;
                                option.text = `${item.full_name} - ${item.student.class_section.full_name}`;
                                StudentSelect.appendChild(option);
                            });
                        }
                        // Enable student selection after populating
                        $('#student_id').prop({ disabled: false, required: true });
                    })
                    .catch(error => console.error('Error fetching student data:', error));
            }
        });

        // Pickup point change handler
        $('#pickup_point_id').on('select2:select', function () {
            getPickupPoints();
        });

        // Fee change handler
        $('#fee_id').on('select2:select', function () {
            $('#amount').val($('#fee_id').find(':selected').data('amount'));
        });

        function getPickupPoints() {
            const pickupPointId = document.getElementById('pickup_point_id').value;
            //  const shiftId = document.getElementById('shift_id').value || 'null';
            const vehicleRouteSelect = document.getElementById('route_vehicle_id');
            const feeSelect = document.getElementById('fee_id');

            // Clear old options
            vehicleRouteSelect.innerHTML = `<option value="">${selectVehicleRouteText}</option>`;
            feeSelect.innerHTML = `<option value="">${feeText}</option>`;

            if (pickupPointId) {
                fetch(`/transportation-requests/get-vehicle-routes/${pickupPointId}`)
                    .then(response => response.json())
                    .then(result => {
                        const vehicleRouteSelect = document.getElementById('route_vehicle_id');
                        vehicleRouteSelect.innerHTML = '<option value="">{{ __("select_vehicle/route") }}</option>';
                        feeSelect.innerHTML = '<option value="">{{ __("select_transportation_fee") }}</option>';

                        if (Array.isArray(result.data)) {
                            result.data.forEach(item => {
                                let capacity = item.vehicle.capacity || 0;
                                let assigned = result.assignedCounts[item.id] || 0;
                                let remainingSeats = capacity - assigned;

                                // Get shift name if available
                                let shiftName = (item.route && item.route.shift && item.route.shift.name) ? ` - ${item.route.shift.name}` : '';

                                let option = document.createElement('option');
                                option.value = item.id;
                                option.text = `${item.vehicle.name} (${item.route.name}${shiftName}) - ${remainingSeats} out of ${capacity} seats are left`;
                                vehicleRouteSelect.appendChild(option);
                            });
                        }
                        if (Array.isArray(result.fees)) {
                            result.fees.forEach(fee => {
                                let option = document.createElement('option');
                                option.value = fee.id;
                                option.text = `Amount: ${fee.fee_amount} (${fee.duration} Days)`;
                                option.setAttribute('data-amount', fee.fee_amount);
                                feeSelect.appendChild(option);
                            });
                        }
                    })
                    .catch(error => console.error('Error fetching vehicle/route data:', error));
            }
        }

        function successFunction() {
            window.location.href = "{{route('transportation-requests.index')}}";
        }
    </script>
@endsection