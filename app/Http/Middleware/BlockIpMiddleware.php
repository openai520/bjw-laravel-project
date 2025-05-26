<?php

namespace App\Http\Middleware;

use App\Models\BlockedIp;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class BlockIpMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 获取当前访问者的IP地址
        $ip = $request->ip();

        // 从缓存中获取被禁止的IP列表，如果缓存不存在，则从数据库中获取
        $blockedIps = Cache::remember('blocked_ips_list', now()->addMinutes(60), function () {
            return BlockedIp::pluck('ip_address')->toArray();
        });

        // 如果IP在被禁止列表中，则返回403错误
        if (in_array($ip, $blockedIps)) {
            abort(403, '您的IP地址已被禁止访问。');
        }

        return $next($request);
    }
}
