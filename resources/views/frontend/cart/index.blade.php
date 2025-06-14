@extends('frontend.layouts.app')

@section('title', __('messages.your_shopping_cart'))

@section('content')
    @php
        $jsTranslations = [
            'cart_updated' => __('cart.cart_updated'),
            'update_failed' => __('cart.update_failed'),
            'item_removed' => __('cart.item_deleted'),
            'remove_failed' => __('cart.delete_failed'),
            'error_updating' => __('cart.error_occurred'),
            'error_removing' => __('cart.error_occurred'),
            'confirm_remove' => __('cart.confirm_delete_message'),
            'inquiry_submitted' => __('cart.inquiry_success_message'),
            'submission_failed' => __('cart.inquiry_failed'),
            'error_submitting' => __('cart.error_occurred'),
        ];
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-5">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">{{ __('messages.shopping_cart') }}</h1>
        </div>

        <div x-data="shoppingCartData(
            {{ Js::from($cart) }},
            {{ $total }},
            '{{ route('cart.update.temp', ['itemId' => 'PLACEHOLDER_ID']) }}',
            '{{ route('cart.remove.temp', ['itemId' => 'PLACEHOLDER_ID']) }}',
            '{{ csrf_token() }}',
            '{{ route('frontend.inquiries.store', ['lang' => app()->getLocale()]) }}'
        )"
            class="w-full py-0">
            <!-- 加载状态指示器 -->
            <div x-show="isLoading"
                class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
                <div class="bg-white p-4 rounded-lg shadow-xl">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                    <p class="mt-2 text-gray-600">加载中...</p>
                </div>
            </div>

            <!-- 购物车表格容器 -->
            <div>
                <!-- 表格头部 -->

                <!-- 购物车列表 -->
                <ul role="list" class="">
                    <template x-if="Object.keys(cartItems).length > 0">
                        <template x-for="(item, itemId) in cartItems" :key="itemId">
                            <li x-if="item" class="bg-white shadow overflow-hidden mb-4"
                                style="border-radius: 20px !important;">
                                <div class="px-4 py-4 sm:px-6">
                                    <div class="grid grid-cols-6 sm:grid-cols-12 gap-x-4 sm:gap-x-6 items-center">
                                        <!-- 图片 -->
                                        <div class="col-span-2 sm:col-span-2">
                                            <div class="aspect-square bg-gray-100 rounded overflow-hidden">
                                                <img :src="item.main_image_url" :alt="item.name"
                                                    class="w-full h-full object-cover object-center">
                                            </div>
                                        </div>
                                        <!-- Info & Actions Container -->
                                        <div
                                            class="col-span-4 sm:col-span-10 flex flex-col items-stretch space-y-3 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                                            <!-- Title div -->
                                            <div class="min-w-0 sm:mr-6">
                                                <p class="text-xs sm:text-sm font-medium text-gray-900 leading-normal truncate"
                                                    x-text="item.name"></p>
                                            </div>

                                            <!-- Controls Group div -->
                                            <div
                                                class="flex flex-wrap items-center justify-start gap-x-4 gap-y-2 sm:flex-nowrap sm:justify-start">
                                                <!-- Quantity div -->
                                                <div class="relative flex items-center space-x-1">
                                                    <button type="button"
                                                        @click="item.quantity > item.min_order_quantity && updateCartItemQuantity(itemId, item.quantity - 1)"
                                                        class="flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 border border-gray-300 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-full focus:outline-none focus:ring-2 focus:ring-gray-400 disabled:opacity-50 transition-colors"
                                                        :disabled="item.quantity <= item.min_order_quantity">
                                                        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd"
                                                                d="M5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z"
                                                                clip-rule="evenodd"></path>
                                                        </svg>
                                                    </button>
                                                    <input type="text"
                                                        x-model.number="item.quantity"
                                                        @change="validateCartItemQuantity(itemId)"
                                                        class="w-16 sm:w-20 h-8 text-center text-base font-medium text-gray-800 focus:outline-none border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm p-0">
                                                    <button type="button"
                                                        @click="updateCartItemQuantity(itemId, item.quantity + 1)"
                                                        class="flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 bg-gray-800 text-white hover:bg-gray-700 rounded-full focus:outline-none focus:ring-2 focus:ring-gray-600 transition-colors">
                                                        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd"
                                                                d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                                                                clip-rule="evenodd"></path>
                                                        </svg>
                                                    </button>
                                                </div>

                                                <!-- Unit Price div -->
                                                <div>
                                                    <p class="text-sm font-bold text-gray-900">¥<span
                                                            x-text="parseFloat(item.price).toFixed(2)"></span></p>
                                                </div>

                                                <!-- Subtotal div -->
                                                <div>
                                                    <p class="text-base font-extrabold text-red-600">¥<span
                                                            x-text="(item.quantity * item.price).toFixed(2)"></span></p>
                                                </div>

                                                <!-- Delete div -->
                                                <div>
                                                    <button type="button" @click="itemToRemove = itemId"
                                                        class="text-gray-400 hover:text-red-500 p-1 sm:p-2">
                                                        <span class="sr-only">{{ __('messages.remove') }}</span>
                                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd"
                                                                d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                                clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </template>
                    </template>

                    <!-- 购物车为空时显示 -->
                    <template x-if="Object.keys(cartItems).length === 0">
                        <div class="text-center py-10">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" aria-hidden="true">
                                <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('messages.cart_empty') }}</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ __('messages.cart_empty_prompt') }}</p>
                            <div class="mt-6">
                                <a href="{{ route('frontend.home', ['lang' => app()->getLocale()]) }}"
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                        fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd"
                                            d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    {{ __('messages.continue_shopping') }}
                                </a>
                            </div>
                        </div>
                    </template>
                </ul>

                <!-- 购物车总计和询价按钮 -->
                <template x-if="Object.keys(cartItems).length > 0">
                    <div class="border-t border-gray-200 px-6 py-4 bg-gray-50">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                            <div class="space-y-2">
                                <div class="flex items-baseline">
                                    <span class="text-gray-700">{{ __('messages.total_items') }}:</span>
                                    <span x-text="totalQuantity" class="ml-2 text-xl font-bold text-blue-600"></span>
                                    <span class="ml-1 text-sm text-gray-500">{{ __('messages.pieces') }}</span>
                                </div>
                                <div class="flex items-baseline">
                                    <span class="text-gray-700">{{ __('messages.total_amount') }}:</span>
                                    <span class="ml-2 text-2xl font-bold text-red-600">¥<span
                                            x-text="totalAmount.toFixed(2)"></span></span>
                                </div>
                            </div>
                            <button type="button"
                                @click="showInquiryModal = true"
                                class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                {{ __('messages.submit_inquiry') }}
                            </button>
                        </div>
                    </div>
                </template>

                @if (count($cart) > 0)
                    <!-- 询价表单弹窗 -->
                    <div x-show="showInquiryModal"
                        x-cloak
                        class="fixed inset-0 z-50 overflow-y-auto"
                        aria-labelledby="modal-title"
                        role="dialog"
                        aria-modal="true">
                        <div
                            class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            <!-- 背景遮罩 -->
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                                aria-hidden="true"
                                @click="showInquiryModal = false"></div>

                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                                aria-hidden="true">&#8203;</span>

                            <!-- 弹窗内容 -->
                            <div
                                class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                                <div class="sm:flex sm:items-start">
                                    <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                            {{ __('messages.submit_inquiry') }}
                                        </h3>
                                        <div class="mt-2">
                                            <p class="text-sm text-gray-500">
                                                {{ __('messages.please_fill_info') }}
                                            </p>
                                        </div>

                                        <form @submit.prevent="submitInquiryForm" class="mt-5 space-y-4">
                                            <div>
                                                <label for="name" class="block text-sm font-medium text-gray-700">
                                                    {{ __('messages.name') }} *
                                                </label>
                                                <input type="text"
                                                    name="name"
                                                    id="name"
                                                    required
                                                    x-model="formData.name"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                            </div>

                                            <div>
                                                <label for="country" class="block text-sm font-medium text-gray-700">
                                                    {{ __('messages.country') }} *
                                                </label>
                                                <input type="text"
                                                    name="country"
                                                    id="country"
                                                    required
                                                    x-model="formData.country"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                            </div>

                                            <div>
                                                <label for="phone" class="block text-sm font-medium text-gray-700">
                                                    {{ __('messages.phone') }} *
                                                </label>
                                                <input type="tel"
                                                    name="phone"
                                                    id="phone"
                                                    required
                                                    x-model="formData.phone"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                            </div>

                                            <div>
                                                <label for="whatsapp" class="block text-sm font-medium text-gray-700">
                                                    WhatsApp
                                                </label>
                                                <input type="tel"
                                                    name="whatsapp"
                                                    id="whatsapp"
                                                    x-model="formData.whatsapp"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                            </div>

                                            <div>
                                                <label for="wechat" class="block text-sm font-medium text-gray-700">
                                                    WeChat
                                                </label>
                                                <input type="text"
                                                    name="wechat"
                                                    id="wechat"
                                                    x-model="formData.wechat"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                            </div>

                                            <!-- 留言字段 -->
                                            <div>
                                                <label for="message" class="block text-sm font-medium text-gray-700">
                                                    {{ __('messages.message') }} <span
                                                        class="text-gray-400 text-xs">({{ __('messages.optional') }})</span>
                                                </label>
                                                <textarea
                                                    name="message"
                                                    id="message"
                                                    x-model="formData.message"
                                                    rows="3"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                                    placeholder="{{ __('messages.message_placeholder') }}"></textarea>
                                            </div>

                                            <!-- 表单反馈信息 -->
                                            <div x-show="formFeedback"
                                                x-text="formFeedback"
                                                :class="{ 'text-red-600': isFormError, 'text-green-600': !isFormError }"
                                                class="mt-2 text-sm"></div>

                                            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                                <button type="submit"
                                                    :disabled="isSubmitting"
                                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                                                    <span x-show="!isSubmitting">{{ __('messages.submit') }}</span>
                                                    <span x-show="isSubmitting">{{ __('messages.submitting') }}</span>
                                                </button>
                                                <button type="button"
                                                    @click="showInquiryModal = false"
                                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">
                                                    {{ __('messages.cancel') }}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- 删除确认模态框 -->
            <div x-show="itemToRemove !== null"
                x-cloak
                class="fixed inset-0 z-50 overflow-y-auto"
                aria-labelledby="modal-title"
                role="dialog"
                aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                        aria-hidden="true"
                        @click="itemToRemove = null"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div
                        class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    {{ __('messages.delete_confirm') }}
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        {{ __('messages.confirm_delete_message') }}
                                    </p>
                                </div>
                                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                    <button type="button"
                                        @click="removeCartItem(itemToRemove); itemToRemove = null;"
                                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                        {{ __('messages.delete') }}
                                    </button>
                                    <button type="button"
                                        @click="itemToRemove = null"
                                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                                        {{ __('messages.cancel') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 询价成功提示模态框 -->
            <div x-show="showInquirySuccessModal"
                x-cloak
                class="fixed inset-0 z-50 overflow-y-auto"
                aria-labelledby="modal-title"
                role="dialog"
                aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                        aria-hidden="true"
                        @click="showInquirySuccessModal = false; window.location.reload();"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div
                        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full p-6 text-center">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                            <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium leading-6 text-gray-900">{{ __('messages.success') }}</h3>
                        <div class="mt-2 px-7 py-3">
                            <p class="text-sm text-gray-500">{{ __('messages.inquiry_success_message') }}</p>
                        </div>
                        <div class="items-center px-4 py-3">
                            <button @click="showInquirySuccessModal = false; window.location.reload();"
                                class="px-4 py-2 bg-green-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-300">
                                {{ __('messages.ok') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // 将 PHP 翻译对象传递给 JS
        const translations = @json($jsTranslations);

        function showToast(messageKey, type = 'success', replacements = {}) {
            let message = translations[messageKey] || messageKey; // 获取翻译，找不到则显示 key
            // 简单的占位符替换 (如果需要)
            for (const key in replacements) {
                message = message.replace(`:${key}`, replacements[key]);
            }

            const toast = document.createElement('div');
            toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        } text-white z-50 transform transition-all duration-300 translate-y-0`;
            toast.textContent = message;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.classList.add('translate-y-full', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        function shoppingCartData(initialCart, initialTotal, updateUrlTemplate, removeUrlTemplate, csrfToken,
            inquiryStoreUrl) {
            // 确保 initialCart 是对象，并且每个 item 都有 quantity (防御性处理)
            let processedCart = {};
            console.log('初始化购物车数据:', initialCart, initialTotal);

            if (initialCart && typeof initialCart === 'object') {
                Object.entries(initialCart).forEach(([key, value]) => {
                    if (value && typeof value === 'object') {
                        processedCart[key] = {
                            ...value,
                            quantity: parseInt(value.quantity || value.min_order_quantity || 1, 10)
                        };
                    }
                });
            }

            return {
                cartItems: processedCart,
                totalAmount: parseFloat(initialTotal) || 0,
                showInquiryModal: false,
                showInquirySuccessModal: false,
                itemToRemove: null,
                formData: {
                    name: '',
                    country: '',
                    phone: '',
                    whatsapp: '',
                    wechat: '',
                    message: ''
                },
                isSubmitting: false,
                formFeedback: '',
                isFormError: false,
                isLoading: false,

                // 计算属性：总数量
                get totalQuantity() {
                    console.log('计算总数量');
                    let total = 0;
                    for (const itemId in this.cartItems) {
                        const item = this.cartItems[itemId];
                        if (item && typeof item.quantity !== 'undefined') {
                            total += parseInt(item.quantity, 10);
                            console.log(`商品${itemId}数量: ${item.quantity}, 当前总量: ${total}`);
                        }
                    }
                    return total;
                },

                // 计算总金额
                calculateTotalAmount() {
                    console.log('计算总金额开始');
                    let total = 0;
                    for (const itemId in this.cartItems) {
                        const item = this.cartItems[itemId];
                        if (item && item.price && item.quantity) {
                            const price = parseFloat(item.price);
                            const quantity = parseInt(item.quantity, 10);
                            const subtotal = price * quantity;
                            console.log(`商品${itemId}小计: ${quantity} × ¥${price} = ¥${subtotal.toFixed(2)}`);
                            total += subtotal;
                        } else {
                            console.warn('商品数据不完整:', itemId, item);
                        }
                    }
                    console.log('总金额计算结果:', total);
                    this.totalAmount = total;
                    return total;
                },

                init() {
                    console.log('购物车初始化完成');
                    this.isLoading = false;
                    // 确保总金额计算正确
                    this.calculateTotalAmount();

                    // 预加载图片，提高性能
                    if (this.cartItems) {
                        Object.values(this.cartItems).forEach(item => {
                            if (item && item.main_image_url) {
                                // 使用 Image 对象预加载图片
                                const img = new Image();
                                img.src = item.main_image_url;
                            }
                        });
                    }
                },

                validateCartItemQuantity(itemId) {
                    const item = this.cartItems[itemId];
                    if (!item) return;

                    let newQuantity = parseInt(item.quantity, 10);

                    if (isNaN(newQuantity) || newQuantity < item.min_order_quantity) {
                        newQuantity = parseInt(item.min_order_quantity, 10);
                    }
                    // 更新模型前先确保数值正确，避免不必要的API调用
                    if (item.quantity !== newQuantity) {
                        item.quantity = newQuantity;
                        this.updateCartItemQuantity(itemId, newQuantity); // 只有在数量有效改变后才调用更新
                    } else if (String(this.cartItems[itemId].quantity) !== String(newQuantity)) {
                        // 处理输入 "05" 变成 5 这种情况，强制刷新一下显示
                        this.cartItems[itemId].quantity = newQuantity;
                    }
                },

                updateCartItemQuantity(itemId, newQuantity) {
                    console.log('更新购物车商品数量', itemId, newQuantity);
                    const item = this.cartItems[itemId];
                    if (!item) {
                        console.error('商品不存在:', itemId);
                        return;
                    }

                    // 确保数量是有效的整数值，并且不小于最小订购量
                    newQuantity = parseInt(newQuantity, 10) || item.min_order_quantity;
                    newQuantity = Math.max(parseInt(item.min_order_quantity, 10), newQuantity);

                    // 保存原始数量用于可能的回滚
                    const originalQuantity = parseInt(item.quantity, 10);
                    console.log(`商品${itemId}数量变更: ${originalQuantity} -> ${newQuantity}`);

                    // 乐观更新UI
                    this.cartItems[itemId].quantity = newQuantity;
                    this.calculateTotalAmount();

                    // 更新本地购物车计数
                    window.dispatchEvent(new CustomEvent('cart-updated', {
                        detail: {
                            count: this.totalQuantity
                        }
                    }));

                    // 发送请求到服务器
                    fetch(updateUrlTemplate.replace('PLACEHOLDER_ID', itemId), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                quantity: newQuantity
                            })
                        })
                        .then(response => {
                            console.log('服务器响应状态:', response.status);
                            return response.json().then(data => ({
                                status: response.status,
                                ok: response.ok,
                                body: data
                            }));
                        })
                        .then(({
                            status,
                            ok,
                            body
                        }) => {
                            console.log('更新响应:', status, ok, body);
                            if (ok && body.success) {
                                // 更新为服务器返回的实际值
                                this.cartItems[itemId].quantity = parseInt(body.new_quantity || newQuantity, 10);

                                if (body.total_amount) {
                                    this.totalAmount = parseFloat(body.total_amount);
                                } else {
                                    this.calculateTotalAmount();
                                }

                                showToast('cart_updated');
                            } else {
                                // 如果请求失败，回滚更改
                                console.warn('更新失败，回滚数量:', originalQuantity);
                                this.cartItems[itemId].quantity = originalQuantity;
                                this.calculateTotalAmount();

                                window.dispatchEvent(new CustomEvent('cart-updated', {
                                    detail: {
                                        count: this.totalQuantity
                                    }
                                }));

                                showToast(body.message || 'update_failed', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('购物车更新错误:', error);
                            // 如果请求失败，回滚更改
                            this.cartItems[itemId].quantity = originalQuantity;
                            this.calculateTotalAmount();

                            window.dispatchEvent(new CustomEvent('cart-updated', {
                                detail: {
                                    count: this.totalQuantity
                                }
                            }));

                            showToast('error_updating', 'error');
                        });
                },

                removeCartItem(itemId) {
                    console.log('移除购物车商品', itemId);
                    if (!this.cartItems[itemId]) {
                        console.error('要删除的商品不存在:', itemId);
                        return;
                    }

                    // 保存要删除的商品以便回滚
                    const tempItem = {
                        ...this.cartItems[itemId]
                    };

                    // 乐观更新 UI - 创建不包含要删除项的新对象
                    const newCart = {};
                    for (const id in this.cartItems) {
                        if (id !== itemId) {
                            newCart[id] = this.cartItems[id];
                        }
                    }

                    // 替换整个购物车对象，确保UI刷新
                    this.cartItems = newCart;
                    console.log('删除后的购物车:', this.cartItems);

                    // 重新计算总金额
                    this.calculateTotalAmount();

                    // 更新购物车计数
                    window.dispatchEvent(new CustomEvent('cart-updated', {
                        detail: {
                            count: this.totalQuantity
                        }
                    }));

                    // 发送删除请求到服务器
                    fetch(removeUrlTemplate.replace('PLACEHOLDER_ID', itemId), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => {
                            console.log('删除请求状态:', response.status);
                            return response.json().then(data => ({
                                status: response.status,
                                ok: response.ok,
                                body: data
                            }));
                        })
                        .then(({
                            status,
                            ok,
                            body
                        }) => {
                            console.log('删除响应:', status, ok, body);
                            if (ok && body.success) {
                                // 显示成功提示
                                showToast('item_removed');

                                // 如果购物车为空，刷新页面显示空状态
                                if (Object.keys(this.cartItems).length === 0) {
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 500);
                                }
                            } else {
                                // 如果请求失败，回滚更改
                                console.warn('删除失败，回滚购物车');
                                this.cartItems[itemId] = tempItem;
                                this.calculateTotalAmount();

                                window.dispatchEvent(new CustomEvent('cart-updated', {
                                    detail: {
                                        count: this.totalQuantity
                                    }
                                }));

                                showToast(body.message || 'remove_failed', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('移除购物车商品错误:', error);
                            // 如果请求失败，回滚更改
                            this.cartItems[itemId] = tempItem;
                            this.calculateTotalAmount();

                            window.dispatchEvent(new CustomEvent('cart-updated', {
                                detail: {
                                    count: this.totalQuantity
                                }
                            }));

                            showToast('error_removing', 'error');
                        });
                },

                async submitInquiryForm() {
                    if (this.isSubmitting) return;
                    this.isSubmitting = true;
                    this.formFeedback = '';
                    this.isFormError = false;

                    try {
                        const formData = {
                            ...this.formData,
                            items: Object.entries(this.cartItems).map(([id, item]) => ({
                                product_id: parseInt(id),
                                quantity: item.quantity,
                                price: item.price
                            }))
                        };

                        const response = await fetch(inquiryStoreUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(formData)
                        });

                        const data = await response.json();
                        if (data.success) {
                            this.cartItems = {};
                            this.totalAmount = 0;
                            this.formData = {
                                name: '',
                                country: '',
                                phone: '',
                                whatsapp: '',
                                wechat: '',
                                message: ''
                            };

                            this.showInquiryModal = false;
                            this.showInquirySuccessModal = true;

                            window.dispatchEvent(new CustomEvent('cart-updated', {
                                detail: {
                                    count: 0
                                }
                            }));
                        } else {
                            this.formFeedback = data.message || translations['submission_failed'];
                            this.isFormError = true;
                        }
                    } catch (error) {
                        this.formFeedback = translations['error_submitting'];
                        this.isFormError = true;
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            };
        }
    </script>
@endpush
