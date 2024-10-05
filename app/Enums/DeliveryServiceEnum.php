<?php

namespace App\Enums;

class DeliveryServiceEnum
{
    const LAUT = 1;
    const UDARA = 2;

    public static function all()
    {
        return [
            self::LAUT => "Laut",
            self::UDARA => "Udara"
        ];
    }

    public static function show($id)
    {
        return self::all()[$id];
    }
}
