<nav class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-40" x-data="{ openSearch: false }">
    <div class="mx-auto px-4 sm:px-6 lg:px-8" style="width: 1564px; max-width: 100%;">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="{{ route('frontend.home', ['lang' => app()->getLocale()]) }}" class="text-2xl font-bold text-neutral-700">
                    KALALA
                </a>
            </div>

            <!-- 中间: 搜索框 (大屏幕) -->
            <div class="hidden sm:flex sm:items-center sm:ml-6 flex-grow">
                <!-- 搜索框 -->
                <div class="ml-auto flex-grow max-w-xs lg:max-w-sm">
                    <form action="{{ route('frontend.products.index', ['lang' => app()->getLocale()]) }}"
                          method="GET"
                          class="relative"
                          x-data="{ searchTerm: '{{ request('search', '') }}' }">
                        <input type="search"
                               name="search"
                               x-model="searchTerm"
                               placeholder="{{ __('messages.search_products') }}"
                               class="block w-full rounded-md border-0 py-1.5 pl-3 pr-12 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">

                        <!-- 清除按钮 - 小屏幕隐藏 -->
                        <button type="button"
                                x-show="searchTerm"
                                @click="searchTerm = ''; $refs.searchForm.submit();"
                                class="absolute inset-y-0 right-8 hidden sm:flex items-center pr-2">
                            <svg class="h-4 w-4 text-gray-400 hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>

                        <!-- 搜索按钮 - 始终显示 -->
                        <button type="submit"
                                class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="h-5 w-5 text-gray-400 hover:text-gray-500"
                                 xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 20 20"
                                 fill="currentColor"
                                 aria-hidden="true">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>

            <!-- 右侧工具栏 -->
            <div class="flex items-center space-x-4">
                <!-- 搜索图标 (小屏幕) -->
                <div class="flex items-center sm:hidden">
                    <button type="button" @click="openSearch = true" class="p-2 text-gray-600 hover:text-gray-900">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </div>

                <!-- 语言切换器 -->
                <div class="flex items-center bg-gray-200 rounded-full p-0.5">
                    @php $currentLang = app()->getLocale(); @endphp
                        <a href="{{ route('language.switch', ['lang' => 'en']) }}"
                       class="px-3 py-1 text-sm font-medium rounded-full transition-colors duration-150 ease-in-out
                              {{ $currentLang == 'en' ? 'bg-blue-600 text-white shadow-md' : 'text-gray-800 hover:bg-gray-300' }}">
                        EN
                    </a>
                        <a href="{{ route('language.switch', ['lang' => 'fr']) }}"
                       class="px-3 py-1 text-sm font-medium rounded-full transition-colors duration-150 ease-in-out
                              {{ $currentLang == 'fr' ? 'bg-blue-600 text-white shadow-md' : 'text-gray-800 hover:bg-gray-300' }}">
                        FR
                    </a>
                </div>

                <!-- 购物车图标 -->
                <a href="{{ route('frontend.cart.index', ['lang' => app()->getLocale()]) }}"
                   class="relative p-2 rounded-full text-gray-600 hover:text-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 bg-blue-100 hover:bg-blue-200 transition duration-150 ease-in-out">
                    <span class="sr-only">{{ __('messages.shopping_cart') }}</span>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span x-data="{ cartCount: {{ session('cart') ? count(session('cart')) : 0 }} }"
                          x-init="window.addEventListener('cart-updated', event => cartCount = event.detail.count)"
                          x-show="cartCount > 0"
                          class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full"
                          x-text="cartCount"
                          style="display: none;"></span>
                </a>
            </div>
        </div>
    </div>

    <!-- 小屏幕展开的搜索框 -->
    <div x-show="openSearch"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-1"
         class="absolute top-full left-0 w-full bg-white p-4 shadow-md sm:hidden">
        <form action="{{ route('frontend.products.index', ['lang' => app()->getLocale()]) }}" method="GET" class="relative">
            <input type="search" name="search" value="{{ request('search') }}"
                   placeholder="{{ __('messages.search_products') }}"
                   class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            <button type="submit" class="absolute inset-y-0 right-0 flex items-center pr-3">
                <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                </svg>
            </button>
        </form>
        <button @click="openSearch = false" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</nav>

@push('styles')
<style>
    /* 隐藏 Webkit (Chrome, Safari, Edge) 浏览器的搜索框清除按钮 */
    input[type="search"]::-webkit-search-cancel-button,
    input[type="search"]::-webkit-search-clear-button {
        display: none;
        -webkit-appearance: none;
        appearance: none;
    }
    /* 隐藏 IE/Edge 的清除按钮 */
    input[type="search"]::-ms-clear {
        display: none;
        width: 0;
        height: 0;
    }
    /* 隐藏 Firefox 的清除按钮 (可能需要针对特定版本) */
    input[type="search"]::-moz-clear { /* Older Firefox */
        display: none;
    }
     /* 对于 type="search" 可能还需要 */
    input[type="search"]::-webkit-search-decoration,
    input[type="search"]::-webkit-search-results-button,
    input[type="search"]::-webkit-search-results-decoration {
        display: none;
    }
</style>
@endpush