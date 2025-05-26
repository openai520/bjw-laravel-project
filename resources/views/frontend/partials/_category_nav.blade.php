@if(isset($categories) && $categories->count())
    <div class="mx-auto px-4 py-2" style="width: 1564px; max-width: 100%;">
        <div class="flex gap-2 sm:gap-3 overflow-x-auto py-1 no-scrollbar">
            <a href="{{ route('frontend.home', ['lang' => app()->getLocale()]) }}"
               class="inline-block px-4 py-1.5 rounded-full text-sm font-semibold transition-all duration-200 shadow-sm hover:shadow-md whitespace-nowrap {{ !request()->has('category') ? 'bg-black text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-100 hover:border-gray-300' }}">
                {{ __('All') }}
            </a>
            @foreach($categories as $category)
                <a href="{{ route('frontend.products.index', ['lang' => app()->getLocale(), 'category' => $category->slug]) }}"
                   class="inline-block px-4 py-1.5 rounded-full text-sm font-semibold transition-all duration-200 shadow-sm hover:shadow-md whitespace-nowrap {{ request('category') === $category->slug ? 'bg-black text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-100 hover:border-gray-300' }}">
                    {{ app()->getLocale() === 'fr' ? $category->name_fr : $category->name_en }}
                </a>
            @endforeach
        </div>
    </div>
@endif