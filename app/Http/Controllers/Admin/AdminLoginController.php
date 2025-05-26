<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class AdminLoginController extends Controller
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * 显示管理员登录表单
     */
    public function showLoginForm(): View
    {
        return view('admin.auth.login');
    }

    /**
     * 处理管理员登录请求
     */
    public function login(Request $request): RedirectResponse
    {
        // 验证请求数据
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // 尝试登录
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // 检查是否是管理员
            if (!Auth::user()->is_admin) {
                Auth::logout();
                Session::invalidate();
                Session::regenerateToken();
                
                return back()
                    ->withInput($request->only('email', 'remember'))
                    ->withErrors([
                        'email' => __('You do not have permission to access the admin area.'),
                    ]);
            }

            $request->session()->regenerate();

            return redirect()
                ->intended(route('admin.dashboard'))
                ->with('success', __('Welcome back!'));
        }

        // 登录失败
        return back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors([
                'email' => __('The provided credentials do not match our records.'),
            ]);
    }

    /**
     * 处理管理员登出请求
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        Session::invalidate();
        Session::regenerateToken();

        return redirect()
            ->route('admin.login')
            ->with('success', __('You have been logged out successfully.'));
    }
}
