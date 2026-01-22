<?php

namespace App\Models;

use App\Repositories\Semester\SemesterInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Traits\DateFormatTrait;

class OnlineExamQuestion extends Model {
    use HasFactory, DateFormatTrait;

    protected $fillable = [
        'question',
        'image_url',
        'note',
        'difficulty',
        'last_edited_by',
        'school_id',
        'class_id',
        'class_subject_id'
    ];
    protected $appends = ['class_with_medium','subject_with_name'];


    protected static function boot() {
        parent::boot();
        static::deleting(static function ($data) { // before delete() method call this
            if($data->getAttributes()['image_url']){
                if (Storage::disk('public')->exists($data->getAttributes()['image_url'])) {
                    Storage::disk('public')->delete($data->getAttributes()['image_url']);
                }
            }
        });
    }

    public function options() {
        return $this->hasMany(OnlineExamQuestionOption::class, 'question_id');
    }

    public function class() {
        return $this->belongsTo(ClassSchool::class, 'class_id')->with('medium:id,name')->withTrashed();
    }

    public function class_subject() {
        return $this->belongsTo(ClassSubject::class,'class_subject_id');
    }

    public function getImageUrlAttribute($value) {
        if ($value) {
            return url(Storage::url($value));
        }

        return null;
    }

    public function scopeOwner($query)
    {
        if (Auth::user()) {
            if (Auth::user()->hasRole('Super Admin')) {
                return $query;
            }
    
            if (Auth::user()->hasRole('School Admin')) {
                return $query->where('school_id', Auth::user()->school_id);
            }
    
            if(Auth::user()->hasRole('Teacher')){
                $subjectTeacherData = SubjectTeacher::where('teacher_id',Auth::user()->id)->with('class_section')->get();
                $classIds = $subjectTeacherData->pluck('class_section.class_id')->unique()->filter()->toArray();
                $classSubjectIds = $subjectTeacherData->pluck('class_subject_id')->unique()->filter()->toArray();
                return $query->whereIn('class_id', $classIds)
                    ->whereIn('class_subject_id', $classSubjectIds)
                    ->where('school_id', Auth::user()->school_id);
            }
    
    
            if (Auth::user()->hasRole('Student')) {
                return $query->where('school_id', Auth::user()->school_id);
            }
    
            if (Auth::user()->school_id) {
                return $query->where('school_id', Auth::user()->school_id);
            }
        }

        return $query;
    }

    public function getClassWithMediumAttribute() {
        if ($this->relationLoaded('class') && $this->class) {
            $name = $this->class->name;
            if ($this->class->relationLoaded('medium') && $this->class->medium) {
                $name .= ' - ' . $this->class->medium->name;
            }
            return $name;
        }
        return null;
    }

    public function getSubjectWithNameAttribute() {
        if ($this->relationLoaded('class_subject') && $this->class_subject && $this->class_subject->relationLoaded('subject') && $this->class_subject->subject) {
            return $this->class_subject->subject->name . ' - ' . $this->class_subject->subject->type;
        }
        return null;
    }

    public function scopeCurrentSemesterData($query){
        $currentSemester = app(SemesterInterface::class)->default();
        if($currentSemester){
            $query->where(function ($query) use($currentSemester){
                $query->where('semester_id', $currentSemester->id)->orWhereNull('semester_id');
            });
        }
    }

    public function subject_teacher() {
        return $this->hasMany(SubjectTeacher::class, 'class_subject_id','class_subject_id');
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
