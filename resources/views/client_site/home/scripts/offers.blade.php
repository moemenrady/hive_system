@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const offerCards = document.querySelectorAll('.offer-card');
        
        const revealOffers = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.style.opacity = "1";
                        entry.target.style.transform = "translateY(0)";
                    }, index * 150); // Delay لكل كارت عشان يظهروا ورا بعض
                }
            });
        }, { threshold: 0.1 });

        offerCards.forEach(card => {
            card.style.opacity = "0";
            card.style.transform = "translateY(30px)";
            card.style.transition = "all 0.6s ease-out";
            revealOffers.observe(card);
        });
    });
</script>
@endpush