@extends('layouts.frontend')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-4">{{ __('Welcome to Our Store') }}</h1>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($categories as $category)
                            <div class="bg-white p-6 rounded-lg shadow">
                                <h2 class="text-xl font-semibold mb-2">{{ $category->name }}</h2>
                                <p class="text-gray-600">{{ $category->description }}</p>
                                <div class="mt-4">
                                    <a href="#" class="text-indigo-600 hover:text-indigo-900">
                                        {{ __('View Products') }} &rarr;
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
