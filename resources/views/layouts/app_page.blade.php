<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

    <meta charset="UTF-8">
    <title>

        @yield('title')

    </title>
    @yield('style')

    <style>
        /* 🌙 Dark Modern Style */
        :root {
            --bg-color: #0e0e11;
            /* نفس layout */
            --card-bg: #16161a;
            /* لون الكروت */
            --accent: #ffb84d;
            /* أزرار وألوان مميزة */
            --accent-light: #ffe6a3;
            /* ألوان فاتحة للنصوص */
            --text-main: #f5f5f5;
            /* النصوص الأساسية */
            --text-dim: #bfbfbf;
            /* النصوص الثانوية */
            --border-light: rgba(255, 255, 255, 0.08);
            --theme-primary: #1a1a1d;
            /* خلفية الكروت */
            --btn-bg: #ffb84d;
            --btn-border: #f2d35e;
            --btn-text: #111;
        }

        body {
            background: var(--bg-color) !important;
            color: var(--text-main) !important;
        }

        .session-card {
            background: var(--theme-primary) !important;
            color: #fff !important;
        }

        .btn-submit.primary {
            background: linear-gradient(135deg, #ffffff, #747474) !important;
        }


        /* ✅ Navbar */
        .navbar-custom {
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            padding: 14px 24px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 24px;
            backdrop-filter: blur(var(--blur));
            background: rgba(15, 15, 18, 0.6);
            border-bottom: 1px solid var(--border-light);
            z-index: 1500;
        }

        .navbar-custom a {
            text-decoration: none;
            color: var(--text-dim);
            font-weight: 600;
            padding: 10px 18px;
            border-radius: 8px;
            transition: all 0.25s ease;
        }

        .navbar-custom a:hover {
            color: var(--accent);
            background: rgba(255, 184, 77, 0.08);
            transform: translateY(-2px);
        }

        .navbar-custom a.active {
            background: var(--accent);
            color: #000;
            box-shadow: 0 4px 14px rgba(255, 184, 77, 0.3);
            transform: scale(1.05);
        }

        /* 🟡 عنوان الفيتشر */
        .title {
            text-align: center;
            color: var(--accent-light);
            font-size: 22px;
            font-weight: 800;
            margin: 18px auto 28px;
            padding: 16px 20px;
            background: linear-gradient(180deg, #1d1d20, #141416);
            border: 1px solid var(--border-light);
            border-radius: 14px;
            box-shadow: 0 0 22px rgba(255, 184, 77, 0.06);
            width: fit-content;
        }

        /* 🟢 زر تعديل */
        .edit-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(90deg, #ffb84d, #ffda7f);
            color: #1a1a1a;
            padding: 10px 18px;
            border-radius: 12px;
            font-weight: 700;
            text-decoration: none;
            border: none;
            transition: all 0.25s ease;
            box-shadow: 0 8px 22px rgba(255, 184, 77, 0.2);
        }

        .edit-btn:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 30px rgba(255, 184, 77, 0.35);
        }

        /* ⬅️ أزرار الرجوع */
        .back-btn,
        .back-btn2 {
            position: fixed;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            z-index: 2000;
            background: linear-gradient(145deg, #1c1c1f, #111112);
            border: 1px solid var(--border-light);
            color: var(--accent-light);
            box-shadow: 0 0 12px rgba(255, 184, 77, 0.25);
            transition: all 0.25s ease;
        }

        .back-btn {
            top: 70px;
            left: 24px;
            padding: 13px 16px;
        }

        .back-btn2 {
            top: 18px;
            left: 22px;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .back-btn:hover,
        .back-btn2:hover {
            transform: scale(1.1) rotate(-5deg);
            box-shadow: 0 0 22px rgba(255, 184, 77, 0.4);
        }

        /* 📦 Snackbar */
        .snackbar {
            position: fixed;
            top: calc(var(--top-offset) + 10px);
            right: 20px;
            background: rgba(20, 20, 22, 0.9);
            color: var(--text-main);
            padding: 12px 20px;
            border-radius: 12px;
            font-size: 14px;
            z-index: 1990;
            border: 1px solid var(--border-light);
            backdrop-filter: blur(var(--blur));
            transform: translateX(130%);
            opacity: 0;
            transition: transform 0.4s ease, opacity 0.4s ease;
        }

        .snackbar.show {
            transform: translateX(0);
            opacity: 1;
        }

        .snackbar.success {
            border-left: 4px solid #00ff88;
        }

        .snackbar.error {
            border-left: 4px solid #ff4444;
        }

        /* 🔸 محتوى الصفحة */
        .feature-content {
            max-width: 800px;
            margin: 0 auto;
            background: var(--card-bg);
            border-radius: 16px;
            padding: 24px;
            border: 1px solid var(--border-light);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
        }

        .feature-content h3 {
            color: var(--accent-light);
            font-weight: 700;
            margin-bottom: 10px;
        }

        .feature-content p {
            color: var(--text-dim);
            line-height: 1.7;
            font-size: 15px;
        }

        .right {
            background: var(--card-bg);
            /* بدل الأبيض */
            color: var(--text-main);
            /* النص يكون فاتح */
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
            border: 1px solid var(--border-light);
        }

        .input-box {
            background: #1a1a1d;
            /* بدل الفاتح */
            color: var(--text-main);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .counter-box {
            background: #1a1a1d;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        #phone-results,
        #id-results {
            background: #1a1a1d;
            color: var(--text-main);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .result-item:hover {
            background: rgba(255, 184, 77, 0.1);
        }

        .result-item.active {
            background: rgba(255, 184, 77, 0.2);
        }

        .btn-submit.primary {
            background: linear-gradient(135deg, #ffb84d, #ffe6a3);
            color: #111;
        }

        .btn-submit.private {
            background: linear-gradient(135deg, #ffb84d, #ffe6a3);
            color: #fff;
        }


        .counts-container .count-box {
            background: var(--theme-primary);
            color: var(--text-main);
            border: 1px solid var(--border-light);
        }

        .sessions-box {
            border-top: 4px solid #ffb84d;
        }

        .private-box {
            border-top: 4px solid #7b61ff;
        }
    </style>



    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>

<body>
    <div class="desktop-nav">
        @include('layouts.page_navigation')
    </div>
    @yield('page_title')
    @if (session('shift_required'))
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: '⚠️ لم تفتح شيفت بعد',
                    text: 'هل تريد فتح شيفت جديد الآن؟',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'نعم، افتح شيفت',
                    cancelButtonText: 'لاحقًا'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch("{{ route('shift.start') }}", {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            }
                        }).then(() => {
                            location.reload();
                        });
                    }
                });
            });
        </script>
    @endif

    {{-- ✅ تنبيهات Toast باستخدام Snackbar --}}
    @if (session('success'))
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                showSnackbar("{{ session('success') }}", "success");
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                showSnackbar("{{ session('error') }}", "error");
            });
        </script>
    @endif

    {{-- زر الرجوع للرئيسيه --}}
    <form action="{{ route('main.create') }}">
        <button class="back-btn">الرئيسية</button>
    </form>

    <button type="button" class="back-btn2" onclick="history.back()">
        <i class="fas fa-arrow-left"></i>
    </button>

    @yield('content')



    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Pusher JS -->
    <script src="https://js.pusher.com/8.2/pusher.min.js"></script>
    <!-- Laravel Echo (IIFE) -->
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>

    <script>
        function showSnackbar(message, type = "success") {
            const box = document.createElement('div');
            box.className = `snackbar ${type}`;
            box.textContent = message;
            document.body.appendChild(box);

            setTimeout(() => box.classList.add("show"), 50);

            setTimeout(() => {
                box.classList.remove("show");
                setTimeout(() => box.remove(), 300);
            }, 4000);
        }
    </script>



</body>
