<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LanguageController extends Controller
{
    /**
     * 切换应用程序语言
     */
    public function switchLanguage(Request $request, string $lang): RedirectResponse
    {
        // 定义支持的语言
        $supportedLocales = ['en', 'fr'];

        // 记录语言切换请求
        Log::debug('语言切换请求', [
            'requested_lang' => $lang,
            'current_session_locale' => session('locale'),
            'referer' => $request->header('referer'),
            'user_agent' => $request->header('user-agent'),
        ]);

        // 验证语言是否受支持
        if (! in_array($lang, $supportedLocales)) {
            Log::warning('尝试切换到不支持的语言', ['lang' => $lang]);

            return redirect()->back()
                ->with('error', __('Unsupported language selected.'));
        }

        // 清除可能影响语言切换的缓存
        Cache::forget('translations_'.$lang);
        Cache::forget('routes_'.$lang);

        // 将语言存储在会话中
        session(['locale' => $lang]);

        // 记录会话状态
        Log::debug('语言已存入会话', [
            'new_locale' => $lang,
            'session_locale_after_set' => session('locale'),
        ]);

        // 处理重定向逻辑
        $previousUrl = url()->previous();

        // 始终重定向到首页
        Log::debug('重定向到首页', ['lang' => $lang, 'previous' => $previousUrl]);

        return redirect()->route('frontend.home', ['lang' => $lang])
            ->with('success', __('Language changed successfully.'));
    }
}
