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
</style>

{{-- 主页模态框调试脚本 --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('主页已加载，检查模态框功能...');
    
    // 强制定义全局函数，确保其可用
    window.openProductModal = function(productId) {
        console.log('🎯 主页：openProductModal 被调用，产品ID:', productId);
        
        // 触发自定义事件
        const event = new CustomEvent('open-product-modal', {
            detail: { productId: productId }
        });
        window.dispatchEvent(event);
        console.log('🔥 主页：事件已触发');
    };
    
    // 检查全局函数是否存在
    if (typeof window.openProductModal === 'function') {
        console.log('✅ window.openProductModal 函数已定义');
    } else {
        console.log('❌ window.openProductModal 函数未定义');
    }
    
    // 检查Alpine.js数据是否存在
    setTimeout(() => {
        const modalElement = document.getElementById('product-modal');
        if (modalElement) {
            console.log('✅ 产品模态框元素已找到');
            if (modalElement._x_dataStack) {
                console.log('✅ 产品模态框 Alpine.js 数据已初始化');
            } else {
                console.log('❌ 产品模态框 Alpine.js 数据未初始化');
            }
        } else {
            console.log('❌ 产品模态框元素未找到');
        }
    }, 1000);
    
    // 监听模态框打开事件
    window.addEventListener('open-product-modal', function(event) {
        console.log('🔥 主页：接收到打开模态框事件，产品ID:', event.detail.productId);
        
        // 查找模态框元素并触发Alpine.js事件
        const modalElement = document.getElementById('product-modal');
        if (modalElement && modalElement.__x) {
            console.log('📱 通过Alpine.js打开模态框');
            modalElement.__x.$data.openModal(event.detail.productId);
        } else {
            console.log('❌ 无法通过Alpine.js打开模态框');
        }
    });
});

// 立即定义函数，不等待DOM加载
if (typeof window.openProductModal !== 'function') {
    window.openProductModal = function(productId) {
        console.log('🎯 立即执行：openProductModal 被调用，产品ID:', productId);
        window.dispatchEvent(new CustomEvent('open-product-modal', {
            detail: { productId: productId }
        }));
    };
}
</script>
@endsection