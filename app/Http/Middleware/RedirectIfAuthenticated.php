<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // 如果是管理员，重定向到后台仪表盘
                if (Auth::user()->is_admin) {
                    return redirect()->route('admin.dashboard');
                }

                // 如果是普通用户，重定向到前台首页
                return redirect()->route('frontend.home', ['lang' => app()->getLocale()]);
            }
        }

        return $next($request);
    }
}
