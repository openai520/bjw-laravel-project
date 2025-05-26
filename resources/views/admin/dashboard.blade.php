@extends('admin.layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
        <div class="p-6 text-gray-900">
            <h1 class="text-2xl font-semibold mb-4">{{ __('admin.dashboard_welcome') }}</h1>
            <p class="text-gray-600">{{ __('admin.dashboard_intro') }}</p>
        </div>
    </div>
    
    <!-- 统计卡片 -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- 未处理询价 -->
        <a href="{{ route('admin.inquiries.index', ['status' => 'pending']) }}" class="bg-amber-100 rounded-lg shadow-md p-5 hover:shadow-lg transition-shadow">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-amber-800 text-sm font-medium">{{ __('admin.pending_inquiries') }}</p>
                    <p class="text-3xl font-bold text-amber-900">{{ $pendingInquiries }}</p>
                </div>
                <div class="bg-amber-200 rounded-full p-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l-4-4m4 4l4-4" />
                    </svg>
                </div>
            </div>
        </a>
        
        <!-- 已处理询价 -->
        <a href="{{ route('admin.inquiries.index', ['status' => 'processed']) }}" class="bg-emerald-100 rounded-lg shadow-md p-5 hover:shadow-lg transition-shadow">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-emerald-800 text-sm font-medium">{{ __('admin.processed_inquiries') }}</p>
                    <p class="text-3xl font-bold text-emerald-900">{{ $processedInquiries }}</p>
                </div>
                <div class="bg-emerald-200 rounded-full p-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </a>
        
        <!-- 产品总数 -->
        <a href="{{ route('admin.products.index') }}" class="bg-blue-100 rounded-lg shadow-md p-5 hover:shadow-lg transition-shadow">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-blue-800 text-sm font-medium">{{ __('admin.total_products') }}</p>
                    <p class="text-3xl font-bold text-blue-900">{{ $totalProducts }}</p>
                </div>
                <div class="bg-blue-200 rounded-full p-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                    </svg>
                </div>
            </div>
        </a>
        
        <!-- 分类总数 -->
        <a href="{{ route('admin.categories.index') }}" class="bg-purple-100 rounded-lg shadow-md p-5 hover:shadow-lg transition-shadow">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-purple-800 text-sm font-medium">{{ __('admin.total_categories') }}</p>
                    <p class="text-3xl font-bold text-purple-900">{{ $totalCategories }}</p>
                </div>
                <div class="bg-purple-200 rounded-full p-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                </div>
            </div>
        </a>
    </div>
    
    <!-- 最新询价 -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-800">{{ __('admin.dashboard_latest_inquiries') }}</h2>
            <a href="{{ route('admin.inquiries.index') }}" class="text-sm text-blue-600 hover:text-blue-800">{{ __('admin.view_all') }}</a>
        </div>
        
        <div class="p-4">
            @forelse ($latestInquiries as $inquiry)
                <div class="border-b last:border-0 py-3 flex flex-col sm:flex-row sm:justify-between sm:items-center">
                    <div class="mb-2 sm:mb-0">
                        <h3 class="font-medium">{{ $inquiry->name }}</h3>
                        <div class="text-sm text-gray-600 flex flex-col sm:flex-row sm:space-x-4">
                            <span>{{ $inquiry->country }}</span>
                            <span>{{ $inquiry->created_at->diffForHumans() }}</span>
                            @if($inquiry->status === 'pending')
                                <span class="text-amber-600 font-medium">{{ __('admin.status_pending') }}</span>
                            @elseif($inquiry->status === 'processed')
                                <span class="text-green-600 font-medium">{{ __('admin.status_processed') }}</span>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('admin.inquiries.show', $inquiry) }}" class="bg-blue-500 hover:bg-blue-600 text-white text-sm px-4 py-1 rounded-md inline-block mt-2 sm:mt-0">
                        {{ __('admin.view_details') }}
                    </a>
                </div>
            @empty
                <div class="py-8 text-center text-gray-500">
                    <p>{{ __('admin.no_latest_inquiries') }}</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Top Countries by Visits -->
    <div class="mt-8 bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b">
            <h2 class="text-lg font-semibold text-gray-800">{{ __('admin.dashboard_top_countries_by_visits') }}</h2>
        </div>
        <div class="p-4">
            @if($topCountriesByVisits && $topCountriesByVisits->count() > 0)
                <ul class="divide-y divide-gray-200">
                    @foreach($topCountriesByVisits as $index => $countryData)
                        <li class="py-3 flex justify-between items-center">
                            <span class="text-gray-700">
                                <span class="font-medium mr-2">{{ $index + 1 }}.</span>
                                {{ $countryData->country ?? __('admin.unknown_country') }} 
                            </span>
                            <span class="text-gray-600 font-semibold">{{ $countryData->total_visits }} {{ __('admin.visit_count_short') }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="py-8 text-center text-gray-500">
                    <p>{{ __('admin.no_country_data_available') }}</p>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection 