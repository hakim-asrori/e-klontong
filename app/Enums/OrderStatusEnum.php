<?php

namespace App\Enums;

class OrderStatusEnum
{
    const ORDER = 1;
    const PACKING = 2;
    const SEND = 3;
    const RECEIVE = 4;

    public static function all()
    {
        return [
            self::ORDER => "Order",
            self::PACKING => "Packing",
            self::SEND => "Send",
            self::RECEIVE => "Receive",
        ];
    }

    public static function show($id)
    {
        return self::all()[$id];
    }
}
