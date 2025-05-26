@extends('admin.layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">创建分类</h1>
        <a href="{{ route('admin.categories.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded">
            返回列表
        </a>
    </div>

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.categories.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="name_en" class="block text-sm font-medium text-gray-700 mb-2">英文名称</label>
                <input type="text" name="name_en" id="name_en" value="{{ old('name_en') }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    required>
            </div>

            <div class="mb-4">
                <label for="name_fr" class="block text-sm font-medium text-gray-700 mb-2">法文名称</label>
                <input type="text" name="name_fr" id="name_fr" value="{{ old('name_fr') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    required>
            </div>

            <div class="mb-4">
                <label for="show_on_home" class="block text-sm font-medium text-gray-700 mb-2">在首页显示</label>
                <input type="checkbox" name="show_on_home" id="show_on_home" value="1" {{ old('show_on_home') ? 'checked' : '' }}
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            </div>

            <div class="mb-4">
                <label for="display_order" class="block text-sm font-medium text-gray-700 mb-2">首页显示顺序</label>
                <input type="number" name="display_order" id="display_order" value="{{ old('display_order', 999) }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="例如: 1, 2, 3... 值越小越靠前">
                <p class="text-xs text-gray-500 mt-1">用于控制分类在首页的显示顺序，值越小越靠前。默认为999（排在最后）。</p>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">
                    创建分类
                </button>
            </div>
        </form>
    </div>
</div>
@endsection 