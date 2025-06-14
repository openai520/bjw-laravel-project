@extends('admin.layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-semibold text-gray-900">
                    为分类 "{{ $category->name_en }}" 编辑首页推荐产品
                </h1>
                <a href="{{ route('admin.home_settings.index') }}" class="text-sm text-gray-600 hover:text-gray-800">&larr;
                    返回列表</a>
            </div>

            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <strong class="font-bold">发生错误!</strong>
                    <ul class="mt-1 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.home_settings.update_featured_products', $category) }}" method="POST"
                class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                @csrf
                @method('PUT')

                <p class="text-sm text-gray-600 mb-4">请选择最多5个产品，并为它们指定1-5的显示顺序。顺序数字不可重复。</p>

                <div id="featured-products-container" class="space-y-4">
                    @php
                        $currentFeatured = $category->homeFeaturedProducts->keyBy('display_order');
                    @endphp

                    @for ($i = 1; $i <= 5; $i++)
                        <div class="p-4 border rounded-md product-slot" data-slot-id="{{ $i }}">
                            <h3 class="text-lg font-medium mb-2">推荐位 {{ $i }}</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
                                <div>
                                    <label for="product_search_{{ $i }}"
                                        class="block text-sm font-medium text-gray-700">搜索产品</label>
                                    <input type="text" id="product_search_{{ $i }}"
                                        class="mt-1 mb-2 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md product-search-input"
                                        placeholder="输入产品名称搜索...">

                                    <label for="product_id_{{ $i }}"
                                        class="block text-sm font-medium text-gray-700 sr-only">选择产品</label>
                                    <select name="products[{{ $i - 1 }}][product_id]"
                                        id="product_id_{{ $i }}"
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md product-selector"
                                        size="5">
                                        <option value="">-- 不选择产品 --</option>
                                        @foreach ($allProducts as $product)
                                            <option value="{{ $product->id }}"
                                                {{ ($currentFeatured[$i]->product_id ?? null) == $product->id ? 'selected' : '' }}
                                                data-product-name="{{ $product->name }}">
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="display_order_{{ $i }}"
                                        class="block text-sm font-medium text-gray-700">显示顺序 (1-5)</label>
                                    <input type="number" name="products[{{ $i - 1 }}][display_order]"
                                        id="display_order_{{ $i }}"
                                        value="{{ $currentFeatured[$i]->display_order ?? $i }}"
                                        min="1" max="5"
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md display-order-input"
                                        required>
                                </div>
                            </div>
                        </div>
                    @endfor
                </div>

                <div class="mt-6">
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        保存推荐设置
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const productSlots = document.querySelectorAll('.product-slot');
                const form = document.querySelector('form');

                productSlots.forEach(slot => {
                    const searchInput = slot.querySelector('.product-search-input');
                    const productSelector = slot.querySelector('.product-selector');
                    const options = productSelector.options; // Direct reference to live HTMLCollection

                    searchInput.addEventListener('input', function() {
                        const searchTerm = this.value.toLowerCase().trim();

                        for (let i = 0; i < options.length; i++) {
                            const option = options[i];
                            // Ensure to use the actual text content for searching, not a potentially stale data attribute
                            const optionText = option.text.toLowerCase();
                            const optionValue = option.value;

                            // Show option if it's the placeholder (empty value) or if its text includes the search term
                            if (optionValue === "" || optionText.includes(searchTerm)) {
                                option.style.display = '';
                            } else {
                                option.style.display = 'none';
                            }
                        }

                        // After filtering, if the currently selected option is now hidden,
                        // attempt to select the first visible option.
                        // This often defaults to the placeholder ("-- 不选择产品 --") if no other products match.
                        if (productSelector.selectedIndex !== -1 && productSelector.options[
                                productSelector.selectedIndex].style.display === 'none') {
                            let firstVisibleIndex = -1;
                            for (let i = 0; i < options.length; i++) {
                                if (options[i].style.display !== 'none') {
                                    firstVisibleIndex = i;
                                    break;
                                }
                            }

                            if (firstVisibleIndex !== -1) {
                                productSelector.selectedIndex = firstVisibleIndex;
                            } else {
                                // This case should be rare if the placeholder option (value="") is always visible.
                                // As a fallback, clear the selection if absolutely no options are visible.
                                productSelector.value = "";
                            }
                        }
                    });
                });

                form.addEventListener('submit', function(event) {
                    let selectedProducts = [];
                    let displayOrders = [];
                    let hasError = false;

                    productSlots.forEach(slot => {
                        const productId = slot.querySelector('.product-selector').value;
                        const displayOrder = slot.querySelector('.display-order-input').value;

                        if (productId) {
                            if (selectedProducts.includes(productId)) {
                                alert('错误：产品 "' + slot.querySelector('.product-selector option:checked')
                                    .dataset.productName + '" 被重复选择。');
                                hasError = true;
                            }
                            selectedProducts.push(productId);

                            if (displayOrders.includes(displayOrder)) {
                                alert('错误：显示顺序 "' + displayOrder + '" 被重复使用。');
                                hasError = true;
                            }
                            displayOrders.push(displayOrder);

                            if (parseInt(displayOrder) < 1 || parseInt(displayOrder) > 5) {
                                alert('错误：显示顺序 "' + displayOrder + '" 必须在1到5之间。');
                                hasError = true;
                            }
                        }
                    });

                    if (hasError) {
                        event.preventDefault();
                    }
                });
            });
        </script>
    @endpush

@endsection
