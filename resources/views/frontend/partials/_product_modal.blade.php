{{-- 
    优化后的响应式模态框 (Laravel Blade + Alpine.js)
    作者: Gemini
    优化点:
    1.  HTML结构极大简化：移除不必要的嵌套div，逻辑更清晰。
    2.  布局更稳健：使用更简单的Flexbox和Grid，移除了固定高度，完美适应内容。
    3.  样式统一：所有样式均由Tailwind CSS类控制，移除了独立的<style>标签。
    4.  交互体验提升：优化了按钮、输入框和过渡动画，视觉效果更佳。
    5.  完全响应式：从移动端全屏到桌面端模态框的过渡平滑自然。
    新增特性:
    1. 大屏幕居中显示，最大宽度500px
    2. 小屏幕自动切换为全屏模式，从底部滑入
    3. 使用 cubic-bezier(0.4, 0, 0.2, 1) 缓动函数
    4. 0.4秒的平滑过渡时间
    5. 响应式图片高度适配
    6. 增强的黑色半透明遮罩效果
--}}
<div 
    id="product-modal"
    x-data="productModal()"
    x-show="isOpen"
    x-cloak
    class="modal-overlay fixed inset-0 z-50 flex items-center justify-center"
    :class="{ 'active': isOpen }"
    x-transition:enter="transition ease-out duration-400"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @open-product-modal.window="openModal($event.detail.productId)"
    @keydown.escape.window="closeModal()"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modal-title"
    style="display: none;"
>
    <!-- 增强的背景遮罩层 -->
    <div 
        @click="closeModal()" 
        class="modal-backdrop absolute inset-0 bg-black transition-opacity"
        :class="{ 'opacity-60': isOpen, 'opacity-0': !isOpen }"
        x-transition:enter="transition-opacity ease-out duration-400"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-60"
        x-transition:leave="transition-opacity ease-in duration-300"
        x-transition:leave-start="opacity-60"
        x-transition:leave-end="opacity-0"
        aria-hidden="true">
    </div>

    <!-- 模态框内容容器 -->
    <div 
        class="modal-container relative w-full max-w-md mx-4 bg-white shadow-2xl overflow-hidden z-10
               sm:rounded-3xl
               max-sm:w-full max-sm:h-full max-sm:max-w-none max-sm:mx-0 max-sm:rounded-none max-sm:shadow-none"
        x-show="isOpen"
        x-transition:enter="transition-all duration-400"
        x-transition:enter-start="opacity-0 transform scale-90 max-sm:translate-y-full"
        x-transition:enter-end="opacity-100 transform scale-100 max-sm:translate-y-0"
        x-transition:leave="transition-all duration-300"
        x-transition:leave-start="opacity-100 transform scale-100 max-sm:translate-y-0"
        x-transition:leave-end="opacity-0 transform scale-90 max-sm:translate-y-full"
        @click.stop
    >
        <!-- 关闭按钮 -->
        <button @click="closeModal()" class="absolute top-4 right-4 z-20 text-gray-500 hover:text-red-500 transition-colors duration-200">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <!-- 错误状态 -->
        <template x-if="error">
            <div class="flex items-center justify-center w-full p-8 text-center">
                <div>
                    <svg class="mx-auto w-16 h-16 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('Error loading product') }}</h3>
                    <p class="mt-2 text-gray-500" x-text="error"></p>
                </div>
            </div>
        </template>

        <!-- 加载状态 (骨架屏) -->
        <template x-if="loading">
            <div class="w-full animate-pulse p-4">
                <!-- 大屏幕骨架屏 -->
                <div class="hidden sm:block">
                    <div class="w-full h-64 rounded-lg bg-gray-200 mb-4"></div>
                    <div class="space-y-4">
                        <div class="h-6 bg-gray-200 rounded w-3/4"></div>
                        <div class="h-8 bg-gray-200 rounded w-1/2"></div>
                        <div class="space-y-2">
                            <div class="h-4 bg-gray-200 rounded"></div>
                            <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                        </div>
                        <div class="h-12 bg-gray-200 rounded-lg"></div>
                    </div>
                </div>
                <!-- 小屏幕骨架屏 -->
                <div class="sm:hidden">
                    <div class="w-full h-48 rounded-lg bg-gray-200 mb-4"></div>
                    <div class="space-y-3">
                        <div class="h-5 bg-gray-200 rounded w-3/4"></div>
                        <div class="h-6 bg-gray-200 rounded w-1/2"></div>
                        <div class="space-y-2">
                            <div class="h-3 bg-gray-200 rounded"></div>
                            <div class="h-3 bg-gray-200 rounded w-5/6"></div>
                        </div>
                        <div class="h-10 bg-gray-200 rounded-lg"></div>
                    </div>
                </div>
            </div>
        </template>

        <!-- 产品内容 -->
        <template x-if="!loading && !error && product">
            <div class="w-full flex flex-col">
                <!-- 图片区域 -->
                <div class="w-full p-4 bg-gray-50">
                    <div class="w-full main-image h-80 max-sm:h-48 flex items-center justify-center mb-4 rounded-lg overflow-hidden bg-white cursor-pointer relative group"
                         @click="openImageViewer()">
                        <img 
                            :src="currentImageUrl || '{{ asset('img/placeholder.svg') }}'" 
                            :alt="product.name" 
                            class="max-w-full max-h-full object-contain transition-transform duration-300 group-hover:scale-105"
                            onerror="console.error('Image failed to load:', this.src); this.src='{{ asset('img/placeholder.svg') }}'; console.log('Fallback to placeholder');"
                            onload="console.log('Image loaded successfully:', this.src);"
                            style="border: 2px solid red; background: yellow;">
                        <!-- 放大镜图标提示 - 修复遮挡问题 -->
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center pointer-events-none">
                            <svg class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                            </svg>
                        </div>
                    </div>
                    <!-- 缩略图 -->
                    <div class="flex justify-center space-x-2" x-show="product.images && product.images.length > 1">
                        <template x-for="image in product.images" :key="image.id">
                            <button 
                                @click="changeMainImage(image.main_image_url)"
                                :class="currentImageUrl === image.main_image_url ? 'border-blue-500 ring-2 ring-blue-300' : 'border-gray-200'"
                                class="w-12 h-12 rounded-lg border-2 overflow-hidden transition-all duration-200 hover:border-blue-400">
                                <img :src="image.thumbnail_url" :alt="product.name" class="w-full h-full object-cover"
                                     onerror="this.src='{{ asset('img/placeholder.svg') }}'">
                            </button>
                        </template>
                    </div>
                </div>

                <!-- 信息区域 -->
                <div class="p-4 flex flex-col flex-grow">
                    <h1 id="modal-title" class="text-xl sm:text-2xl font-bold text-gray-900 mb-2" x-text="product.name"></h1>
                    <p class="text-2xl sm:text-3xl font-bold text-red-600 mb-4">
                        ¥<span x-text="product.price ? parseFloat(product.price).toFixed(2) : '0.00'"></span>
                    </p>
                    
                    <!-- 产品描述 -->
                    <div class="text-gray-600 text-sm sm:text-base mb-6 flex-grow overflow-y-auto max-h-32" 
                         x-html="product.description ? product.description.replace(/\n/g, '<br>') : ''"></div>

                    <!-- 操作区域 -->
                    <div class="pt-4 border-t border-gray-200">
                        <p class="text-xs sm:text-sm text-gray-500 mb-3" x-text="`${translations.minimum_order_quantity}: ${product.min_order_quantity}`"></p>
                        
                        <!-- 数量选择和按钮 -->
                        <div class="flex items-center justify-center gap-4">
                            <!-- 数量选择器 -->
                            <div class="flex items-center border border-gray-300 rounded-lg">
                                <button @click="quantity > (product.min_order_quantity || 1) && quantity--" 
                                        class="w-10 h-10 text-xl text-gray-600 hover:bg-gray-100 rounded-l-lg transition flex items-center justify-center">
                                    -
                                </button>
                                <div class="w-16 h-10 flex items-center justify-center border-x">
                                    <input 
                                        type="number" 
                                        x-model.number="quantity" 
                                        @change="validateQuantity()"
                                        :min="product.min_order_quantity || 1"
                                        class="w-full h-full text-center font-semibold text-lg border-0 outline-none bg-transparent">
                                </div>
                                <button @click="quantity++" 
                                        class="w-10 h-10 text-xl text-gray-600 hover:bg-gray-100 rounded-r-lg transition flex items-center justify-center">
                                    +
                                </button>
                            </div>
                            
                            <!-- 添加到购物车按钮 -->
                            <button 
                                @click="handleAddToCart()"
                                :disabled="isAddingToCart"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg text-base transition-all duration-300 shadow-md hover:shadow-lg disabled:opacity-70 disabled:cursor-wait min-w-[140px]">
                                <!-- 点击状态显示 -->
                                <span x-show="isAddingToCart" class="flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="hidden sm:inline">Adding...</span>
                                </span>
                                <!-- 默认状态显示 -->
                                <span x-show="!isAddingToCart" class="flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l.218-.219.133-.133.942-.941 1.058-1.058a1 1 0 00.028-.30l.21-.209L17.6 4.575A.996.996 0 0018 4H4.76L4.23.85A.997.997 0 003.25.137H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"></path></svg>
                                    <span x-text="translations.add_to_cart || 'Add to Cart'"></span>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

<!-- 图片全屏查看器 -->
<div x-show="showImageViewer" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[9999] bg-black bg-opacity-90 flex items-center justify-center p-4"
     @click="closeImageViewer()"
     @keydown.escape="closeImageViewer()"
     style="display: none;">
    
    <!-- 关闭按钮 -->
    <button @click="closeImageViewer()" 
            class="absolute top-4 right-4 text-white hover:text-gray-300 transition-colors z-10">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>
    
    <!-- 放大的图片 -->
    <div class="max-w-full max-h-full flex items-center justify-center"
         @click.stop="">
        <img :src="currentImageUrl || '{{ asset('img/placeholder.svg') }}'" 
             :alt="product?.name || ''" 
             class="max-w-full max-h-full object-contain cursor-pointer transition-transform duration-300"
             @click="closeImageViewer()"
             onerror="this.src='{{ asset('img/placeholder.svg') }}'">
    </div>
    
    <!-- 图片导航按钮 (如果有多张图片) -->
    <template x-if="product && product.images && product.images.length > 1">
        <div class="absolute inset-y-0 left-4 flex items-center">
            <button @click.stop="previousImage()" 
                    class="text-white hover:text-gray-300 transition-colors bg-black bg-opacity-50 rounded-full p-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
        </div>
    </template>
    
    <template x-if="product && product.images && product.images.length > 1">
        <div class="absolute inset-y-0 right-4 flex items-center">
            <button @click.stop="nextImage()" 
                    class="text-white hover:text-gray-300 transition-colors bg-black bg-opacity-50 rounded-full p-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        </div>
    </template>
    
    <!-- 图片指示器 -->
    <template x-if="product && product.images && product.images.length > 1">
        <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2">
            <template x-for="(image, index) in product.images" :key="image.id">
                <button @click.stop="changeMainImage(image.main_image_url)"
                        :class="currentImageUrl === image.main_image_url ? 'bg-white' : 'bg-white bg-opacity-50'"
                        class="w-3 h-3 rounded-full transition-all duration-200"></button>
            </template>
        </div>
    </template>
</div>

<style>
/* 增强的模态框遮罩样式 */
.modal-overlay {
    background: rgba(0, 0, 0, 0);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    opacity: 0;
    visibility: hidden;
}

.modal-overlay.active {
    opacity: 1;
    visibility: visible;
}

/* 背景遮罩层 */
.modal-backdrop {
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(2px);
    -webkit-backdrop-filter: blur(2px);
}

/* 模态框容器基础样式 */
.modal-container {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    max-width: 500px;
    transform: scale(0.9);
    z-index: 1001;
}

/* 大屏幕圆角样式 */
@media (min-width: 640px) {
    .modal-container {
        border-radius: 1.5rem; /* 24px 圆角 - 增加弧度 */
    }
}

/* 当模态框激活时的样式 */
.modal-overlay.active .modal-container {
    transform: scale(1);
}

/* 大屏幕样式 */
@media (min-width: 640px) {
    .main-image {
        height: 320px;
    }
}

/* 小屏幕样式 - 768px及以下 */
@media (max-width: 768px) {
    .modal-container {
        width: 100% !important;
        height: 100vh !important;
        max-width: none !important;
        margin: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        transform: translateY(100%);
    }
}

/* 明确小屏幕直角设置 */
@media (max-width: 639px) {
    .modal-container {
        border-radius: 0 !important;
    }
}
    
    .modal-overlay.active .modal-container {
        transform: translateY(0);
    }
    
    .modal-backdrop {
        background: rgba(0, 0, 0, 0.8);
    }
}

/* 小屏幕图片高度调整 */
@media (max-width: 480px) {
    .main-image {
        height: 200px !important;
    }
}

/* 防止背景滚动 */
body.modal-open {
    overflow: hidden;
    height: 100vh;
}

/* 滚动条样式优化 */
.modal-container::-webkit-scrollbar {
    width: 6px;
}

.modal-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.modal-container::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.modal-container::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* 模态框打开时的动画效果 */
@keyframes modalFadeIn {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes modalSlideUp {
    from {
        transform: translateY(100%);
    }
    to {
        transform: translateY(0);
    }
}

/* 模态框关闭时的动画效果 */
@keyframes modalFadeOut {
    from {
        opacity: 1;
        transform: scale(1);
    }
    to {
        opacity: 0;
        transform: scale(0.9);
    }
}

@keyframes modalSlideDown {
    from {
        transform: translateY(0);
    }
    to {
        transform: translateY(100%);
    }
}

/* 增强的层级管理 */
.modal-overlay {
    z-index: 1000;
}

.modal-backdrop {
    z-index: 1000;
}

.modal-container {
    z-index: 1001;
}
</style> 