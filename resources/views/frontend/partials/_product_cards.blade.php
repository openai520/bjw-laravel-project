@foreach($products as $product)
    @include('frontend.partials._product_card', ['product' => $product])
@endforeach 