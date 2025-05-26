<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlockedIp;
use App\Models\VisitorLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdminIpAddressController extends Controller
{
    /**
     * 显示IP地址列表
     */
    public function index(): View
    {
        // 获取访问日志中的唯一IP地址，并按访问次数降序排列
        $ipAddresses = VisitorLog::select('ip_address', 'country')
            ->selectRaw('COUNT(*) as visit_count')
            ->selectRaw('MAX(visited_at) as last_visit')
            ->groupBy('ip_address', 'country')
            ->orderByDesc('visit_count')
            ->paginate(20);

        // 获取被禁止的IP列表
        $blockedIps = BlockedIp::pluck('ip_address')->toArray();

        return view('admin.ip_addresses.index', compact('ipAddresses', 'blockedIps'));
    }

    /**
     * 禁止IP地址
     */
    public function block(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ip_address' => 'required|ip',
        ]);

        // 添加IP到禁止列表
        BlockedIp::firstOrCreate(
            ['ip_address' => $validated['ip_address']],
            ['blocked_at' => now()]
        );

        // 清除缓存
        Cache::forget('blocked_ips_list');

        return redirect()->route('admin.ip_addresses.index')
            ->with('success', __('admin.ip_blocked_success'));
    }

    /**
     * 解除IP地址禁止
     */
    public function unblock(string $ip): RedirectResponse
    {
        // 验证IP格式
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return redirect()->route('admin.ip_addresses.index')
                ->with('error', __('admin.invalid_ip_address'));
        }

        // 从禁止列表中删除IP
        BlockedIp::where('ip_address', $ip)->delete();

        // 清除缓存
        Cache::forget('blocked_ips_list');

        return redirect()->route('admin.ip_addresses.index')
            ->with('success', __('admin.ip_unblocked_success'));
    }

    /**
     * 清除访问日志
     */
    public function clearLogs(Request $request): RedirectResponse
    {
        // 清除所有访问日志
        VisitorLog::truncate();

        return redirect()->route('admin.ip_addresses.index')
            ->with('success', __('admin.logs_cleared_success'));
    }
}
