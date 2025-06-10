@php
    // 优先使用缩略图，然后是主图，最后是占位符
    $imageUrl = $product->thumbnail_url ?? ($product->main_image_url ?? asset('img/placeholder.svg'));
    $productName = $product->getTranslation('name', app()->getLocale()) ?? ($product->name ?? __('N/A'));
    $productPrice = $product->price ?? '0.00';
@endphp

<div class="product-card w-full bg-white rounded-3xl overflow-hidden shadow-sm transition-all duration-300 hover:shadow-md p-3 sm:p-4 flex flex-col cursor-pointer" 
     style="height: 350px; border-radius: 20px !important;"
     @click.prevent="$dispatch('open-product-modal', { productId: {{ $product->id }} })"
     x-data="{ clicked: false }"
     @click="clicked = true; setTimeout(() => clicked = false, 200)"
     :class="{ 'scale-95': clicked }"
     class="transition-transform duration-200">
    <div class="image-container flex items-center justify-center h-[260px] rounded-3xl overflow-hidden bg-gray-50" 
         style="border-radius: 10px !important;">
        <img 
            x-data="{ loaded: false }"
            x-init="$nextTick(() => { if ($el.complete) loaded = true })"
            @load.once="loaded = true"
            src="{{ $imageUrl }}" 
            alt="{{ $productName }}"
            class="object-cover rounded-3xl max-h-full max-w-full transition-opacity duration-300 ease-in-out" 
            :class="{ 'opacity-100': loaded, 'opacity-0': !loaded }" 
            style="border-radius: 10px !important; object-fit: cover;"
            onerror="this.onerror=null; this.src='{{ asset('img/placeholder.svg') }}';"
            loading="lazy"
        />
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