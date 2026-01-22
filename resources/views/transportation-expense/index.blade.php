@extends('layouts.master')

@section('title')
    {{ __('transportation_expense') }}
@endsection

@section('css')
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/css/bootstrap-select.min.css">
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('manage_transportation_expense') }}
            </h3>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center mb-3">
                            <div class="col-6">
                                <h4 class="card-title">
                                    {{ __('create_transportation_expense') }}
                                </h4>
                            </div>
                            <div class="col-6 text-right">
                                <a href="{{ route('expense-category.index') }}"><button id="manage_category" type="button"
                                        class="btn btn-theme">
                                        {{ __('manage_category') }}
                                    </button></a>
                            </div>
                        </div>

                        <form class="pt-3 create-form" id="create-form" action="{{ route('transportation-expense.store') }}"
                            method="POST" novalidate="novalidate" enctype="multipart/form-data">
                            <div class="row">
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('select_vehicle') }} <span class="text-danger">*</span></label>
                                    <select name="vehicle_id" id="vehicle_id"
                                        class="form-control select2-dropdown select2-hidden-accessible"
                                        data-live-search="true" required>
                                        <option value="">{{ __('select_vehicle') }}</option>
                                        @foreach ($vehicles as $vehicle)
                                            <option value="{{ $vehicle->id }}">
                                                {{ $vehicle->name . " (" . $vehicle->vehicle_number . ")" }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('select') }} {{ __('category') }} <span
                                            class="text-danger">*</span></label>
                                    {!! Form::select('category_id', $expenseCategory, null, ['required', 'class' => 'form-control select2-dropdown select2-hidden-accessible', 'placeholder' => __('select') . ' ' . __('category'), 'data-live-search' => "true", 'id' => 'category_id']) !!}
                                </div>

                                <div class="form-group col-sm-12 col-md-4">
                                    <label for="title">{{ __('title') }} <span class="text-danger">*</span></label>
                                    <input name="title" id="title" type="text" placeholder="{{ __('title') }}"
                                        class="form-control" required />
                                </div>

                                <div class="form-group col-sm-12 col-md-4">
                                    <label for="ref_no">{{ __('reference_no') }}</label>
                                    <input name="ref_no" id="ref_no" type="text" placeholder="{{ __('reference_no') }}"
                                        class="form-control" />
                                </div>

                                <div class="form-group col-sm-12 col-md-4">
                                    <label for="amount">{{ __('Amount') }} <span class="text-danger">*</span></label>
                                    <input name="amount" id="amount" type="number" placeholder="{{ __('Amount') }}"
                                        class="form-control" required />
                                </div>

                                <div class="form-group col-sm-12 col-md-4">
                                    <label for="date">{{ __('date') }} <span class="text-danger">*</span></label>
                                    <input name="date" id="date" type="text" placeholder="{{ __('date') }}"
                                        class="datepicker-popup-no-future form-control" autocomplete="off" required />
                                </div>

                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('image/pdf') }}</label>
                                    <input type="file" name="image_pdf" class="file-upload-default"
                                        accept="image/*,application/pdf" />
                                    <div class="input-group col-xs-12">
                                        <input type="text" class="form-control file-upload-info" disabled=""
                                            placeholder="{{ __('image/pdf') }}" />
                                        <span class="input-group-append">
                                            <button class="file-upload-browse btn btn-theme"
                                                type="button">{{ __('upload') }}</button>
                                        </span>
                                    </div>
                                </div>

                                <div class="form-group col-sm-12 col-md-4">
                                    <label for="">{{ __('select') }} {{ __('session_year') }}</label>
                                    {!! Form::select('session_year_id', $sessionYear, $current_session_year->id, ['required', 'class' => 'form-control select2-dropdown select2-hidden-accessible', 'id' => 'session_year_id', 'data-live-search' => "true"]) !!}
                                </div>

                                <div class="form-group col-sm-12 col-md-4">
                                    <label for="description">{{ __('description') }} </label>
                                    <textarea name="description" id="description" placeholder="{{ __('description') }}"
                                        class="form-control"></textarea>
                                </div>

                            </div>
                            <input class="btn btn-theme float-right ml-3" id="create-btn" type="submit" value={{ __('submit') }}>
                            <input class="btn btn-secondary float-right" type="reset" value={{ __('reset') }}>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{{ __('list_expense') }}</h4>
                        <div class="row" id="toolbar">

                            <div class="form-group col-sm-12 col-md-4">
                                <label class="filter-menu">{{ __('category') }}</label>
                                {!! Form::select('category_id', $expenseCategory, null, ['class' => 'form-control select2-dropdown select2-hidden-accessible', 'id' => 'filter_category_id', 'placeholder' => __('all'), 'data-live-search' => "true"]) !!}
                            </div>

                            <div class="form-group col-sm-12 col-md-4">
                                <label class="filter-menu"> {{ __('vehicle') }}</label>
                                <select name="vehicle_id" id="filter_vehicle_id"
                                    class="form-control select2-dropdown select2-hidden-accessible" data-live-search="true"
                                    required>
                                    <option value="">{{ __('all') }}</option>
                                    @foreach ($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}">
                                            {{ $vehicle->name . " (" . $vehicle->vehicle_number . ")" }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-sm-12 col-md-4">
                                <label class="filter-menu">{{ __('session_year') }}</label>
                                {!! Form::select('session_year_id', $sessionYear, null, ['class' => 'form-control select2-dropdown select2-hidden-accessible', 'id' => 'filter_session_year_id', 'placeholder' => __('all'), 'data-live-search' => "true"]) !!}
                            </div>

                        </div>

                        <table aria-describedby="mydesc" class='table' id='table_list' data-toggle="table"
                            data-url="{{ route('transportation-expense.show', [1]) }}" data-click-to-select="true"
                            data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]"
                            data-search="true" data-show-columns="true" data-show-refresh="true" data-fixed-columns="false"
                            data-fixed-number="2" data-fixed-right-number="1" data-trim-on-search="false"
                            data-mobile-responsive="true" data-sort-name="date" data-sort-order="desc"
                            data-maintain-selected="true" data-export-data-type='all'
                            data-query-params="TransportationExpenseQueryParams" data-toolbar="#toolbar"
                            data-export-options='{ "fileName": "expense-list-<?= date('d-m-y') ?>" ,"ignoreColumn":["operate"]}'
                            data-show-export="true" data-show-footer="true" data-escape="true">
                            <thead>
                                <tr>
                                    <th scope="col" data-field="id" data-sortable="true" data-visible="false">{{ __('id') }}
                                    </th>
                                    <th scope="col" data-field="no">{{ __('no.') }}</th>
                                    <th scope="col" data-field="ref_no" data-sortable="false">{{ __('reference_no') }}</th>
                                    <th scope="col" data-field="vehicle" data-sortable="false">{{ __('vehicle') }}</th>
                                    <th scope="col" data-field="title" data-sortable="false">{{ __('title') }}</th>
                                    <th scope="col" data-field="category.name" data-sortable="false">{{ __('category') }}
                                    </th>
                                    <th scope="col" data-field="description" data-sortable="false">{{ __('description') }}
                                    </th>
                                    <th scope="col" data-field="date" data-sortable="false"
                                        data-footer-formatter="totalFormatter">{{ __('date') }}</th>
                                    <th scope="col" data-field="amount" data-sortable="false"
                                        data-formatter="amountFormatter" data-footer-formatter="totalAmountFormatter">
                                        {{ __('Amount') }}
                                    </th>
                                    <th scope="col" data-field="created_by.full_name"
                                        data-formatter="CreatedByNameFormatter" data-sortable="false">{{ __('created_by') }}
                                    </th>
                                    <th scope="col" data-field="file" data-escape="false" data-sortable="false">
                                        {{ __('file') }}
                                    </th>
                                    <th scope="col" data-field="operate" data-events="transportationExpenseEvents"
                                        data-escape="false">
                                        {{ __('action') }}
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">{{ __('edit_transportation_expense') }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form class="pt-3 edit-form" id="" action="{{ url('transportation-expense') }}"
                            novalidate="novalidate">
                            @csrf
                            <div class="modal-body">
                                <input type="hidden" name="id" id="edit_id" value="" />
                                <div class="row">

                                    <div class="form-group col-sm-12 col-md-4">
                                        <label>{{ __('select_vehicle') }} <span class="text-danger">*</span></label>
                                        <select name="vehicle_id" id="edit_vehicle_id"
                                            class="form-control select2-dropdown select2-hidden-accessible"
                                            data-live-search="true" required>
                                            <option value="">{{ __('select_vehicle') }}</option>
                                            @foreach ($vehicles as $vehicle)
                                                <option value="{{ $vehicle->id }}">
                                                    {{ $vehicle->name . " (" . $vehicle->vehicle_number . ")" }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group col-sm-12 col-md-4">
                                        <label>{{ __('select') }} {{ __('category') }} <span
                                                class="text-danger">*</span></label>
                                        {!! Form::select('category_id', $expenseCategory, null, ['required', 'class' => 'form-control select2-dropdown select2-hidden-accessible', 'placeholder' => __('select') . ' ' . __('category'), 'id' => 'edit_category_id']) !!}
                                    </div>

                                    <div class="form-group col-sm-12 col-md-4">
                                        <label for="edit_title">{{ __('title') }} <span class="text-danger">*</span></label>
                                        <input name="title" id="edit_title" type="text" placeholder="{{ __('title') }}"
                                            class="form-control" required />
                                    </div>

                                    <div class="form-group col-sm-12 col-md-4">
                                        <label for="edit_ref_no">{{ __('reference_no') }}</label>
                                        <input name="ref_no" id="edit_ref_no" type="text"
                                            placeholder="{{ __('reference_no') }}" class="form-control" />
                                    </div>

                                    <div class="form-group col-sm-12 col-md-4">
                                        <label for="edit_amount">{{ __('Amount') }} <span
                                                class="text-danger">*</span></label>
                                        <input name="amount" id="edit_amount" type="number" placeholder="{{ __('Amount') }}"
                                            class="form-control" required />
                                    </div>

                                    <div class="form-group col-sm-12 col-md-4">
                                        <label for="edit_date">{{ __('date') }} <span class="text-danger">*</span></label>
                                        <input name="date" id="edit_date" type="text" placeholder="{{ __('date') }}"
                                            class="datepicker-popup-no-future form-control" required />
                                    </div>

                                    <div class="form-group col-sm-12 col-md-4">
                                        <label>{{ __('image/pdf') }}</label>
                                        <input type="file" name="image_pdf" id="edit_image_pdf" class="file-upload-default"
                                            accept="image/*,application/pdf" />
                                        <div class="input-group col-xs-12">
                                            <input type="text" class="form-control file-upload-info" disabled=""
                                                placeholder="{{ __('image/pdf') }}" />
                                            <span class="input-group-append">
                                                <button class="file-upload-browse btn btn-theme"
                                                    type="button">{{ __('upload') }}</button>
                                            </span>
                                        </div>
                                        <div style="width: 300px; height: 300px;">
                                            <div id="edit_image_pdf_preview" class="mt-2"></div>
                                        </div>
                                    </div>

                                    <div class="form-group col-sm-12 col-md-4">
                                        <label for="edit_description">{{ __('description') }}</label>
                                        <textarea name="description" id="edit_description" class="form-control"></textarea>
                                    </div>

                                    <div class="form-group col-sm-12 col-md-4">
                                        <label for="">{{ __('select') }} {{ __('session_year') }}</label>
                                        {!! Form::select('session_year_id', $sessionYear, $current_session_year->id, ['required', 'class' => 'form-control select2-dropdown select2-hidden-accessible', 'id' => 'edit_session_year_id']) !!}
                                    </div>

                                </div>


                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-dismiss="modal">{{ __('close') }}</button>
                                    <input class="btn btn-theme" type="submit" value="{{ __('submit') }}" />
                                </div>
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
        let schoolDateFormat = "{{ $schoolSetting['date_format'] }}";
        $(document).ready(function () {
            $('#edit_date').datepicker({
                format: 'dd-mm-yyyy',
                autoHide: true,
                endDate: new Date()
            });
            $('form.create-form').on('reset', function () {
                setTimeout(function () {
                    $('#session_year_id').trigger('change');
                }, 1);
            });
        });
    </script>
@endsection