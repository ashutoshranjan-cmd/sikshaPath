@extends('layouts.master')

@section('title')
    {{ __('transportation_fees') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('manage_transportation_fees') }}
            </h3>
        </div>

        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="card-title mb-4">
                                    {{ __('manage_transportation_fees') }}
                                </h4>
                            </div>
                            <div class="col-md-6 d-flex justify-content-end">
                                <a class="btn btn-sm btn-theme" style="height: 35px;"
                                    href="{{ route('pickup-points.index') }}">{{ __('back') }}</a>
                            </div>
                        </div>

                        <form class="pt-3 transportation-fee-create-form" id="create-form"
                            data-success-function="formSuccessFunction"
                            action="{{ route('transportation-fees.update') }}" method="POST"
                            novalidate="novalidate">
                            @csrf
                            <input type="hidden" name="id" value="{{ $transportationFees->id }}">
                            <div class="row">
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('pickup_point') }} <span class="text-danger">*</span></label>
                                    <select name="pickup_point_id" class="form-control" required>
                                        <option value="">{{ __('Select Pickup Point') }}</option>
                                        @foreach ($pickupPoints as $item)
                                            <option value="{{ $item->id }}"
                                                {{ $item->id == $transportationFees->id ? 'selected' : '' }}>
                                                {{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Duration and Amount Repeater -->
                                <div class="form-group col-sm-6 col-md-12">
                                    @if ($transportationFees->transportationFees && $transportationFees->transportationFees->count() > 0)
                                        @foreach ($transportationFees->transportationFees as $fee)
                                            <div class="row">
                                                <input type="hidden" name="edit_fees[0][id]" value="{{ $fee->id }}">
                                                <div class="form-group col-md-5">
                                                    <label for="duration-input">{{ __('duration') }}
                                                        <small class="text-muted">({{ __('days') }})</small>
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <input id="duration-input" name="edit_fees[0][duration]" type="number"
                                                        class="form-control" required min="1"
                                                        step="1" placeholder="ex. 7 days"
                                                        value="{{ $fee->duration }}">
                                                </div>
                                                <div class="form-group col-md-5">
                                                    <label>{{ __('amount') }} <span
                                                            class="text-danger">*</span></label>
                                                    <input name="edit_fees[0][fee_amount]" type="number" step="0.01"
                                                        min="0" placeholder="{{ __('amount') }}"
                                                        class="form-control" required
                                                        value="{{ $fee->fee_amount }}" />
                                                </div>
                                                <div class="form-group col-md-2 pl-0 mt-4">
                                                    <button type="button"
                                                        class="btn btn-inverse-danger btn-icon remove-existing-fee"
                                                        data-id="{{ $fee->id }}">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                    <div class="duration-amount-repeater">
                                        <div data-repeater-list="fees">
                                            <div data-repeater-item>
                                                <div class="row">
                                                    <div class="form-group col-md-5">
                                                        <label for="duration-input">{{ __('duration') }}
                                                            <small class="text-muted">({{ __('days') }})</small>
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <input id="duration-input" name="duration" type="number"
                                                            class="form-control" required min="1" step="1"
                                                            placeholder="ex. 7 days">
                                                    </div>
                                                    <div class="form-group col-md-5">
                                                        <label>{{ __('amount') }} <span
                                                                class="text-danger">*</span></label>
                                                        <input name="fee_amount" type="number" step="0.01"
                                                            min="0" placeholder="{{ __('amount') }}"
                                                            class="form-control" required />
                                                    </div>
                                                    <div class="form-group col-md-2 pl-0 mt-4" data-repeater-delete>
                                                        <button type="button"
                                                            class="btn btn-inverse-danger btn-icon remove-duration-amount"
                                                            data-id="0">
                                                            <i class="fa fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row col-md-4 mt-3 mb-3">
                                            <button type="button" class="btn btn-success add-duration-amount"
                                                title="Add new duration and amount" data-repeater-create>
                                                <i class="fa fa-plus"></i> {{ __('add_duration') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input class="btn btn-theme float-right ml-3" id="create-btn" type="submit"
                                value={{ __('submit') }}>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
function formSuccessFunction(response) {
    if (!response.error) {
        setTimeout(() => {
            window.location.href = "{{ route('pickup-points.index') }}"
        }, 1000);
    }
}
</script>
@endsection
