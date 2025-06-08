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
    <div class="modal-container relative w-full max-h-[96vh] sm:max-w-lg md:max-w-4xl lg:max-w-6xl mx-auto my-2 sm:my-4"
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
        
        <!-- 模态框内容 - Grid布局设计 -->
        <div class="modal bg-white shadow-2xl w-full overflow-hidden transition-all duration-300"
             style="border-radius: 16px !important; box-shadow: 0 20px 40px rgba(0,0,0,0.15); height: 600px;">
            
            <!-- 错误状态 -->
            <div x-show="error" class="flex items-center justify-center h-full">
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

            <!-- 产品内容 - Grid布局 -->
            <div x-show="!error" class="modal-grid-content h-full">

                                <!-- 左侧图片区域 -->
                <div class="image-section">
                    <!-- 主图片显示区域 -->
                    <div class="main-image-container">
                        <img :src="currentImageUrl || '{{ asset('img/placeholder.svg') }}'" 
                             :alt="product ? product.name : ''"
                             class="main-image"
                             onerror="this.src='{{ asset('img/placeholder.svg') }}';">
                    </div>
                    
                    <!-- 缩略图区域 -->
                    <div class="thumbnails-container" x-show="product.images && product.images.length > 1">
                        <template x-for="(image, index) in product.images" :key="image.id">
                            <div class="thumbnail" 
                                 :class="{'active': currentImageUrl === image.main_image_url}"
                                 @click="changeMainImage(image.main_image_url)">
                                <img :src="image.thumbnail_url" 
                                     :alt="product.name"
                                     onerror="this.src='{{ asset('img/placeholder.svg') }}';">
                            </div>
                        </template>
                    </div>
                </div>

                <!-- 右侧信息区域 -->
                <div class="info-section">
                    <!-- 产品标题 -->
                    <h2 class="product-title" x-text="product.name"></h2>
                    
                    <!-- 产品价格 -->
                    <div class="product-price">
                        ¥<span x-text="product.price ? parseFloat(product.price).toFixed(2) : '0.00'"></span>
                    </div>
                    
                    <!-- 产品描述 -->
                    <div class="product-description" x-show="product.description">
                        <p x-html="product.description ? product.description.replace(/\n/g, '<br>') : ''"></p>
                    </div>
                    
                    <!-- 订单信息 -->
                    <div class="order-info">
                        <div class="order-label">
                            <span x-text="translations.minimum_order_quantity"></span>: <span x-text="product.min_order_quantity"></span>
                        </div>
                    </div>
                    
                    <!-- 数量选择 -->
                    <div class="quantity-section">
                        <div class="quantity-controls">
                            <button type="button" class="quantity-btn" @click="quantity > (product.min_order_quantity || 1) && quantity--">−</button>
                            <input type="number" class="quantity-input" x-model.number="quantity" @change="validateQuantity()" :min="product.min_order_quantity || 1">
                            <button type="button" class="quantity-btn" @click="quantity++">+</button>
                        </div>
                    </div>
                    
                    <!-- 添加到购物车按钮 -->
                    <button type="button" class="add-to-cart" @click="addToCart()" :disabled="addToCartFeedback !== ''" x-data="{ clicked: false }" @click.debounce.1000ms="clicked = false" @click="clicked = true; setTimeout(() => clicked = false, 2000)">
                        <template x-if="!addToCartFeedback && !clicked">
                            <span>🛒 <span x-text="translations.add_to_cart"></span></span>
                        </template>
                        <template x-if="clicked || addToCartFeedback">
                            <span>✅ <span x-text="addToCartFeedback || translations.added_to_cart || 'Added to Cart!'"></span></span>
                        </template>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div> 



{{-- 优化的Grid布局样式 --}}
<style>
/* 基础Grid布局 - 强制优先级 */
#product-modal .modal-grid-content {
    display: grid !important;
    grid-template-columns: 1fr 350px !important;
    height: 100% !important;
}

/* 图片区域样式 */
#product-modal .image-section {
    padding: 2rem !important;
    background: #f8f9fa !important;
    display: flex !important;
    flex-direction: column !important;
}

.main-image-container {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border-radius: 12px;
    margin-bottom: 1rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.main-image {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    transition: transform 0.3s ease;
}

.main-image-container:hover .main-image {
    transform: scale(1.05);
}

.thumbnails-container {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
    padding: 0 1rem;
}

.thumbnail {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.3s;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
}

.thumbnail.active {
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

.thumbnail:hover {
    border-color: #007bff;
    transform: translateY(-2px);
}

.thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* 信息区域样式 */
#product-modal .info-section {
    padding: 2rem !important;
    display: flex !important;
    flex-direction: column !important;
    background: white !important;
}

.product-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 1rem;
    line-height: 1.4;
}

.product-price {
    font-size: 2rem;
    font-weight: 700;
    color: #e74c3c;
    margin-bottom: 1.5rem;
}

.product-description {
    color: #666;
    line-height: 1.6;
    margin-bottom: 1.5rem;
    flex: 1;
}

.order-info {
    margin-bottom: 1.5rem;
}

.order-label {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 0.5rem;
}

.quantity-section {
    margin-bottom: 2rem;
}

.quantity-controls {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 12px;
    border: 1px solid #e9ecef;
}

.quantity-btn {
    width: 40px;
    height: 40px;
    border: none;
    background: white;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    font-weight: 600;
    color: #333;
    transition: all 0.3s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.quantity-btn:hover {
    background: #007bff;
    color: white;
    transform: scale(1.1);
}

.quantity-input {
    width: 80px;
    height: 40px;
    text-align: center;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 600;
    background: white;
}

.add-to-cart {
    width: 100%;
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    border: none;
    border-radius: 12px;
    padding: 1rem;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.add-to-cart:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,123,255,0.3);
}

.add-to-cart:active {
    transform: translateY(0);
}



/* 桌面端优化 */
@media (min-width: 768px) {
    #product-modal .modal-container {
        width: 900px !important;
        max-width: 90vw !important;
        margin: 2.5vh auto !important;
        max-height: 90vh !important;
    }
    
    #product-modal .modal {
        height: 600px !important;
    }
    
    #product-modal .modal-grid-content {
        display: grid !important;
        grid-template-columns: 1fr 350px !important;
        height: 100% !important;
    }
}

/* 移动端响应式布局 */
@media (max-width: 767px) {
    #product-modal .modal-container {
        width: 95vw !important;
        height: 95vh !important;
        margin: 2.5vh auto !important;
    }
    
    #product-modal .modal {
        height: 100% !important;
        overflow-y: hidden !important;
    }
    
    #product-modal .modal-grid-content {
        grid-template-columns: 1fr !important;
        grid-template-rows: 40vh auto !important;
        height: 100% !important;
    }
    
    #product-modal .image-section {
        padding: 0.75rem !important;
        height: 40vh !important;
        max-height: 40vh !important;
    }
    
    #product-modal .main-image-container {
        height: calc(40vh - 3rem) !important;
        margin-bottom: 0.5rem !important;
    }
    
    #product-modal .thumbnails-container {
        padding: 0 !important;
        gap: 0.25rem !important;
    }
    
    #product-modal .thumbnail {
        width: 45px !important;
        height: 45px !important;
    }
    
    #product-modal .info-section {
        padding: 1rem !important;
        overflow-y: auto !important;
        height: calc(60vh - 2rem) !important;
    }
    
    #product-modal .product-title {
        font-size: 1.1rem !important;
        margin-bottom: 0.5rem !important;
        line-height: 1.3 !important;
    }
    
    #product-modal .product-price {
        font-size: 1.4rem !important;
        margin-bottom: 0.75rem !important;
    }
    
    #product-modal .product-description {
        font-size: 0.9rem !important;
        margin-bottom: 0.75rem !important;
        line-height: 1.4 !important;
    }
    
    #product-modal .order-info {
        margin-bottom: 0.75rem !important;
        font-size: 0.85rem !important;
    }
    
    #product-modal .quantity-section {
        margin-bottom: 1rem !important;
    }
    
    #product-modal .quantity-controls {
        padding: 0.5rem !important;
    }
    
    #product-modal .add-to-cart {
        font-size: 0.95rem !important;
        padding: 0.75rem !important;
        margin-top: 0.5rem !important;
    }
    
    .product-title {
        font-size: 1.25rem;
    }
    
    .product-price {
        font-size: 1.5rem;
    }
    
    .thumbnails-container {
        padding: 0;
    }
    
    .thumbnail {
        width: 50px;
        height: 50px;
    }
}

@media (max-width: 480px) {
    #product-modal .modal-container {
        width: 100vw !important;
        height: 100vh !important;
        margin: 0 !important;
    }
    
    #product-modal .modal {
        border-radius: 0 !important;
        height: 100vh !important;
        overflow-y: hidden !important;
    }
    
    #product-modal .modal-grid-content {
        grid-template-rows: 35vh auto !important;
        height: 100vh !important;
    }
    
    #product-modal .image-section {
        padding: 0.5rem !important;
        height: 35vh !important;
        max-height: 35vh !important;
    }
    
    #product-modal .main-image-container {
        height: calc(35vh - 2.5rem) !important;
        margin-bottom: 0.25rem !important;
    }
    
    #product-modal .thumbnails-container {
        gap: 0.2rem !important;
    }
    
    #product-modal .thumbnail {
        width: 40px !important;
        height: 40px !important;
    }
    
    #product-modal .info-section {
        padding: 0.75rem !important;
        height: calc(65vh - 1.5rem) !important;
        overflow-y: auto !important;
    }
    
    #product-modal .product-title {
        font-size: 1rem !important;
        margin-bottom: 0.4rem !important;
        line-height: 1.2 !important;
    }
    
    #product-modal .product-price {
        font-size: 1.3rem !important;
        margin-bottom: 0.6rem !important;
    }
    
    #product-modal .product-description {
        font-size: 0.85rem !important;
        margin-bottom: 0.6rem !important;
        line-height: 1.3 !important;
    }
    
    #product-modal .order-info {
        margin-bottom: 0.6rem !important;
        font-size: 0.8rem !important;
    }
    
    #product-modal .quantity-section {
        margin-bottom: 0.8rem !important;
    }
    
    #product-modal .quantity-controls {
        padding: 0.4rem !important;
        gap: 0.5rem !important;
    }
    
    #product-modal .quantity-btn {
        width: 32px !important;
        height: 32px !important;
        font-size: 0.9rem !important;
    }
    
    #product-modal .quantity-input {
        width: 55px !important;
        height: 32px !important;
        font-size: 0.9rem !important;
    }
    
    #product-modal .add-to-cart {
        font-size: 0.9rem !important;
        padding: 0.6rem !important;
        min-height: 42px !important;
        margin-top: 0.4rem !important;
    }
}

/* 矮屏幕设备优化（横屏手机等） */
@media (max-height: 600px) {
    #product-modal .modal-container {
        height: 100vh !important;
        margin: 0 !important;
    }
    
    #product-modal .modal {
        height: 100vh !important;
        border-radius: 0 !important;
    }
    
    #product-modal .modal-grid-content {
        grid-template-rows: 45vh auto !important;
        height: 100vh !important;
    }
    
    #product-modal .image-section {
        height: 45vh !important;
        padding: 0.5rem !important;
    }
    
    #product-modal .main-image-container {
        height: calc(45vh - 2rem) !important;
        margin-bottom: 0.25rem !important;
    }
    
    #product-modal .info-section {
        height: calc(55vh - 1rem) !important;
        padding: 0.5rem !important;
        overflow-y: auto !important;
    }
    
    #product-modal .product-title {
        font-size: 0.95rem !important;
        margin-bottom: 0.3rem !important;
    }
    
    #product-modal .product-price {
        font-size: 1.2rem !important;
        margin-bottom: 0.5rem !important;
    }
    
    #product-modal .product-description {
        font-size: 0.8rem !important;
        margin-bottom: 0.5rem !important;
        line-height: 1.2 !important;
    }
    
    #product-modal .order-info {
        margin-bottom: 0.5rem !important;
        font-size: 0.75rem !important;
    }
    
    #product-modal .quantity-section {
        margin-bottom: 0.6rem !important;
    }
    
    #product-modal .add-to-cart {
        font-size: 0.85rem !important;
        padding: 0.5rem !important;
        min-height: 38px !important;
    }
}

/* 平板设备优化 */
@media (min-width: 768px) and (max-width: 1024px) {
    .modal-container {
        max-width: 90vw !important;
        max-height: 94vh !important;
        margin: 3vh auto !important;
    }
    
    .modal {
        height: 100% !important;
    }
}

/* 大屏幕（桌面端）左右分栏布局优化 */
@media (min-width: 1025px) {
    .modal-container {
        max-width: 1200px !important;
        max-height: 92vh !important;
        margin: 4vh auto !important;
    }
    
    .modal {
        height: 100% !important;
    }
    
    /* 大屏幕时左右区域平衡 */
    #product-modal .modal .md\\:w-1\\/2 {
        flex: 1;
    }
}

/* 响应式图片显示优化 */
@media (min-width: 768px) {
    /* 桌面端图片区域圆角调整 */
    #product-modal .modal .md\\:w-1\\/2:first-child .bg-gradient-to-br {
        border-top-left-radius: 20px !important;
        border-bottom-left-radius: 20px !important;
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    
    /* 桌面端信息区域圆角调整 */
    #product-modal .modal .md\\:w-1\\/2:last-child {
        border-top-right-radius: 20px !important;
        border-bottom-right-radius: 20px !important;
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }
}

/* 图片缩略图交互优化 */
.modal .overflow-x-auto button:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.modal .overflow-x-auto button:active {
    transform: scale(0.98);
}

/* 响应式过渡动画 */
.modal .md\\:flex-row {
    transition: flex-direction 0.3s ease;
}

.modal .md\\:w-1\\/2 {
    transition: width 0.3s ease, border-radius 0.3s ease;
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
#product-modal .modal .h-56 {
    height: 224px !important;
    min-height: 224px !important;
    max-height: 224px !important;
}

#product-modal .modal .sm\\:h-64 {
    height: 256px !important;
    min-height: 256px !important;
    max-height: 256px !important;
}

#product-modal .modal .lg\\:h-72 {
    height: 288px !important;
    min-height: 288px !important;
    max-height: 288px !important;
}

/* 确保图片不会溢出容器 */
#product-modal .modal .flex.items-center.justify-center {
    overflow: hidden !important;
}
</style> 