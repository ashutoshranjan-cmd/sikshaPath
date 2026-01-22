<div class="form-group col-sm-12 col-md-6">
    <input type="text" name="pickup_date" id="pickup_date" placeholder="Date" class="datepicker-popup form-control">
</div>
<div class="row">
    <!-- Pickup Trip -->
    <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Pickup Trip</h4>

                <div class="row mb-3">
                </div>

                <div id="pickup_trip_info" class="mb-3 p-3 border rounded bg-light">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <strong>Type:</strong> <span id="pickup_trip_type">—</span>
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Status:</strong> <span id="pickup_trip_status">—</span>
                        </div>
                    </div>
                </div>

                <table aria-describedby="pickup-trip-table" class="table table-bordered" id="pickup_table"
                    data-toggle="table" data-url="{{ route('route-vehicle.trip-details', [$id]) }}"
                    data-pagination="false" data-search="false" data-show-columns="false" data-show-refresh="true"
                    data-mobile-responsive="true" data-type="pickup" data-query-params="tripDetailsParamsPickup"
                    data-side-pagination="server" data-sort-order="asc">

                    <thead class="table-light">
                        <tr>
                            <th scope="col" data-field="name">Pickup Point Name</th>
                            <th scope="col" data-field="scheduled_time">Scheduled Time</th>
                            <th scope="col" data-field="actual_time">Actual Time</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Drop Trip -->
    <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Drop Trip</h4>

                <div class="row mb-3">
                   
                </div>

                <div id="drop_trip_info" class="mb-3 p-3 border rounded bg-light">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <strong>Type:</strong> <span id="drop_trip_type">—</span>
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Status:</strong> <span id="drop_trip_status">—</span>
                        </div>
                    </div>
                </div>

                <table aria-describedby="drop-trip-table" class="table table-bordered" id="drop_table"
                    data-toggle="table" data-url="{{ route('route-vehicle.trip-details', [$id]) }}"
                    data-pagination="false" data-search="false" data-show-columns="false" data-show-refresh="true"
                    data-mobile-responsive="true" data-type="drop" data-query-params="tripDetailsParamsDrop"
                    data-side-pagination="server" data-sort-order="asc">

                    <thead class="table-light">
                        <tr>
                            <th scope="col" data-field="name">Pickup Point Name</th>
                            <th scope="col" data-field="scheduled_time">Scheduled Time</th>
                            <th scope="col" data-field="actual_time">Actual Time</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // ----- Pickup Table -----
    function tripDetailsParamsPickup(p) {
        return {
            route_vehicle_id: {{ $id }},
            date: $('#pickup_date').val(),
            type: 'pickup'
        };
    }

    $('#pickup_date').on('change', function () {
        $('#pickup_table').bootstrapTable('refresh');
        $('#drop_table').bootstrapTable('refresh');
    });

    $('#pickup_table').on('load-success.bs.table', function (e, data) {
        if (data && data.trip_info) {
            $('#pickup_trip_type').text(data.trip_info.type || '—');
            $('#pickup_trip_status').text(data.trip_info.status || '—');
        }
    });

    // ----- Drop Table -----
    function tripDetailsParamsDrop(p) {
        return {
            route_vehicle_id: {{ $id }},
            date: $('#pickup_date').val(),
            type: 'drop'
        };
    }

    $('#drop_table').on('load-success.bs.table', function (e, data) {
        if (data && data.trip_info) {
            $('#drop_trip_type').text(data.trip_info.type || '—');
            $('#drop_trip_status').text(data.trip_info.status || '—');
        }
    });
</script>