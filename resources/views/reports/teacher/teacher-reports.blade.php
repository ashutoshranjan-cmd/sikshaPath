@extends('layouts.master')

@section('title')
    {{ __('teachers') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('manage_teachers') }}
            </h3>
        </div>

        <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('list_teachers') }}
                        </h4>

                        <div class="row">
                            <div class="col-12">
                                <table aria-describedby="mydesc" class='table' id='table_list'
                                       data-toggle="table" data-url="{{ route('reports.teacher.teacher-reports.show',[1]) }}" data-click-to-select="true"
                                       data-side-pagination="server" data-pagination="true"
                                       data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                                       data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true" data-fixed-columns="false"
                                       data-trim-on-search="false" data-mobile-responsive="true" data-sort-name="id"
                                       data-sort-order="desc" data-maintain-selected="true" data-export-types="['pdf','json', 'xml', 'csv', 'txt', 'sql', 'doc', 'excel']" data-show-export="true"
                                       data-export-options='{ "fileName": "teachers-list-<?= date('d-m-y') ?>" ,"ignoreColumn": ["operate"]}' data-query-params="teacherReportsQueryParams"
                                       data-check-on-init="true" data-escape="true">
                                    <thead>
                                    <tr>
                                        <th scope="col" data-field="id" data-sortable="true" data-visible="false">{{ __('id') }}</th>
                                        <th scope="col" data-field="no">{{ __('no.') }}</th>
                                        <th scope="col" data-field="user.full_name" data-formatter="TeacherNameFormatter">{{ __('name') }}</th>
                                        <th scope="col" data-field="gender">{{ __('gender') }}</th>
                                        <th scope="col" data-field="mobile">{{ __('mobile') }}</th>
                                        <th scope="col" data-field="dob" data-visible="false" >{{ __('dob') }}</th>
                                        <th scope="col" data-field="staff.qualification">{{ __('qualification') }}</th>
                                        <th scope="col" data-field="current_address">{{ __('current_address') }}</th>
                                        <th scope="col" data-field="permanent_address">{{ __('permanent_address') }}</th>
                                        <th scope="col" data-field="staff.salary" data-visible="false"> {{ __('Salary') }}</th>
                                        
                                        {{-- Extra form fields --}}
                                        @foreach ($extraFields as $field)
                                        <th scope="col" data-visible="false" data-escape="false" data-field="{{ $field->name }}">{{ $field->name }}</th>
                                        @endforeach
                                        {{-- End extra form fields --}}
                                        <th data-events="teacherEvents" scope="col" data-field="operate" data-escape="false">{{ __('action') }}</th>
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

