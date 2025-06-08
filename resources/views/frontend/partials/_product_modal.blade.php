{{-- 产品详情模态框 - 半透明遮罩效果 --}}
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
     class="modal-overlay fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4" 
     style="display: none; background-color: rgba(0, 0, 0, 0.4); backdrop-filter: blur(2px);"
     @click.self="closeModal()">
    
    <!-- 模态框容器 - 用于相对定位关闭按钮，响应式宽度 -->
    <div class="modal-container relative w-full max-h-[96vh] sm:max-w-lg md:max-w-xl lg:max-w-2xl mx-auto my-2 sm:my-4"
         x-transition:enter="transition ease-out duration-300 delay-100"
         x-transition:enter-start="opacity-0 transform scale-90 translate-y-5"
         x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 transform scale-90 translate-y-5">
        
        <!-- 红色圆形关闭按钮 - 位于模态框外部左上角 -->
        <button @click="closeModal()" 
                class="absolute"
                style="position: absolute; top: -20px; left: -20px; width: 40px; height: 40px; background-color: #ef4444; border: 3px solid white; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease; z-index: 60; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);"
                onmouseover="this.style.backgroundColor='#dc2626'; this.style.transform='scale(1.1)'; this.style.boxShadow='0 6px 16px rgba(0, 0, 0, 0.2)';"
                onmouseout="this.style.backgroundColor='#ef4444'; this.style.transform='scale(1)'; this.style.boxShadow='0 4px 12px rgba(0, 0, 0, 0.15)';"
                onmousedown="this.style.transform='scale(0.95)';"
                onmouseup="this.style.transform='scale(1)';">
            <svg style="width: 18px; height: 18px; color: white; stroke-width: 2.5;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        
        <!-- 模态框内容 - 响应式尺寸和圆角矩形设计 -->
        <div class="modal bg-white shadow-2xl w-full h-full flex flex-col transition-all duration-300"
             style="border-radius: 20px !important; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
            
            <!-- 隐藏加载状态 - 改为永远不显示 -->
            <div x-show="false" class="flex items-center justify-center h-32 sm:h-48">
                <div class="flex items-center space-x-2">
                    <svg class="animate-spin h-6 w-6 sm:h-8 sm:w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 0 1 4 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-sm text-gray-600">{{ __('Loading...') }}</span>
                </div>
            </div>

            <!-- 错误状态 -->
            <div x-show="error && !loading" class="flex items-center justify-center h-32 sm:h-48">
                <div class="text-center p-4">
                    <svg class="mx-auto h-8 w-8 sm:h-12 sm:w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('Error loading product') }}</h3>
                    <p class="mt-1 text-xs text-gray-500" x-text="error"></p>
                    <button @click="closeModal()" class="mt-3 bg-red-600 text-white px-3 py-1 text-sm rounded-lg hover:bg-red-700 transition-colors">
                        {{ __('Close') }}
                    </button>
                </div>
            </div>

            <!-- 产品内容 - 直接显示，无需等待加载完成 -->
            <div x-show="!error" class="flex flex-col h-full">

                <!-- 产品图片区域 - 固定高度避免挤压 -->
                <div class="flex-shrink-0 p-3 sm:p-4 bg-gradient-to-br from-blue-50 to-indigo-100" style="border-top-left-radius: 20px; border-top-right-radius: 20px; border-bottom-left-radius: 0; border-bottom-right-radius: 0;">
                    <div class="flex flex-col gap-3">
                        <!-- 主图片 -->
                        <div class="w-full">
                            <div class="relative bg-white shadow-sm" style="border-radius: 10px; padding: 8px;">
                                <div class="flex items-center justify-center h-44 sm:h-48">
                                    <img :src="product && product.main_image_url ? product.main_image_url : '{{ asset('img/placeholder.svg') }}'" 
                                         :alt="product ? product.name : ''"
                                         class="object-contain"
                                         style="border-radius: 8px; max-width: 100%; max-height: 100%; width: auto; height: auto;"
                                         onerror="this.src='{{ asset('img/placeholder.svg') }}';">
                                </div>
                            </div>
                        </div>

                        <!-- 缩略图 - 水平排列，更紧凑 -->
                        <div class="flex space-x-2 justify-center overflow-x-auto" x-show="product.images && product.images.length > 1">
                            <template x-for="(image, index) in product.images" :key="image.id">
                                <button @click="product.main_image_url = image.main_image_url"
                                        :class="{'border-blue-500': product.main_image_url === image.main_image_url, 'border-gray-200': product.main_image_url !== image.main_image_url}"
                                        class="w-12 h-12 sm:w-14 sm:h-14 border-2 transition-all overflow-hidden flex-shrink-0"
                                        style="border-radius: 8px;">
                                    <img :src="image.thumbnail_url" 
                                         :alt="product.name"
                                         class="w-full h-full object-cover"
                                         onerror="this.src='{{ asset('img/placeholder.svg') }}';">
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- 产品信息区域 - 可滚动内容 -->
                <div class="flex flex-col flex-grow overflow-hidden">
                    <div class="p-3 sm:p-4 lg:p-5 flex-grow overflow-y-auto">
                        <!-- 标题和价格 -->
                        <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900 mb-2 sm:mb-4" x-text="product.name"></h2>
                        
                        <p class="text-xl sm:text-2xl lg:text-3xl font-semibold text-red-600 mb-3 sm:mb-5">
                            ¥<span x-text="product.price ? parseFloat(product.price).toFixed(2) : '0.00'"></span>
                        </p>

                        <!-- 描述 -->
                        <div class="prose prose-sm max-w-none text-gray-600 mb-3 sm:mb-6 text-sm" x-show="product.description">
                            <p x-html="product.description ? product.description.replace(/\n/g, '<br>') : ''"></p>
                        </div>

                        <!-- 最小订单数量 -->
                        <p class="text-xs sm:text-sm text-gray-500 mb-3 sm:mb-5">
                            <span x-text="translations.minimum_order_quantity"></span>: 
                            <span x-text="product.min_order_quantity"></span>
                        </p>

                        <!-- 购买操作 -->
                        <div class="space-y-3 sm:space-y-4">
                            <!-- 数量选择器 - 删除Quantity标签，优化小屏幕显示 -->
                            <div class="mb-3 sm:mb-4">
                                <div class="quantity-controller" style="display: flex; align-items: center; background-color: white; border: 2px solid #e5e7eb; border-radius: 12px; padding: 4px; width: fit-content; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; margin: 0 auto; max-width: 100%;">
                                    <button type="button"
                                            @click="quantity > (product.min_order_quantity || 1) && quantity--"
                                            class="quantity-btn minus"
                                            style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; background-color: transparent; border: none; border-radius: 8px; color: #6b7280; font-size: 18px; font-weight: 500; cursor: pointer; transition: all 0.2s ease; user-select: none; flex-shrink: 0;"
                                            :disabled="quantity <= (product.min_order_quantity || 1)"
                                            onmouseover="this.style.backgroundColor='#f3f4f6'; this.style.color='#374151';"
                                            onmouseout="this.style.backgroundColor='transparent'; this.style.color='#6b7280';"
                                            onmousedown="this.style.backgroundColor='#e5e7eb'; this.style.transform='scale(0.95)';"
                                            onmouseup="this.style.transform='scale(1)';">
                                        −
                                    </button>
                                    <input type="text" 
                                           x-model.number="quantity"
                                           @change="validateQuantity()"
                                           class="quantity-display"
                                           style="display: flex; align-items: center; justify-content: center; min-width: 50px; width: auto; height: 40px; padding: 0 12px; font-size: 16px; font-weight: 600; color: #111827; background-color: transparent; border: none; text-align: center; outline: none; flex-shrink: 0;"
                                           readonly>
                                    <button type="button"
                                            @click="quantity++"
                                            class="quantity-btn plus"
                                            style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; background-color: transparent; border: none; border-radius: 8px; color: #6b7280; font-size: 18px; font-weight: 500; cursor: pointer; transition: all 0.2s ease; user-select: none; flex-shrink: 0;"
                                            onmouseover="this.style.backgroundColor='#f3f4f6'; this.style.color='#374151';"
                                            onmouseout="this.style.backgroundColor='transparent'; this.style.color='#6b7280';"
                                            onmousedown="this.style.backgroundColor='#e5e7eb'; this.style.transform='scale(0.95)';"
                                            onmouseup="this.style.transform='scale(1)';">
                                        +
                                    </button>
                                </div>
                            </div>

                            <!-- 添加到购物车按钮 -->
                            <button type="button"
                                    @click="addToCart()"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 sm:py-3 px-4 sm:px-8 transition duration-300 flex items-center justify-center space-x-2 text-sm sm:text-base"
                                    style="border-radius: 10px;"
                                    :disabled="addToCartFeedback !== ''"
                                    :class="{ 'opacity-50 cursor-not-allowed': addToCartFeedback !== '' }">
                                <!-- 购物车图标 -->
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l-1 8H6L5 9z"></path>
                                </svg>
                                <span x-show="!addToCartFeedback" x-text="translations.add_to_cart"></span>
                                <span x-show="addToCartFeedback" x-text="addToCartFeedback"></span>
                                <svg x-show="addToCartFeedback && addToCartFeedback === translations.processing" class="animate-spin h-4 w-4 sm:h-5 sm:w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 0 1 4 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 

{{-- 移动端响应式样式 - 优化显示 --}}
<style>
@media (max-width: 640px) {
    .modal-container {
        width: 96vw !important;
        max-width: 96vw !important;
        margin: 2vh auto !important;
        max-height: 96vh !important;
    }
    
    .modal {
        border-radius: 16px !important;
        max-height: 96vh !important;
        height: 100% !important;
    }
    
    /* 移动端关闭按钮位置调整 */
    .modal-container .absolute {
        top: -15px !important;
        right: -15px !important;
        left: auto !important;
        width: 36px !important;
        height: 36px !important;
    }
    
    /* 移动端缩略图优化 */
    .modal .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
    }
    
    /* 移动端图片区域高度调整 - 覆盖全局设置 */
    #product-modal .modal .h-44 {
        height: 150px !important;
        min-height: 150px !important;
        max-height: 150px !important;
    }
    
    #product-modal .modal .sm\\:h-48 {
        height: 150px !important;
        min-height: 150px !important;
        max-height: 150px !important;
    }
}

/* 平板设备样式 */
@media (min-width: 641px) and (max-width: 1024px) {
    .modal-container {
        max-width: 90vw !important;
        max-height: 94vh !important;
        margin: 3vh auto !important;
    }
    
    .modal {
        height: 100% !important;
    }
}

/* 大屏幕（桌面端）样式 - 确保垂直布局 - 更高优先级 */
@media (min-width: 1025px) {
    .modal-container {
        max-width: 700px !important;
        max-height: 92vh !important;
        margin: 4vh auto !important;
    }
    
    .modal {
        height: 100% !important;
    }
    
    /* 强制所有屏幕尺寸使用垂直布局 - 最高优先级 */
    #product-modal .modal .flex.flex-col,
    #product-modal .modal > div[x-show]:not([x-show="false"]) > .flex.flex-col,
    #product-modal .modal div[x-show]:not([x-show="false"]).flex.flex-col {
        flex-direction: column !important;
        display: flex !important;
    }
    
    /* 强制图片容器垂直布局 */
    #product-modal .modal .flex.flex-col.gap-3,
    #product-modal .modal div.flex.flex-col.gap-3 {
        flex-direction: column !important;
        display: flex !important;
    }
    
    /* 强制产品信息区域垂直布局 */
    #product-modal .modal .flex.flex-col:not(.gap-3),
    #product-modal .modal div.flex.flex-col:not(.gap-3) {
        flex-direction: column !important;
        display: flex !important;
    }
    
    /* 缩略图在大屏幕上水平排列 */
    #product-modal .modal .flex.space-x-2.justify-center.overflow-x-auto {
        justify-content: center !important;
        overflow-x: visible !important;
        flex-direction: row !important;
    }
    
    /* 大屏幕上的缩略图尺寸 */
    #product-modal .modal .w-12.h-12.sm\\:w-14.sm\\:h-14 {
        width: 60px !important;
        height: 60px !important;
    }
    
    /* 确保主容器也是垂直布局 */
    #product-modal .modal-container .modal > div > div {
        flex-direction: column !important;
    }
}

/* 全局强制垂直布局 - 适用于所有屏幕尺寸 - 超高优先级 */
#product-modal .modal .flex.flex-col,
#product-modal .modal .flex.flex-col.lg\\:flex-row,
#product-modal .modal div.flex.flex-col,
#product-modal .modal div.flex.flex-col.lg\\:flex-row {
    flex-direction: column !important;
    display: flex !important;
}

/* 强制主要内容区域垂直排列 - 超高优先级 */
#product-modal .modal div[x-show]:not([x-show="false"]),
#product-modal .modal div[x-show]:not([x-show="false"]).flex,
#product-modal .modal div[x-show]:not([x-show="false"]).flex.flex-col,
#product-modal .modal div[x-show]:not([x-show="false"]).flex.flex-col.lg\\:flex-row {
    flex-direction: column !important;
    display: flex !important;
}

/* 强制覆盖所有可能的Tailwind响应式类 */
@media (min-width: 1024px) {
    #product-modal .modal .lg\\:flex-row,
    #product-modal .modal div.lg\\:flex-row,
    #product-modal .modal .flex.lg\\:flex-row,
    #product-modal .modal div.flex.lg\\:flex-row {
        flex-direction: column !important;
    }
}

/* 全局样式优化 */
#product-modal .modal {
    /* 确保模态框本身不会溢出 */
    overflow: hidden !important;
}

#product-modal .modal .overflow-y-auto {
    /* 改善滚动性能 */
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
}

#product-modal .modal .overflow-y-auto::-webkit-scrollbar {
    width: 4px;
}

#product-modal .modal .overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}

#product-modal .modal .overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 2px;
}

#product-modal .modal .overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background-color: rgba(156, 163, 175, 0.7);
}

/* 确保按钮区域始终可见 */
#product-modal .modal .space-y-3,
#product-modal .modal .space-y-4 {
    padding-bottom: 8px !important;
}

/* 图片尺寸严格控制 */
#product-modal .modal img {
    max-width: 100% !important;
    max-height: 100% !important;
    object-fit: contain !important;
}

/* 图片容器严格高度控制 */
#product-modal .modal .h-44 {
    height: 176px !important;
    min-height: 176px !important;
    max-height: 176px !important;
}

#product-modal .modal .sm\\:h-48 {
    height: 192px !important;
    min-height: 192px !important;
    max-height: 192px !important;
}

/* 确保图片不会溢出容器 */
#product-modal .modal .flex.items-center.justify-center {
    overflow: hidden !important;
}
</style> 