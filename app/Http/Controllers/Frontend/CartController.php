<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        $total = 0;
        $needsUpdate = false;

        // 收集所有需要更新图片URL的产品ID
        $productIds = [];
        foreach ($cart as $productId => $item) {
            $total += $item['price'] * $item['quantity'];
            if (!isset($item['main_image_url'])) {
                $productIds[] = $productId;
                $needsUpdate = true;
            }
        }

        // 如果有需要更新的产品，批量查询数据库
        if ($needsUpdate) {
            try {
                // 批量获取所有需要的产品
                $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
                
                // 更新购物车数据
                foreach ($cart as $productId => &$item) {
                    if (!isset($item['main_image_url'])) {
                        if (isset($products[$productId])) {
                            $item['main_image_url'] = $products[$productId]->main_image_url;
                        } else {
                            $item['main_image_url'] = $this->getDefaultImageSvg();
                        }
                    }
                }
                unset($item); // 解除引用

                // 更新session中的购物车数据
                session()->put('cart', $cart);
                
                \Log::info("Updated cart items with missing image URLs", [
                    'updated_products' => count($productIds),
                    'total_cart_items' => count($cart)
                ]);
            } catch (\Exception $e) {
                \Log::error("Error updating cart items with images: " . $e->getMessage());
                // 为所有缺少图片的项目设置默认图片
                foreach ($cart as $productId => &$item) {
                    if (!isset($item['main_image_url'])) {
                        $item['main_image_url'] = $this->getDefaultImageSvg();
                    }
                }
                unset($item);
            }
        }

        return view('frontend.cart.index', compact('cart', 'total'));
    }

    /**
     * 获取默认的SVG图片
     */
    protected function getDefaultImageSvg()
    {
        return 'data:image/svg+xml;base64,' . base64_encode('
            <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
                <rect width="100" height="100" fill="#f3f4f6"/>
                <text x="50" y="50" font-family="Arial" font-size="12" fill="#9ca3af" text-anchor="middle" dy=".3em">暂无图片</text>
            </svg>
        ');
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $product = Product::findOrFail($request->product_id);
            $cart = session()->get('cart', []);
            $quantity = max($request->quantity, $product->min_order_quantity);

            // 获取主图URL并记录日志
            $mainImageUrl = $product->main_image_url;
            \Log::debug("Adding product to cart", [
                'product_id' => $product->id,
                'main_image_url' => $mainImageUrl
            ]);

            if (isset($cart[$product->id])) {
                $cart[$product->id]['quantity'] += $quantity;
            } else {
                $cart[$product->id] = [
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $quantity,
                    'min_order_quantity' => $product->min_order_quantity,
                    'main_image_url' => $mainImageUrl
                ];
            }

            session()->put('cart', $cart);

            return response()->json([
                'success' => true,
                'message' => __('cart.item_added_to_cart'),
                'cart_count' => count($cart)
            ]);

        } catch (\Exception $e) {
            \Log::error("Error adding product to cart: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('cart.error_occurred')
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $quantity = $request->input('quantity');
            
            if ($request->isJson()) {
                $quantity = $request->json('quantity');
            }

            if (!is_numeric($quantity)) {
                return response()->json([
                    'success' => false,
                    'message' => __('cart.error_occurred')
                ], 422);
            }
            
            $cart = session()->get('cart', []);
            
            if (!isset($cart[$id])) {
                return response()->json([
                    'success' => false,
                    'message' => __('cart.error_occurred')
                ], 404);
            }

            $quantity = (int)$quantity;
            $minOrderQuantity = $cart[$id]['min_order_quantity'];
            
            if ($quantity < $minOrderQuantity) {
                return response()->json([
                    'success' => false,
                    'message' => __('cart.min_quantity_error', ['min' => $minOrderQuantity]),
                    'min_order_quantity' => $minOrderQuantity
                ], 422);
            }

            $cart[$id]['quantity'] = $quantity;
            session()->put('cart', $cart);

            // 计算新的总数和总金额
            $totalQuantity = 0;
            $totalAmount = 0;
            foreach ($cart as $item) {
                $totalQuantity += $item['quantity'];
                $totalAmount += $item['quantity'] * $item['price'];
            }
            
            return response()->json([
                'success' => true,
                'message' => __('cart.cart_updated'),
                'new_quantity' => $quantity,
                'total_quantity' => $totalQuantity,
                'total_amount' => $totalAmount
            ]);

        } catch (\Exception $e) {
            \Log::error('更新购物车失败: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('cart.error_occurred')
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $cart = session()->get('cart', []);
            
            if (!isset($cart[$id])) {
                return response()->json([
                    'success' => false,
                    'message' => __('cart.error_occurred')
                ], 404);
            }

            unset($cart[$id]);
            session()->put('cart', $cart);

            // 计算新的总数和总金额
            $totalQuantity = 0;
            $totalAmount = 0;
            foreach ($cart as $item) {
                $totalQuantity += $item['quantity'];
                $totalAmount += $item['quantity'] * $item['price'];
            }
            
            return response()->json([
                'success' => true,
                'message' => __('cart.item_deleted'),
                'cart_count' => count($cart),
                'total_quantity' => $totalQuantity,
                'total_amount' => $totalAmount
            ]);
        } catch (\Exception $e) {
            \Log::error('删除购物车商品失败: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('cart.error_occurred')
            ], 500);
        }
    }
}
