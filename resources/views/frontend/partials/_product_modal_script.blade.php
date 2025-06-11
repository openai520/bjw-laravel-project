{{-- 产品模态框JavaScript --}}
<script>
// 等待Alpine.js初始化完成
document.addEventListener('alpine:init', function() {
    console.log('🎯 Alpine.js 已初始化，产品模态框功能使用事件总线');
    console.log('✅ 旧的openProductModal全局函数已清理，现在使用Alpine.js事件分发机制');
});

function productModal() {
    return {
        isOpen: false,
        loading: false,
        error: null,
        product: { id: null, name: '', description: '', price: 0, min_order_quantity: 1, images: [], main_image_url: '' },
        currentImageUrl: '',
        quantity: 1,
        isAddingToCart: false,
        cartStoreUrl: '',
        csrfToken: '',
        translations: {},

        // 彻底移除init()和$watch，回归最简单的事件驱动
        
        async openModal(productId) {
            if (!productId) return;

            // 1. 设置初始状态
            this.isOpen = true;
            this.loading = true;
            this.error = null;
            document.body.classList.add('modal-open');

            // 2. 获取数据
            try {
                const lang = document.documentElement.lang || 'en';
                const url = `/${lang}/api/products/${productId}/modal`;
                const response = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });

                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

                const data = await response.json();

                if (data.success) {
                    this.product = data.product;
                    this.cartStoreUrl = data.cart_store_url;
                    this.csrfToken = data.csrf_token;
                    this.translations = data.translations || {};
                    this.quantity = this.product.min_order_quantity || 1;
                    this.currentImageUrl = this.product.main_image_url || (this.product.images.length > 0 ? this.product.images[0].main_image_url : '{{ asset('img/placeholder.svg') }}');
                } else {
                    throw new Error(data.message || 'Failed to load product data');
                }
            } catch (err) {
                console.error('Error fetching product data:', err);
                this.error = err.message || 'An error occurred';
            } finally {
                // 3. 结束加载状态
                this.loading = false;
            }
        },

        closeModal() {
            this.isOpen = false;
            document.body.classList.remove('modal-open');
        },
        
        onModalClose() {
            this.loading = false;
            this.error = null;
            this.product = { id: null, name: '', description: '', price: 0, min_order_quantity: 1, images: [], main_image_url: '' };
            this.currentImageUrl = '';
            this.quantity = 1;
            this.isAddingToCart = false;
        },

        changeMainImage(url) {
            this.currentImageUrl = url;
        },

        validateQuantity() {
            const minQty = this.product.min_order_quantity || 1;
            if (this.quantity < minQty || !Number.isInteger(this.quantity)) {
                this.quantity = minQty;
            }
        },
        
        // 处理购物车逻辑
        async handleAddToCart() {
            if (this.isAddingToCart) return;
            this.isAddingToCart = true;

            try {
                const response = await fetch(this.cartStoreUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        product_id: this.product.id,
                        quantity: this.quantity
                    })
                });

                const data = await response.json();

                if (!response.ok) throw new Error(data.message || 'Failed to add to cart');
                
                // 触发全局事件，通知导航栏等其他组件更新购物车
                this.$dispatch('cart-updated', { cart: data.cart });
                
                // (可选) 短暂显示成功状态，然后关闭模态框
                // setTimeout(() => this.closeModal(), 1000);

            } catch (err) {
                console.error('Add to cart error:', err);
                // (可选) 显示错误信息给用户
            } finally {
                this.isAddingToCart = false;
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