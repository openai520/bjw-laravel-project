<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // 将分类数据绑定到需要的视图
        View::composer([
            'frontend.home.index',
            'frontend.products.index',
            'frontend.partials._category_nav',
        ], function ($view) {
            $locale = app()->getLocale();
            $categories = Category::orderBy('sort_order', 'asc')
                ->get();

            $view->with('categories', $categories);
        });
    }
}
