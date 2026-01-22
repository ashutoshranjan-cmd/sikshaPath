<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoutePickupPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_id',
        'pickup_point_id',
        'pickup_time',
        'drop_time',
        'order'
    ];

    protected $casts = [
        'order' => 'integer'
    ];

    /**
     * Get the route that owns this route pickup point.
     */
    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Get the pickup point that belongs to this route pickup point.
     */
    public function pickupPoint()
    {
        return $this->belongsTo(PickupPoint::class);
    }

    /**
     * Scope a query to order by the order field.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
    
}