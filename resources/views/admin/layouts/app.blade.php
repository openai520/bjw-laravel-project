<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - {{ __('admin.admin_panel') }}</title>

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- FilePond CSS -->
    <link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
    <link href="https://unpkg.com/filepond-plugin-image-preview@^4/dist/filepond-plugin-image-preview.css" rel="stylesheet" />
    <link href="https://unpkg.com/filepond-plugin-image-edit@^1/dist/filepond-plugin-image-edit.css" rel="stylesheet" />
    
    <!-- Alpine.js for dynamic interactions - TEMPORARILY DISABLED -->
    <!-- <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script> -->
</head>
<body class="font-sans antialiased bg-gray-50" data-sidebar-open="false">
    <!-- 桌面端侧边栏 -->
    <div class="hidden lg:flex lg:w-64 lg:flex-col lg:fixed lg:inset-y-0">
        <div class="flex-1 flex flex-col min-h-0 bg-white shadow-lg border-r border-gray-200">
            <!-- 侧边栏头部 -->
            <div class="flex items-center h-16 px-6 bg-blue-600 text-white">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <span class="text-lg font-bold">管理后台</span>
                </div>
            </div>

            <!-- 导航菜单 -->
            <nav class="mt-8 px-4 space-y-1 flex-1">
                <a href="{{ route('admin.dashboard') }}"
                   class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-150 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-700 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0 {{ request()->routeIs('admin.dashboard') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    仪表盘
                </a>

                <a href="{{ route('admin.categories.index') }}"
                   class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-150 {{ request()->routeIs('admin.categories.*') ? 'bg-blue-50 text-blue-700 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0 {{ request()->routeIs('admin.categories.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    分类管理
                </a>

                <a href="{{ route('admin.products.index') }}"
                   class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-150 {{ request()->routeIs('admin.products.*') ? 'bg-blue-50 text-blue-700 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0 {{ request()->routeIs('admin.products.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    产品管理
                </a>

                <a href="{{ route('admin.inquiries.index') }}"
                   class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-150 {{ request()->routeIs('admin.inquiries.*') ? 'bg-blue-50 text-blue-700 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0 {{ request()->routeIs('admin.inquiries.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                    </svg>
                    询价管理
                </a>

                <a href="{{ route('admin.product_analytics.index') }}"
                   class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-150 {{ request()->routeIs('admin.product_analytics.*') ? 'bg-blue-50 text-blue-700 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0 {{ request()->routeIs('admin.product_analytics.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    访问统计
                </a>

                <a href="{{ route('admin.ip_addresses.index') }}"
                   class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-150 {{ request()->routeIs('admin.ip_addresses.*') ? 'bg-blue-50 text-blue-700 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0 {{ request()->routeIs('admin.ip_addresses.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    IP地址管理
                </a>

                <a href="{{ route('admin.home_settings.index') }}"
                   class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-150 {{ request()->routeIs('admin.home_settings.*') ? 'bg-blue-50 text-blue-700 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0 {{ request()->routeIs('admin.home_settings.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    首页设置
                </a>

                <div class="border-t border-gray-200 pt-4 mt-6">
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="group flex items-center w-full px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-red-50 hover:text-red-700 transition-colors duration-150">
                            <svg class="w-5 h-5 mr-3 flex-shrink-0 text-gray-400 group-hover:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            退出登录
                        </button>
                    </form>
                </div>
            </nav>
        </div>
    </div>

    <!-- 主内容区域 -->
    <div class="lg:pl-64">
        <div class="flex flex-col flex-1 min-w-0">
            <!-- 顶部栏 -->
            <div class="sticky top-0 z-10 flex-shrink-0 flex h-16 bg-white shadow-sm border-b border-gray-200 lg:border-none">
                <!-- 移动端菜单按钮 - DISABLED -->
                <button disabled
                        class="px-4 border-r border-gray-200 text-gray-300 focus:outline-none lg:hidden cursor-not-allowed">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- 页面标题 -->
                <div class="flex-1 px-4 flex justify-between items-center">
                    <div class="flex-1 flex">
                        <div class="w-full flex md:ml-0">
                            <h1 class="text-xl font-semibold text-gray-900">
                                @yield('page-title', '管理后台')
                            </h1>
                        </div>
                    </div>
                    
                    <!-- 用户信息 -->
                    <div class="ml-4 flex items-center md:ml-6">
                        <div class="flex items-center space-x-3">
                            <span class="text-sm text-gray-600">
                                欢迎，{{ auth()->user()->name ?? '管理员' }}
                            </span>
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 主要内容 -->
            <main class="flex-1 relative overflow-y-auto focus:outline-none bg-gray-50" style="min-height: 500px; background-color: #f9fafb !important;">
                <div class="py-6" style="min-height: 400px;">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" style="min-height: 300px; background-color: rgba(255,0,0,0.1);">
                        <!-- Flash 消息 -->
                        @if(session('success'))
                            <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4" role="alert">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 20 20" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4" role="alert">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 20 20" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- 页面内容 -->
                        <div class="space-y-6">
                            @yield('content')
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- FilePond JS -->
    <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview@^4/dist/filepond-plugin-image-preview.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-resize@^2/dist/filepond-plugin-image-resize.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-transform@^3/dist/filepond-plugin-image-transform.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-type@^1/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size@^2/dist/filepond-plugin-file-validate-size.js"></script>
    
    @stack('scripts')
    @yield('scripts')
</body>
</html>