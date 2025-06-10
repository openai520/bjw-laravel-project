<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // 获取最新的12个已发布的产品 - 修复N+1查询问题
        $latestProducts = Product::with(['mainImage', 'images' => function($query) {
                                $query->where('is_main', true);
                            }])
                               ->where('status', 'published')
                               ->latest()
                               ->take(12)
                               ->get();

        // 获取所有希望在首页显示的分类，并预加载它们设置的推荐产品 - 修复N+1查询问题
        $categories = Category::where('show_on_home', true)
                            ->orderBy('display_order')
                            ->with([
                                'homeFeaturedProducts' => function ($query) {
                                    $query->orderBy('display_order');
                                }, 
                                'homeFeaturedProducts.product' => function ($query) {
                                    $query->where('status', 'published')
                                          ->with(['mainImage', 'images' => function($subQuery) {
                                              $subQuery->where('is_main', true);
                                          }]);
                                }
                            ])
                            ->get();

        // 对每个分类，确保其 homeFeaturedProducts 集合中的 product 不为 null
        $categories->each(function ($category) {
            $category->homeFeaturedProducts = $category->homeFeaturedProducts->filter(function ($featuredProduct) {
                return $featuredProduct->product !== null;
            });
        });

        // 获取当前分类（如果有）
        $currentCategory = null;
        if ($categorySlug = $request->input('category')) {
            $currentCategory = Category::where('slug', $categorySlug)->first();
        }

        return view('frontend.home.index', compact('latestProducts', 'categories', 'currentCategory'));
    }
}
