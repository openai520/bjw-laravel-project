@extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">编辑分类</h1>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                返回列表
            </a>
        </div>

        @if ($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white shadow rounded-lg">
            <form action="{{ route('admin.categories.update', $category) }}" method="POST" class="p-6">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label for="name_en" class="block text-sm font-medium text-gray-700">英文名称</label>
                    <input type="text" name="name_en" id="name_en" value="{{ old('name_en', $category->name_en) }}"
                           class="input">
                </div>

                <div class="mb-4">
                    <label for="name_fr" class="block text-sm font-medium text-gray-700">法文名称</label>
                    <input type="text" name="name_fr" id="name_fr" value="{{ old('name_fr', $category->name_fr) }}"
                           class="input">
                </div>

                <div class="mb-4">
                    <label for="show_on_home" class="block text-sm font-medium text-gray-700">在首页显示</label>
                    <input type="checkbox" name="show_on_home" id="show_on_home" value="1" {{ old('show_on_home', $category->show_on_home) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <div class="mb-4">
                    <label for="display_order" class="block text-sm font-medium text-gray-700">首页显示顺序</label>
                    <input type="number" name="display_order" id="display_order" value="{{ old('display_order', $category->display_order) }}" 
                           class="input"
                           placeholder="例如: 1, 2, 3... 值越小越靠前">
                    <p class="text-xs text-gray-500 mt-1">用于控制分类在首页的显示顺序，值越小越靠前。</p>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="submit" class="btn btn-primary">
                        更新分类
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 