<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DateFormatTrait;

class TransportationFee extends Model
{
    use HasFactory;
    use DateFormatTrait;

    protected $fillable = [
        'pickup_point_id',
        'duration',
        'fee_amount'
    ];

    protected $casts = [
        'fee_amount' => 'decimal:2'
    ];

    /**
     * Get the pickup point that owns this transportation fee.
     */
    public function pickupPoint()
    {
        return $this->belongsTo(PickupPoint::class);
    }

    public function getCreatedAtAttribute()
    {
        return $this->formatDateValue($this->getRawOriginal('created_at'));
    }

    public function getUpdatedAtAttribute()
    {
        return $this->formatDateValue($this->getRawOriginal('updated_at'));
    }
}