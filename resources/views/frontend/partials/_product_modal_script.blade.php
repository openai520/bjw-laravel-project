{{-- 产品模态框JavaScript --}}
<script>
// 等待Alpine.js初始化完成
document.addEventListener('alpine:init', function() {
    console.log('🎯 Alpine.js 已初始化，注册产品模态框功能');
    
    // 确保全局函数可用
    if (typeof window.openProductModal !== 'function') {
        window.openProductModal = function(productId) {
            console.log('🚀 开启产品模态框，产品ID:', productId);
            window.dispatchEvent(new CustomEvent('open-product-modal', {
                detail: { productId: productId }
            }));
        };
    }
});

// 立即定义函数作为备用方案
if (typeof window.openProductModal !== 'function') {
    window.openProductModal = function(productId) {
        console.log('🚀 备用方案：开启产品模态框，产品ID:', productId);
        window.dispatchEvent(new CustomEvent('open-product-modal', {
            detail: { productId: productId }
        }));
    };
}

// 确保在DOM加载完成后也初始化
document.addEventListener('DOMContentLoaded', function() {
    console.log('Product modal script loaded');
    
    // 确保全局函数可用
    if (typeof window.openProductModal !== 'function') {
        window.openProductModal = function(productId) {
            console.log('Opening product modal for product ID:', productId);
            window.dispatchEvent(new CustomEvent('open-product-modal', {
                detail: { productId: productId }
            }));
        };
    }
});

function productModal() {
    return {
        isOpen: false,
        loading: false,
        error: null,
        product: {
            id: null,
            name: '',
            description: '',
            price: 0,
            min_order_quantity: 1,
            category: null,
            images: [],
            main_image_url: '',
            thumbnail_url: ''
        },
        currentImageUrl: '', // 当前显示的主图URL
        quantity: 1,
        addToCartFeedback: '',
        isAddingToCart: false, // 新增：控制按钮状态
        cartStoreUrl: '',
        csrfToken: '',
        translations: {},
        
        // 焦点管理相关属性
        previousActiveElement: null,
        focusableElements: [],
        
        // 监听数量变化并更新按钮状态
        init() {
            this.$watch('quantity', () => {
                this.updateButtonStates();
            });
            this.$watch('product.min_order_quantity', () => {
                this.updateButtonStates();
            });
            
            // 监听模态框开启和关闭事件
            this.$watch('isOpen', (isOpen) => {
                if (isOpen) {
                    this.trapFocus();
                } else {
                    this.restoreFocus();
                }
            });
        },

        updateButtonStates() {
            this.$nextTick(() => {
                const minusBtn = this.$el.querySelector('.quantity-btn.minus');
                const minQty = this.product.min_order_quantity || 1;
                
                if (minusBtn) {
                    if (this.quantity <= minQty) {
                        minusBtn.style.color = '#d1d5db';
                        minusBtn.style.cursor = 'not-allowed';
                        minusBtn.disabled = true;
                    } else {
                        minusBtn.style.color = '#6b7280';
                        minusBtn.style.cursor = 'pointer';
                        minusBtn.disabled = false;
                    }
                }
            });
        },

        openModal(productId) {
            if (!productId) return;
            
            console.log('Opening modal for product ID:', productId);
            this.isOpen = true;
            this.error = null;
            
            // 增强的背景滚动锁定
            document.body.classList.add('modal-open');
            document.body.style.overflow = 'hidden';
            document.body.style.height = '100vh';
            
            // 在后台获取产品数据，但不显示加载状态
            this.fetchProductData(productId);
        },

        closeModal() {
            this.isOpen = false;
            this.loading = false;
            this.error = null;
            this.resetProduct();
            
            // 恢复背景滚动
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.height = '';
        },

        resetProduct() {
            this.product = {
                id: null,
                name: '',
                description: '',
                price: 0,
                min_order_quantity: 1,
                category: null,
                images: [],
                main_image_url: '',
                thumbnail_url: ''
            };
            this.currentImageUrl = '';
            this.quantity = 1;
            this.addToCartFeedback = '';
            this.isAddingToCart = false;
        },

        async fetchProductData(productId) {
            try {
                const lang = document.documentElement.lang || 'en';
                const url = `/${lang}/api/products/${productId}/modal`;
                
                console.log('Fetching product data from:', url);
                
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                console.log('Product data received:', data);

                if (data.success) {
                    // 确保所有字段都有默认值
                    this.product = {
                        id: data.product.id || null,
                        name: data.product.name || '',
                        description: data.product.description || '',
                        price: data.product.price || 0,
                        min_order_quantity: data.product.min_order_quantity || 1,
                        category: data.product.category || null,
                        images: data.product.images || [],
                        main_image_url: data.product.main_image_url || '{{ asset('img/placeholder.svg') }}',
                        thumbnail_url: data.product.thumbnail_url || '{{ asset('img/placeholder.svg') }}'
                    };
                    
                    this.cartStoreUrl = data.cart_store_url;
                    this.csrfToken = data.csrf_token;
                    this.translations = data.translations || {};
                    this.quantity = this.product.min_order_quantity || 1;
                    
                    // 设置当前显示的主图 - 添加更多调试和回退逻辑
                    let imageUrl = this.product.main_image_url;
                    
                    // 如果主图URL无效，尝试使用第一个图片
                    if (!imageUrl || imageUrl === '{{ asset('img/placeholder.svg') }}') {
                        if (this.product.images && this.product.images.length > 0) {
                            imageUrl = this.product.images[0].main_image_url || this.product.images[0].thumbnail_url;
                        }
                    }
                    
                    // 最终回退到占位符
                    if (!imageUrl) {
                        imageUrl = '{{ asset('img/placeholder.svg') }}';
                    }
                    
                    this.currentImageUrl = imageUrl;
                    
                    // 更新按钮状态
                    this.updateButtonStates();
                    
                    console.log('Product set:', this.product);
                    console.log('Main image URL:', this.product.main_image_url);
                    console.log('Current image URL:', this.currentImageUrl);
                    console.log('Product images:', this.product.images);
                    
                    // 强制触发界面更新
                    this.$nextTick(() => {
                        console.log('UI updated, currentImageUrl in DOM should be:', this.currentImageUrl);
                    });
                } else {
                    throw new Error(data.message || 'Failed to load product data');
                }
            } catch (error) {
                console.error('Error fetching product data:', error);
                this.error = error.message || 'An error occurred while loading product data';
                // 即使出错也设置一个默认图片
                this.currentImageUrl = '{{ asset('img/placeholder.svg') }}';
            }
        },

        validateQuantity() {
            const minQty = this.product.min_order_quantity || 1;
            if (isNaN(this.quantity) || this.quantity < minQty) {
                this.quantity = minQty;
            } else {
                this.quantity = parseInt(this.quantity, 10);
            }
            this.updateButtonStates();
        },

        // 切换主图
        changeMainImage(imageUrl) {
            this.currentImageUrl = imageUrl;
            console.log('Main image changed to:', imageUrl);
        },

        // 焦点管理：捕获焦点
        trapFocus() {
            this.$nextTick(() => {
                // 保存当前焦点元素
                this.previousActiveElement = document.activeElement;
                
                // 获取模态框内所有可聚焦元素
                const modal = this.$el;
                this.focusableElements = modal.querySelectorAll(
                    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
                );
                
                // 聚焦到第一个可聚焦元素
                if (this.focusableElements.length > 0) {
                    this.focusableElements[0].focus();
                }
                
                // 添加键盘事件监听
                document.addEventListener('keydown', this.handleKeyDown);
            });
        },
        
        // 焦点管理：恢复焦点
        restoreFocus() {
            // 移除键盘事件监听
            document.removeEventListener('keydown', this.handleKeyDown);
            
            // 恢复之前的焦点
            if (this.previousActiveElement) {
                this.previousActiveElement.focus();
                this.previousActiveElement = null;
            }
        },
        
        // 处理键盘事件
        handleKeyDown(e) {
            // ESC键关闭模态框
            if (e.key === 'Escape') {
                this.closeModal();
                return;
            }
            
            // Tab键循环聚焦
            if (e.key === 'Tab') {
                this.handleTabKey(e);
            }
        },
        
        // 处理Tab键循环
        handleTabKey(e) {
            if (this.focusableElements.length === 0) return;
            
            const firstElement = this.focusableElements[0];
            const lastElement = this.focusableElements[this.focusableElements.length - 1];
            
            if (e.shiftKey) {
                // Shift + Tab：向前循环
                if (document.activeElement === firstElement) {
                    lastElement.focus();
                    e.preventDefault();
                }
            } else {
                // Tab：向后循环
                if (document.activeElement === lastElement) {
                    firstElement.focus();
                    e.preventDefault();
                }
            }
        },

        // 处理添加到购物车按钮点击
        async handleAddToCart() {
            if (this.isAddingToCart) return; // 防止重复点击
            
            this.isAddingToCart = true;
            try {
                await this.addToCart();
            } finally {
                // 1秒后恢复按钮状态，即使出错也要恢复
                setTimeout(() => {
                    this.isAddingToCart = false;
                }, 1000);
            }
        },

        async addToCart() {
            if (!this.product.id || !this.cartStoreUrl) {
                console.error('Missing product ID or cart URL');
                return;
            }

            this.validateQuantity();
            this.addToCartFeedback = this.translations.processing || 'Processing...';

            try {
                const response = await fetch(this.cartStoreUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        product_id: this.product.id,
                        quantity: this.quantity
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success || data.message) {
                    // 通知购物车数量更新
                    window.dispatchEvent(new CustomEvent('cart-updated', {
                        detail: { count: data.cart_count }
                    }));
                    
                    // 显示成功消息
                    window.dispatchEvent(new CustomEvent('cart-message', {
                        detail: { 
                            message: this.translations.item_added_to_cart || 'Item added to cart', 
                            isError: false 
                        }
                    }));

                    // 显示成功反馈
                    this.addToCartFeedback = this.translations.item_added_to_cart || 'Item added to cart';
                    
                    // 2秒后清除反馈
                    setTimeout(() => {
                        this.addToCartFeedback = '';
                    }, 2000);
                } else {
                    throw new Error(data.message || 'Failed to add item to cart');
                }
            } catch (error) {
                console.error('Error adding to cart:', error);
                
                // 显示错误消息
                window.dispatchEvent(new CustomEvent('cart-message', {
                    detail: { 
                        message: this.translations.error_adding || 'Error adding item to cart', 
                        isError: true 
                    }
                }));

                this.addToCartFeedback = this.translations.error_adding || 'Error adding to cart';
                
                // 3秒后清除错误反馈
                setTimeout(() => {
                    this.addToCartFeedback = '';
                }, 3000);
            }
        }
    };
}

// 立即定义全局函数（不等待DOM加载）
window.openProductModal = function(productId) {
    console.log('Opening product modal for product ID:', productId);
    window.dispatchEvent(new CustomEvent('open-product-modal', {
        detail: { productId: productId }
    }));
};
</script> 