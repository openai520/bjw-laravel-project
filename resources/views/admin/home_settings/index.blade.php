@extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">{{ __('admin.home_settings_title') }}</h1>
            {{-- <a href="#" class="btn btn-primary">全局操作按钮</a> --}}
        </div>

        {{-- Flash messages section from app.blade.php is already part of the layout --}}
        {{-- @if(session('success')) ... @endif --}}
        {{-- @if(session('error')) ... @endif --}}

        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('admin.category') }}
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('admin.home_featured_products') }} ({{ __('admin.max_featured_products') }})
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('admin.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($categories as $category)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $category->name_en }}</div>
                                <div class="text-sm text-gray-500">{{ $category->name_zh }} {{-- Assuming name_zh exists, or use name_zh --}}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($category->homeFeaturedProducts->isNotEmpty())
                                    <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                                        @foreach($category->homeFeaturedProducts->sortBy('display_order') as $featuredProduct)
                                            <li>
                                                <span class="font-medium">{{ $featuredProduct->product->name ?? '[产品信息丢失]' }}</span>
                                                ({{ __('admin.display_order') }}: {{ $featuredProduct->display_order }})
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-sm text-gray-400 italic">{{ __('admin.no_featured_products') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.home_settings.edit_featured_products', $category) }}" class="btn btn-secondary">
                                    {{ __('admin.edit_featured_products') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                {{ __('admin.category_not_on_home') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination if needed in the future --}}
        {{-- <div class="mt-4">
            $categories->links()
        </div> --}}
    </div>
</div>
@endsection 