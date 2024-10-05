<?php

namespace App\Http\Middleware;

use App\Facades\MessageFixer;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        if (!$request->hasSession() || !$request->user()) {
            return MessageFixer::render(code: MessageFixer::UNAUTHORIZATION, message: 'Unauthorized');
        }

        return $next($request);
    }
}
