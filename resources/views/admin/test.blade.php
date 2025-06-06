@extends('admin.layouts.app')

@section('page-title', '测试页面')

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <h2 class="text-2xl font-bold text-gray-900 mb-4">测试页面</h2>
    <p class="text-gray-600">如果您能看到这条消息，说明布局文件工作正常。</p>
    <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded">
        <p class="text-green-800">✅ 视图渲染成功</p>
        <p class="text-green-800">✅ CSS样式加载正常</p>
        <p class="text-green-800">✅ 布局结构正确</p>
    </div>
</div>
@endsection 