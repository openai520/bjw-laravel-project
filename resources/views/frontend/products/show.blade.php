@extends('frontend.layouts.app')

@section('title', $product->name)

@section('content')
    @php
        // 获取主图URL
        $initialImageUrl = $product->main_image_url;

        $jsTranslations = [
            'item_added_to_cart' => __('cart.item_added_to_cart'),
            'error_adding' => __('cart.error_occurred'),
            'processing' => __('cart.processing'),
        ];
    @endphp

    <div x-data="productShowData(
            {{ $product->min_order_quantity }},
            {{ $product->id }},
            '{{ route('frontend.cart.store', ['lang' => app()->getLocale()]) }}',
            '{{ csrf_token() }}',
            '{{ $initialImageUrl }}'
        )"
         class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">

        <!-- 提示消息 -->
        <div x-data="{ showMessage: false, message: '', isError: false }"
             x-show="showMessage"
             x-transition
             @cart-message.window="showMessage = true; message = $event.detail.message; isError = $event.detail.isError; setTimeout(() => showMessage = false, 3000)"
             :class="isError ? 'bg-red-100 border-red-400 text-red-700' : 'bg-green-100 border-green-400 text-green-700'"
             class="border px-4 py-3 rounded relative mb-4"
             style="display: none;">
            <span x-text="message"></span>
        </div>

        <!-- 主内容区 - 改为响应式 Grid 布局 -->
        <div class="md:grid md:grid-cols-12 md:gap-x-6 lg:gap-x-8">
            <!-- 图片区域 - Grid 左侧 -->
            <div class="md:col-span-5 lg:col-span-5 mb-8 md:mb-0">
                <!-- 主图 -->
                <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden mb-4">
                    <img :src="mainImageUrl"
                         alt="{{ $product->name }}"
                         class="w-full h-full object-contain object-center"
                         onerror="this.onerror=null; this.src='{{ asset('img/placeholder.svg') }}';">
                </div>

                <!-- 缩略图轮播 -->
                <div class="grid grid-cols-5 md:grid-cols-6 gap-2">
                    @foreach($product->images as $image)
                        <div class="aspect-square bg-gray-100 rounded overflow-hidden cursor-pointer border-2 hover:border-blue-400 transition-colors duration-200"
                             :class="{ 'border-blue-500': mainImageUrl === '{{ $image->main_image_url }}', 'border-gray-200': mainImageUrl !== '{{ $image->main_image_url }}' }"
                             @click="mainImageUrl = '{{ $image->main_image_url }}'">
                            <img src="{{ $image->thumbnail_url }}"
                                 alt="{{ $product->name }} thumbnail"
                                 class="w-full h-full object-cover object-center"
                                 onerror="this.onerror=null; this.src='{{ asset('img/placeholder.svg') }}';">
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- 产品信息区域 - Grid 右侧 -->
            <div class="md:col-span-7 lg:col-span-7">
                <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-3">
                    {{ $product->name }}
                </h1>

                <p class="text-3xl font-semibold text-red-600 mb-5">
                    ¥{{ number_format($product->price, 2) }}
                </p>

                <div class="prose prose-sm max-w-none text-gray-600 mb-6">
                    {!! nl2br(e($product->description)) !!}
                </div>

                <p class="text-sm text-gray-500 mb-5">
                    {{ __('messages.minimum_order_quantity') }}: {{ $product->min_order_quantity }}
                </p>

                <!-- 数量和按钮容器 - 修改为垂直堆叠 -->
                <div class="mt-6">
                    <!-- 数量选择器 -->
                    <div class="mb-4">
                        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('messages.quantity') }}
                        </label>
                        <div class="relative flex items-center space-x-1">
                            <button type="button"
                                    @click="quantity > minQuantity && quantity--"
                                    class="flex items-center justify-center w-8 h-8 border border-gray-300 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-full focus:outline-none focus:ring-2 focus:ring-gray-400 disabled:opacity-50 transition-colors"
                                    :disabled="quantity <= minQuantity">
                                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z" clip-rule="evenodd"></path></svg>
                            </button>
                            <input type="text" 
                                id="quantity"
                                x-model.number="quantity"
                                @change="validateQuantity()"
                                class="w-20 h-8 text-center text-base font-medium text-gray-800 focus:outline-none border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm p-0">
                            <button type="button"
                                    @click="quantity++"
                                    class="flex items-center justify-center w-8 h-8 bg-gray-800 text-white hover:bg-gray-700 rounded-full focus:outline-none focus:ring-2 focus:ring-gray-600 transition-colors">
                                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
                            </button>
                        </div>
                    </div>

                    <!-- 添加到购物车按钮 -->
                    <div>
                        <button type="button"
                                @click="addToCart()"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg transition duration-300 flex items-center justify-center space-x-2"
                                :disabled="addToCartFeedback !== ''">
                            <span x-show="!addToCartFeedback">{{ __('messages.add_to_cart') }}</span>
                            <span x-show="addToCartFeedback" x-text="addToCartFeedback"></span>
                            <svg x-show="addToCartFeedback" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- 添加购物车反馈信息 -->
                <div class="mt-2 h-6">
                    <!-- 反馈信息已经在按钮中显示 -->
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const translations = @json($jsTranslations);

    function productShowData(minQuantity, productId, cartStoreUrl, csrfToken, initialImageUrl) {
        return {
            quantity: minQuantity,
            minQuantity: minQuantity,
            mainImageUrl: initialImageUrl,
            addToCartFeedback: '',

            validateQuantity() {
                if (isNaN(this.quantity) || this.quantity < this.minQuantity) {
                    this.quantity = this.minQuantity;
                } else {
                    this.quantity = parseInt(this.quantity, 10); //确保是整数
                }
            },

            addToCart() {
                this.validateQuantity(); // 添加到购物车前再次验证
                this.addToCartFeedback = translations['processing'];
                fetch(cartStoreUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        quantity: this.quantity
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success || data.message) {
                        window.dispatchEvent(new CustomEvent('cart-updated', {
                            detail: { count: data.cart_count }
                        }));
                        window.dispatchEvent(new CustomEvent('cart-message', {
                            detail: { message: translations['item_added_to_cart'], isError: false }
                        }));
                    } else {
                        window.dispatchEvent(new CustomEvent('cart-message', {
                            detail: { message: translations['error_adding'], isError: true }
                        }));
                    }
                    this.addToCartFeedback = '';
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    window.dispatchEvent(new CustomEvent('cart-message', {
                        detail: { message: translations['error_adding'], isError: true }
                    }));
                    this.addToCartFeedback = '';
                });
            }
        };
    }
</script>
@endpush