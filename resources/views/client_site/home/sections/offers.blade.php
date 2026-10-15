<section id="offers-section" class="offers-section py-20">
    <div class="container mx-auto px-4">
        <div class="section-header text-center mb-12">
            <span class="exclusive-badge">Exclusive Deals</span>
            <h2 class="text-4xl font-extrabold text-olive mt-4">Unmissable Offers</h2>
            <div class="header-line"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($offers as $offer)
                <div class="offer-card group">
                    <div class="offer-image-wrapper">
                        <img src="{{ asset('storage/' . $offer->image) }}" alt="{{ $offer->title }}">
                        <div class="discount-tag">-{{ $offer->discount_percentage }}%</div>
                        <div class="offer-overlay">
                            <a href="{{ $offer->link }}" class="shop-now-btn">Shop Offer</a>
                        </div>
                    </div>
                    <div class="offer-details text-center mt-6">
                        <span class="sub-title">{{ $offer->sub_title }}</span>
                        <h3 class="offer-title">{{ $offer->title }}</h3>
                        @if ($offer->expires_at)
                            <div class="countdown" data-date="{{ $offer->expires_at->format('Y/m/d H:i:s') }}">
                                Expires in: <span class="timer-values">00:00:00</span>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-center col-span-full" style="color: var(--gold-soft);">
                    Stay tuned! New offers are coming soon.
                </p>
            @endforelse
        </div>
    </div>
</section>
@include('client_site.home.styles.offers')
@include('client_site.home.scripts.offers')
