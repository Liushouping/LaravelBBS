<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        // 三個判斷：
        // 1. 如果用户已经登陸
        // 2. 並且还未認證 Email
        // 3. 並且访问的不是 email 驗證相關 URL 或者退出的 URL。
        if ($request->user() &&
            ! $request->user()->hasVerifiedEmail() &&
            ! $request->is('email/*', 'logout')) {

            // 根據客户端返回對應的内容
            return $request->expectsJson()
                        ? abort(403, '您的信箱尚未進行認證')
                        : redirect()->route('verification.notice');
        }

        return $next($request);
    }
}