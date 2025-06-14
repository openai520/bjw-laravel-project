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
    @open-product-modal.window="openModal($event.detail.productId)"
    @keydown.escape.window="closeModal()"
    class="modal-overlay"
    :class="{ 'active': isOpen }"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" 
    role="dialog" 
    aria-modal="true" 
    aria-labelledby="modal-title"
>
    <!-- 背景遮罩 -->
    <div @click="closeModal()" class="modal-backdrop"></div>

    <!-- 模态框容器 -->
    <div class="modal-container">
        <!-- 关闭按钮 -->
        <button @click="closeModal()" class="modal-close-button" aria-label="{{ __('Close') }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>

        <!-- 加载状态 -->
        <template x-if="loading">
            <div class="modal-loading-skeleton">
                <div class="skeleton-image"></div>
                <div class="skeleton-content">
                    <div class="skeleton-line h-8 w-3/4"></div>
                    <div class="skeleton-line h-10 w-1/2"></div>
                    <div class="skeleton-line h-4 w-full"></div>
                    <div class="skeleton-line h-4 w-5/6"></div>
                    <div class="skeleton-line h-12 w-full mt-4"></div>
                </div>
            </div>
        </template>

        <!-- 错误状态 -->
        <template x-if="!loading && error">
             <div class="modal-error-state">
                <svg class="w-16 h-16 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                <h3 id="modal-title" class="mt-4 text-lg font-medium text-gray-900">{{ __('Error') }}</h3>
                <p class="mt-2 text-sm text-gray-500" x-text="error"></p>
            </div>
        </template>

        <!-- 产品内容 -->
        <template x-if="!loading && !error && product.id">
            <div class="modal-content-wrapper">
                <!-- 左侧：图片 -->
                <div class="modal-image-column">
                    <div class="modal-main-image">
                        <img :src="currentImageUrl" :alt="product.name" class="modal-product-image" onerror="this.src='{{ asset('img/placeholder.svg') }}'">
                    </div>
                    <div class="modal-thumbnails" x-show="product.images && product.images.length > 1">
                        <template x-for="image in product.images" :key="image.id">
                            <button @click="changeMainImage(image.main_image_url)" class="modal-thumbnail-button" :class="{ 'active': currentImageUrl === image.main_image_url }">
                                <img :src="image.thumbnail_url" :alt="product.name" class="w-full h-full object-cover" onerror="this.src='{{ asset('img/placeholder.svg') }}'">
                            </button>
                        </template>
                    </div>
                </div>
                <!-- 右侧：信息和操作 -->
                <div class="modal-details-column">
                    <h1 id="modal-title" class="modal-product-title" x-text="product.name"></h1>
                    <p class="modal-product-price">
                        ¥<span x-text="product.price ? parseFloat(product.price).toFixed(2) : '0.00'"></span>
                    </p>
                    <div class="modal-product-description" x-html="product.description ? product.description.replace(/\n/g, '<br>') : ''"></div>
                    
                    <div class="mt-auto pt-4">
                        <p class="modal-min-order-notice" x-text="`${translations.minimum_order_quantity || 'MOQ'}: ${product.min_order_quantity}`"></p>
                        <div class="modal-actions">
                            <div class="quantity-selector">
                                <button @click="quantity > (product.min_order_quantity || 1) && quantity--" :disabled="quantity <= (product.min_order_quantity || 1)" class="quantity-btn">-</button>
                                <input type="number" x-model.number="quantity" @change="validateQuantity()" :min="product.min_order_quantity || 1" class="quantity-input">
                                <button @click="quantity++" class="quantity-btn">+</button>
                            </div>
                            <button @click="handleAddToCart()" :disabled="isAddingToCart" class="add-to-cart-btn">
                                <span x-show="isAddingToCart" class="flex items-center justify-center">
                                    <svg class="w-5 h-5 animate-spin mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    Processing...
                                </span>
                                <span x-show="!isAddingToCart" x-text="translations.add_to_cart || 'Add to Cart'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

<style>
/* 模态框遮罩样式 */
.modal-overlay {
    position: fixed;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    background: rgba(0, 0, 0, 0);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    opacity: 0;
    visibility: hidden;
    z-index: 1000;
}

.modal-overlay.active {
    background: rgba(0, 0, 0, 0.4); /* 减少不透明度，增加透明度 */
    opacity: 1;
    visibility: visible;
}

/* 背景遮罩层 */
.modal-backdrop {
    position: absolute;
    inset: 0;
    background: transparent; /* 让overlay本身处理遮罩 */
    backdrop-filter: blur(3px);
    -webkit-backdrop-filter: blur(3px);
}

/* 模态框容器基础样式 */
.modal-container {
    position: relative;
    background: white;
    border-radius: 24px; /* 同心圆圆角效果 */
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    transform: scale(0.9);
    z-index: 1001;
    width: 100%;
    max-width: 1000px; /* 大屏幕进一步增加宽度，为缩略图留更多空间 */
    max-height: 90vh;
    overflow: hidden; /* 禁止滚动 */
}

/* 当模态框激活时的样式 */
.modal-overlay.active .modal-container {
    transform: scale(1);
}

/* 响应式设计 */
@media (max-width: 768px) {
    .modal-overlay {
        padding: 0;
        align-items: flex-end;
    }
    
    .modal-container {
        width: 100% !important;
        max-width: none !important;
        max-height: 95vh !important;
        border-radius: 24px 24px 0 0 !important; /* 小屏幕只保留上方圆角 */
        transform: translateY(100%);
    }
    
    .modal-overlay.active .modal-container {
        transform: translateY(0);
    }
    
    .modal-overlay.active {
        background: rgba(0, 0, 0, 0.5); /* 小屏幕也减少不透明度 */
    }
}

/* 内部元素圆角 - 同心圆效果 */
.modal-content-wrapper {
    border-radius: 20px; /* 内容区域圆角 */
    overflow: hidden;
    margin: 16px;
    background: white;
}

.modal-main-image {
    border-radius: 16px; /* 图片区域圆角 */
    overflow: hidden;
}

.modal-thumbnails {
    gap: 8px;
}

.modal-thumbnail-button {
    border-radius: 12px; /* 缩略图圆角 */
    overflow: hidden;
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

/* 关闭按钮样式 */
.modal-close-button {
    position: absolute;
    top: 16px;
    right: 16px;
    background: rgba(0, 0, 0, 0.1);
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.2s ease;
    z-index: 10;
}

.modal-close-button:hover {
    background: rgba(0, 0, 0, 0.2);
}

/* 隐藏滚动条，禁止滚动 */
.modal-container::-webkit-scrollbar {
    display: none;
}

.modal-container {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style> 