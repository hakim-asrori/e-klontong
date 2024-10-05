<?php

namespace App\Models;

use App\Facades\RegionFixer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function province()
    {
        return $this->hasOne(Province::class, 'id', 'province_id');
    }

    public function regency()
    {
        return $this->hasOne(Regency::class, 'id', 'regency_id');
    }

    public function district()
    {
        return $this->hasOne(District::class, 'id', 'district_id');
    }

    public function village()
    {
        return $this->hasOne(Village::class, 'id', 'village_id');
    }
}
