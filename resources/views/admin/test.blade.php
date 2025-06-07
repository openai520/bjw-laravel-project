@extends('admin.layouts.app')

@section('page-title', '测试页面')

@section('content')
<div class="bg-red-100 border-4 border-red-500 p-8 mb-6" style="background-color: red !important; color: white !important; padding: 2rem !important;">
    <h1 style="font-size: 2rem; font-weight: bold; margin-bottom: 1rem;">🚨 这是强制显示的测试内容 🚨</h1>
    <p style="font-size: 1.2rem;">如果您能看到这个红色框，说明内容渲染正常</p>
</div>

<div class="bg-white shadow rounded-lg p-6 border-2 border-blue-500">
    <h2 class="text-2xl font-bold text-gray-900 mb-4">测试页面</h2>
    <p class="text-gray-600">如果您能看到这条消息，说明布局文件工作正常。</p>
    
    <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded">
        <p class="text-green-800">✅ 视图渲染成功</p>
        <p class="text-green-800">✅ CSS样式加载正常</p>
        <p class="text-green-800">✅ 布局结构正确</p>
    </div>
    
    <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded">
        <p class="text-blue-800">🔧 调试信息：</p>
        <p class="text-blue-800">• 当前时间: {{ date('Y-m-d H:i:s') }}</p>
        <p class="text-blue-800">• Laravel 版本: {{ app()->version() }}</p>
        <p class="text-blue-800">• 用户: {{ auth('admin')->user()->name ?? '未登录' }}</p>
    </div>
</div>

<div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
    <h3 class="text-lg font-semibold text-yellow-800 mb-2">CSS 测试</h3>
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-red-200 p-3 text-center">红色块</div>
        <div class="bg-green-200 p-3 text-center">绿色块</div>
        <div class="bg-blue-200 p-3 text-center">蓝色块</div>
    </div>
</div>
@endsection 