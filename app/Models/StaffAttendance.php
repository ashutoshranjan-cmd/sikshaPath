<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Traits\DateFormatTrait;
use App\Services\CachingService;

class StaffAttendance extends Model
{
    use HasFactory, DateFormatTrait;

    protected $hidden = ["remark"];
    protected $fillable = [
        'staff_id',
        'session_year_id',
        'type',
        'date',
        'remark',
        'school_id',
        'leave_id',
        'leave_detail_id',
    ];

    protected $appends = ['get_date_original', 'date_format'];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'staff_id')->withTrashed();
    }

    public function scopeOwner($query)
    {
        if(Auth::user()) {
            if (Auth::user()->hasRole('Super Admin')) {
                return $query;
            }

            if (Auth::user()->hasRole('School Admin') || Auth::user()->hasRole('Teacher')) {
                return $query->where('school_id', Auth::user()->school_id);
            }

            if (Auth::user()->hasRole('Staff')) {
                return $query->where('school_id', Auth::user()->school_id);
            }
        }
        return $query;
    }

    public function getCreatedAtAttribute()
    {
        return $this->formatDateValue($this->getRawOriginal('created_at'));
    }
    
    public function getUpdatedAtAttribute()
    {
        return $this->formatDateValue($this->getRawOriginal('updated_at'));
    }

    public function getDateAttribute($value)
    {
        return $this->formatDateOnly($value);
    }

    public function getDateFormatAttribute()
    {
        $cache = app(CachingService::class);
        return $cache->getSchoolSettings('date_format');
    }

    public function getGetDateOriginalAttribute()
    {
        return $this->getRawOriginal('date');
    }
} 