@include('client_site.home.styles.hero')

<img src="{{ asset('hive_logo.png') }}" alt="Hive Logo" id="scroll-logo">
<section class="hero">
    <div class="polygon-reveal" id="polygon-reveal">
        
        <img src="{{ asset('home/base_image.jpeg') }}" alt="">
    </div>

    <img src="{{ asset('home/base_image.jpeg') }}" class="hero-image-bg" alt="">

    <div class="hero-overlay">
        <a href="/">
            <div class="brand">Hive</div>
        </a>
        <nav class="hero-nav">

                <a href="/HomePage">Home</a>
            <a href="{{ route('home') }}#menu-section">Menu</a>
            <a href="{{ route('home') }}#offers-section">Offers</a>
            <a href="{{ route('home') }}#about-us-section">About Us</a>
        </nav>
        <div class="hero-icons">
            <a href="{{ route('search') }}" class="hero-icon" title="Search">
                <img src="{{ asset('icons/search.svg') }}" alt="Search">
            </a>
            <a href="{{ route('profile') }}" class="hero-icon" title="Profile">
                <img src="{{ asset('icons/profile.svg') }}" alt="Profile">
            </a>
        </div>

        <h1 class="hero-title">
            <span>Best WorkSpace <br> Cafe</span>
        </h1>
    </div>
</section>

@include('client_site.home.scripts.scroll-logo')
