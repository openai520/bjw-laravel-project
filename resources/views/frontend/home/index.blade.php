@extends('frontend.layouts.app')

@section('title', __('Home'))

@section('content')
@php
    // use Illuminate\Support\Facades\Storage; // Not explicitly used in this section
@endphp
<main>
    @include('frontend.partials._category_nav')

    <div class="w-full px-4 py-4">
        <!-- 每个分类及其产品 -->
        @foreach($categories as $category) 
            {{-- 只处理那些设置了推荐产品并且推荐产品不为空的分类 --}}
            @if($category->homeFeaturedProducts && $category->homeFeaturedProducts->count() > 0)
                <div class="mb-8 mx-auto" style="width: 1564px; max-width: 100%;">
                    <!-- 分类标题和查看更多 -->
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold text-neutral-700">
                            {{ app()->getLocale() === 'fr' ? ($category->name_fr ?? $category->name_en) : $category->name_en }}
                        </h2>
                        <a href="{{ route('frontend.products.index', ['lang' => app()->getLocale(), 'category' => $category->slug]) }}"
                           class="text-blue-600 hover:text-blue-800 flex items-center text-sm">
                            {{ __('messages.view_more') }}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>

                    <!-- 全屏幕滑动布局 -->
                    <div class="overflow-x-auto hide-scrollbar pb-4 home-horizontal-scroll-products">
                        <div class="flex gap-4 min-w-max">
                            {{-- 遍历通过 homeFeaturedProducts 加载的产品 --}}
                            @foreach($category->homeFeaturedProducts as $featuredProduct)
                                @if($featuredProduct->product) {{-- 确保产品存在 --}}
                                    <div class="w-[300px] flex-shrink-0">
                                        @include('frontend.partials._product_card', ['product' => $featuredProduct->product])
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- 旧的网格布局注释保持不变 --}}
                    {{-- <div class="hidden md:grid md:grid-cols-2 lg:grid-cols-3 gap-6 home-category-product-grid">
                        @foreach($categoryProducts[$category->id]->take(5) as $product)
                            @include('frontend.partials._product_card', ['product' => $product])
                        @endforeach
                    </div> --}}
                </div>
            @endif
        @endforeach
    </div>
</main>

<style>
/* 隐藏滚动条 */
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}

.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

/* 主页分类产品卡片特定样式 - 旧的网格布局，大部分将不再直接应用或被覆盖 */
/*
.home-category-product-grid .product-card {
    height: 380px;
    display: flex;
    flex-direction: column;
    background-color: #fff;
    border-radius: 0.375rem;
    border: none !important;
}

.home-category-product-grid .product-card .image-container {
    height: 240px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.home-category-product-grid .product-card .product-image {
    max-height: 100%;
    max-width: 100%;
    object-fit: cover;
    display: block;
}

.home-category-product-grid .product-card .product-info {
    padding: 0.5rem;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    flex-grow: 1;
    text-align: center;
}

.home-category-product-grid .product-card .product-info h3 {
    font-size: 1rem;
    font-weight: 500;
    min-height: 40px;
    max-height: 40px;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    margin-bottom: 0.25rem;
    line-height: 1.25rem;
}

.home-category-product-grid .product-card .product-info p {
    font-size: 1.125rem;
    font-weight: 600;
    line-height: 1.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
}
*/

/* 新增：主页水平滚动产品卡片特定样式 */
.home-horizontal-scroll-products .flex-shrink-0 { /* Targeting the w-[300px] div */
    width: 300px !important;
    max-width: 300px !important;
    min-width: 300px !important; /* Ensure this width is enforced */
    overflow: hidden !important; /* Prevent content from stretching it */
}

.home-horizontal-scroll-products .product-card {
    height: 400px !important; /* New height: 400px */
    padding: 0.75rem !important; /* p-3 (12px), to match _product_card base padding */
    width: 100% !important; 
    max-width: 100% !important;
    box-sizing: border-box !important;
    overflow: hidden !important; 
    display: flex !important; 
    flex-direction: column !important;
    /* 外卡片圆角 is 20px via inline style on .product-card in _product_card.blade.php */
}

.home-horizontal-scroll-products .product-card .info-wrapper {
    margin-top: 0.5rem !important; /* mt-2, Increased from 0.125rem */
    width: 100% !important;
    flex-grow: 1;
    overflow: visible !important; /* 改为visible防止文字被遮挡 */
    display: flex;
    flex-direction: column;
    justify-content: flex-start !important; /* 改为flex-start防止文字被推到下面 */
    align-items: flex-start !important; /* Ensure text content aligns to the start of the cross-axis */
}

.home-horizontal-scroll-products .product-card .image-container {
    height: 260px !important; /* 减少图片高度，留出更多空间给文字 */
    border-radius: 8px !important; /* Inner radius: 20px card - 12px padding = 8px */
    width: 100% !important; 
    flex-shrink: 0; 
    display: flex; /* Added to help center the <a> tag */
    align-items: center; /* Added to help center the <a> tag */
    justify-content: center; /* Added to help center the <a> tag */
}

/* Ensure the link inside image-container takes full space */
.home-horizontal-scroll-products .product-card .image-container a {
    display: flex;
    width: 100%;
    height: 100%;
    align-items: center;
    justify-content: center;
}

.home-horizontal-scroll-products .product-card .image-container img { /* Changed selector from .product-image */
    object-fit: cover !important;
    width: 100% !important;
    height: 100% !important;
    border-radius: 8px !important; /* Override inline style from _product_card for homepage */
}

/* Override title and price styles for homepage cards */
.home-horizontal-scroll-products .product-card h2.product-title {
    font-size: 1rem !important; /* text-base */
    line-height: 1.5rem !important;
    max-height: 3rem !important; /* Allow 2 lines for text-base */
    min-height: auto !important;
    margin-bottom: 0.25rem !important; /* mb-1 */
    -webkit-line-clamp: 2 !important; 
}

.home-horizontal-scroll-products .product-card span.product-price {
    font-size: 1.25rem !important; /* text-xl */
    line-height: 1.75rem !important;
}
</style>

{{-- 主页模态框调试脚本 --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('主页已加载，检查模态框功能...');
    
    // 检查全局函数是否存在
    if (typeof window.openProductModal === 'function') {
        console.log('✅ window.openProductModal 函数已定义');
    } else {
        console.log('❌ window.openProductModal 函数未定义');
    }
    
    // 检查Alpine.js数据是否存在
    setTimeout(() => {
        const modalElement = document.getElementById('product-modal');
        if (modalElement && modalElement._x_dataStack) {
            console.log('✅ 产品模态框 Alpine.js 数据已初始化');
        } else {
            console.log('❌ 产品模态框 Alpine.js 数据未初始化');
        }
    }, 1000);
    
    // 监听模态框打开事件
    window.addEventListener('open-product-modal', function(event) {
        console.log('🔥 主页：接收到打开模态框事件，产品ID:', event.detail.productId);
    });
});
</script>
@endsection