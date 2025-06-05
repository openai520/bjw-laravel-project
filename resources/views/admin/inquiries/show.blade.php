@extends('admin.layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold mb-4">询价详情 #{{ $inquiry->id }}</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <h3 class="text-lg font-semibold mb-3">客户信息</h3>
                <p><strong>姓名:</strong> {{ $inquiry->name }}</p>
                <p><strong>国家:</strong> {{ $inquiry->country }}</p>
                <p><strong>电话:</strong> {{ $inquiry->phone ?? '未提供' }}</p>
                @if($inquiry->email)
                <p><strong>邮箱:</strong> {{ $inquiry->email }}</p>
                @endif
                @if($inquiry->whatsapp)
                <p><strong>WhatsApp:</strong> {{ $inquiry->whatsapp }}</p>
                @endif
                @if($inquiry->wechat)
                <p><strong>微信:</strong> {{ $inquiry->wechat }}</p>
                @endif
                @if($inquiry->message)
                <p><strong>留言:</strong> {{ $inquiry->message }}</p>
                @endif
                <p><strong>提交时间:</strong> {{ $inquiry->created_at->format('Y-m-d H:i:s') }}</p>
            </div>
            
            <div>
                <h3 class="text-lg font-semibold mb-3">状态管理</h3>
                <p><strong>当前状态:</strong> 
                    @if($inquiry->status == 'pending')
                        <span class="text-yellow-600">待处理</span>
                    @elseif($inquiry->status == 'processed')
                        <span class="text-green-600">已处理</span>
                    @else
                        {{ $inquiry->status }}
                    @endif
                </p>
                
                <form action="{{ route('admin.inquiries.updateStatus', $inquiry) }}" method="POST" class="mt-3">
                    @csrf
                    @method('PATCH')
                    <select name="status" class="border rounded px-3 py-2 mr-2">
                        <option value="pending" {{ $inquiry->status == 'pending' ? 'selected' : '' }}>待处理</option>
                        <option value="processed" {{ $inquiry->status == 'processed' ? 'selected' : '' }}>已处理</option>
                    </select>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">更新状态</button>
                </form>
            </div>
        </div>
        
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-3">商品列表</h3>
            @if($inquiry->items && $inquiry->items->count() > 0)
                <div class="space-y-3">
                @foreach($inquiry->items as $item)
                    <div class="border rounded p-3">
                        <p><strong>商品:</strong> {{ $item->product ? $item->product->name : '商品已删除' }}</p>
                        <p><strong>数量:</strong> {{ $item->quantity }}</p>
                        <p><strong>价格:</strong> ${{ number_format($item->price ?? 0, 2) }}</p>
                    </div>
                @endforeach
                </div>
            @else
                <p>没有商品信息</p>
            @endif
        </div>
        
        <div class="mt-6">
            <a href="{{ route('admin.inquiries.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded">
                返回列表
            </a>
        </div>
    </div>
</div>
@endsection
