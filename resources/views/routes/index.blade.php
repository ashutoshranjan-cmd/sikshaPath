@extends('layouts.master')

@section('title')
    {{ __('routes') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('manage_routes') }}
            </h3>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('create_route') }}
                        </h4>
                        <form class="pt-3 route-create-form" id="create-form" action="{{ route('routes.store') }}"
                            method="POST" novalidate="novalidate">
                            @csrf
                            <div class="row">
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('name') }} <span class="text-danger">*</span></label>
                                    <input name="name" type="text" placeholder="{{ __('name') }}"
                                        class="form-control" required />
                                </div>
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('distance') }} (km)</label>
                                    <input name="distance" type="number" step="0.01" placeholder="{{ __('distance') }}"
                                        class="form-control" />
                                </div>
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('Shift') }}</label>
                                    <select name="shift_id" class="form-control" required>
                                        <option value="">{{ __('Select Shift') }}</option>
                                        @foreach ($shifts as $shift)
                                            <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('status') }}</label>
                                    <select name="status" class="form-control">
                                        <option value="1">{{ __('Active') }}</option>
                                        <option value="0">{{ __('Inactive') }}</option>
                                    </select>
                                </div>


                                <!-- Pickup Points Repeater Section -->
                                <div class="form-group col-sm-6 col-md-12">
                                    <label>{{ __('pickup_points_with_time') }}</label>
                                    <hr>
                                    <div class="pickup-points-repeater">
                                        <div data-repeater-list="pickup_points">
                                            <div data-repeater-item>
                                                <div class="row">
                                                    <div class="form-group col-md-4">
                                                        <label>{{ __('pickup_point') }}</label>
                                                        <select name="pickup_point_id" class="form-control" required>
                                                            <option value="">{{ __('select_pickup_point') }}</option>
                                                            @foreach ($pickupPoints as $pickupPoint)
                                                                <option value="{{ $pickupPoint->id }}">
                                                                    {{ $pickupPoint->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-md-3">
                                                        <label>{{ __('pickup_time') }}</label>
                                                        <input type="time" name="pickup_time" class="form-control"
                                                            placeholder="{{ __('pickup_time') }}" required>
                                                    </div>
                                                    <div class="form-group col-md-3">
                                                        <label>{{ __('drop_time') }}</label>
                                                        <input type="time" name="drop_time" class="form-control"
                                                            placeholder="{{ __('drop_time') }}" required>
                                                    </div>
                                                    <div class="form-group col-md-2 pl-0 mt-4" data-repeater-delete>
                                                        <button type="button"
                                                            class="btn btn-inverse-danger btn-icon remove-pickup-point">
                                                            <i class="fa fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row col-md-4 mt-3 mb-3">
                                            <button type="button" class="btn btn-success add-pickup-point"
                                                title="Add new pickup point" data-repeater-create>
                                                <i class="fa fa-plus"></i> {{ __('add_pickup_point') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input class="btn btn-theme float-right" id="create-btn" type="submit"
                                value={{ __('submit') }}>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('list_routes') }}
                        </h4>
                        <table aria-describedby="mydesc" class='table' id='table_list' data-toggle="table"
                            data-url="{{ route('routes.show', [1]) }}" data-click-to-select="true"
                            data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]"
                            data-search="true" data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true"
                            data-fixed-right-number="1" data-trim-on-search="false" data-mobile-responsive="true"
                            data-sort-name="id" data-sort-order="desc" data-maintain-selected="true"
                            data-query-params="queryParams" data-show-export="true" data-fixed-columns="false"
                            data-fixed-number="2" data-fixed-right-number="1"
                            data-export-options='{"fileName": "routes-list-<?= date('d-m-y') ?>","ignoreColumn":
                            ["operate"]}'
                            data-escape="true">
                            <thead>
                                <tr>
                                    <th scope="col" data-field="id" data-sortable="true" data-visible="false">
                                        {{ __('id') }}</th>
                                    <th scope="col" data-field="no">{{ __('no.') }}</th>
                                    <th scope="col" data-field="name" data-sortable="true">{{ __('name') }}</th>
                                    <th scope="col" data-field="distance" data-sortable="true">{{ __('distance') }}
                                    </th>
                                    <th scope="col" data-field="shift_name" data-sortable="false">{{ __('Shift') }}
                                    </th>
                                    <th scope="col" data-field="status" data-formatter="activeStatusFormatter"
                                        data-sortable="false">{{ __('status') }}</th>
                                    <th scope="col" data-field="pickup_points_count" data-sortable="false">
                                        {{ __('pickup_points') }}</th>
                                    <th scope="col" data-field="created_at" data-sortable="true"
                                        data-visible="false">{{ __('created_at') }}</th>
                                    <th scope="col" data-field="updated_at" data-sortable="true"
                                        data-visible="false">{{ __('updated_at') }}</th>
                                    <th scope="col" data-field="operate" data-formatter="actionColumnFormatter" data-escape="false">{{ __('action') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
