@extends('layouts.master')

@section('title')
    {{ __('receive_payment') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('manage') . ' ' . __('subscription') }}
            </h3>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">


                        <div class="col-12 text-right">
                            <a href="{{ url('subscriptions/report') }}" class="btn btn-sm btn-theme">{{ __('back') }}</a>
                        </div>
                        {!! Form::model($subscriptionBill, [
        'route' => ['subscriptions-bill-payment.update', $subscriptionBill->id],
        'method' => 'post',
        'class' => 'edit-form',
        'novalidate' => 'novalidate',
        'data-success-function' => 'formSuccessFunction'
    ]) !!}

                        <div class="border border-secondary rounded-lg my-4 mx-1">
                            <div class="col-md-12 mt-3">
                                <h4>{{ __('subscription') . ' ' . __('bill') }} {{ __('receive_payment') }}</h4>
                            </div>
                            <div class="col-12 mb-3">
                                <hr class="mt-0">
                            </div>
                            <div class="row my-4 mx-1">
                                <div class="form-group col-md-6 col-sm-12">
                                    <h4>{{ $subscriptionBill->school->name }} <span
                                            class="text-info">#{{ $subscriptionBill->subscription->name }}</span></h4>
                                </div>

                                <div class="form-group col-md-6 col-sm-12 text-right">
                                    <span
                                        class="billing_cycle btn-gradient-dark p-2">{{ date($systemSettings['date_format'], strtotime($subscriptionBill->subscription->start_date)) }}
                                        -
                                        {{ date($systemSettings['date_format'], strtotime($subscriptionBill->subscription->end_date)) }}</span>
                                </div>
                            </div>

                            <div class="form-group col-sm-12 col-md-12">
                                <div class="form-group col-sm-12 col-md-12 table-responsive">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>{{ __('user') }}</th>
                                            <th>{{ __('charges') }}</th>
                                            <th>{{ __('total_user') }}</th>
                                            <th>{{ __('total_amount') }} ({{ $systemSettings['currency_symbol'] ?? '' }})
                                            </th>
                                        </tr>

                                        @php
                                            $total_user_charge = ($student_charges * $subscriptionBill->total_student) + ($staff_charges * $subscriptionBill->total_staff);
                                            $addon_charges = 0;
                                        @endphp
                                        @if ($package_type == 1)
                                            <tr>
                                                <td>{{ __('students') }}</td>
                                                <td class="text-right">{{ number_format($student_charges, 2) }}</td>
                                                <td class="text-right">{{ $subscriptionBill->total_student }}</td>
                                                <td class="text-right">
                                                    {{ number_format($student_charges * $subscriptionBill->total_student, 2) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>{{ __('staffs') }}
                                                    <div class="text-small text-muted mt-2">
                                                        {{ __('including_teachers_and_other_staffs') }}
                                                    </div>
                                                </td>
                                                <td class="text-right">{{ number_format($staff_charges, 2) }}</td>
                                                <td class="text-right">{{ $subscriptionBill->total_staff }}</td>
                                                <td class="text-right">
                                                    {{ number_format($staff_charges * $subscriptionBill->total_staff, 2) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th colspan="3">{{ __('Total User Charges') }}</th>
                                                <th class="text-right">{{ $systemSettings['currency_symbol'] ?? '' }}
                                                    {{ number_format($total_user_charge, 2) }}
                                                </th>
                                            </tr>
                                        @elseif ($package_type == 0)
                                            <tr>
                                                <td>{{ __('students') }}</td>
                                                <td class="text-right">{{ __('included_in_package') }}</td>
                                                <td class="text-right">{{ $subscriptionBill->total_student }}</td>
                                                <td class="text-right">{{ __('included_in_package') }}</td>
                                            </tr>
                                            <tr>
                                                <td>{{ __('staffs') }}
                                                    <div class="text-small text-muted mt-2">
                                                        {{ __('including_teachers_and_other_staffs') }}
                                                    </div>
                                                </td>
                                                <td class="text-right">{{ __('included_in_package') }}</td>
                                                <td class="text-right">{{ $subscriptionBill->total_staff }}</td>
                                                <td class="text-right">{{ __('included_in_package') }}</td>
                                            </tr>
                                            <tr>
                                                <th colspan="3">{{ __('Total User Charges') }}</th>
                                                <th class="text-right">{{ __('included_in_package') }}</th>
                                            </tr>
                                        @endif
                                        @php
                                            $exists = $subscriptionBill->subscription->addons
                                                ->contains(fn($addon) => !is_null($addon->payment_transaction_id));
                                        @endphp
                                        @if ($exists)
                                            <tr>
                                                <th colspan="4" class="text-center">
                                                    {{ __('addon_charges') }}
                                                </th>
                                            </tr>
                                            <tr>
                                                <th colspan="3">
                                                    {{ __('addon') }}
                                                </th>
                                                <th>
                                                    {{ __('total_amount') }} ({{ $systemSettings['currency_symbol'] ?? '' }})
                                                </th>
                                            </tr>
                                        @endif
                                        @foreach ($subscriptionBill->subscription->addons as $addon)
                                            <tr>
                                                @if ($addon && $addon->payment_transaction_id != null)
                                                    <td colspan="3">{{ $addon->feature->name }}</td>
                                                    <td class="text-right">
                                                        {{ number_format($addon->price, 2) }}
                                                        @if ($addon && $addon->payment_transaction_id != null)
                                                            @php
                                                                $addon_charges += $addon->price;
                                                            @endphp
                                                        @else
                                                            @php
                                                                $addon_charges = 0;
                                                            @endphp
                                                        @endif

                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                        @if ($exists)
                                            <tr>
                                                <th colspan="3">
                                                    {{ __('total_addon_charges') }}
                                                </th>
                                                <th class="text-right">
                                                    {{ $systemSettings['currency_symbol'] ?? '' }}
                                                    {{ number_format($addon_charges, 2) }}
                                                </th>
                                            </tr>
                                        @endif

                                        @if ($subscriptionBill->subscription->package_type == 0)
                                            <tr>
                                                <th colspan="3">
                                                    {{ __('package_amount') }}
                                                </th>
                                                <th class="text-right">
                                                    {{ $systemSettings['currency_symbol'] ?? '' }}
                                                    {{ number_format(ceil(($subscriptionBill->amount) * 100) / 100, 2) }}
                                                </th>
                                            </tr>
                                        @endif

                                        <tr>
                                            <th colspan="3">
                                                {{ __('total_bill_amount') }}
                                            </th>
                                            <th class="text-right">
                                                @if ($subscriptionBill->subscription->package_type == 1)
                                                    {{ $systemSettings['currency_symbol'] ?? '' }}
                                                    {{ number_format(ceil(($total_user_charge + $addon_charges) * 100) / 100, 2) }}
                                                @else
                                                    {{ $systemSettings['currency_symbol'] ?? '' }}
                                                    {{ number_format(ceil(($subscriptionBill->amount + $addon_charges) * 100) / 100, 2) }}
                                                @endif
                                            </th>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            {!! Form::hidden('school_id', $subscriptionBill->school_id, [null]) !!}
                            {!! Form::hidden('amount', number_format(ceil(($subscriptionBill->amount) * 100) / 100, 2), [null]) !!}

                            <div class="form-group col-sm-12 col-md-12 mt-4">
                                <label>{{ __('payment_type') }} <span class="text-danger">*</span></label>
                                <div class="d-flex">
                                    @if ($subscriptionBill->transaction && $subscriptionBill->transaction->payment_gateway == 'Cash')
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                {!! Form::radio('subscription_transaction[payment_gateway]', 'Cash', true, ['class' => 'form-check-input payment_type cash']) !!}
                                                {{ __('cash') }}
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                {!! Form::radio('subscription_transaction[payment_gateway]', 'Cheque', null, ['class' => 'form-check-input payment_type cheque']) !!}
                                                {{ __('cheque') }}
                                            </label>
                                        </div>
                                    @else
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                {!! Form::radio('subscription_transaction[payment_gateway]', 'Cash', false, ['class' => 'form-check-input payment_type cash']) !!}
                                                {{ __('cash') }}
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                {!! Form::radio('subscription_transaction[payment_gateway]', 'Cheque', true, ['class' => 'form-check-input payment_type cheque']) !!}
                                                {{ __('cheque') }}
                                            </label>
                                        </div>
                                    @endif

                                </div>
                            </div>
                            <div class="form-group col-sm-12 col-md-4 cheque_input">
                                <label for="">{{ __('cheque_no') }} <span class="text-danger">*</span></label>
                                {!! Form::text('cheque_number', $subscriptionBill->transaction ? $subscriptionBill->transaction->order_id : null, ['required', 'class' => 'form-control', 'placeholder' => __('enter_cheque_number')]) !!}
                            </div>

                        </div>
                        <input class="btn btn-theme" type="submit" value={{ __('submit') }}>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
@section('script')
    <script>
        setTimeout(() => {
            window.onload = $('.payment_type').trigger('change');
        }, 500);
        $('.payment_type').change(function (e) {
            e.preventDefault();
            if ($("input[type='radio'].cash:checked").val() == 'Cash') {
                $('.cheque_input').slideUp(500);
            } else {
                $('.cheque_input').slideDown(500);
            }
        });

        function formSuccessFunction(response) {
            setTimeout(() => {
                window.location.href = "{{url('subscriptions/report')}}"
            }, 2000);
        }
    </script>
@endsection