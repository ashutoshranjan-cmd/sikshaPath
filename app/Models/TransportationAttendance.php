<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransportationAttendance extends Model
{
    use HasFactory;

    protected $table = 'transportation_attendance';

    protected $fillable = [
        'user_id',
        'pickup_point_id',
        'route_vehicle_id',
        'shift_id',
        'date',
        'status',
        'created_by',
        'pickup_drop',
        'trip_id'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pickupPoint()
    {
        return $this->belongsTo(PickupPoint::class);
    }

    public function routeVehicle()
    {
        return $this->belongsTo(RouteVehicle::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
