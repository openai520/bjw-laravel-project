<?php

namespace App\Http\Middleware;

use App\Models\VisitorLog;
use App\Jobs\ProcessGeoIpLookup;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
            $ip = $request->ip();
            
            // 使用缓存避免重复记录同一IP在短时间内的访问
            $cacheKey = "visitor_logged_{$ip}_" . date('Y-m-d-H'); // 每小时只记录一次同一IP
            
            if (Cache::has($cacheKey)) {
                // 如果这个IP在这小时内已经记录过，跳过
                return $response;
            }

            // 先创建基本的访问日志记录（不包含地理位置信息）
            $visitorLog = VisitorLog::create([
                'ip_address' => $ip,
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'referer' => $request->header('referer'),
                'country' => 'Unknown', // 默认设为Unknown，后台队列处理
                'visited_at' => Carbon::now(),
            ]);

            // 将地理位置查询任务放入队列异步处理
            ProcessGeoIpLookup::dispatch($visitorLog->id);

            // 设置缓存，1小时内不再记录同一IP
            Cache::put($cacheKey, true, 3600); // 3600秒 = 1小时

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
