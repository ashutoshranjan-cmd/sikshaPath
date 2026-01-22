<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Vehicle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'vehicle_number',
        'capacity',
        'status',
    ];

    protected $appends = array('file_extension');

    protected static function boot() {
        parent::boot();
        static::deleting(static function ($vehicle) { // before delete() method call this
            if ($vehicle->file) {
                foreach ($vehicle->file as $file) {
                    if (Storage::disk('public')->exists($file->getRawOriginal('file_url'))) {
                        Storage::disk('public')->delete($file->getRawOriginal('file_url'));
                    }
                    if ($file->file_thumbnail && Storage::disk('public')->exists($file->getRawOriginal('file_thumbnail'))) {
                        Storage::disk('public')->delete($file->getRawOriginal('file_thumbnail'));
                    }
                }

                $vehicle->file()->delete();
            }
            if ($vehicle->topic) {
                $vehicle->topic()->delete();
            }
        });
    }

    public function routeVehicles()
    {
        return $this->hasMany(RouteVehicle::class, 'vehicle_id');
    }

    public function file() {
        return $this->morphMany(File::class, 'modal');
    }

    public function getFileExtensionAttribute() {
        if (!empty($this->thumbnail)) {
            return pathinfo(url(Storage::url($this->thumbnail)), PATHINFO_EXTENSION);
        }

        return "";
    }
}
