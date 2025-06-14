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
            @foreach ($categories as $category)
                {{-- 只处理那些设置了推荐产品并且推荐产品不为空的分类 --}}
                @if ($category->homeFeaturedProducts && $category->homeFeaturedProducts->count() > 0)
                    <div class="mb-8 mx-auto" style="width: 1564px; max-width: 100%;">
                        <!-- 分类标题和查看更多 -->
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-xl font-bold text-neutral-700">
                                {{ app()->getLocale() === 'fr' ? $category->name_fr ?? $category->name_en : $category->name_en }}
                            </h2>
                            <a href="{{ route('frontend.products.index', ['lang' => app()->getLocale(), 'category' => $category->slug]) }}"
                                class="text-blue-600 hover:text-blue-800 flex items-center text-sm">
                                {{ __('messages.view_more') }}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>

                        <!-- 全屏幕滑动布局 -->
                        <div class="overflow-x-auto hide-scrollbar pb-4 home-horizontal-scroll-products">
                            <div class="flex gap-4 min-w-max">
                                {{-- 遍历通过 homeFeaturedProducts 加载的产品 --}}
                                @foreach ($category->homeFeaturedProducts as $featuredProduct)
                                    @if ($featuredProduct->product)
                                        {{-- 确保产品存在 --}}
                                        <div class="w-[300px] flex-shrink-0">
                                            @include('frontend.partials._product_card', [
                                                'product' => $featuredProduct->product,
                                            ])
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        {{-- 旧的网格布局注释保持不变 --}}
                        {{-- <div class="hidden md:grid md:grid-cols-2 lg:grid-cols-3 gap-6 home-category-product-grid">
                        @foreach ($categoryProducts[$category->id]->take(5) as $product)
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


@endsection
