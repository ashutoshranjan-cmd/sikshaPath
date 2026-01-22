<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RouteVehicle extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'route_id',
        'vehicle_id',
        'driver_id',
        'helper_id',
        'shift_id',
        'status',
        'created_at',
        'updated_at',
        'history_id',
        'pickup_start_time',
        'pickup_end_time',
        'drop_start_time',
        'drop_end_time',
    ];
    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class);
    }

    public function helper()
    {
        return $this->belongsTo(User::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function transportationPayments()
    {
        return $this->hasMany(TransportationPayment::class, 'route_vehicle_id');
    }

    public function lastPickupPoint()
    {
        return $this->belongsTo(PickupPoint::class, 'last_pickup_point_id');
    }
}
