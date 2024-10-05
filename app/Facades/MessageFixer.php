<?php

namespace App\Facades;

use Illuminate\Support\Str;

class MessageFixer
{
    const ERROR_SERVER = 999;
    const CONTENT_TYPE_NULL = 998;
    const CONTENT_TYPE_WRONG = 997;
    const METHOD_REQUIRED_POST = 990;
    const FUNCTION_NOT_FOUND = 989;
    const WARNING_PROCESS = 919;
    const INVALID_BODY = 897;
    const UNAUTHORIZATION = 880;
    const DATA_NULL = 200;
    const DATA_OK = "000";

    public static function render($code = self::DATA_OK, $message = null, $data = null, $paginate = null)
    {
        $result = ["code" => $code];

        if ($message) {
            $result["messages"] = $message;
        }

        if ($data) {
            $result["data"] = $data;
        }

        if ($paginate) {
            $result["pagination"] = $paginate;
        }

        return response()->json($result);
    }

    public static function success($message)
    {
        return self::render(code: self::DATA_OK, message: $message);
    }

    public static function error($message)
    {
        return self::render(code: self::ERROR_SERVER, message: $message);
    }
}
