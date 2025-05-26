<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Builder;

class ProductService
{
    /**
     * 获取产品列表
     *
     * @param array $filters 过滤条件
     * @param int $perPage 每页显示数量
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getProductsList(array $filters = [], int $perPage = 12)
    {
        $query = Product::with(['category', 'images' => function($query) {
                    $query->where('is_main', true);
                }])
                ->where('status', 'published');
        
        // 应用分类过滤
        if (!empty($filters['category'])) {
            $query->whereHas('category', function (Builder $query) use ($filters) {
                $query->where('slug', $filters['category']);
            });
        }
        
        // 应用搜索过滤
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function (Builder $query) use ($search) {
                $query->where('name', 'like', $search)
                      ->orWhere('description', 'like', $search)
                      ->orWhere('sku', 'like', $search);
            });
        }
        
        // 应用价格过滤
        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        
        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }
        
        // 应用排序
        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDirection = $filters['order_direction'] ?? 'desc';
        
        $allowedOrderBy = ['created_at', 'price', 'name'];
        $orderBy = in_array($orderBy, $allowedOrderBy) ? $orderBy : 'created_at';
        
        $query->orderBy($orderBy, $orderDirection);
        
        return $query->paginate($perPage);
    }
    
    /**
     * 获取最新产品
     *
     * @param int $limit 限制数量
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLatestProducts(int $limit = 12)
    {
        $cacheKey = 'products.latest.' . $limit;
        
        return Cache::remember($cacheKey, now()->addHours(2), function () use ($limit) {
            return Product::with(['category', 'images' => function($query) {
                        $query->where('is_main', true);
                    }])
                    ->where('status', 'published')
                    ->latest()
                    ->take($limit)
                    ->get();
        });
    }
    
    /**
     * 获取单个产品详情
     *
     * @param int $productId 产品ID
     * @return Product|null
     */
    public function getProductDetail(int $productId)
    {
        $cacheKey = 'products.detail.' . $productId;
        
        return Cache::remember($cacheKey, now()->addHour(), function () use ($productId) {
            return Product::with(['category', 'images'])
                    ->where('status', 'published')
                    ->findOrFail($productId);
        });
    }
    
    /**
     * 获取所有分类
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllCategories()
    {
        return Cache::remember('categories.all', now()->addDay(), function () {
            return Category::orderBy('sort_order', 'asc')->get();
        });
    }
} 