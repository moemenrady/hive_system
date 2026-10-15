@push('scripts')
    <script>
        const scrollLogo = document.getElementById('scroll-logo');

        window.addEventListener('scroll', () => {
            const scrollY = window.scrollY;

            if (scrollY > 100) {
                scrollLogo.classList.add('show');
                scrollLogo.classList.remove('hide');
            } else {
                scrollLogo.classList.add('hide');
                scrollLogo.classList.remove('show');
            }
        });
    </script>
    <script>
        function smoothScrollToTop(duration = 800) {
            const start = window.scrollY;
            const startTime = performance.now();

            function scrollStep(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);

                const ease = 1 - Math.pow(1 - progress, 3); // easeOutCubic
                window.scrollTo(0, start * (1 - ease));

                if (progress < 1) {
                    requestAnimationFrame(scrollStep);
                }
            }

            requestAnimationFrame(scrollStep);
        }

        scrollLogo.addEventListener('click', () => {
            smoothScrollToTop(900);
        });
    </script>


    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const polygonReveal = document.getElementById("polygon-reveal");
            const heroImageBg = document.querySelector(".hero-image-bg");

            // لو المستخدم مش في أعلى الصفحة، نخفي الأنيميشن فوراً
            if (window.scrollY > 0) {
                polygonReveal.style.display = "none";
                heroImageBg.style.opacity = "1"; // نعرض الصورة مباشرة
            } else {
                // لو المستخدم في الأعلى، نخلي الأنيميشن يظهر
                polygonReveal.style.display = "flex";

                // بعد انتهاء الأنيميشن (2 ثانية) نخفي الحاوية ونظهر الصورة
                setTimeout(() => {
                    polygonReveal.style.display = "none";
                    heroImageBg.style.opacity = "1";
                }, 2000); // نفس مدة الأنيميشن
            }
        });
    </script>
@endpush
