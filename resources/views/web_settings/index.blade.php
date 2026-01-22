@extends('layouts.master')

@section('title')
    {{ __('web_settings') }}
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/wizard.css') }}">
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('manage') }} {{ __('web_settings') }}
            </h3>
        </div>

        <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body custom-card-body">
                        <form class="create-form-without-reset" id="wizard-form" action="{{ route('web-settings.store') }}" enctype="multipart/form-data" method="POST" novalidate="novalidate">
                            @csrf

                            <div class="wizard-container">
                                <!-- Sidebar Navigation -->
                                <div class="wizard-sidebar">
                                    <div class="steps-list d-flex flex-column">
                                        <a href="#" class="step-item active" data-step="0">
                                            <span class="step-number">1</span>
                                            <span>{{ __('colour_settings') }}</span>
                                        </a>
                                        <a href="#" class="step-item" data-step="1">
                                            <span class="step-number">2</span>
                                            <span>{{ __('general_settings') }}</span>
                                        </a>
                                        <a href="#" class="step-item" data-step="2">
                                            <span class="step-number">3</span>
                                            <span>{{ __('about_us') }}</span>
                                        </a>
                                        <a href="#" class="step-item" data-step="3">
                                            <span class="step-number">4</span>
                                            <span>{{ __('custom_package_section') }}</span>
                                        </a>
                                        <a href="#" class="step-item" data-step="4">
                                            <span class="step-number">5</span>
                                            <span>{{ __('download_our_app_section') }}</span>
                                        </a>
                                        <a href="#" class="step-item" data-step="5">
                                            <span class="step-number">6</span>
                                            <span>{{ __('social_media_links') }}</span>
                                        </a>
                                        <a href="#" class="step-item" data-step="6">
                                            <span class="step-number">7</span>
                                            <span>{{ __('Footer Settings') }}</span>
                                        </a>
                                    </div>
                                </div>

                                <!-- Content Area -->
                                <div class="wizard-content">
                                    <!-- Step 1: Colour Settings -->
                                    <div class="wizard-step active" data-step="0">
                                        <div class="wizard-step-content">
                                            <h4 class="h4 fw-bold mb-4 pb-2 border-bottom">{{ __('colour_settings') }}</h4>
                                            <div class="row">
                                                <div class="form-group col-md-3 col-sm-12">
                                                    <label for="theme_primary_color">{{ __('theme_primary_color') }} <span class="text-danger">*</span></label>
                                                    <input name="theme_primary_color" id="theme_primary_color"
                                                        value="{{ $settings['theme_primary_color'] ?? '' }}" type="text" required
                                                        placeholder="{{ __('theme_primary_color') }}" class="theme_primary_color color-picker form-control" />
                                                    <small>
                                                        <a href="javascript:void(0)" onclick="restore_default_color(1)">{{__('restore_default')}}</a>
                                                    </small>
                                                </div>
                                                <div class="form-group col-md-3 col-sm-12">
                                                    <label for="theme_secondary_color">{{ __('theme_secondary_color') }} <span class="text-danger">*</span></label>
                                                    <input name="theme_secondary_color" id="theme_secondary_color"
                                                        value="{{ $settings['theme_secondary_color'] ?? '' }}" type="text" required
                                                        placeholder="{{ __('theme_secondary_color') }}" class="theme_secondary_color color-picker form-control" />
                                                    <small>
                                                        <a href="javascript:void(0)" onclick="restore_default_color(2)">{{__('restore_default')}}</a>
                                                    </small>
                                                </div>
                                                <div class="form-group col-md-3 col-sm-12">
                                                    <label for="theme_secondary_color_1">{{ __('theme_secondary_color_1') }} <span class="text-danger">*</span></label>
                                                    <input name="theme_secondary_color_1" id="theme_secondary_color_1"
                                                        value="{{ $settings['theme_secondary_color_1'] ?? '' }}" type="text" required
                                                        placeholder="{{ __('theme_secondary_color_1') }}" class="theme_secondary_color_1 color-picker form-control" />
                                                    <small>
                                                        <a href="javascript:void(0)" onclick="restore_default_color(3)">{{__('restore_default')}}</a>
                                                    </small>
                                                </div>
                                                <div class="form-group col-md-3 col-sm-12">
                                                    <label for="theme_primary_background_color">{{ __('theme_primary_background_color') }} <span class="text-danger">*</span></label>
                                                    <input name="theme_primary_background_color" id="theme_primary_background_color"
                                                        value="{{ $settings['theme_primary_background_color'] ?? '' }}" type="text" required
                                                        placeholder="{{ __('theme_primary_background_color') }}" class="theme_primary_background_color color-picker form-control" />
                                                    <small>
                                                        <a href="javascript:void(0)" onclick="restore_default_color(4)">{{__('restore_default')}}</a>
                                                    </small>
                                                </div>
                                                <div class="form-group col-md-3 col-sm-12">
                                                    <label for="theme_text_secondary_color">{{ __('theme_text_secondary_color') }} <span class="text-danger">*</span></label>
                                                    <input name="theme_text_secondary_color" id="theme_text_secondary_color"
                                                        value="{{ $settings['theme_text_secondary_color'] ?? '' }}" type="text" required
                                                        placeholder="{{ __('theme_text_secondary_color') }}" class="theme_text_secondary_color color-picker form-control" />
                                                    <small>
                                                        <a href="javascript:void(0)" onclick="restore_default_color(5)">{{__('restore_default')}}</a>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Step 2: General Settings -->
                                    <div class="wizard-step" data-step="1">
                                        <div class="wizard-step-content">
                                            <h4 class="h4 fw-bold mb-4 pb-2 border-bottom">{{ __('general_settings') }}</h4>
                                            <div class="row">
                                                <div class="form-group col-sm-12 col-md-6">
                                                    <label for="image">{{ __('hero_image') }} </label>
                                                    <input type="file" name="home_image" class="file-upload-default" accept="image/png, image/jpeg, image/jpg, image/webp" />
                                                    <div class="input-group col-xs-12">
                                                        <input type="text" class="form-control file-upload-info"
                                                            id="home_image" disabled=""
                                                            placeholder="{{ __('home_image') }}" />
                                                        <span class="input-group-append">
                                                            <button class="file-upload-browse btn btn-theme"
                                                                type="button">{{ __('upload') }}</button>
                                                        </span>
                                                        <div class="col-md-12 mt-2">
                                                            <img height="50px" src='{{ $settings['home_image'] ?? asset('assets/landing_page_images/heroImg.png') }}'
                                                                alt="">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-6 col-sm-12">
                                                    <label for="hero_title_1">{{ __('hero_title_1') }}</label>
                                                    <input name="hero_title_1" id="hero_title_1" value="{{ $settings['hero_title_1'] ?? '' }}" type="text" placeholder="{{ __('hero_title_1') }}" class="form-control" maxlength="200" />
                                                </div>
                                                <div class="form-group col-md-4 col-sm-12">
                                                    <label for="hero_title_2">{{ __('hero_title_2') }}</label>
                                                    <input name="hero_title_2" id="hero_title_2" value="{{ $settings['hero_title_2'] ?? '' }}" type="text" placeholder="{{ __('hero_title_2') }}" class="form-control" maxlength="50"/>
                                                </div>
                                                <div class="form-group col-sm-12 col-md-4">
                                                    <label for="image">{{ __('hero_image_2') }} </label>
                                                    <input type="file" name="hero_title_2_image" class="file-upload-default" accept="image/png, image/jpeg, image/jpg, image/webp" />
                                                    <div class="input-group col-xs-12">
                                                        <input type="text" class="form-control file-upload-info"
                                                            id="hero_title_2_image" disabled=""
                                                            placeholder="{{ __('hero_title_2_image') }}" />
                                                        <span class="input-group-append">
                                                            <button class="file-upload-browse btn btn-theme"
                                                                type="button">{{ __('upload') }}</button>
                                                        </span>
                                                        <div class="col-md-12 mt-2">
                                                            <img height="50px" src='{{ $settings['hero_title_2_image'] ?? asset('assets/landing_page_images/user.png') }}'
                                                                alt="">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-sm-12 col-md-4">
                                                    <div class="d-flex">
                                                        <div class="form-check w-fit-content ml-3">
                                                            <label class="form-check-label ml-4">
                                                                @if (isset($settings['display_school_logos']))
                                                                    <input type="checkbox" class="form-check-input" name="display_school_logos" value="1" {{ $settings['display_school_logos'] ? 'checked' : '' }}>{{ __('display_school_logos') }}
                                                                @else
                                                                    <input type="checkbox" class="form-check-input" name="display_school_logos" value="1" checked>{{ __('display_school_logos') }}
                                                                @endif
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Step 3: About Us -->
                                    <div class="wizard-step" data-step="2">
                                        <div class="wizard-step-content">
                                            <h4 class="h4 fw-bold mb-4 pb-2 border-bottom">{{ __('about_us') }}</h4>
                                            <div class="row">
                                                <div class="form-group col-sm-12 col-md-6">
                                                    <label for="title">{{ __('title') }} <span class="text-danger">*</span></label>
                                                    {!! Form::text('about_us_title', $settings['about_us_title'] ?? null, ['required','class' => 'form-control','placeholder' => __('title')]) !!}
                                                </div>
                                                <div class="form-group col-sm-12 col-md-6">
                                                    <label for="heading">{{ __('heading') }} <span class="text-danger">*</span></label>
                                                    {!! Form::text('about_us_heading', $settings['about_us_heading'] ?? null, ['required','class' => 'form-control', 'placeholder' => __('heading')]) !!}
                                                </div>
                                                <div class="form-group col-sm-12 col-md-12">
                                                    <label for="description">{{ __('description') }} <span class="text-danger">*</span></label>
                                                    {!! Form::textarea('about_us_description', $settings['about_us_description'] ?? null, ['required','class' => 'form-control','rows' => 5, 'placeholder' => __('description')]) !!}
                                                </div>
                                                <div class="form-group col-sm-12 col-md-12">
                                                    <label for="points">{{ __('points') }} <span class="text-danger">*</span> <span class="text-small text-info">({{ __('please_use_commas_or_press_enter_to_add_multiple_points') }})</label>
                                                    {!! Form::text('about_us_points', $settings['about_us_points'] ?? null, ['required','class' => 'form-control', 'id' => 'tags', 'placeholder' => __('about_us_points')]) !!}
                                                </div>
                                                <div class="form-group col-sm-12 col-md-4">
                                                    <label for="image">{{ __('image') }} </label>
                                                    <input type="file" name="about_us_image" accept="image/png, image/jpeg, image/jpg, image/webp" class="file-upload-default" />
                                                    <div class="input-group col-xs-12">
                                                        <input type="text" class="form-control file-upload-info"
                                                            id="about_us_image" disabled=""
                                                            placeholder="{{ __('image') }}" />
                                                        <span class="input-group-append">
                                                            <button class="file-upload-browse btn btn-theme"
                                                                type="button">{{ __('upload') }}</button>
                                                        </span>
                                                        <div class="col-md-12 mt-2">
                                                            <img height="50px" src='{{ $settings['about_us_image'] ?? asset('assets/landing_page_images/whyBestImg.png') }}'
                                                                alt="">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Step 4: Custom Package Section -->
                                    <div class="wizard-step" data-step="3">
                                        <div class="wizard-step-content">
                                            <h4 class="h4 fw-bold mb-4 pb-2 border-bottom">{{ __('custom_package_section') }}</h4>
                                            <div class="row">
                                                <div class="form-group col-sm-12 col-md-12">
                                                    <label>{{ __('status') }} <span class="text-danger">*</span></label>
                                                    <div class="d-flex">
                                                        @if (isset($settings['custom_package_status']) && $settings['custom_package_status'] == 1)
                                                            <div class="form-check form-check-inline">
                                                                <label class="form-check-label">
                                                                    <input type="radio" class="form-check-input" name="custom_package_status" id="enable" checked value="1">{{__("enable")}} </label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <label class="form-check-label">
                                                                    <input type="radio" class="form-check-input" name="custom_package_status" id="disable" value="0">{{__("disable")}}
                                                                </label>
                                                            </div>
                                                        @else
                                                            <div class="form-check form-check-inline">
                                                                <label class="form-check-label">
                                                                    <input type="radio" class="form-check-input" name="custom_package_status" id="enable" value="1">{{__("enable")}} </label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <label class="form-check-label">
                                                                    <input type="radio" class="form-check-input" name="custom_package_status" checked id="disable" value="0">{{__("disable")}}
                                                                </label>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="form-group col-sm-12 col-md-12">
                                                    <label for="description">{{ __('description') }} </label>
                                                    {!! Form::textarea('custom_package_description', $settings['custom_package_description'] ?? null, ['class' => 'form-control','rows' => 5, 'placeholder' => __('description')]) !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Step 5: Download Our App Section -->
                                    <div class="wizard-step" data-step="4">
                                        <div class="wizard-step-content">
                                            <h4 class="h4 fw-bold mb-4 pb-2 border-bottom">{{ __('download_our_app_section') }}</h4>
                                            <div class="row">
                                                <div class="form-group col-sm-12 col-md-4">
                                                    <label for="image">{{ __('image') }} </label>
                                                    <input type="file" name="download_our_app_image" accept="image/png, image/jpeg, image/jpg, image/webp" class="file-upload-default" />
                                                    <div class="input-group col-xs-12">
                                                        <input type="text" class="form-control file-upload-info"
                                                            id="download_our_app_image" disabled=""
                                                            placeholder="{{ __('image') }}" />
                                                        <span class="input-group-append">
                                                            <button class="file-upload-browse btn btn-theme"
                                                                type="button">{{ __('upload') }}</button>
                                                        </span>
                                                        <div class="col-md-12 mt-2">
                                                            <img height="50px" src='{{ $settings['download_our_app_image'] ?? asset('assets/landing_page_images/ourApp.png') }}'
                                                                alt="">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-sm-12 col-md-12">
                                                    <label for="description">{{ __('description') }} <span class="text-danger">*</span></label>
                                                    {!! Form::textarea('download_our_app_description', $settings['download_our_app_description'] ?? null, ['required','class' => 'form-control','rows' => 5, 'placeholder' => __('description')]) !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Step 6: Social Media Links -->
                                    <div class="wizard-step" data-step="5">
                                        <div class="wizard-step-content">
                                            <h4 class="h4 fw-bold mb-4 pb-2 border-bottom">{{ __('social_media_links') }}</h4>
                                            <div class="row">
                                                <div class="form-group col-md-6 col-sm-12">
                                                    <label for="facebook">{{ __('facebook') }}</label>
                                                    <input name="facebook" id="facebook" value="{{ $settings['facebook'] ?? '' }}" type="text" placeholder="{{ __('facebook') }}" class="form-control" />
                                                </div>
                                                <div class="form-group col-md-6 col-sm-12">
                                                    <label for="instagram">{{ __('instagram') }}</label>
                                                    <input name="instagram" id="instagram" value="{{ $settings['instagram'] ?? '' }}" type="text" placeholder="{{ __('instagram') }}" class="form-control" />
                                                </div>
                                                <div class="form-group col-md-6 col-sm-12">
                                                    <label for="linkedin">{{ __('linkedin') }} </label>
                                                    <input name="linkedin" id="linkedin" value="{{ $settings['linkedin'] ?? '' }}" type="text" placeholder="{{ __('linkedin') }}" class="form-control" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Step 7: Footer Settings -->
                                    <div class="wizard-step" data-step="6">
                                        <div class="wizard-step-content">
                                            <h4 class="h4 fw-bold mb-4 pb-2 border-bottom">{{ __('Footer Settings') }}</h4>
                                            <div class="row">
                                                <div class="form-group col-sm-12 col-md-12">
                                                    <label for="short_description">{{ __('short_description') }}</label>
                                                    <textarea name="short_description" class="form-control" id="short_description" required placeholder="{{__('short_description')}}">{{$settings['short_description'] ?? ''}}</textarea>
                                                </div>
                                                <div class="form-group col-sm-12 col-md-12">
                                                    <label for="footer_text">{{ __('footer_text') }}</label>
                                                    <textarea id="tinymce_message" name="footer_text" required placeholder="{{__('footer_text')}}">{{$settings['footer_text'] ?? ''}}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Navigation Buttons -->
                                    <div class="wizard-actions">
                                        <button type="button" class="btn btn-secondary" id="prev-btn" style="display: none;">{{ __('Previous') }}</button>
                                        <button type="button" class="btn btn-theme" id="next-btn">{{ __('Next') }}</button>
                                        <button type="submit" class="btn btn-theme" id="submit-btn" style="display: none;">{{ __('Submit') }}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script src="{{ asset('assets/js/custom/wizard.js') }}"></script>
<script>
    function restore_default_color(value) {
        if (value == 1) {
            $('#theme_primary_color').val('#56CC99');
            if (typeof $('.theme_primary_color').asColorPicker === 'function') {
                $('.theme_primary_color').asColorPicker('val', '#56CC99');
            }
        }
        if (value == 2) {
            $('#theme_secondary_color').val('#215679');
            if (typeof $('.theme_secondary_color').asColorPicker === 'function') {
                $('.theme_secondary_color').asColorPicker('val', '#215679');
            }
        }
        if (value == 3) {
            $('#theme_secondary_color_1').val('#38A3A5');
            if (typeof $('.theme_secondary_color_1').asColorPicker === 'function') {
                $('.theme_secondary_color_1').asColorPicker('val', '#38A3A5');
            }
        }
        if (value == 4) {
            $('#theme_primary_background_color').val('#F2F5F7');
            if (typeof $('.theme_primary_background_color').asColorPicker === 'function') {
                $('.theme_primary_background_color').asColorPicker('val', '#F2F5F7');
            }
        }
        if (value == 5) {
            $('#theme_text_secondary_color').val('#5C788C');
            if (typeof $('.theme_text_secondary_color').asColorPicker === 'function') {
                $('.theme_text_secondary_color').asColorPicker('val', '#5C788C');
            }
        }
    }

    $(document).ready(function() {
        // Set validation message
        window.wizardValidationMessage = '{{ __("Please fill all required fields") }}';
        
        // Initialize wizard
        $('.wizard-container').initWizard({
            validateOnNext: true,
            scrollOnStepChange: true,
            onStepChange: function(stepIndex, direction) {
                // Reinitialize color pickers when on color settings step
                if (stepIndex === 0) {
                    setTimeout(function() {
                        $('.color-picker').each(function() {
                            if (typeof $(this).asColorPicker === 'function' && !$(this).hasClass('colorpicker-initialized')) {
                                $(this).asColorPicker({
                                    color: $(this).val() || '#000000'
                                });
                                $(this).addClass('colorpicker-initialized');
                            }
                        });
                    }, 100);
                }
            }
        });

        // Initialize color pickers
        setTimeout(function() {
            $('.color-picker').each(function() {
                if (typeof $(this).asColorPicker === 'function') {
                    $(this).asColorPicker({
                        color: $(this).val() || '#000000'
                    });
                    $(this).addClass('colorpicker-initialized');
                }
            });
        }, 300);

        // Initialize tags input
        if ($('#tags').length && typeof $('#tags').tagsInput === 'function') {
            $('#tags').tagsInput({
                'defaultText': '{{ __('add_point') }}',
                'width': '100%'
            });
        }

        // Initialize TinyMCE
        if ($('#tinymce_message').length && typeof tinymce !== 'undefined') {
            tinymce.init({
                selector: '#tinymce_message',
                height: 300,
                menubar: false,
                plugins: [
                    'advlist autolink lists link image charmap print preview anchor',
                    'searchreplace visualblocks code fullscreen',
                    'insertdatetime media table paste code help wordcount'
                ],
                toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
                content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
            });
        }
    });
</script>
@endsection
