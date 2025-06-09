{{-- 
    优化后的响应式模态框 (Laravel Blade + Alpine.js)
    作者: Gemini
    优化点:
    1.  HTML结构极大简化：移除不必要的嵌套div，逻辑更清晰。
    2.  布局更稳健：使用更简单的Flexbox和Grid，移除了固定高度，完美适应内容。
    3.  样式统一：所有样式均由Tailwind CSS类控制，移除了独立的<style>标签。
    4.  交互体验提升：优化了按钮、输入框和过渡动画，视觉效果更佳。
    5.  完全响应式：从移动端全屏到桌面端模态框的过渡平滑自然。
--}}
<div 
    id="product-modal"
    x-data="productModal()"
    x-show="isOpen"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @open-product-modal.window="openModal($event.detail.productId)"
    @keydown.escape.window="closeModal()"
    class="fixed inset-0 z-50 flex items-start justify-center md:items-center"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modal-title"
    style="display: none;"
>
    <!-- 背景遮罩 -->
    <div @click="closeModal()" class="fixed inset-0 bg-black/60 backdrop-blur-sm" x-show="isOpen" x-transition.opacity></div>

    <!-- 模态框内容容器 -->
    <div 
        class="relative w-full h-full overflow-y-auto bg-white
               md:h-auto md:max-h-[90vh] md:w-auto md:max-w-4xl md:rounded-2xl md:shadow-2xl md:m-4
               flex flex-col md:flex-row"
        x-show="isOpen"
        x-transition:enter="transition-all ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-full md:translate-y-10 md:scale-95"
        x-transition:enter-end="opacity-100 transform translate-y-0 md:scale-100"
        x-transition:leave="transition-all ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-y-0 md:scale-100"
        x-transition:leave-end="opacity-0 transform translate-y-full md:translate-y-10 md:scale-95"
    >
        <!-- 关闭按钮 -->
        <button @click="closeModal()" class="absolute top-4 right-4 z-20 text-gray-500 hover:text-red-500 transition-colors">
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
            <div class="w-full flex flex-col md:flex-row animate-pulse">
                <div class="w-full md:w-2/5 p-4"><div class="w-full h-80 rounded-lg bg-gray-200"></div></div>
                <div class="w-full md:w-3/5 p-8 space-y-6">
                    <div class="h-8 bg-gray-200 rounded w-3/4"></div>
                    <div class="h-12 bg-gray-200 rounded w-1/2"></div>
                    <div class="space-y-3">
                        <div class="h-4 bg-gray-200 rounded"></div>
                        <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                    </div>
                    <div class="h-16 bg-gray-200 rounded-lg"></div>
                </div>
            </div>
        </template>

        <!-- 产品内容 -->
        <template x-if="!loading && !error && product">
            <div class="w-full flex flex-col md:flex-row">
                <!-- 左侧: 图片区 -->
                <div class="w-full md:w-2/5 md:flex-shrink-0 p-4 md:p-6 bg-gray-50 flex flex-col items-center">
                    <div class="w-full aspect-square flex items-center justify-center mb-4">
                        <img 
                            :src="currentImageUrl || '{{ asset('img/placeholder.svg') }}'" 
                            :alt="product.name" 
                            class="max-w-full max-h-full object-contain transition-transform duration-300 group-hover:scale-105"
                            onerror="this.src='{{ asset('img/placeholder.svg') }}'">
                    </div>
                    <div class="flex space-x-3" x-show="product.images && product.images.length > 1">
                        <template x-for="image in product.images" :key="image.id">
                            <button 
                                @click="changeMainImage(image.main_image_url)"
                                :class="currentImageUrl === image.main_image_url ? 'border-blue-500 ring-2 ring-blue-300' : 'border-gray-200'"
                                class="w-16 h-16 rounded-lg border-2 overflow-hidden transition-all duration-200 hover:border-blue-400">
                                <img :src="image.thumbnail_url" :alt="product.name" class="w-full h-full object-cover"
                                     onerror="this.src='{{ asset('img/placeholder.svg') }}'">
                            </button>
                        </template>
                    </div>
                </div>

                <!-- 右侧: 信息区 -->
                <div class="w-full md:w-3/5 p-6 flex flex-col">
                    <h1 id="modal-title" class="text-2xl md:text-3xl font-extrabold text-gray-900" x-text="product.name"></h1>
                    <p class="mt-4 text-3xl md:text-4xl font-bold text-red-600">
                        ¥<span x-text="product.price ? parseFloat(product.price).toFixed(2) : '0.00'"></span>
                    </p>
                    
                    <div class="mt-6 text-gray-600 space-y-4 text-sm md:text-base flex-grow" x-html="product.description ? product.description.replace(/\n/g, '<br>') : ''"></div>

                    <div class="mt-8 pt-6 border-t">
                        <p class="text-sm text-gray-500 mb-2" x-text="`${translations.minimum_order_quantity}: ${product.min_order_quantity}`"></p>
                        <div class="flex items-center justify-between gap-4">
                            <!-- 数量选择 -->
                            <div class="flex items-center border border-gray-300 rounded-lg">
                                <button @click="quantity > (product.min_order_quantity || 1) && quantity--" class="w-10 h-10 text-2xl text-gray-600 hover:bg-gray-100 rounded-l-lg transition">-</button>
                                <input 
                                    type="number" 
                                    x-model.number="quantity" 
                                    @change="validateQuantity()"
                                    :min="product.min_order_quantity || 1"
                                    class="w-16 h-10 text-center font-semibold border-x text-lg">
                                <button @click="quantity++" class="w-10 h-10 text-2xl text-gray-600 hover:bg-gray-100 rounded-r-lg transition">+</button>
                            </div>
                            <!-- 添加到购物车按钮 -->
                            <button 
                                @click="handleAddToCart()"
                                :disabled="isAddingToCart"
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-5 rounded-lg text-lg transition-all duration-300 shadow-md hover:shadow-lg disabled:opacity-70 disabled:cursor-wait">
                                <!-- 点击状态显示 -->
                                <span x-show="isAddingToCart" class="flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>Adding...</span>
                                </span>
                                <!-- 默认状态显示 -->
                                <span x-show="!isAddingToCart" class="flex items-center justify-center gap-2">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l.218-.219.133-.133.942-.941 1.058-1.058a1 1 0 00.028-.03l.21-.209L17.6 4.575A.996.996 0 0018 4H4.76L4.23.85A.997.997 0 003.25.137H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"></path></svg>
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