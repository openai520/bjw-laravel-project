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
    <link rel="stylesheet" href="{{ asset('css/masonry.css') }}">
    {{-- <style>
        /* 内联样式确保列数 */
        .masonry-container {
            -webkit-column-count: 4 !important;
               -moz-column-count: 4 !important;
                    column-count: 4 !important;
        }
        
        @media (max-width: 1400px) {
            .masonry-container {
                -webkit-column-count: 3 !important;
                   -moz-column-count: 3 !important;
                        column-count: 3 !important;
            }
        }
        
        @media (max-width: 1024px) {
            .masonry-container {
                -webkit-column-count: 2 !important;
                   -moz-column-count: 2 !important;
                        column-count: 2 !important;
            }
        }
        
        @media (max-width: 640px) {
            .masonry-container {
                -webkit-column-count: 1 !important;
                   -moz-column-count: 1 !important;
                        column-count: 1 !important;
            }
        }
        
        /* 强化图片样式 */
        .product-image {
            height: 250px !important;
            object-fit: cover !important;
        }
    </style> --}}
@endsection

@section('content')
    <main>
        @include('frontend.partials._category_nav')

        <div class="w-full px-4 py-6"
             x-data="infiniteScrollHandler('{{ $products->nextPageUrl() }}', '{{ $currentCategory ? $currentCategory->slug : '' }}')">
            <!-- 产品网格容器 -->
            <div id="product-list-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 xl:grid-cols-5 gap-4">
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

            <!-- 加载更多指示器 -->
            <div class="loading-indicator"
                 :class="{ 'opacity-100 h-auto py-4': loading, 'opacity-0 h-px overflow-hidden pointer-events-none': !loading }"
                 style="transition: opacity 0.3s, height 0.3s;">
                <div class="flex justify-center items-center">
                    <svg class="animate-spin h-5 w-5 text-primary mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ __('Loading more products...') }}</span>
                </div>
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

            init() {
                if (!this.nextPageUrl) {
                    this.noMorePages = true;
                    console.log("InfiniteScroll: Initial nextPageUrl is empty. No more pages from the start.");
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
                        if (entry.isIntersecting) {
                            console.log("Loading indicator is intersecting!");
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
                if (this.loading || this.noMorePages || !this.nextPageUrl) {
                    console.log("loadMore returned early. Loading:", this.loading, "NoMorePages:", this.noMorePages, "Has nextPageUrl:", !!this.nextPageUrl);
                    if (!this.nextPageUrl && !this.noMorePages) { // Ensure noMorePages is set if URL is missing
                        this.noMorePages = true;
                    }
                    return;
                }

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
