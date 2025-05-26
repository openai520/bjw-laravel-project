<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\InquiryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InquiryController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'wechat' => 'nullable|string|max:50',
        ]);

        try {
            DB::beginTransaction();

            // 获取IP地址
            $ipAddress = $request->ip();

            // 生成询价单号 (前缀INQ + 当前日期 + 随机6位数字)
            $inquiryNo = 'INQ' . date('Ymd') . mt_rand(100000, 999999);

            // 计算总数量和总金额
            $cart = session()->get('cart', []);
            $totalQuantity = 0;
            $totalAmount = 0;

            foreach ($cart as $item) {
                $totalQuantity += $item['quantity'];
                $totalAmount += $item['quantity'] * $item['price'];
            }

            // 创建询价单
            $inquiry = Inquiry::create([
                'inquiry_no' => $inquiryNo,
                'name' => $request->name,
                'country' => $request->country,
                'phone' => $request->phone,
                'whatsapp' => $request->whatsapp,
                'wechat' => $request->wechat,
                'ip_address' => $ipAddress,
                'total_quantity' => $totalQuantity,
                'total_amount' => $totalAmount,
                'status' => 'pending',
            ]);

            // 创建询价单项目
            foreach ($cart as $productId => $item) {
                InquiryItem::create([
                    'inquiry_id' => $inquiry->id,
                    'product_id' => $productId,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            // 清空购物车
            session()->forget('cart');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '询价单提交成功！'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('询价单提交失败: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => '提交失败，请重试'
            ], 500);
        }
    }
}
