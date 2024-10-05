<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    use HasFactory;

    protected $guarded = [];

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($productImage) {
            if ($productImage->path) {
                Storage::disk('public')->delete($productImage->path);
            }
        });
    }

    public function getRealPathAttribute()
    {
        return str_replace(url('/storage') . '/', "", $this->attributes['path']);
    }

    public function setPathAttribute($value)
    {
        if ($this->path && $this->path !== $value) {
            Storage::disk('public')->delete($this->path);
        }

        $this->attributes['path'] = $value;
    }
}
