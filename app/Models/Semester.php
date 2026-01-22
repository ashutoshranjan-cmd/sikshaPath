<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Traits\DateFormatTrait;
use App\Services\CachingService;
use Carbon\Carbon;

class Semester extends Model {
    use HasFactory, SoftDeletes, DateFormatTrait;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'school_id',
        'created_at',
        'updated_at',
    ];
    protected $connection = 'school';

    protected $appends = ['current'];

    public function scopeOwner($query) {
        if (Auth::user()) {
            if (Auth::user()->school_id) {
                if (Auth::user()->hasRole('School Admin') || Auth::user()->hasRole('Teacher')) {
                    return $query->where('school_id', Auth::user()->school_id);
                }
        
                if (Auth::user()->hasRole('Student')) {
                    return $query->where('school_id', Auth::user()->school_id);
                }
                return $query->where('school_id', Auth::user()->school_id);
            }

            if (!Auth::user()->school_id) {
                if (Auth::user()->hasRole('Super Admin')) {
                    return $query;
                }
        
                if (Auth::user()->hasRole('Guardian')) {
                    return $query;
                }
                return $query;
            }
        }

        return $query;
    }

    public function class_subjects() {
        return $this->hasMany(ClassSubject::class, 'semester_id', 'id')->with('subject');
    }

    public function getCurrentAttribute() {
        $currentSemester = app(CachingService::class)->getDefaultSemesterData();
        if ($currentSemester && $this->id == $currentSemester->id) {
            return true;
        } else {
            return false;
        }
    }

    public function getCreatedAtAttribute()
    {
        return $this->formatDateValue($this->getRawOriginal('created_at'));
    }
    
    public function getUpdatedAtAttribute()
    {
        return $this->formatDateValue($this->getRawOriginal('updated_at'));
    }
    
    public function getStartDateAttribute($value) {
        return $this->formatDateOnly($value);
    }
    
    public function getEndDateAttribute($value) {
        return $this->formatDateOnly($value);
    }
}
