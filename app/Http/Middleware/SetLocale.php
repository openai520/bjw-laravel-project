<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 定义支持的语言
        $supportedLocales = ['en', 'fr'];

        // 记录调试信息
        Log::debug('SetLocale 中间件开始', [
            'route_lang' => $request->route('lang'),
            'session_locale' => session('locale'),
            'url' => $request->url(),
            'path' => $request->path()
        ]);
        
        // 优先从会话中获取语言
        $locale = session('locale');
        
        // 如果会话中没有语言设置，则从路由参数获取
        if (!$locale) {
            $locale = $request->route('lang');
        }
        
        // 如果路由中也没有语言参数，则从浏览器首选项中获取
        if (!$locale) {
            $locale = $request->getPreferredLanguage($supportedLocales);
        }
        
        // 如果获取的语言不受支持，则使用默认语言
        if (!in_array($locale, $supportedLocales)) {
            $locale = config('app.fallback_locale', 'en');
        }
        
        // 设置应用程序语言
        App::setLocale($locale);
        
        // 将语言存储在会话中
        session(['locale' => $locale]);
        
        // 设置 URL 默认参数
        URL::defaults(['lang' => $locale]);
        
        // 记录最终设置的语言
        Log::debug('最终设置的语言', [
            'locale' => $locale,
            'app_locale' => App::getLocale()
        ]);
        
        return $next($request);
    }
}
