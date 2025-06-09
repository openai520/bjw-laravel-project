{{-- 响应式模态框到全屏视图 - 产品详情 --}}
<div id="product-modal" 
     x-data="productModal()" 
     x-show="isOpen" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @keydown.escape.window="closeModal()"
     @open-product-modal.window="openModal($event.detail.productId)"
     class="fixed inset-0 z-50 
            block
            md:flex md:items-center md:justify-center md:p-4"
     style="display: none; background-color: rgba(0, 0, 0, 0.4); backdrop-filter: blur(2px);"
     @click.self="closeModal()">
    
    <!-- 响应式容器：移动端全屏，桌面端模态框 -->
    <div class="relative 
                w-full h-full rounded-none shadow-none
                md:max-w-4xl md:max-h-[90vh] md:rounded-lg md:shadow-xl
                mx-auto"
         x-transition:enter="transition ease-out duration-300 delay-100"
         x-transition:enter-start="opacity-0 md:transform md:scale-90 md:translate-y-5"
         x-transition:enter-end="opacity-100 md:transform md:scale-100 md:translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 md:transform md:scale-100 md:translate-y-0"
         x-transition:leave-end="opacity-0 md:transform md:scale-90 md:translate-y-5">
        
        <!-- 响应式关闭按钮 -->
        <button @click="closeModal()" 
                class="absolute z-50
                       top-4 right-4 w-8 h-8 bg-black/50 text-white rounded-full flex items-center justify-center
                       md:top-[-20px] md:left-[-20px] md:w-10 md:h-10 md:bg-red-500 md:border-3 md:border-white
                       hover:bg-black/70 md:hover:bg-red-600 md:hover:scale-110
                       transition-all duration-300"
                style="backdrop-filter: blur(4px);">
            <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        
        <!-- 响应式内容容器 -->
        <div class="bg-white w-full h-full overflow-hidden
                    rounded-none shadow-none
                    md:rounded-lg md:shadow-2xl
                    flex flex-col md:flex-row">
            
            <!-- 错误状态 -->
            <div x-show="error" class="flex items-center justify-center h-full">
                <div class="text-center p-4 md:p-6">
                    <svg class="mx-auto w-12 h-12 md:w-16 md:h-16 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <h3 class="mt-2 text-base md:text-lg font-medium text-gray-900">{{ __('Error loading product') }}</h3>
                    <p class="mt-1 text-sm md:text-base text-gray-500" x-text="error"></p>
                    <button @click="closeModal()" class="mt-4 bg-red-600 text-white px-4 py-2 text-sm md:text-base rounded-lg hover:bg-red-700 transition-colors">
                        {{ __('Close') }}
                    </button>
                </div>
            </div>

            <!-- 产品内容 - 响应式布局 -->
            <div x-show="!error" class="flex flex-col md:flex-row h-full">

                <!-- 图片区域：移动端占50%高度，桌面端占60%宽度 -->
                <div class="flex-1 md:flex-[0_0_60%] bg-gray-50 p-3 md:p-6 flex flex-col">
                    <!-- 主图片显示区域 -->
                    <div class="flex-1 bg-white rounded-lg shadow-sm mb-3 md:mb-4 flex items-center justify-center overflow-hidden">
                        <img :src="currentImageUrl || '{{ asset('img/placeholder.svg') }}'" 
                             :alt="product ? product.name : ''"
                             class="w-full h-full object-contain transition-transform duration-300 hover:scale-105"
                             onerror="this.src='{{ asset('img/placeholder.svg') }}';">
                    </div>
                    
                    <!-- 缩略图区域 -->
                    <div class="flex gap-2 justify-center overflow-x-auto py-1" x-show="product.images && product.images.length > 1">
                        <template x-for="(image, index) in product.images" :key="image.id">
                            <div class="flex-shrink-0 w-12 h-12 md:w-16 md:h-16 rounded-lg overflow-hidden cursor-pointer border-2 transition-all duration-300
                                       hover:border-blue-400 hover:scale-105"
                                 :class="currentImageUrl === image.main_image_url ? 'border-blue-500 shadow-md' : 'border-transparent'"
                                 @click="changeMainImage(image.main_image_url)">
                                <img :src="image.thumbnail_url" 
                                     :alt="product.name"
                                     class="w-full h-full object-cover"
                                     onerror="this.src='{{ asset('img/placeholder.svg') }}';">
                            </div>
                        </template>
                    </div>
                </div>

                <!-- 信息区域：移动端占50%高度，桌面端占40%宽度 -->
                <div class="flex-1 md:flex-[0_0_40%] p-4 md:p-6 flex flex-col bg-white overflow-y-auto custom-scrollbar">
                    <!-- 产品标题 -->
                    <h2 class="text-lg md:text-2xl font-bold text-gray-900 mb-2 md:mb-4" x-text="product.name"></h2>
                    
                    <!-- 产品价格 -->
                    <div class="text-xl md:text-3xl font-bold text-red-600 mb-3 md:mb-6">
                        ¥<span x-text="product.price ? parseFloat(product.price).toFixed(2) : '0.00'"></span>
                    </div>
                    
                    <!-- 产品描述 -->
                    <div class="flex-1 text-sm md:text-base text-gray-600 leading-relaxed mb-4 md:mb-6" x-show="product.description">
                        <p x-html="product.description ? product.description.replace(/\n/g, '<br>') : ''"></p>
                    </div>
                    
                    <!-- 订单信息 -->
                    <div class="text-xs md:text-sm text-gray-500 mb-3 md:mb-4">
                        <span x-text="translations.minimum_order_quantity"></span>: <span x-text="product.min_order_quantity"></span>
                    </div>
                    
                    <!-- 数量选择 -->
                    <div class="mb-4 md:mb-6">
                        <div class="flex items-center justify-center gap-3 md:gap-4 bg-gray-50 p-3 md:p-4 rounded-lg">
                            <button type="button" 
                                    class="w-8 h-8 md:w-10 md:h-10 bg-white rounded-lg shadow-sm flex items-center justify-center text-lg md:text-xl font-semibold text-gray-700 hover:bg-gray-100 transition-colors" 
                                    @click="quantity > (product.min_order_quantity || 1) && quantity--">−</button>
                            <input type="number" 
                                   class="w-16 md:w-20 h-8 md:h-10 text-center border border-gray-300 rounded-lg text-sm md:text-base font-semibold" 
                                   x-model.number="quantity" 
                                   @change="validateQuantity()" 
                                   :min="product.min_order_quantity || 1">
                            <button type="button" 
                                    class="w-8 h-8 md:w-10 md:h-10 bg-white rounded-lg shadow-sm flex items-center justify-center text-lg md:text-xl font-semibold text-gray-700 hover:bg-gray-100 transition-colors" 
                                    @click="quantity++">+</button>
                        </div>
                    </div>
                    
                    <!-- 添加到购物车按钮 -->
                    <button type="button" 
                            class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold py-3 md:py-4 px-6 rounded-lg transition-all duration-300 hover:shadow-lg hover:scale-[1.02] disabled:opacity-50 flex items-center justify-center gap-2 text-sm md:text-base" 
                            @click="addToCart()" 
                            :disabled="addToCartFeedback !== ''" 
                            x-data="{ clicked: false }" 
                            @click.debounce.1000ms="clicked = false" 
                            @click="clicked = true; setTimeout(() => clicked = false, 2000)">
                        <template x-if="!addToCartFeedback && !clicked">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                                </svg>
                                <span x-text="translations.add_to_cart"></span>
                            </span>
                        </template>
                        <template x-if="clicked || addToCartFeedback">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span x-text="addToCartFeedback || translations.added_to_cart || 'Added to Cart!'"></span>
                            </span>
                        </template>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div> 



{{-- 响应式模态框到全屏视图样式 --}}
<style>
/* 移动端全屏背景模糊效果 */
@media (max-width: 767px) {
    #product-modal {
        backdrop-filter: none !important;
        background-color: transparent !important;
    }
}

/* 桌面端模态框阴影效果 */
@media (min-width: 768px) {
    #product-modal {
        backdrop-filter: blur(8px) !important;
    }
}



/* 自定义滚动条样式 */
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 2px;
}





</style> 