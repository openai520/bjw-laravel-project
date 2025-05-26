<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-200 min-h-screen flex flex-col">
    <!-- 导航栏 -->
    @include('frontend.partials._navbar')

    <!-- 主要内容区域 -->
    <main class="flex-grow w-full">
        @yield('content')
    </main>

    <!-- 页脚 -->
    {{-- @include('frontend.partials._footer') --}}

    <!-- 全局购物车图标 -->
    <div id="cart-icon"
         class="fixed bottom-4 right-4 bg-white rounded-full shadow-lg p-4 cursor-pointer z-50 transition-transform hover:scale-105"
         x-data="{ itemCount: 0 }"
         x-show="itemCount > 0"
         x-transition>
        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-6 w-6 flex items-center justify-center"
              x-text="itemCount"></span>
        <!-- 购物车图标和数量将通过 JS 更新 -->
    </div>

    {{-- Toast Notification - REVERTED --}}
    {{-- <div id="toast-notification" ...> ... </div> --}}

    {{-- Language Guide Modal - REVERTED --}}
    {{-- <div id="language-guide-modal" ...> ... </div> --}}

    <!-- 产品详情模态框 -->
    @include('frontend.partials._product_modal')

    @stack('scripts')

    <!-- 产品模态框脚本 -->
    @include('frontend.partials._product_modal_script')

{{-- Script for Toast & Modal - REVERTED --}}
{{-- <script>
document.addEventListener('DOMContentLoaded', function() {
    // ... toast logic ...
    // ... modal logic ...
});
</script> --}}

</body>
</html>