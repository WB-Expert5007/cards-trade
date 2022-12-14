<div class="product-wrap">
    <div class="product text-center">
        <figure class="product-media">
            <a href="{{ route('product.detail', $product->id) }}">
                @if(file_exists(public_path('storage/cards/'.$product->image)) && !empty($product->image))
                <img src="{{ asset('storage/cards/'.$product->image) }}" alt="{{ $product->name }}" width="300" height="338" />
                @else
                <img src="{{ asset('user-assets/images/default-card.png') }}" width="300px" height="300px" alt="{{ $product->title }}" />
                @endif
            </a>
            @auth
            <div class="product-action-horizontal">
                {{-- <a href="#" class="btn-product-icon btn-cart w-icon-cart" data-id="{{ $product->id }}" title="Add to cart"></a> --}}

                <a href="#" class="btn-product-icon btn-wishlist {{ Helper::checkIfWishlist($product->id)?'added w-icon-heart-full':'w-icon-heart' }}" data-id="{{ $product->id }}" title="Wishlist"></a>

                {{-- <a href="#" class="btn-product-icon btn-compare w-icon-compare"
                    title="Compare"></a> --}}
                {{-- <a href="{{ route('getQuickView', $product->id) }}" class="btn-product-icon btn-quickview w-icon-search"
                    title="Quick View"></a> --}}
            </div>
            @endauth
        </figure>
        <div class="product-details">
            <div class="product-cat">
                <a href="#">{{ Helper::getProductCategory($product->category)->title }}</a>
            </div>
            <h3 class="product-name">
                <a href="{{ route('product.detail', $product->id) }}">{{ $product->name }}</a>
            </h3>
            <div class="product-pa-wrapper">
                <div class="product-price">
                    ${{ $product->value }}
                </div>
            </div>
        </div>
    </div>
</div>
