<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class RecordLastActivedTime
{
    public function handle($request, Closure $next)
    {
        // 如果是登陸用户的话
        if (Auth::check()) {
            // 紀錄最後登陸時間
            Auth::user()->recordLastActivedAt();
        }

        return $next($request);
    }
}