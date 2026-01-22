@extends('layouts.master')

@section('title')
    {{ __('edit_route') }}
@endsection
@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('edit_route') }}: {{ $route->name }}
            </h3>
        </div>
        
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-end">
                            <a class="btn btn-sm btn-theme" href="{{ route('routes.index') }}">{{ __('back') }}</a>
                        </div>
                        <h4 class="card-title">{{ __('edit_route_details') }}</h4>            
                        <form class="pt-3 edit-form" data-success-function="formSuccessFunction" action="{{ route('routes.update', $route->id) }}" method="POST" novalidate="novalidate">
                            @csrf
                            @method('PUT')
                            
                            <div class="row">
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('name') }} <span class="text-danger">*</span></label>
                                    <input name="name" id="edit_name" type="text" placeholder="{{ __('name') }}" class="form-control" value="{{ $route->name }}" required/>
                                </div>
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('distance') }} (km)</label>
                                    <input name="distance" id="edit_distance" type="number" step="0.01" placeholder="{{ __('distance') }}" class="form-control" value="{{ $route->distance }}"/>
                                </div>
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('Shift') }}</label>
                                    <select name="shift_id" id="edit_shift_id" class="form-control" required>
                                        <option value="">{{ __('Select Shift') }}</option>
                                        @foreach($shifts as $shift)
                                            <option value="{{ $shift->id }}" {{ $route->shift_id == $shift->id ? 'selected' : '' }}>{{ $shift->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-sm-6 col-md-6">
                                    <label>{{ __('status') }}</label>
                                    <select name="status" id="edit_status" class="form-control">
                                        <option value="1" {{ $route->status ? 'selected' : '' }}>{{ __('Active') }}</option>
                                        <option value="0" {{ !$route->status ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Pickup Points Repeater Section -->
                            <div class="form-group col-sm-6 col-md-12">
                                <label>{{ __('pickup_points_with_time') }}</label>
                                <hr>
                                <div class="pickup-points-repeater">
                                    <div data-repeater-list="pickup_points">
                                        <div data-repeater-item>
                                            <div class="row">
                                                <input type="hidden" name="id" class="pickup_point_id" value="">
                                                <input type="hidden" name="order" class="pickup_point_order" value="">
                                                <div class="form-group col-md-4">
                                                    <label>{{ __('pickup_point') }}</label>
                                                    <select name="pickup_point_id" class="form-control" required>
                                                        <option value="">{{ __('select_pickup_point') }}</option>
                                                        @foreach($pickupPoints as $pickupPoint)
                                                            <option value="{{ $pickupPoint->id }}">{{ $pickupPoint->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label>{{ __('pickup_time') }}</label>
                                                    <input type="text" name="pickup_time" class="form-control pickup-time" placeholder="{{ __('pickup_time') }}" required data-convert="time">
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label>{{ __('drop_time') }}</label>
                                                    <input type="text" name="drop_time" class="form-control drop-time" placeholder="{{ __('drop_time') }}" required data-convert="time">
                                                </div>
                                                <div class="form-group col-md-2 pl-0 mt-4" data-repeater-delete>
                                                    <button type="button" class="btn btn-inverse-danger btn-icon remove-pickup-point">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row col-md-4 mt-3 mb-3">
                                        <button type="button" class="btn btn-success add-pickup-point" title="Add new pickup point" data-repeater-create>
                                            <i class="fa fa-plus"></i> {{ __('add_pickup_point') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        
                            
                            <input class="btn btn-theme float-right" id="create-btn" type="submit" value={{ __('submit') }}>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        @if(isset($route->routePickupPoints) && $route->routePickupPoints->isNotEmpty())
            routePickupPointRepeater.setList([
                @foreach ($route->routePickupPoints as $routePickupPoint)
                {
                    id: "{{ $routePickupPoint->id }}",
                    pickup_point_id: "{{ $routePickupPoint->pickup_point_id }}",
                    pickup_time: moment("{{ $routePickupPoint->pickup_time }}", 'HH:mm:ss').format('HH:mm'),
                    drop_time: moment("{{ $routePickupPoint->drop_time }}", 'HH:mm:ss').format('HH:mm'),
                    order: "{{ $routePickupPoint->order }}",
                },
                @endforeach
            ])
        @else
            $('.add-pickup-point').trigger('click')
        @endif

        $(document).ready(function () {
            @if(isset($route->routePickupPoints) && $route->routePickupPoints->isNotEmpty())
                @foreach ($route->routePickupPoints as $key=>$routePickupPoint)
                $('#remove-pickup-point-' + {{$key}}).attr('data-id', {{$routePickupPoint->id}});
                @endforeach
            @endif

            $('body').on('focus', ".pickup-time, .drop-time", function () {
                $(this).timepicker({
                    timeFormat: 'HH:mm',
                    interval: 15,
                    minTime: '00:00',
                    maxTime: '23:59',
                    defaultTime: '00:00',
                    startTime: '00:00',
                    dynamic: false,
                    dropdown: true,
                    scrollbar: true
                });
            });
        });

        function formSuccessFunction(response) {
            if (!response.error) {
                setTimeout(() => {
                    window.location.href = "{{route('routes.index')}}"
                }, 1000);
            }
        }
    </script>
@endsection