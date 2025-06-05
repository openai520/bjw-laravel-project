<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductView;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class ProductAnalyticsController extends Controller
{
    /**
     * 显示产品访问统计主页
     */
    public function index(Request $request): View
    {
        $days = $request->get('days', 30); // 默认显示30天内的数据
        
        // 获取热门产品排行
        $popularProducts = ProductView::select('product_id', \DB::raw('COUNT(*) as view_count'))
            ->where('viewed_at', '>=', now()->subDays($days))
            ->groupBy('product_id')
            ->orderByDesc('view_count')
            ->limit(20)
            ->with(['product' => function($query) {
                $query->select('id', 'name', 'price', 'status');
            }])
            ->get();

        // 获取每日访问统计（最近7天）
        $dailyStats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = ProductView::whereDate('viewed_at', $date)->count();
            $dailyStats[] = [
                'date' => $date->format('Y-m-d'),
                'date_cn' => $date->format('m月d日'),
                'count' => $count
            ];
        }

        // 总体统计
        $totalViews = ProductView::count();
        $todayViews = ProductView::whereDate('viewed_at', today())->count();
        $thisWeekViews = ProductView::where('viewed_at', '>=', now()->startOfWeek())->count();
        $thisMonthViews = ProductView::where('viewed_at', '>=', now()->startOfMonth())->count();

        return view('admin.analytics.index', compact(
            'popularProducts', 
            'dailyStats', 
            'totalViews', 
            'todayViews', 
            'thisWeekViews', 
            'thisMonthViews',
            'days'
        ));
    }

    /**
     * 显示产品详细访问统计
     */
    public function productDetail(Request $request, Product $product): View
    {
        $days = $request->get('days', 30);

        // 获取产品访问历史
        $views = $product->views()
            ->where('viewed_at', '>=', now()->subDays($days))
            ->orderByDesc('viewed_at')
            ->paginate(50);

        // 获取每日访问统计
        $dailyStats = [];
        for ($i = intval($days) - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = $product->views()->whereDate('viewed_at', $date)->count();
            $dailyStats[] = [
                'date' => $date->format('Y-m-d'),
                'date_cn' => $date->format('m月d日'),
                'count' => $count
            ];
        }

        // 访问来源统计
        $referrerStats = $product->views()
            ->where('viewed_at', '>=', now()->subDays($days))
            ->selectRaw('referer, COUNT(*) as count')
            ->groupBy('referer')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return view('admin.analytics.product', compact(
            'product', 
            'views', 
            'dailyStats', 
            'referrerStats',
            'days'
        ));
    }
}
