<?php

namespace App\Http\Middleware;

use App\Models\VisitorLog;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Stevebauman\Location\Facades\Location;
use Symfony\Component\HttpFoundation\Response;

class LogVisitor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 先处理请求
        $response = $next($request);

        // 不记录后台访问和资源文件访问
        if ($this->shouldSkipLogging($request)) {
            return $response;
        }

        try {
            // 获取IP地址
            $ip = $request->ip();
            $country = 'Unknown';

            // 获取国家信息
            try {
                $location = Location::get($ip);
                if ($location) {
                    $country = $location->countryName ?? 'Unknown';
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to get location for IP: ' . $ip . '. Error: ' . $e->getMessage());
            }

            // 记录访问日志
            VisitorLog::create([
                'ip_address' => $ip,
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'referer' => $request->header('referer'),
                'country' => $country,
                'visited_at' => Carbon::now(),
            ]);
        } catch (\Exception $e) {
            // 记录日志失败不应影响用户体验，只静默失败
            \Log::error('Failed to log visitor: ' . $e->getMessage());
        }

        return $response;
    }

    /**
     * 判断是否应该跳过记录
     */
    private function shouldSkipLogging(Request $request): bool
    {
        // 跳过后台路由
        if ($request->is('admin*')) {
            return true;
        }

        // 跳过静态资源
        $path = $request->path();
        if (preg_match('/\.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$/i', $path)) {
            return true;
        }

        // 跳过API请求
        if ($request->is('api*')) {
            return true;
        }

        return false;
    }
}
