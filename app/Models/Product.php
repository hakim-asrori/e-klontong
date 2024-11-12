<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price', 'description', 'status', 'weight', 'weight_param', 'slug'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($product) {
            if ($product->image->path) {
                Storage::disk('public')->delete($product->image->path);
            }
        });
    }

    public function pathImage()
    {
        return 'products/' . date('Y/Ym/Ymd');
    }

    public function image()
    {
        return $this->hasOne(ProductImage::class, 'product_id', 'id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    public function deliveryService()
    {
        return $this->hasOne(ProductService::class, 'product_id', 'id');
    }
}
