<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        dd("OK");
        return response()->json([
            'message' => 'Custom unauthorized message here.'
        ], 401);
        if ($request->expectsJson()) {
        }

        return redirect()->guest(route('login'));
    }

    public function render($request, Throwable $exception)
    {
        return parent::render($request, $exception);
    }
}
