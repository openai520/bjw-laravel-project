{{-- 假设这是产品卡片的部分视图 --}}
<div class="product-card">
    {{-- 使用占位图，并将真实图片路径放入 data-src --}}
    <img src="{{ asset('images/placeholder.png') }}" {{-- 确保您有一个占位符图片 --}}
        data-src="{{ asset('storage/' . $product->image) }}" {{-- 假设图片存储在 storage/app/public 下，并已链接 --}}
        alt="{{ $product->name }}" {{-- 直接使用产品名称 --}}
        class="product-image lazy-load"> {{-- 添加一个类用于JS选择 --}}

    {{-- ... 其他产品信息 ... --}}
</div>
