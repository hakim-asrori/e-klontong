<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProductCategory extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $with = ['category'];

    public $timestamps = false;

    public function category(): HasOne
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }
}
