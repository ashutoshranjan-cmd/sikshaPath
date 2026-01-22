@extends('layouts.master')

@section('title')
    {{ __('Staff Attendance') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('month_wise_staff_attendance') }}
            </h3>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('month_wise_staff_attendance') }}
                        </h4>
                        <div class="row" id="toolbar">
                            <div class="form-group col-sm-12 col-md-4">
                                <label for="session_year_id">{{__('session_year')}}</label>
                                {!! Form::select('session_year_id', $sessionYears, null, ['class' => 'form-control', 'id' => 'session_year_id']) !!}
                            </div>
                            <div class="form-group col-sm-12 col-md-4">
                                <label for="month">{{__('month')}}</label>
                                <select required name="month" id="month" class="form-control select2" style="width:100%;" tabindex="-1" aria-hidden="true">
                                    <option value="">{{__('select') . ' ' . __('Month')}}</option>
                                    <option value="1">{{__('January')}}</option>
                                    <option value="2">{{__('February')}}</option>
                                    <option value="3">{{__('March')}}</option>
                                    <option value="4">{{__('April')}}</option>
                                    <option value="5">{{__('May')}}</option>
                                    <option value="6">{{__('June')}}</option>
                                    <option value="7">{{__('July')}}</option>
                                    <option value="8">{{__('August')}}</option>
                                    <option value="9">{{__('September')}}</option>
                                    <option value="10">{{__('October')}}</option>
                                    <option value="11">{{__('November')}}</option>
                                    <option value="12">{{__('December')}}</option>
                                </select>
                            </div>
                            
                        </div>

                        <div class="show_month_wise_attendance">
                            <table aria-describedby="mydesc" class='table staff_table' id='table_list'
                                   data-toggle="table" data-url="{{ route('staff-attendance.month-wise.show') }}" data-click-to-select="true"
                                   data-side-pagination="server" data-pagination="true"
                                   data-page-list="[5, 10, 20, 50, 100, 200,All]" data-search="true" data-toolbar="#toolbar"
                                   data-show-columns="true" data-show-refresh="true" data-fixed-columns="false"
                                   data-fixed-number="2" data-fixed-right-number="1" data-trim-on-search="false"
                                   data-mobile-responsive="true" data-sort-name="id" data-sort-order="desc"
                                   data-maintain-selected="true" data-export-data-type='all' data-show-export="true"
                                   data-export-options='{ "fileName": "month-wise-staff-attendance-<?= date('d-m-y') ?>" ,"ignoreColumn": ["operate"]}'
                                   data-query-params="queryParams" data-escape="true">
                                <thead>
                                <tr>
                                    <th scope="col" data-field="id" data-sortable="true" data-visible="false">{{__('id')}}</th>
                                    <th scope="col" data-field="no">{{__('no.')}}</th>
                                    <th scope="col" data-field="name" data-sortable="false">{{__('name')}}</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function queryParams(p) {
            return {
                limit: p.limit,
                sort: p.sort,
                order: p.order,
                offset: p.offset,
                search: p.search,
                'session_year_id': $('#session_year_id').val(),
                'month': $('#month').val(),
            };
        }
    </script>

    <script>
        $('#session_year_id,#month').on('input change', function () {
            $('.staff_table').bootstrapTable('refresh');
        });
    </script>

<script>
        
    const sessionYearSelect = document.getElementById('session_year_id');
    const monthSelect = document.getElementById('month');

    async function handleSelectChange() {
        var month = $('#month').val();
        var session_year_id = $('#session_year_id').val();
        var table = $('#table_list');
        const response = await fetch(`/staff-attendance/month-wise/list?session_year_id=${session_year_id}&month=${month}`);
        const data = await response.json();
        table.bootstrapTable('load', data);
        try {
            // Fetch the attendance data
            // Ensure data is loaded before refreshing the table
                    
            // Update the table columns dynamically based on the month
            table.bootstrapTable('refreshOptions', {
                columns: [
                    {
                        field: 'user_id',
                        title: 'User ID'
                    },
                    {
                        field: 'full_name',
                        title: 'Staff Name'
                    },
                    ...generateDayColumns(month)
                ]
            });
            
            
        } catch (error) {
            console.error('Error fetching attendance data:', error);
        }
    }

    sessionYearSelect.addEventListener('change', handleSelectChange);
    monthSelect.addEventListener('change', handleSelectChange);

    function generateDayColumns(month) {
        var currentYear = new Date().getFullYear();
        const daysInMonth = new Date(currentYear, month, 0).getDate(); // Month is zero-indexed, so no need to subtract 1
        const columns = [];

        for (let day = 1; day <= daysInMonth; day++) {
            columns.push({
                field: `day_${day}`,
                title: `${day}`,
                formatter: attendanceFormatter
            });
        }
        return columns;
        
    }


    function attendanceFormatter(value, row, index) {
        if (value == 1) {
            return '<i class="fa fa-check text-success"></i>';
        } else if(value == 0) {
            return '<i class="fa fa-times text-danger"></i>';
        } else if(value == 3){
            return '<i class="fa fa-power-off text-info"></i>';
        }
        return '-'; // return empty string for no data
    }    

</script>
@endsection 