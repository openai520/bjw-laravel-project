@extends('frontend.layouts.app')

@section('title')
    @if($currentCategory)
        {{ app()->getLocale() === 'fr' ? $currentCategory->name_fr : $currentCategory->name_en }} - {{ __('Products') }}
    @elseif($searchTerm)
        {{ __('Search Results') }}: {{ $searchTerm }} - {{ __('Products') }}
    @else
        {{ __('All Products') }}
    @endif
@endsection

@section('styles')
    <!-- 样式已整合到app.css，无需额外引用 -->
@endsection

@section('content')
    <main>
        @include('frontend.partials._category_nav')

        <div class="w-full px-4 py-6"
             x-data="infiniteScrollHandler('{{ $products->nextPageUrl() }}', '{{ $currentCategory ? $currentCategory->slug : '' }}')">
            <!-- 产品网格容器 - 响应式优化 -->
            <div id="product-list-grid" class="grid gap-4" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
                @forelse($products as $product)
                    @include('frontend.partials._product_card', ['product' => $product])
                @empty
                    <div class="col-span-full text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('No products found') }}</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            @if($searchTerm)
                                {{ __('Try adjusting your search terms or filters.') }}
                            @else
                                {{ __('Products will be added soon.') }}
                            @endif
                        </p>
                    </div>
                @endforelse
            </div>

            <!-- 加载更多指示器 - 只有在有下一页且不是正在加载时才显示 -->
            <div class="loading-indicator"
                 x-show="!noMorePages && !loading && nextPageUrl"
                 class="flex justify-center items-center py-8">
                <div class="text-gray-500 text-sm">{{ __('Scroll to load more products...') }}</div>
            </div>
            
            <!-- 正在加载指示器 -->
            <div x-show="loading" class="flex justify-center items-center py-8">
                <svg class="animate-spin h-5 w-5 text-blue-600 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-gray-600 font-medium">{{ __('Loading more products...') }}</span>
            </div>
            
            <!-- 没有更多产品指示器 -->
            <div x-show="noMorePages" class="flex justify-center items-center py-8">
                <div class="text-gray-500 text-sm">{{ __('No more products to load.') }}</div>
            </div>
            
            <!-- 骨架屏占位符（在没有更多内容时显示） -->
            <div x-show="loading" class="grid gap-4 mt-4" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
                <template x-for="i in 8">
                    <div class="skeleton h-[350px] w-full"></div>
                </template>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('infiniteScrollHandler', (initialNextPageUrl, categorySlug = '') => ({
            nextPageUrl: initialNextPageUrl,
            loading: false,
            noMorePages: false,
            observer: null,
            // 节流相关属性
            lastRequestTime: 0,
            throttleDelay: 300, // 300ms节流延迟

            init() {
                if (!this.nextPageUrl) {
                    this.noMorePages = true;
                    console.log("InfiniteScroll: Initial nextPageUrl is empty. No more pages from the start.");
                    return; // 如果没有下一页，不需要设置滚动监听
                }
                this.setupInfiniteScroll();
            },

            setupInfiniteScroll() {
                const options = {
                    root: null,
                    rootMargin: '100px',
                    threshold: 0.1
                };

                this.observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting && !this.loading && !this.noMorePages && this.nextPageUrl) {
                            console.log("Loading indicator is intersecting! Triggering loadMore");
                            this.loadMore();
                        }
                    });
                }, options);

                const loadingIndicator = this.$el.querySelector('.loading-indicator');
                console.log("this.$el for infinite scroll:", this.$el);
                console.log("Found loading indicator for infinite scroll:", loadingIndicator);

                if (loadingIndicator) {
                    this.observer.observe(loadingIndicator);
                    console.log("Observer is now observing the loading indicator.");
                } else {
                    console.error("Failed to find .loading-indicator to observe for infinite scroll.");
                }
            },

            loadMore() {
                console.log("loadMore called. Current nextPageUrl:", this.nextPageUrl, "Loading:", this.loading, "NoMorePages:", this.noMorePages);
                
                // 检查节流条件
                const currentTime = Date.now();
                if (currentTime - this.lastRequestTime < this.throttleDelay) {
                    console.log("loadMore: Request throttled. Last request was", currentTime - this.lastRequestTime, "ms ago");
                    return;
                }
                
                if (this.loading || this.noMorePages || !this.nextPageUrl) {
                    console.log("loadMore returned early. Loading:", this.loading, "NoMorePages:", this.noMorePages, "Has nextPageUrl:", !!this.nextPageUrl);
                    if (!this.nextPageUrl && !this.noMorePages) { // Ensure noMorePages is set if URL is missing
                        this.noMorePages = true;
                    }
                    return;
                }

                // 更新最后请求时间
                this.lastRequestTime = currentTime;
                this.loading = true;
                console.log("loadMore: Set loading to true. Fetching URL:", this.nextPageUrl);

                fetch(this.nextPageUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => {
                        console.log("loadMore: Fetch response received. Status:", response.status);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log("loadMore: JSON data received:", data);
                        
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = data.html;
                        const newProducts = tempDiv.querySelectorAll('.product-card');
                        console.log("loadMore: Found new products in data.html:", newProducts.length);
                        
                        const container = document.getElementById('product-list-grid');
                        if (!container) {
                            console.error("loadMore: Could not find product-list-grid container!");
                            this.loading = false;
                            return;
                        }

                        newProducts.forEach(product => {
                            container.appendChild(product.cloneNode(true));
                        });
                        console.log("loadMore: Appended new products to container.");

                        this.nextPageUrl = data.next_page_url ? data.next_page_url : null;
                        console.log("loadMore: Extracted next_page_url from JSON data:", this.nextPageUrl);

                        if (!this.nextPageUrl) {
                            console.log("loadMore: No next page URL in JSON data. Stopping infinite scroll.");
                            this.noMorePages = true;
                        }
                        console.log("loadMore: Next page URL set to:", this.nextPageUrl);

                        this.loading = false;
                        console.log("loadMore: Set loading to false (success).");
                    })
                    .catch(error => {
                        console.error('loadMore: Error loading more products:', error);
                        this.loading = false;
                        console.log("loadMore: Set loading to false (error).");
                        this.noMorePages = true; 
                    });
            }
        }));
    });
</script>
@endpush
