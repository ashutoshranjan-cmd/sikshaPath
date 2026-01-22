@extends('layouts.master')

@section('title')
    {{ __('transportation_requests') }}
@endsection

@section('css')
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/css/bootstrap-select.min.css">
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('manage_transportation_requests') }}
            </h3>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center mb-3">
                            <div class="col-6">
                                <h4 class="card-title mb-0">
                                    {{ __('list_transportation_requests') }}
                                </h4>
                            </div>
                            <div class="col-6 text-right">
                                <a href="{{ route('transportation-requests.offline-entry') }}"><button id="offlineEntry"
                                        type="button" class="btn btn-theme">
                                        {{ __('offline_request_entry') }}
                                    </button></a>
                            </div>
                        </div>
                        <div class="row" id="toolbar">
                            @php
                                $pickupPoints = $transportationRequests->pluck('pickupPoint')->unique('id');
                            @endphp
                            <div class="form-group mb-2 mr-3" style="min-width: 150px;">
                                <label for="filter_pickup_point_id" class="filter-menu ">{{ __('pickup_point') }}</label>
                                <select name="filter_pickup_point_id" id="filter_pickup_point_id"
                                    class="form-control select2-dropdown select2-hidden-accessible">
                                    <option value=""> {{ __('select_pickup_point') }}</option>
                                    @foreach ($pickupPoints as $request)
                                        <option value="{{ $request->id }}">
                                            {{ $request->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @php
                                $shifts = $transportationRequests->pluck('shift')->filter()->unique('id');
                            @endphp
                            @if ($shifts->isNotEmpty())
                                <div class="form-group mb-2 mr-3">
                                    <label for="filter_shift_id" class="filter-menu ">{{ __('Shift') }}</label>
                                    <select name="filter_shift_id" id="filter_shift_id"
                                        class="form-control select2-dropdown select2-hidden-accessible">
                                        <option value=""> {{ __('Select Shift') }}</option>
                                        @foreach ($shifts as $shift)
                                            <option value="{{ $shift->id ?? "" }}">
                                                {{ $shift->name ?? "" }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="form-group mb-2 mr-3">
                                <label for="filter_vehicle_route_id" class="filter-menu">{{ __('vehicle_route') }}</label>
                                <select name="filter_vehicle_route_id" id="filter_vehicle_route_id"
                                    class="form-control select2-dropdown select2-hidden-accessible">
                                    <option value=""> {{ __('select_vehicle/route') }}</option>
                                </select>
                            </div>
                            <div class="form-group mb-2 mr-3">
                                <button id="update-status" class="btn btn-success" disabled>
                                    <span class="update-status-btn-name">{{ __('assign') }}</span>
                                </button>
                            </div>
                            <div class="form-group mb-2 mr-3" style="min-width: 150px;">
                                <label for="filter_include_amount" class="filter-menu">{{ __('include_amount') }}</label>
                                <select name="filter_include_amount" id="filter_include_amount"
                                    class="form-control select2-dropdown select2-hidden-accessible">
                                    <option value=""> {{ __('select_status') }}</option>
                                    <option value="1"> {{ __('Yes') }}</option>
                                    <option value="0"> {{ __('no') }}</option>
                                    <option value="2"> {{ __('not_specified') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 text-right">
                            <b><a href="#" class="table-list-type active mr-2 text-danger"
                                    data-id="0">{{__('unassigned')}}</a></b> | <a href="#"
                                class="ml-2 table-list-type text-success" data-id="1">{{__("assigned")}}</a>
                        </div>
                        <table aria-describedby="mydesc" class='table' id='table_list' data-toggle="table"
                            data-url="{{ route('transportation-requests.show', [1]) }}" data-click-to-select="true"
                            data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]"
                            data-search="true" data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true"
                            data-trim-on-search="false" data-mobile-responsive="true" data-sort-name="id"
                            data-sort-order="desc" data-maintain-selected="true" data-export-data-type='all'
                            data-export-options='{ "fileName": "{{__('transportation_requests') }}-<?= date(' d-m-y') ?>" ,"ignoreColumn":["operate"]}'
                            data-show-export="true" data-query-params="transportationRequestQueryParams" data-escape="true">
                            <thead>
                                <tr>
                                    <th data-field="state" data-checkbox="true"></th>
                                    <th scope="col" data-field="id" data-sortable="true" data-visible="false">{{ __('id') }}
                                    </th>
                                    <th scope="col" data-field="no">{{ __('no.') }}</th>
                                    <th scope="col" data-field="user.full_name" data-formatter="StudentNameFormatter">
                                        {{ __('name') }}
                                    </th>
                                    <th scope="col" data-field="role">
                                        {{ __('role') }}
                                    </th>
                                    <th scope="col" data-field="pickup_point.name">{{__('pickup_point')}}</th>
                                    <th scope="col" data-field="include_amount">{{__('include_amount')}}</th>
                                    @if ($shifts->isNotEmpty())
                                        <th scope="col" data-field="shift.name">{{__('Shift')}}</th>
                                    @endif
                                    <th scope="col" data-field="operate" data-formatter="actionColumnFormatter"
                                        data-events="transportationRequestEvents" data-escape="false">{{ __('action') }}
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editTransportationRequestLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editTransportationRequestLabel">
                            {{ __('assign_transportation_requests') }}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true"><i class="fa fa-close"></i></span>
                        </button>
                    </div>
                    <form id="edit-form" class="pt-3 edit-form" action="{{ url('transportation-requests') }}">
                        <input type="hidden" id="edit_request_id" name="edit_request_id">

                        <div class="modal-body">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="edit_user_name">{{ __('name') }}</label>
                                    <input type="text" id="edit_user_name" class="form-control" readonly>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="edit_pickup_point">{{ __('pickup_point') }}</label>
                                    <input type="text" id="edit_pickup_point" class="form-control" readonly>
                                </div>
                                @if ($shifts->isNotEmpty())
                                    <div class="form-group col-md-6">
                                        <label for="edit_shift">{{ __('Shift') }}</label>
                                        <input type="text" id="edit_shift" class="form-control" readonly>
                                    </div>
                                @endif
                                <div class="form-group col-md-6">
                                    <label for="edit_route_id">{{ __('vehicle/route') }} <span
                                            class="text-danger">*</span></label>
                                    <select id="edit_route_id" name="edit_route_id"
                                        class="form-control select2-dropdown select2-hidden-accessible"
                                        data-live-search="true">
                                        <option value="">{{ __('select_vehicle/route') }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6 d-none" id="amount_include">
                                    <div class="mt-4 mb-4"><span class="text-danger">*If not specified, the selected value will remain effective.</span></div>
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
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('close') }}</button>
                            <input type="submit" class="btn btn-theme" value="{{ __('submit') }}">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/js/bootstrap-select.min.js"></script>
    <script>
        function updateUserStatus(tableId, buttonClass) {
            var selectedRows = $(tableId).bootstrapTable('getSelections');
            var selectedRowsValues = selectedRows.map(function (row) {
                return row.id;
            });
            userIds = JSON.stringify(selectedRowsValues);

            if (buttonClass != null) {
                if (selectedRowsValues.length) {
                    $(buttonClass).prop('disabled', false);
                } else {
                    $(buttonClass).prop('disabled', true);
                }
            }
        }

        $('#table_list').bootstrapTable({
            onCheck: function (row) {
                updateUserStatus("#table_list", '#update-status');
            },
            onUncheck: function (row) {
                updateUserStatus("#table_list", '#update-status');
            },
            onCheckAll: function (rows) {
                updateUserStatus("#table_list", '#update-status');
            },
            onUncheckAll: function (rows) {
                updateUserStatus("#table_list", '#update-status');
            }
        });
        $("#update-status").on('click', function (e) {
            Swal.fire({
                title: window.trans["Are you sure"],
                text: window.trans["Change Status For Selected Users"],
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: window.trans["Yes, Change it"],
                cancelButtonText: window.trans["Cancel"]
            }).then((result) => {
                if (result.isConfirmed) {
                    let url = baseUrl + '/transportation-requests/change-status-bulk';
                    let data = new FormData();
                    let routeId = $('#filter_vehicle_route_id').val();
                    data.append("ids", userIds)
                    data.append("vehicle_route", routeId)

                    function successCallback(response) {
                        $('#table_list').bootstrapTable('refresh');
                        $('#update-status').prop('disabled', true);
                        userIds = null;
                        showSuccessToast(response.message);
                    }

                    function errorCallback(response) {
                        showErrorToast(response.message);
                    }

                    ajaxRequest('POST', url, data, null, successCallback, errorCallback);
                }
            })
        })
        const selectVehicleRouteText = @json(__('select_vehicle/route'));
        $(document).ready(function () {
            const shiftSelect = document.getElementById("filter_shift_id");
            if (shiftSelect) {
                $('#filter_pickup_point_id').on('select2:select', function (e) {
                    const shiftId = document.getElementById("filter_shift_id").value;
                    const pickupPointId = document.getElementById("filter_pickup_point_id").value;
                    if (shiftId && pickupPointId) {
                        getRouteVehicle(pickupPointId, shiftId)
                    } else {
                        $('#filter_vehicle_route_id').empty().append($('<option>', { value: '', text: "{{ __('select_vehicle/route') }}" })).val('').trigger('change');
                    }
                })
                $('#filter_shift_id').on('select2:select', function (e) {
                    const shiftId = document.getElementById("filter_shift_id").value;
                    const pickupPointId = document.getElementById("filter_pickup_point_id").value;
                    if (shiftId && pickupPointId) {
                        getRouteVehicle(pickupPointId, shiftId)
                    } else {
                        $('#filter_vehicle_route_id').empty().val('').trigger('change');
                    }
                })
            } else {
                $('#filter_pickup_point_id').on('select2:select', function (e) {
                    const pickupPointId = document.getElementById("filter_pickup_point_id").value;
                    getRouteVehicle(pickupPointId, shiftId = 'null')
                });
            }
        });
        function getRouteVehicle(pickupPointId) {
            $('#filter_vehicle_route_id').select2({
                placeholder: selectVehicleRouteText,
                allowClear: true,
                width: '100%',
                templateResult: function (data) {
                    if (!data.id) return data.text;

                    let remaining = $(data.element).data('remaining');
                    let capacity = $(data.element).data('capacity');
                    let text = $(data.element).data('vehiclename') + ' (' + $(data.element).data('routename') + $(data.element).data('shiftname') + ')';

                    // Choose color
                    let color = 'green';
                    if (remaining <= 0) {
                        color = 'red';
                    } else if (remaining < 5) {
                        color = 'orange';
                    }

                    // Build HTML with only remaining seats in color
                    return $(
                        `<span>${text} - <span style="color:${color}">${remaining} out of ${capacity} seats are left</span></span>`
                    );
                },
                templateSelection: function (data) {
                    if (!data.id) return data.text;

                    let remaining = $(data.element).data('remaining');
                    let capacity = $(data.element).data('capacity');
                    let text = $(data.element).data('vehiclename') + ' (' + $(data.element).data('routename') + $(data.element).data('shiftname') + ')';

                    let color = 'green';
                    if (remaining <= 0) {
                        color = 'red';
                    } else if (remaining < 5) {
                        color = 'orange';
                    }

                    return $(
                        `<span>${text} - <span style="color:${color}">${remaining} out of ${capacity} seats are left</span></span>`
                    );
                }
            });
            Callajax(pickupPointId)
        }
        function Callajax(pickupPointId) {
            const vehicleRouteSelect = $('#filter_vehicle_route_id');

            // Clear old options
            vehicleRouteSelect.empty().append(
                $('<option>', { value: '', text: selectVehicleRouteText })
            );
            fetch(`/transportation-requests/get-vehicle-routes/${pickupPointId}`)
                .then(response => response.json())
                .then(result => {
                    vehicleRouteSelect.empty().append(
                        $('<option>', { value: '', text: "{{ __('select_vehicle/route') }}" })
                    );

                    if (Array.isArray(result.data)) {
                        result.data.forEach(item => {
                            let capacity = item.vehicle.capacity || 0;
                            let assigned = result.assignedCounts[item.id] || 0;
                            let remainingSeats = capacity - assigned;
                            let shiftName = (item.route && item.route.shift && item.route.shift.name) ? ` - ${item.route.shift.name}` : '';

                            vehicleRouteSelect.append(
                                $('<option>', {
                                    value: item.id,
                                    text: `${item.vehicle.name} (${item.route.name})`
                                })
                                    .attr('data-vehiclename', item.vehicle.name)
                                    .attr('data-shiftName', shiftName)
                                    .attr('data-routename', item.route.name)
                                    .attr('data-capacity', capacity)
                                    .attr('data-remaining', remainingSeats)
                            );
                        });
                    }
                    vehicleRouteSelect.trigger('change');
                })
                .catch(error => console.error('Error fetching vehicle/route data:', error));
        }
    </script>
@endsection