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

        try {
            // 从缓存中获取被禁止的IP列表，如果缓存不存在，则从数据库中获取
            $blockedIps = Cache::remember('blocked_ips_list', now()->addMinutes(60), function () {
                // 使用try/catch防止数据库查询失败导致整个应用崩溃
                try {
                    return BlockedIp::pluck('ip_address')->toArray();
                } catch (\Exception $e) {
                    // 记录错误但返回空数组
                    \Illuminate\Support\Facades\Log::error('获取禁止IP列表失败: ' . $e->getMessage());
                    return [];
                }
            });

            // 如果IP在被禁止列表中，则返回403错误
            if (in_array($ip, $blockedIps)) {
                abort(403, '您的IP地址已被禁止访问。');
            }
        } catch (\Exception $e) {
            // 捕获任何异常，记录日志但允许请求继续
            \Illuminate\Support\Facades\Log::error('IP屏蔽检查失败: ' . $e->getMessage());
        }

        return $next($request);
    }
} 