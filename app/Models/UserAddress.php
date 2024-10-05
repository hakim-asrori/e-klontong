<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $table = 'vw_user_addresses';

    public $incrementing = false;
    protected $primaryKey = null;
}
