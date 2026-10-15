@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const collections = document.querySelectorAll('.collection-wrapper');

            window.addEventListener('scroll', () => {
                collections.forEach((section, index) => {
                    const rect = section.getBoundingClientRect();
                    if (rect.top < window.innerHeight - 100) {
                        setTimeout(() => {
                            section.classList.add('visible');
                        }, index * 150);
                    }
                });
            });
        });
    </script>
    <script>
document.querySelectorAll('.product-card').forEach(card => {
    card.addEventListener('click', () => {
        const url = card.dataset.url;
        if(url) {
            window.location.href = url; // يفتح صفحة المنتج
        }
    });
});
</script>

@endpush
