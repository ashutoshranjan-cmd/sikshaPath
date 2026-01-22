@extends('layouts.master')

@section('title')
    {{ __('add_bulk_questions') }}
@endsection


@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('add_bulk_questions') }}
                {{-- {{ storage_path('images/online_exam.png') }} --}}
            </h3>
        </div>
        <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <form class="pt-3" id="create-form" enctype="multipart/form-data"
                            action="{{ route('online-exam-question.store-bulk-questions') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>{{ __('Class') }} <span class="text-danger">*</span></label>
                                    <select name="class_id" required id="class-id"
                                        class="form-control select2 online-exam-class-id select2-dropdown select2-hidden-accessible"
                                        style="width:100%;" tabindex="-1" aria-hidden="true">
                                        <option value="">--- {{ __('select') . ' ' . __('Class') }} ---</option>
                                        @foreach ($classes as $data)
                                            <option value="{{ $data->id }}" data-medium-id="{{ $data->medium_id }}">
                                                {{ $data->name }}@if ($data->medium)
                                                    - {{ $data->medium->name }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>{{ __('subject') }} <span class="text-danger">*</span></label>
                                    <select required name="subject_id" id="subject-id" class="form-control" disabled>
                                        <option value="">-- {{ __('Select Subject') }} --</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12 col-md-6">
                                    <label for="file-upload-default">{{ __('file_upload') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="file" name="file" class="file-upload-default" />
                                    <div class="input-group col-xs-12">
                                        <input type="text" class="form-control file-upload-info" id="file-upload-default"
                                            disabled="" placeholder="{{ __('file_upload') }}" required="required" />
                                        <span class="input-group-append">
                                            <button class="file-upload-browse btn btn-theme"
                                                type="button">{{ __('upload') }}</button>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group col-sm-12 col-xs-12">
                                    <input class="btn btn-theme submit_bulk_file float-right" type="submit"
                                        value="{{ __('submit') }}" name="submit" id="submit_bulk_file">
                                </div>
                            </div>
                        </form>
                        <hr>
                        <div class="row form-group col-sm-12 col-md-4 mt-5">
                            <a class="btn btn-theme form-control"
                                href="{{ route('online-exam-question.download-smaple-data-file') }}" download>
                                <strong>{{ __('download_dummy_file') }}</strong>
                            </a>
                        </div>
                        <div class="row col-sm-12 col-xs-12">
                            <span style="font-size: 14px">
                                <b>{{ __('note') }} :-</b><br>
                                1. {{ __('First download dummy file and convert to .csv file then upload it') }}. <br>
                                2.
                                {{ __('If want more question options, then add after option_d column into csv file. e.g.- option_e, option_f') }}.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
