@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const aboutSection = document.querySelector('.about-us-section');

        const observerOptions = {
            threshold: 0.3 // يشتغل لما يظهر 30% من السيكشن
        };

        const aboutObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    aboutSection.classList.add('visible');
                }
            });
        }, observerOptions);

        aboutObserver.observe(aboutSection);
    });
</script>
@endpush