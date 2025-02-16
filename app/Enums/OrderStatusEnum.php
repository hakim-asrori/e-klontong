<?php

namespace App\Enums;

class OrderStatusEnum
{
    const ORDER = 1;
    const PACKING = 2;
    const SEND = 3;
    const RECEIVE = 4;
    const CANCEL = 5;

    public static function all()
    {
        return [
            self::ORDER => "Order",
            self::PACKING => "Packing",
            self::SEND => "Send",
            self::RECEIVE => "Receive",
            self::CANCEL => "Cancel"
        ];
    }

    public static function show($id)
    {
        dd(self::all(), $id);
        return self::all()[$id];
    }
}
