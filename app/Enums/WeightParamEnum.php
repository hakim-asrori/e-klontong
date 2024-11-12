<?php

namespace App\Enums;

class WeightParamEnum
{
    const GRAM = 1;
    const KILO = 2;

    public static function all()
    {
        return [
            self::GRAM => "Gr",
            self::KILO => "Kg",
        ];
    }

    public static function show($id)
    {
        return self::all()[$id];
    }
}
