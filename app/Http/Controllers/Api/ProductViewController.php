<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\RecordProductView;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductViewController extends Controller
{
    /**
     * 记录产品访问（异步处理）
     */
    public function recordView(Request $request, Product $product): JsonResponse
    {
        try {
            // 获取访问信息
            $ipAddress = $request->ip();
            $userAgent = $request->userAgent();
            $referer = $request->header('referer');

            // 临时直接同步执行，用于测试
            // 检查是否为重复访问（同一IP在1小时内访问同一产品只记录一次）
            if (!\App\Models\ProductView::isDuplicateView($product->id, $ipAddress, 60)) {
                // 记录访问
                \App\Models\ProductView::create([
                    'product_id' => $product->id,
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent,
                    'referer' => $referer,
                    'viewed_at' => now(),
                ]);

                \Log::info('产品访问记录成功', [
                    'product_id' => $product->id,
                    'ip_address' => $ipAddress
                ]);
            } else {
                \Log::info('重复访问被过滤', [
                    'product_id' => $product->id,
                    'ip_address' => $ipAddress
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => '访问统计已记录'
            ]);

        } catch (\Exception $e) {
            \Log::error('记录产品访问API错误: ' . $e->getMessage(), [
                'product_id' => $product->id,
                'ip' => $request->ip(),
                'trace' => $e->getTraceAsString()
            ]);

            // 即使统计失败也返回成功，不影响用户体验
            return response()->json([
                'success' => true,
                'message' => '访问统计处理中'
            ]);
        }
    }

    /**
     * 获取产品访问统计
     */
    public function getStats(Product $product): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'total_views' => $product->view_count,
                    'today_views' => $product->today_view_count,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取统计数据失败'
            ], 500);
        }
    }
}
