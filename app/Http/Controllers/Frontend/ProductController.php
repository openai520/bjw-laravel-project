<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $currentPage = $request->get('page', 1); // 获取当前页码
        $categorySlug = $request->get('category'); // 获取分类slug
        $searchTerm = $request->get('search'); // 获取搜索词

        // 获取当前分类（如果有）
        $currentCategory = null;
        $categoryId = null;

        if ($categorySlug) {
            // 尝试通过slug查找分类
            $currentCategory = Category::where('slug', $categorySlug)->first();

            // 如果找不到，尝试通过ID查找（兼容旧链接）
            if (!$currentCategory && is_numeric($categorySlug)) {
                $currentCategory = Category::find($categorySlug);
            }

            if ($currentCategory) {
                $categoryId = $currentCategory->id;
            }
        }

        // 修改缓存策略，不使用标签，使用更具体的缓存键
        // 构建包含所有筛选条件的缓存键
        $cacheKey = 'products.index.page.' . $currentPage;
        if ($categoryId) {
            $cacheKey .= '.category.' . $categoryId;
        }
        if ($searchTerm) {
            $cacheKey .= '.search.' . $searchTerm;
        }

        // 减少缓存时间到10分钟，避免长时间缓存不更新
        $cacheDuration = now()->addMinutes(10);

        // 之前的缓存尝试，确保它们都被注释掉或移除
        /*
        $products = Cache::tags(['products.list'])->remember($cacheKey, $cacheDuration, function () use ($categoryId, $searchTerm) {
            // ...
        });
        */

        /*
        $products = Cache::remember($cacheKey, $cacheDuration, function () use ($categoryId, $searchTerm) {
            // ...
        });
        */

        // ---- START: DIRECT QUERY FOR DEBUGGING ----
        \Log::debug('[DIRECT_QUERY] ProductController@index: Querying products directly (cache bypassed).', [
            'cacheKey_would_be' => $cacheKey,
            'categoryId' => $categoryId,
            'searchTerm' => $searchTerm,
            'status' => 'published'
        ]);

        // 修复N+1查询问题 - 预加载所有需要的关系
        $query = Product::with(['category', 'mainImage', 'images']);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($searchTerm) {
            $query->where('name', 'like', "%{$searchTerm}%");
        }

        $products = $query->where('status', 'published')
                            ->latest()
                            ->paginate(12);

        \Log::debug('[DIRECT_QUERY] ProductController@index: Products query result.', [
            'count_on_current_page' => $products->count(),
            'total_items_matched' => $products->total(),
            // 'items_on_current_page' => $products->items() // Potentially large, uncomment if needed for specific item debug
        ]);
        // ---- END: DIRECT QUERY FOR DEBUGGING ----

        // 保留请求中的查询参数到分页链接中
        $products->appends($request->except('page'));

        // 处理AJAX请求，返回JSON格式
        if ($request->ajax()) {
            $view = view('frontend.partials._product_cards', compact('products'))->render();
            return response()->json([
                'html' => $view,
                'next_page_url' => $products->nextPageUrl(),
                'has_category' => !empty($categoryId)
            ]);
        }

        // 获取所有分类供导航使用
        $categories = Cache::remember('all.categories', now()->addHours(1), function () {
            return Category::orderBy('sort_order')->get();
        });

        return view('frontend.products.index', compact('products', 'currentCategory', 'searchTerm', 'categories'));
    }

    public function show(string $lang, Product $product)
    {
        try {
            // \Log::info("ProductController@show attempting to process lang: {$lang}, productId: {$product->id}");

            // 路由模型绑定已完成，$product 是 Product 模型实例

            // if (!$product) { // 这个检查理论上不需要了，因为路由模型绑定失败会404
            //     \Log::error("ProductController@show: Product not found (this should not happen with RMD). Lang: {$lang}");
            //     abort(404, 'Product specified was not found.');
            // }

            // \Log::info("ProductController@show: Product found: ID " . $product->id . ", Name: " . $product->name);

            // 确保产品已发布
            if ($product->status !== 'published') {
                // \Log::warning("ProductController@show: Product ID {$product->id} is not published. Lang: {$lang}");
                abort(404, 'Product is not available.');
            }

            $product->load('mainImage', 'images', 'category'); // 预加载所需关联

            $relatedProducts = Product::where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->where('status', 'published')
                ->with('mainImage') // 确保相关产品也加载主图
                ->inRandomOrder()
                ->take(4)
                ->get();

            return view('frontend.products.show', compact('product', 'relatedProducts', 'lang'));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // 特别捕获模型未找到的异常，以确保正确的404
            \Log::warning('ModelNotFoundException in ProductController@show for lang ' . $lang . ', product ID attempted: ' . request()->route('product') . '. Error: ' . $e->getMessage());
            abort(404, 'The requested product could not be found.');
        } catch (\Throwable $e) {
            // \Log::error('Critical error in ProductController@show for lang ' . $lang . ': ' . $e->getMessage());
            // \Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            // \Log::error('Stack trace: ' . $e->getTraceAsString());
            \Log::error('Critical error in ProductController@show for lang ' . $lang . ', product ID attempted: ' . request()->route('product') . ': ' . $e->getMessage(), ['exception' => $e]);
            return response('An internal server error occurred. Please try again later.', 500);
        }
    }

    /**
     * 获取产品详情数据用于模态框
     */
    public function getModalData(string $lang, Product $product)
    {
        try {
            // 确保产品已发布
            if ($product->status !== 'published') {
                return response()->json([
                    'success' => false,
                    'message' => 'Product is not available.'
                ], 404);
            }

            // 预加载所需关联，优化性能
            $product->load('mainImage', 'images', 'category');

            // 高效准备图片数据，避免重复的文件系统检查
            $images = $product->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'thumbnail_url' => $this->getOptimizedImageUrl($image, 'thumbnail'),
                    'main_image_url' => $this->getOptimizedImageUrl($image, 'main'),
                    'is_main' => $image->is_main
                ];
            });

            // 如果没有图片，提供占位符
            if ($images->isEmpty()) {
                $placeholderUrl = asset('img/placeholder.svg');
                $images = collect([[
                    'id' => 0,
                    'thumbnail_url' => $placeholderUrl,
                    'main_image_url' => $placeholderUrl,
                    'is_main' => true
                ]]);
            }

            // 获取主图URL，避免重复的文件系统检查
            $mainImageUrl = $this->getProductMainImageUrl($product);

            // 返回产品数据
            return response()->json([
                'success' => true,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->getTranslation('name', $lang) ?? $product->name,
                    'description' => $product->getTranslation('description', $lang) ?? $product->description,
                    'price' => $product->price,
                    'min_order_quantity' => $product->min_order_quantity,
                    'category' => [
                        'id' => $product->category->id ?? null,
                        'name' => $product->category ? ($product->category->getTranslation('name', $lang) ?? $product->category->name_en) : null,
                    ],
                    'images' => $images,
                    'main_image_url' => $mainImageUrl,
                    'thumbnail_url' => $mainImageUrl, // 使用相同的主图作为缩略图
                ],
                'cart_store_url' => route('frontend.cart.store', ['lang' => $lang]),
                'csrf_token' => csrf_token(),
                'translations' => [
                    'item_added_to_cart' => __('cart.item_added_to_cart'),
                    'error_adding' => __('cart.error_occurred'),
                    'processing' => __('cart.processing'),
                    'quantity' => __('messages.quantity'),
                    'minimum_order_quantity' => __('messages.minimum_order_quantity'),
                    'add_to_cart' => __('messages.add_to_cart'),
                    'product_details' => __('messages.product_details'),
                    'close' => __('messages.close'),
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in ProductController@getModalData: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while loading product data.'
            ], 500);
        }
    }

    /**
     * 高效获取图片URL，避免重复的文件系统检查
     */
    protected function getOptimizedImageUrl($image, $type = 'main')
    {
        if (!$image) {
            return asset('img/placeholder.svg');
        }

        $path = $type === 'thumbnail' && $image->thumbnail_path 
            ? $image->thumbnail_path 
            : $image->image_path;

        if ($path) {
            $cleanPath = ltrim($path, '/');
            return asset('storage/' . $cleanPath);
        }
        
        return asset('img/placeholder.svg');
    }

    /**
     * 高效获取产品主图URL
     */
    protected function getProductMainImageUrl($product)
    {
        // 如果已经预加载了mainImage关联
        if ($product->relationLoaded('mainImage') && $product->mainImage) {
            $cleanPath = ltrim($product->mainImage->image_path, '/');
            return asset('storage/' . $cleanPath);
        }

        // 如果没有主图，尝试获取第一个图片
        if ($product->relationLoaded('images')) {
            $firstImage = $product->images->first();
            if ($firstImage) {
                $cleanPath = ltrim($firstImage->image_path, '/');
                return asset('storage/' . $cleanPath);
            }
        }

        // 返回默认图片
        return asset('img/placeholder.svg');
    }
} 