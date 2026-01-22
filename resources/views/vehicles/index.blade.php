@extends('layouts.master')

@section('title')
    {{ __('vehicles') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('manage_vehicles') }}
            </h3>
        </div>

        <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('vehicles.store') }}" method="POST" class="create-form" id="create-form"
                            data-success-function="formSuccessFunction" enctype="multipart/form-data"
                            novalidate="novalidate">
                            @csrf
                            <h4 class="card-title mb-4">{{ __('create_vehicle') }}</h4>
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="name">{{ __('name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control"
                                        placeholder="{{ __('vehicle_name') }}" required>
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="vehicle_number">{{ __('vehicle_number') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="vehicle_number" id="vehicle_number" class="form-control"
                                        placeholder="{{ __('vehicle_number') }}" required>
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="capacity">{{ __('vehicle_capacity') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="capacity" id="capacity" class="form-control"
                                        placeholder="{{ __('vehicle_capacity') }}" min="1" required>
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="status">{{ __('status') }} <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-control" required>
                                        <option value="1">{{ __('active') }}</option>
                                        <option value="0">{{ __('inactive') }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>{{ __('images') }} <span class="text-small text-info">
                                            ({{ __('upload_multiple_images') }})</span></label>
                                    <input type="file" multiple name="images[]" id="uploadInput" class="file-upload-default"
                                        accept="image/*" />
                                    <div class="input-group col-xs-12">
                                        <input type="text" class="form-control file-upload-info" disabled=""
                                            placeholder="{{ __('images') }}" required aria-label="" />
                                        <span class="input-group-append">
                                            <button class="file-upload-browse btn btn-theme"
                                                type="button">{{ __('upload') }}</button>
                                        </span>
                                    </div>
                                    <div id="selectedFiles" class="mt-3" style="max-height: 200px; overflow-y: auto;">
                                        <!-- Selected files will be listed here -->
                                    </div>
                                </div>
                            </div>

                            <input class="btn btn-theme float-right ml-3" id="create-btn" type="submit" value={{ __('submit') }}>
                            <input class="btn btn-secondary float-right" type="reset" value={{ __('reset') }}>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('list_vehicles') }}
                        </h4>
                        <div class="col-12 text-right">
                            <b><a href="#" class="table-list-type active mr-2" data-id="0">{{__('all')}}</a></b> | <a
                                href="#" class="ml-2 table-list-type" data-id="1">{{__("Trashed")}}</a>
                        </div>
                        <table aria-describedby="mydesc" class='table' id='table_list' data-toggle="table"
                            data-url="{{ route('vehicles.show', 1) }}" data-click-to-select="true"
                            data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]"
                            data-search="true" data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true"
                            data-trim-on-search="false" data-mobile-responsive="true" data-sort-name="id"
                            data-sort-order="desc" data-maintain-selected="true" data-export-data-type='all'
                            data-export-options='{ "fileName": "{{__('vehicle') }}-<?= date(' d-m-y') ?>" ,"ignoreColumn":["operate"]}'
                            data-show-export="true" data-query-params="schoolQueryParams" data-escape="true">
                            <thead>
                                <tr>
                                    <th scope="col" data-field="id" data-sortable="true" data-visible="false">{{ __('id') }}
                                    </th>
                                    <th scope="col" data-field="no">{{ __('no.') }}</th>
                                    <th scope="col" data-field="name">{{ __('name') }}</th>
                                    <th scope="col" data-field="vehicle_number">{{__('vehicle_number')}}</th>
                                    <th scope="col" data-field="capacity">{{__('vehicle_capacity')}}</th>
                                    <th scope="col" data-field="status" data-formatter="activeStatusFormatter">
                                        {{ __('status') }}
                                    </th>
                                    <th scope="col" data-field="operate" data-formatter="actionColumnFormatter"
                                        data-events="vehicleEvents" data-escape="false">{{ __('action') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editVehicleLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editVehicleLabel">{{ __('edit_vehicle') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true"><i class="fa fa-close"></i></span>
                        </button>
                    </div>
                    <form id="edit-form" class="pt-3 edit-form" action="{{ url('transportation/vehicles') }}"
                        data-success-function="formSuccessFunction">
                        <input type="hidden" id="edit_vehicle_id" name="edit_vehicle_id">

                        <div class="modal-body">
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label for="edit_vehicle_name">{{ __('name') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="edit_vehicle_name" id="edit_vehicle_name" class="form-control"
                                        placeholder="{{ __('vehicle_name') }}" required>
                                </div>

                                <div class="form-group col-md-12">
                                    <label for="edit_vehicle_number">{{ __('Vehicle') }} {{ __('number') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="edit_vehicle_number" id="edit_vehicle_number"
                                        class="form-control" placeholder="{{ __('vehicle_number') }}" required>
                                </div>

                                <div class="form-group col-md-12 ">
                                    <label for="edit_capacity">{{ __('Capacity') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="number" min="1" name="edit_capacity" id="edit_capacity"
                                        class="form-control" placeholder="{{ __('vehicle_capacity') }}" required>
                                </div>

                                <div class="form-group col-md-12">
                                    <label for="edit_status">{{ __('status') }}</label>
                                    <select name="edit_status" id="edit_status" class="form-control">
                                        <option value="1">{{ __('active') }}</option>
                                        <option value="0">{{ __('inactive') }}</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-12">
                                    <label>{{ __('images') }} <span class="text-small text-info">
                                            ({{ __('upload_multiple_images') }})</span></label>
                                    <input type="file" multiple name="edit_images[]" id="uploadInputEdit"
                                        class="file-upload-default" accept="image/*" />
                                    <div class="input-group col-xs-12">
                                        <input type="text" class="form-control file-upload-info" disabled=""
                                            placeholder="{{ __('images') }}" required aria-label="" />
                                        <span class="input-group-append">
                                            <button class="file-upload-browse btn btn-theme"
                                                type="button">{{ __('upload') }}</button>
                                        </span>
                                    </div>
                                    <div id="selectedFilesEdit" class="mt-3" style="max-height: 200px; overflow-y: auto;">
                                        <!-- Selected files will be listed here -->
                                    </div>
                                </div>

                                <div class="col-lg-12 grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-sm-12 col-md-12 mb-3">
                                                    <h4 class="card-title">{{ __('files') }}</h4>
                                                </div>

                                                <!-- Scrollable container -->
                                                <div style="max-height: 250px; overflow-y: auto; padding-right: 8px;">
                                                    <div id="lightgallery" class="row lightGallery">
                                                        <div id="edit_vehicle_files" class="row"></div>
                                                    </div>
                                                </div>
                                                <!-- END scrollable -->
                                            </div>
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
    <script>
        const uploadInput = document.getElementById('uploadInput');
        const selectedFilesContainer = document.getElementById('selectedFiles');
        let fileList = [];

        // Event listener to handle file selection
        uploadInput.addEventListener('change', function () {
            // Store files in our array
            fileList = Array.from(this.files);
            updateFilePreview();
        });

        function updateFilePreview() {
            // Update file counter
            const fileCount = fileList.length;
            $(uploadInput).parent().find('.form-control').val(fileCount + (fileCount === 1 ? ' file selected' : ' files selected'));

            // Clear previous preview
            selectedFilesContainer.innerHTML = '';

            // Create preview for each selected file
            fileList.forEach((file, index) => {
                const fileDiv = document.createElement('div');
                fileDiv.className = 'selected-file d-flex align-items-center p-2 border-bottom';

                if (file.type.startsWith('image/')) {
                    // For images, show thumbnail
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        fileDiv.innerHTML = `
                                            <img src="${e.target.result}" alt="${file.name}" style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px;">
                                                <div class="flex-grow-1">
                                                    <div class="font-weight-bold">${file.name}</div>
                                                    <div class="text-muted small">${(file.size / 1024).toFixed(2)} KB</div>
                                                </div>
                                            <button type="button" class="btn btn-sm btn-danger remove-file" style="padding: 2px 8px; line-height: 1;" data-index="${index}">×</button>
                                            `;

                        // Add click handler for remove button
                        const removeBtn = fileDiv.querySelector('.remove-file');
                        removeBtn.addEventListener('click', function () {
                            removeFile(index);
                        });
                    };
                    reader.readAsDataURL(file);
                } else {
                    // For non-images, show simple text
                    fileDiv.innerHTML = `
                                        <div class="mr-3">📄</div>
                                            <div class="flex-grow-1">
                                                <div class="font-weight-bold">${file.name}</div>
                                                <div class="text-muted small">${(file.size / 1024).toFixed(2)} KB</div>
                                            </div>
                                        <button type="button" class="btn btn-sm btn-danger remove-file" style="padding: 2px 8px; line-height: 1;" data-index="${index}">×</button>
                                        `;

                    // Add click handler for remove button
                    const removeBtn = fileDiv.querySelector('.remove-file');
                    removeBtn.addEventListener('click', function () {
                        removeFile(index);
                    });
                }

                selectedFilesContainer.appendChild(fileDiv);
            });
        }

        function removeFile(index) {
            // Remove file from our array
            fileList.splice(index, 1);

            // Update the file input
            const dt = new DataTransfer();
            fileList.forEach(file => dt.items.add(file));
            uploadInput.files = dt.files;

            // Update the preview
            updateFilePreview();
        }

        // EDIT modal upload elements
        const uploadInputEdit = document.getElementById('uploadInputEdit');
        const selectedFilesContainerEdit = document.getElementById('selectedFilesEdit');
        let fileListEdit = [];

        uploadInputEdit.addEventListener('change', function () {
            fileListEdit = Array.from(this.files);
            updateFilePreviewEdit();
        });

        function updateFilePreviewEdit() {

            const fileCount = fileListEdit.length;
            $(uploadInputEdit).parent().find('.form-control')
                .val(fileCount + (fileCount === 1 ? ' file selected' : ' files selected'));

            selectedFilesContainerEdit.innerHTML = '';

            fileListEdit.forEach((file, index) => {
                const fileDiv = document.createElement('div');
                fileDiv.className = 'selected-file d-flex align-items-center p-2 border-bottom';

                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        fileDiv.innerHTML = `
                            <img src="${e.target.result}" style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px;">
                            <div class="flex-grow-1">
                                <div class="font-weight-bold">${file.name}</div>
                                <div class="text-muted small">${(file.size / 1024).toFixed(2)} KB</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-danger remove-file-edit"
                                data-index="${index}" style="padding: 2px 8px;">×</button>
                        `;
                        const removeBtn = fileDiv.querySelector('.remove-file-edit');
                        removeBtn.addEventListener('click', function () {
                            removeFileEdit(index);
                        });
                    };
                    reader.readAsDataURL(file);

                } else {
                    fileDiv.innerHTML = `
                        <div class="mr-3">📄</div>
                        <div class="flex-grow-1">
                            <div class="font-weight-bold">${file.name}</div>
                            <div class="text-muted small">${(file.size / 1024).toFixed(2)} KB</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-danger remove-file-edit"
                            data-index="${index}" style="padding: 2px 8px;">×</button>
                    `;
                    const removeBtn = fileDiv.querySelector('.remove-file-edit');
                    removeBtn.addEventListener('click', function () {
                        removeFileEdit(index);
                    });
                }

                selectedFilesContainerEdit.appendChild(fileDiv);
            });
        }

        function removeFileEdit(index) {
            fileListEdit.splice(index, 1);

            const dt = new DataTransfer();
            fileListEdit.forEach(file => dt.items.add(file));
            uploadInputEdit.files = dt.files;

            updateFilePreviewEdit();
        }


        function formSuccessFunction(response) {
            setTimeout(() => {
                window.location.reload()
            }, 1000);
        }
    </script>
@endsection