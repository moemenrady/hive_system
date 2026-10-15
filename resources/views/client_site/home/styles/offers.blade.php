@push('styles')
<style>
    .offers-section {
        background: var(--bg-soft);
        position: relative;
    }

    .exclusive-badge {
        background: var(--gold-soft);
        color: var(--olive-dark);
        padding: 5px 15px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 2px;
    }

    .header-line {
        width: 80px;
        height: 3px;
        background: var(--olive-dark);
        margin: 15px auto;
        border-radius: 2px;
    }

    /* Offer Card Style */
    .offer-card {
        background: white;
        padding: 15px;
        border-radius: 20px;
        transition: all 0.4s ease;
        border: 1px solid rgba(0,0,0,0.05);
    }

    .offer-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(78, 81, 70, 0.15);
    }

    .offer-image-wrapper {
        position: relative;
        border-radius: 15px;
        overflow: hidden;
        aspect-ratio: 1/1;
    }

    .offer-image-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s ease;
    }

    .offer-card:hover .offer-image-wrapper img {
        transform: scale(1.1);
    }

    .discount-tag {
        position: absolute;
        top: 15px;
        left: 15px;
        background: #A55C5C; /* error-muted used for tag */
        color: white;
        padding: 8px 12px;
        border-radius: 12px;
        font-weight: 800;
        font-size: 18px;
        z-index: 2;
    }

    .offer-overlay {
        position: absolute;
        inset: 0;
        background: rgba(78, 81, 70, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .offer-card:hover .offer-overlay {
        opacity: 1;
    }

    .shop-now-btn {
        background: var(--gold-soft);
        color: var(--olive-dark);
        padding: 12px 25px;
        border-radius: 50px;
        font-weight: 700;
        text-decoration: none;
        transform: translateY(20px);
        transition: all 0.4s ease;
    }

    .offer-card:hover .shop-now-btn {
        transform: translateY(0);
    }

    .offer-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--olive-dark);
    }

    .countdown {
        margin-top: 10px;
        font-size: 13px;
        color: #A55C5C;
        font-weight: 600;
    }
</style>
@endpush