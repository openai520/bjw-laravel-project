{{-- 根据文档要求添加 x-data --}}
<nav x-data="{ openSearch: false }" class="bg-white shadow">
    <div class="flex items-center">
        <a href="{{ route('frontend.products.index', ['lang' => app()->getLocale()]) }}"
            class="text-2xl font-bold text-gray-800">
            KALALA
        </a>
    </div>

    {{-- 大屏幕搜索框 --}}
    <form action="{{ route('frontend.products.index', ['lang' => app()->getLocale()]) }}" method="GET"
        class="hidden sm:flex flex-1 max-w-lg">
        <div class="relative w-full">
            <input type="search" name="search" placeholder="{{ __('messages.search_products') }}"
                class="w-full pl-4 pr-10 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
            <button type="submit" class="absolute right-2 top-1/2 transform -translate-y-1/2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </button>
        </div>
    </form>

    {{-- 小屏幕搜索按钮 --}}
    <button type="button" @click.prevent="openSearch = !openSearch"
        class="sm:hidden p-2 text-gray-500 hover:text-gray-600">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
    </button>

    // ... existing code ...
</nav>

{{-- 小屏幕展开搜索框 --}}
<div x-show="openSearch"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 transform -translate-y-2"
    x-transition:enter-end="opacity-100 transform translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 transform translate-y-0"
    x-transition:leave-end="opacity-0 transform -translate-y-2"
    class="relative sm:hidden bg-white shadow-md p-4 z-20">
    <form action="{{ route('frontend.products.index', ['lang' => app()->getLocale()]) }}" method="GET"
        class="relative">
        <input type="search" name="search" placeholder="{{ __('messages.search_products') }}"
            class="w-full pl-4 pr-10 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
        <div class="absolute right-0 top-0 flex items-center">
            <button type="submit" class="p-2 text-gray-500 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </button>
            <button type="button" @click="openSearch = false" class="p-2 text-gray-500 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </form>
</div>
