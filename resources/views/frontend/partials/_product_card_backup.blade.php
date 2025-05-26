{{-- _product_card_backup.blade.php - 简化版本 --}}
@php
    // 优先使用缩略图，如果没有则使用默认占位符
    $thumbnailUrl = $product->thumbnail_url ?? asset('img/placeholder.svg');
@endphp
<a href="{{ route('frontend.products.show', ['lang' => app()->getLocale(), 'product' => $product->id]) }}"
   class="group block bg-white rounded-lg overflow-hidden border border-gray-200">

    <!-- 图片容器 -->
    <div class="w-full overflow-hidden">
        <img src="{{ $thumbnailUrl }}"
             alt="{{ $product->name ?? 'Product Image' }}"
             class="w-full h-40 object-contain object-center"
             loading="lazy">
    </div>

    <!-- 内容容器 -->
    <div class="p-3">
        <!-- 产品名称 -->
        <h3 class="text-sm text-gray-700 mb-2 font-medium text-center">
            {{ $product->name ?? 'N/A' }}
        </h3>

        <!-- 价格 -->
        <p class="text-base font-semibold text-red-600 text-center">
            ¥{{ number_format($product->price, 2) }}
        </p>
    </div>
</a> 