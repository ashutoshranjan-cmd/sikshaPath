<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransportationPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'pickup_point_id',
        'user_id',
        'payment_transaction_id',
        'transportation_fee_id',
        'amount',
        'status',
        'paid_at',
        'session_year_id',
        'route_vehicle_id',
        'expiry_date',
        'shift_id',
        'include_amount',
        'included_amount',
    ];

    /**
     * Pickup point relation.
     */
    public function pickupPoint()
    {
        return $this->belongsTo(PickupPoint::class);
    }

    /**
     * User (student/parent) relation.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Payment transaction relation.
     */
    public function paymentTransaction()
    {
        return $this->belongsTo(PaymentTransaction::class);
    }

    /**
     * Transportation fee relation.
     */
    public function transportationFee()
    {
        return $this->belongsTo(TransportationFee::class);
    }

    /**
     * Session year relation.
     */
    public function sessionYear()
    {
        return $this->belongsTo(SessionYear::class);
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
