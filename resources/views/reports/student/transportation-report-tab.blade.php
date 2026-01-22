<style>
    /* Responsive driver/helper cards */
    .driver-helper-card {
        display: flex;
        align-items: center;
        padding: 0.75rem;
        border: 1px solid #e0e0e0;
        border-radius: 0.5rem;
        flex-wrap: wrap;
    }

    .driver-helper-card img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        flex-shrink: 0;
    }

    .driver-helper-info {
        margin-left: 1rem;
        flex: 1;
        min-width: 0;
    }

    .driver-helper-info h6 {
        margin-bottom: 0.25rem;
        font-weight: 600;
        word-wrap: break-word;
    }

    .driver-helper-info small {
        display: block;
        color: #6c757d;
        font-size: 0.875rem;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    @media (max-width: 576px) {
        .driver-helper-card {
            flex-direction: column;
            align-items: flex-start;
            text-align: left;
        }

        .driver-helper-info {
            margin-left: 0;
            margin-top: 0.75rem;
            width: 100%;
        }

        .driver-helper-card img {
            align-self: center;
        }
    }
</style>

@if ($transportation)
    <div class="col-md-12 grid-margin">
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h6 class="fw-bold text-center text-primary text-dark mb-3">{{ __('plan_details') }}</h6>
                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('plan_status') }}</span>
                                <span class="fw-semibold">{{ $transportation['plan']['plan_status'] ?? '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('vehicle_assignment') }}</span>
                                <span class="fw-semibold">{{ $transportation['plan']['vehicle_assignment'] ?? '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('expiry_date') }}</span>
                                <span class="fw-semibold">{{ $transportation['plan']['expiry_date'] ?? '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('paid_amount') }}</span>
                                <span class="fw-semibold">{{ $transportation['plan']['paid_amount'] ?? '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('payment_mode') }}</span>
                                <span class="fw-semibold">{{ $transportation['plan']['payment_mode'] ?? '-' }}</span>
                            </li>
                        </ul>

                        <h6 class="fw-bold text-center text-primary text-dark mb-3">{{ __('shift_details') }}</h6>
                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('name') }}</span>
                                <span class="fw-semibold">{{ $transportation['shift']['name'] ?? '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('start_time') }}</span>
                                <span class="fw-semibold">{{ $transportation['shift']['start_time'] ?? '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('end_time') }}</span>
                                <span class="fw-semibold">{{ $transportation['shift']['end_time'] ?? '-' }}</span>
                            </li>
                        </ul>

                        <h6 class="fw-bold text-center text-primary text-dark mb-3">{{ __('fee_details') }}</h6>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('duration') }}</span>
                                <span class="fw-semibold">{{ $transportation['fee']['duration'] ?? '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('amount') }}</span>
                                <span class="fw-semibold">{{ $transportation['fee']['amount'] ?? '-' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h6 class="fw-bold text-center text-primary text-dark mb-3">{{ __('route_details') }}</h6>
                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('route_name') }}</span>
                                <span class="fw-semibold">{{ $transportation['route']['route_name'] ?? '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('pickup_point_name') }}</span>
                                <span class="fw-semibold">{{ $transportation['route']['pickup_point_name'] ?? '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('pickup_time') }}</span>
                                <span class="fw-semibold">{{ $transportation['route']['pickup_time'] ?? '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('drop_time') }}</span>
                                <span class="fw-semibold">{{ $transportation['route']['drop_time'] ?? '-' }}</span>
                            </li>
                        </ul>

                        <h6 class="fw-bold text-center text-primary text-dark mb-3">{{ __('vehicle_details') }}</h6>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('name') }}</span>
                                <span class="fw-semibold">{{ $transportation['vehicle']['name'] ?? '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('number') }}</span>
                                <span class="fw-semibold">{{ $transportation['vehicle']['number'] ?? '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">{{ __('capacity') }}</span>
                                <span class="fw-semibold">{{ $transportation['vehicle']['capacity'] ?? '-' }}</span>
                            </li>
                            <li class="list-group-item">
                                <span class="text-muted d-block mb-2">{{ __('driver') }}</span>
                                @if (!isset($transportation['vehicle']['driver']))
                                    <div class="text-muted">{{ __('No driver assigned') }}</div>
                                @else
                                <div class="driver-helper-card">
                                    <a data-toggle="lightbox" href="{{ $transportation['vehicle']['driver']['image'] }}">
                                        <img src="{{ $transportation['vehicle']['driver']['image'] }}" 
                                             class="rounded-circle border"
                                             alt="Driver"
                                             onerror="onErrorImage(event)" />
                                    </a>
                                    <div class="driver-helper-info">
                                        <h6 class="mb-0">{{ $transportation['vehicle']['driver']['full_name'] ?? '-' }}</h6>
                                        <small>{{ $transportation['vehicle']['driver']['email'] ?? '-' }}</small>
                                        <small>{{ $transportation['vehicle']['driver']['mobile'] ?? '-' }}</small>
                                    </div>
                                </div>
                                @endif
                            </li>
                            <li class="list-group-item">
                                <span class="text-muted d-block mb-2">{{ __('helper') }}</span>
                                @if (!isset($transportation['vehicle']['helper']))
                                    <div class="text-muted">{{ __('No helper assigned') }}</div>
                                @else
                                <div class="driver-helper-card">
                                    <a data-toggle="lightbox" href="{{ $transportation['vehicle']['helper']['image'] }}">
                                        <img src="{{ $transportation['vehicle']['helper']['image'] }}" 
                                             class="rounded-circle border"
                                             alt="Helper"
                                             onerror="onErrorImage(event)" />
                                    </a>
                                    <div class="driver-helper-info">
                                        <h6 class="mb-0">{{ $transportation['vehicle']['helper']['full_name'] ?? '-' }}</h6>
                                        <small>{{ $transportation['vehicle']['helper']['email'] ?? '-' }}</small>
                                        <small>{{ $transportation['vehicle']['helper']['mobile'] ?? '-' }}</small>
                                    </div>
                                </div>
                                @endif
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="col-md-12 text-center py-5">
        <div class="alert alert-info shadow-sm border-0 d-inline-block px-4 py-3">
            {{ __('Did not opt for transportation service') }}
        </div>
    </div>
@endif