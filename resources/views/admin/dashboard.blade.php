@extends('admin.layouts.app')

@section('page-title', '仪表盘')

@section('content')
<!-- 欢迎消息 -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-2">欢迎使用后台管理系统</h2>
    <p class="text-gray-600">您可以在这里管理网站的各项内容，包括产品、分类、询价等。</p>
</div>

<!-- 统计卡片 -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <!-- 未处理询价 -->
    <a href="{{ route('admin.inquiries.index', ['status' => 'pending']) }}" 
       class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow group">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">未处理询价</p>
                <p class="text-2xl font-bold text-amber-600">{{ $pendingInquiries }}</p>
            </div>
            <div class="bg-amber-100 rounded-lg p-3 group-hover:bg-amber-200 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l-4-4m4 4l4-4" />
                </svg>
            </div>
        </div>
    </a>
    
    <!-- 已处理询价 -->
    <a href="{{ route('admin.inquiries.index', ['status' => 'processed']) }}" 
       class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow group">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">已处理询价</p>
                <p class="text-2xl font-bold text-emerald-600">{{ $processedInquiries }}</p>
            </div>
            <div class="bg-emerald-100 rounded-lg p-3 group-hover:bg-emerald-200 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </a>
    
    <!-- 产品总数 -->
    <a href="{{ route('admin.products.index') }}" 
       class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow group">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">产品总数</p>
                <p class="text-2xl font-bold text-blue-600">{{ $totalProducts }}</p>
            </div>
            <div class="bg-blue-100 rounded-lg p-3 group-hover:bg-blue-200 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                </svg>
            </div>
        </div>
    </a>
    
    <!-- 分类总数 -->
    <a href="{{ route('admin.categories.index') }}" 
       class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow group">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">分类总数</p>
                <p class="text-2xl font-bold text-purple-600">{{ $totalCategories }}</p>
            </div>
            <div class="bg-purple-100 rounded-lg p-3 group-hover:bg-purple-200 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
            </div>
        </div>
    </a>
</div>

<!-- 最新询价 -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">最新询价</h3>
            <a href="{{ route('admin.inquiries.index') }}" 
               class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                查看全部 →
            </a>
        </div>
    </div>
    
    <div class="p-6">
        @forelse ($latestInquiries as $inquiry)
            <div class="flex items-center justify-between py-4 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $inquiry->name }}</p>
                            <p class="text-sm text-gray-500">{{ $inquiry->country }} • {{ $inquiry->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    @if($inquiry->status === 'pending')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                            待处理
                        </span>
                    @elseif($inquiry->status === 'processed')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            已处理
                        </span>
                    @endif
                    <a href="{{ route('admin.inquiries.show', $inquiry) }}" 
                       class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                        查看详情
                    </a>
                </div>
            </div>
        @empty
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">暂无询价记录</h3>
                <p class="mt-1 text-sm text-gray-500">当有新的询价提交时，会在这里显示。</p>
            </div>
        @endforelse
    </div>
</div>

<!-- 访问量最高的国家/地区 -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">访问量最高的国家/地区</h3>
    </div>
    <div class="p-6">
        @if($topCountriesByVisits && $topCountriesByVisits->count() > 0)
            <div class="space-y-4">
                @foreach($topCountriesByVisits as $index => $countryData)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center justify-center h-8 w-8 rounded-full {{ $index === 0 ? 'bg-yellow-100 text-yellow-800' : ($index === 1 ? 'bg-gray-100 text-gray-800' : ($index === 2 ? 'bg-orange-100 text-orange-800' : 'bg-gray-50 text-gray-600')) }} text-sm font-medium">
                                    {{ $index + 1 }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $countryData->country ?? '未知国家' }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-900">{{ $countryData->total_visits }}</p>
                            <p class="text-xs text-gray-500">次访问</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2-2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">暂无访问数据</h3>
                <p class="mt-1 text-sm text-gray-500">等待用户访问后会显示统计数据。</p>
            </div>
        @endif
    </div>
</div>
@endsection 