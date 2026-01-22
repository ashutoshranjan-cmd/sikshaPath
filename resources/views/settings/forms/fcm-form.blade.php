<!-- Firebase Service Configuration Section -->
<div class="row mb-4">
    <div class="col-12">
        <h5 class="mb-3">
            <i class="mdi mdi-web"></i> {{ __('Firebase Service Configuration') }}
        </h5>
    </div>
    <hr>
    {{-- <div class="form-group col-md-6 col-sm-12">
        <label for="firebase_api_key">{{ __('firebase_api_key') }}</label>
        <input name="firebase_api_key" id="firebase_api_key" value="{{ $api_key ?? '' }}"
            placeholder="{{ __('firebase_api_key') }}" class="form-control" />
    </div>

    <div class="form-group col-md-6 col-sm-12">
        <label for="firebase_auth_domain">{{ __('firebase_auth_domain') }}</label>
        <input name="firebase_auth_domain" id="firebase_auth_domain" value="{{ $auth_domain ?? '' }}"
            placeholder="{{ __('firebase_auth_domain') }}" class="form-control" />
    </div>
    <div class="form-group col-md-6 col-sm-12">
        <label for="firebase_storage_bucket">{{ __('firebase_storage_bucket') }}</label>
        <input name="firebase_storage_bucket" id="firebase_storage_bucket" value="{{ $storage_bucket ?? '' }}"
            placeholder="{{ __('firebase_storage_bucket') }}" class="form-control" />
    </div>

    <div class="form-group col-md-6 col-sm-12">
        <label for="firebase_messaging_sender_id">{{ __('firebase_messaging_sender_id') }}</label>
        <input name="firebase_messaging_sender_id" id="firebase_messaging_sender_id"
            value="{{ $messaging_sender_id ?? '' }}" placeholder="{{ __('firebase_messaging_sender_id') }}"
            class="form-control" />
    </div>

    <div class="form-group col-md-6 col-sm-12">
        <label for="firebase_app_id">{{ __('firebase_app_id') }}</label>
        <input name="firebase_app_id" id="firebase_app_id" value="{{ $app_id ?? '' }}"
            placeholder="{{ __('firebase_app_id') }}" class="form-control" />
    </div>

    <div class="form-group col-md-6 col-sm-12">
        <label for="firebase_measurement_id">{{ __('firebase_measurement_id') }}</label>
        <input name="firebase_measurement_id" id="firebase_measurement_id" value="{{ $measurement_id ?? '' }}"
            placeholder="{{ __('firebase_measurement_id') }}" class="form-control" />
    </div> --}}

    <div class="form-group col-md-6 col-sm-12">
        <label for="firebase_project_id">{{ __('firebase_project_id') }} <span class="text-danger">*</span></label>
        <input name="firebase_project_id" id="firebase_project_id" value="{{ $project_id ?? '' }}" required
            placeholder="{{ __('firebase_project_id') }}" class="form-control" />
    </div>

    <div class="form-group col-md-6 col-sm-12">
        <label>{{ __('firebase_service_file') }} <span
                class="text-info text-small">({{ __('Only Json File Allowed') }})</span></label>
        <input type="file" name="firebase_service_file" class="file-upload-default" accept="application/json" />
        <div class="input-group col-xs-12">
            <input type="text" class="form-control file-upload-info" accept="application/json" disabled=""
                placeholder="{{ __('firebase_service_file') }}" aria-label="" />
            <span class="input-group-append">
                <button class="file-upload-browse btn btn-theme" type="button">{{ __('upload') }}</button>
            </span>
        </div>
        <div class="mt-3">
            <a href="{{ asset('assets/notification-format.json') }}" target="_blank"
                class="btn btn-sm btn-outline-info">
                <i class="mdi mdi-file-document-outline"></i> {{ __('Sample Service File') }}
            </a>
        </div>
    </div>
</div>
