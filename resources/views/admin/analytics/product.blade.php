@extends('admin.layouts.app')

@section('page-title', '产品详细统计')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <!-- 面包屑导航 -->
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('admin.product_analytics.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        访问统计
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500">{{ $product->name }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- 产品信息和时间筛选 -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">{{ $product->name }}</h1>
                <p class="mt-1 text-sm text-gray-600">产品ID: {{ $product->id }} | 价格: ${{ number_format($product->price, 2) }}</p>
            </div>
            <div class="flex space-x-2">
                <form method="GET" class="flex items-center space-x-2">
                    <select name="days" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="7" {{ request('days') == 7 ? 'selected' : '' }}>最近7天</option>
                        <option value="30" {{ request('days', 30) == 30 ? 'selected' : '' }}>最近30天</option>
                        <option value="90" {{ request('days') == 90 ? 'selected' : '' }}>最近90天</option>
                    </select>
                    <button type="submit" class="btn btn-primary">筛选</button>
                </form>
            </div>
        </div>

        <!-- 统计卡片 -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">总访问次数</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ number_format($product->view_count) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">今日访问</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ number_format($product->today_view_count) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">平均日访问</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ number_format($product->view_count / max(1, request('days', 30)), 1) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 两列布局 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- 访问趋势图 -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">访问趋势 ({{ request('days', 30) }}天)</h3>
                <div class="space-y-3">
                    @php
                        $maxCount = max(array_column($dailyStats, 'count'));
                        $maxCount = $maxCount > 0 ? $maxCount : 1;
                    @endphp
                    @foreach($dailyStats as $stat)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <span class="text-sm text-gray-600 w-16">{{ $stat['date_cn'] }}</span>
                                <div class="w-40 bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $stat['count'] > 0 ? ($stat['count'] / $maxCount) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <span class="text-sm font-medium text-gray-900">{{ $stat['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- 访问来源分析 -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">访问来源 TOP 10</h3>
                <div class="space-y-3">
                    @forelse($referrerStats as $index => $referrer)
                        @php
                            $refererDisplay = $referrer->referer ?? '直接访问';
                            if ($refererDisplay !== '直接访问') {
                                $parsed = parse_url($refererDisplay);
                                $refererDisplay = $parsed['host'] ?? $refererDisplay;
                            }
                        @endphp
                        <div class="flex items-center justify-between p-3 border-b border-gray-100">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-gray-200 text-gray-600 text-xs font-medium">
                                        {{ $index + 1 }}
                                    </span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $refererDisplay }}
                                    </p>
                                    @if($referrer->referer && $referrer->referer !== '')
                                        <p class="text-xs text-gray-500 truncate">
                                            {{ Str::limit($referrer->referer, 50) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="text-sm font-bold text-blue-600">{{ $referrer->count }}</span>
                                <span class="text-xs text-gray-500">次访问</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">暂无来源数据</h3>
                            <p class="mt-1 text-sm text-gray-500">等待更多访问数据收集。</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- 详细访问记录 -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">详细访问记录</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">访问时间</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP地址</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">来源</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">浏览器</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($views as $view)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $view->viewed_at->format('Y-m-d H:i:s') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $view->ip_address ?? '未知' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($view->referer)
                                        @php
                                            $parsed = parse_url($view->referer);
                                            $domain = $parsed['host'] ?? $view->referer;
                                        @endphp
                                        <span title="{{ $view->referer }}">{{ $domain }}</span>
                                    @else
                                        <span class="text-gray-400">直接访问</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    @if($view->user_agent)
                                        @php
                                            // 简化浏览器信息显示
                                            $userAgent = $view->user_agent;
                                            if (str_contains($userAgent, 'Chrome')) {
                                                $browser = 'Chrome';
                                            } elseif (str_contains($userAgent, 'Firefox')) {
                                                $browser = 'Firefox';
                                            } elseif (str_contains($userAgent, 'Safari')) {
                                                $browser = 'Safari';
                                            } elseif (str_contains($userAgent, 'Edge')) {
                                                $browser = 'Edge';
                                            } else {
                                                $browser = 'Other';
                                            }
                                        @endphp
                                        <span title="{{ $view->user_agent }}">{{ $browser }}</span>
                                    @else
                                        <span class="text-gray-400">未知</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">暂无访问记录</h3>
                                    <p class="mt-1 text-sm text-gray-500">该产品在所选时间段内没有访问记录。</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- 分页 -->
            @if($views->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $views->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 