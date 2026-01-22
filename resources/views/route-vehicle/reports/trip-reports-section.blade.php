<div class="col-md-12 grid-margin stretch-card">
    <div class="card">
        <div class="card-body">
            <input type="hidden" name="id" value="{{ $id }}" id="id">
            {{--<div class="row" id="toolbar">
                 <div class="form-group col-sm-12 col-md-4">
                    <label class="filter-menu"> {{ __('month') }}</label>
                    {!! Form::select('month', [], null, ['class' => 'form-control', 'id' => 'filter_month', 'placeholder' => __('all')]) !!}
                </div>

            </div> --}}

            <table aria-describedby="mydesc" class='table' id='table_list' data-toggle="table"
                data-url="{{ route('route-vehicle.trip-reports', [1]) }}" data-click-to-select="true" data-side-pagination="server"
                data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                data-show-columns="true" data-show-refresh="true" data-fixed-columns="false" data-fixed-number="2"
                data-fixed-right-number="1" data-trim-on-search="false" data-mobile-responsive="true"
                data-sort-name="id" data-sort-order="desc" data-maintain-selected="true" data-export-data-type='all'
                data-query-params="tripReportsQueryParams" data-toolbar="#toolbar"
                data-export-options='{ "fileName": "trip_reports-<?= date('d-m-y') ?>" ,"ignoreColumn":["operate"]}'
                data-show-export="true" data-escape="true">
                <thead>
                    <tr>
                        <th scope="col" data-field="id" data-sortable="true" data-visible="false">{{ __('id') }}
                        </th>
                        <th scope="col" data-field="route" data-sortable="false">{{ __('route') }}</th>
                        <th scope="col" data-field="trip_type" data-sortable="false">{{ __('trip_type') }}</th>
                        <th scope="col" data-field="pickup_point" data-sortable="false">{{ __('pickup_point') }}</th>
                        {{-- <th scope="col" data-field="title" data-sortable="false">{{ __('title') }}  --}}
                        </th>
                        <th scope="col" data-field="description" data-sortable="false">{{ __('description') }}
                        </th>
                        <th scope="col" data-field="date" data-sortable="false">
                            {{ __('date') }}</th>
                        <th scope="col" data-field="created_by" data-formatter="tripReportUserFormatter" data-sortable="false">{{ __('created_by') }}
                        </th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>