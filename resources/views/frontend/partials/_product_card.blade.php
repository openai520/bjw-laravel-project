@php
    // 优先使用缩略图，然后是主图，最后是占位符
    $imageUrl = $product->thumbnail_url ?? ($product->main_image_url ?? asset('img/placeholder.svg'));
    $productName = $product->getTranslation('name', app()->getLocale()) ?? ($product->name ?? __('N/A'));
    $productPrice = $product->price ?? '0.00';
@endphp

<div class="product-card w-full bg-white rounded-3xl overflow-hidden shadow-sm transition-all duration-300 hover:shadow-md p-3 sm:p-4 flex flex-col cursor-pointer" 
     style="height: 350px; border-radius: 20px !important;"
     onclick="console.log('🎯 产品卡片被点击！产品ID: {{ $product->id }}'); console.log('🔍 检查openProductModal函数:', typeof window.openProductModal); if(typeof window.openProductModal === 'function') { console.log('📞 调用openProductModal函数...'); window.openProductModal({{ $product->id }}); } else { console.error('❌ openProductModal函数未找到!'); }">
    {{-- 用 x-data 初始化一个 'loaded' 状态 --}}
    <div class="image-container flex justify-center h-[260px] rounded-3xl overflow-hidden" 
         x-data="{ loaded: false }"
         style="border-radius: 10px !important;">
        <div class="flex items-center justify-center w-full h-full">
            <img 
                src="{{ $imageUrl }}" 
                alt="{{ $productName }}"
                class="object-cover rounded-3xl max-h-full max-w-full transition-opacity duration-300 ease-in-out" 
                :class="{ 'opacity-100': loaded, 'opacity-0': !loaded }" 
                style="border-radius: 10px !important; object-fit: cover;"
                onerror="this.onerror=null; this.src='{{ asset('img/placeholder.svg') }}';"
                loading="lazy"
                @load="loaded = true" 
            />
        </div>
    </div>

    <div class="info-wrapper mt-2 flex flex-col justify-start flex-grow">
        <div>
            <h2 class="text-base font-bold text-neutral-700 truncate max-w-full mb-1">
                {{ $productName }}
            </h2>
        </div>
        <div>
            <span class="text-xl font-bold text-red-600 whitespace-nowrap">¥{{ number_format((float)$productPrice, 2) }}</span>
        </div>
    </div>
</div> 