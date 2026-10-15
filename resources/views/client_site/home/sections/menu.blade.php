@include('client_site.home.styles.collections')
<section id="menu-section" class="menu-section">
    @foreach ($collections as $collection)
        <div class="collection-wrapper">
            <div class="collection-header center-header">
                <h2>{{ $collection->name }}</h2>
                <a href="{{ route('collection.show', $collection->id) }}" class="view-all">
                    View all
                    <span>→</span>
                </a>
            </div>



            <div class="scroll-controls">
                <button class="nav-btn btn-prev" onclick="scrollRow(this, -1)">&#10094;</button>
                <div class="products-row">
                    @foreach ($collection->products as $product)
                        <div class="product-card" data-url="{{ route('product.show', $product->id) }}">
                            <div class="product-image-container">
                               <img src="{{ asset('products/' . $product->image) }}" alt="{{ $product->name }}">
                            </div>

                            <div class="product-info">
                                <h3>{{ $product->name }}</h3>
                                <p class="price">{{ $product->price }} EGP</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                <button class="nav-btn btn-next" onclick="scrollRow(this, 1)">&#10095;</button>
            </div>
        </div>
    @endforeach
</section>

@include('client_site.home.scripts.collections')
