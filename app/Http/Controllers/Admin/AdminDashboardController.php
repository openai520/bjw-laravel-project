<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\Product;
use App\Models\Category;
use App\Models\VisitorLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * 显示管理后台仪表盘
     */
    public function index(): View
    {
        // 获取统计数据
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $pendingInquiries = Inquiry::where('status', 'pending')->count();
        $processedInquiries = Inquiry::where('status', 'processed')->count();
        
        // 获取最新的5条询价单记录
        $latestInquiries = Inquiry::latest()->take(5)->get();

        // 获取访问次数最多的前10个国家
        $topCountriesByVisits = VisitorLog::select('country')
            ->selectRaw('COUNT(*) as total_visits')
            ->whereNotNull('country')
            ->where('country', '!=', 'Unknown')
            ->groupBy('country')
            ->orderByDesc('total_visits')
            ->limit(10)
            ->get();
        
        return view('admin.dashboard', compact(
            'totalProducts', 
            'totalCategories', 
            'pendingInquiries', 
            'processedInquiries',
            'latestInquiries',
            'topCountriesByVisits'
        ));
    }
}
