<!DOCTYPE html>


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

    <title>@yield('page_title')</title>

    <link rel="stylesheet" href="{{ asset('css/admin-style.css') }}">
    <!-- Bootstrap CSS --><html lang="ar" dir="rtl"></html>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
</head>

<body>

    @include('layouts.navigation')

    @yield('content')
    <!-- Bootstrap JS Bundle -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Drawer -->
    <div class="drawer" id="drawer">
        <!-- زرار قفل -->
        {{-- <button class="close-btn" onclick="toggleDrawer()">>></button> --}}

        <ul>
            <li>
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    تسجيل الخروج
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </li>
        </ul>
    </div>

    <!-- JavaScript -->
    <script>
        function toggleDrawer() {
            document.getElementById("drawer").classList.toggle("open");
        }
    </script>
    <script>
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 2000
            })
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: "{{ session('error') }}",
                showConfirmButton: false,
                timer: 2000
            })
        @endif
    </script>

    <!-- Pusher JS -->
    <script src="https://js.pusher.com/8.2/pusher.min.js"></script>
    <!-- Laravel Echo (IIFE) -->
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>

    <script>
        // عرض Snackbar بسيط
        function showSnackbar(message) {
            const box = document.createElement('div');
            box.style.position = 'fixed';
            box.style.bottom = '20px';
            box.style.left = '50%';
            box.style.transform = 'translateX(-50%)';
            box.style.padding = '12px 16px';
            box.style.background = '#333';
            box.style.color = '#fff';
            box.style.borderRadius = '8px';
            box.style.zIndex = '9999';
            box.textContent = message;
            document.body.appendChild(box);
            setTimeout(() => box.remove(), 3500);
        }

        // إعداد Echo على Pusher
        window.Echo = new Echo.Echo({
            broadcaster: 'pusher',
            key: '{{ env('PUSHER_APP_KEY') }}',
            cluster: '{{ env('PUSHER_APP_CLUSTER', 'mt1') }}',
            forceTLS: true
        });

        // الاستماع لقناة "bookings" والحدث "booking.status.updated"
        window.Echo.channel('bookings')
            .listen('.booking.status.updated', (e) => {
                // e جاي من broadcastWith
                if (e?.title) {
                    showSnackbar(`📣 الحجز "${e.title}" أصبح Due الآن`);
                } else {
                    showSnackbar('📣 تم تحديث حالة حجز إلى Due');
                }
            });
    </script>
</body>

</html>
