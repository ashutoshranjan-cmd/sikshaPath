<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RouteVehicleHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'route_id',
        'vehicle_id',
        'driver_id',
        'helper_id',
        'shift_id',
        'status',
        'created_at',
        'updated_at',
        'date',
        'session_year_id',
        'created_by',
        'start_time',
        'end_time',
        'actual_start_time',
        'actual_end_time',
        'status',
        'type'
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function helper()
    {
        return $this->belongsTo(User::class, 'helper_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function lastPickupPoint()
    {
        return $this->belongsTo(PickupPoint::class, 'last_pickup_point_id');
    }
    public function route()
    {
        return $this->belongsTo(Route::class, 'route_id');
    }
}
