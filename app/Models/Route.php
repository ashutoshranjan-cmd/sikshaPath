<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'distance',
        'status',
        'shift_id'
    ];

    protected $casts = [
        'distance' => 'decimal:2'
    ];

    /**
     * Get the route pickup points for this route.
     */
    public function routePickupPoints()
    {
        return $this->hasMany(RoutePickupPoint::class)->orderBy('order');
    }

    /**
     * Get the pickup points associated with this route.
     */
    public function pickupPoints()
    {
        return $this->belongsToMany(PickupPoint::class, 'route_pickup_points')
                    ->withPivot('order', 'pickup_time', 'drop_time')
                    ->withTimestamps()
                    ->orderBy('pivot_order');
    }

    public function routeVehicle(){
        return $this->hasMany(RouteVehicle::class);
    }

    /**
     * Get the shift that this route belongs to.
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

}