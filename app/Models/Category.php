<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        // static::deleting(function ($category) {
        //     if ($category->image) {
        //         Storage::disk('public')->delete($category->image);
        //     }
        // });
    }

    // public function setImageAttribute($value)
    // {
    //     if ($this->image && $this->image !== $value) {
    //         Storage::disk('public')->delete($this->image);
    //     }

    //     $this->attributes['image'] = $value;
    // }

    public function pathImage()
    {
        return 'categories/' . date('Y/Ym/Ymd');
    }

    public function getPathImageAttribute()
    {
        return str_replace(url('/storage') . '/', "", $this->attributes['image']);
    }

    // public function products() : BelongsToMany {
    //     return $this->belongsToMany(Product::class, 'product_categories');
    // }
}
