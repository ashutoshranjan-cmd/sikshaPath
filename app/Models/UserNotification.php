<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    use HasFactory;

    protected $fillable = ['notification_id', 'user_id'];

    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public function getCreatedAtAttribute()
    // {
    //     return $this->formatDateValue($this->getRawOriginal('created_at'));
    // }

    // public function getUpdatedAtAttribute()
    // {
    //     return $this->formatDateValue($this->getRawOriginal('updated_at'));
    // }
}
