<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <title>
        @yield('title')
    </title>
    @yield('style')
    <style>
        :root {
            --bg-color: #0e0e11;
            --card-bg: #16161a;
            --accent: #ffb84d;
            --accent-light: #ffe6a3;
            --text-main: #f5f5f5;
            --text-dim: #bfbfbf;
            --border-light: rgba(255, 255, 255, 0.08);
            --theme-primary: #1a1a1d;
            --btn-bg: #ffb84d;
            --btn-border: #f2d35e;
            --btn-text: #111;
        }

        /* ✅ خلفية عامة */
        body {
            background: var(--bg-color) !important;
            color: var(--text-main) !important;
            font-family: 'Cairo', sans-serif;
        }

        /* ✅ الكروت */
        .session-card,
        .card,
        .error-box {
            background: var(--card-bg) !important;
            color: var(--text-main) !important;
            border: 1px solid var(--border-light);
            border-radius: 16px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.25);
            transition: all 0.3s ease;
        }

        .session-card:hover,
        .card:hover,
        .error-box:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.4);
            border-color: var(--accent);
        }

        /* ✅ الأزرار */
        .btn-submit.primary,
        button,
        a.btn {
            background: var(--btn-bg);
            color: var(--btn-text);
            border: 1px solid var(--btn-border);
            border-radius: 10px;
            padding: 10px 18px;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-submit.primary:hover,
        button:hover,
        a.btn:hover {
            background: var(--accent-light);
            color: #000;
        }

        .space {
            height: 60px;
        }

        /* ✅ Navbar شفافة تمامًا */
        .navbar-custom {
            position: fixed;
            top: 10px;
            /* تبعد شوية عن الحافة */
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 24px;
            z-index: 1500;

            /* مفيش خلفية أو حدود أو ظل */
            background: none !important;
            border: none !important;
            box-shadow: none !important;
            backdrop-filter: none !important;
        }

        /* الروابط */
        .navbar-custom a {
            text-decoration: none;
            color: var(--text-dim);
            font-weight: 600;
            padding: 10px 18px;
            border-radius: 8px;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* hover و active يفضلوا يشتغلوا عادي */
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

        /* 📱 إخفاء navbar على الموبايل */
        @media (max-width: 768px) {
            .navbar-custom {
                display: none !important;
            }
        }



        /* 🟡 عنوان الفيتشر */
        .title {
            text-align: center;
            color: var(--accent-ligt);
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
            color: #ffffff;
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

        #client-results {
            background: #1e1e1e !important;
            /* خلفية داكنة */
            color: #fff !important;
            border: 1px solid #555 !important;
            border-radius: 10px;
            margin-top: 5px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.6);
            overflow-y: auto;
            max-height: 250px;
            position: absolute;
            width: 100%;
            z-index: 9999;
        }

        #client-results .result-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 12px;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        #client-results .result-item span.name {
            color: #fff !important;
            font-weight: 500;
        }

        #client-results .result-item span.id {
            color: #d4af37 !important;
            /* ذهبي محروق */
            font-weight: bold;
        }

        #client-results .result-item:hover {
            background: rgba(212, 175, 55, 0.15);
            transform: scale(1.02);
        }

        #client-results .no-results {
            padding: 10px;
            color: rgba(255, 255, 255, 0.7);
            text-align: center;
            font-style: italic;
        }

        /* 📱 ريسبونسيف */
        @media (max-width: 768px) {
            #client-results {
                max-height: 180px;
                font-size: 0.9rem;
            }

            #client-results .result-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
            }
        }
/* ===============================
   Client Snackbar – Dark Hive Style
================================ */
.client-snackbar {
    position: fixed;
    top: 24px;
    right: 24px;
    width: 320px;
    padding: 18px 20px;

    background: #121212; /* أسود فخم */
    color: #f1f1f1;

    border-radius: 18px;
    border: 1px solid rgba(255, 215, 0, 0.18); /* جولد محروق خفيف */

    box-shadow:
        0 20px 45px rgba(0, 0, 0, 0.65),
        inset 0 0 0 1px rgba(255, 255, 255, 0.03);

    z-index: 9999;
    animation: slideIn .35s cubic-bezier(.4,0,.2,1);
    backdrop-filter: blur(6px);
}

/* ===============================
   Title
================================ */
.client-snackbar h4 {
    margin: 0 0 6px;
    font-weight: 800;
    font-size: 15px;
    letter-spacing: .4px;
    color: #ffd700; /* جولد محروق */
}

/* ===============================
   Text
================================ */
.client-snackbar p {
    margin: 2px 0;
    font-size: 13.5px;
    color: #d6d6d6;
}

/* ===============================
   Main Button
================================ */
.client-snackbar button {
    margin-top: 14px;
    width: 100%;
    padding: 12px;

    border-radius: 14px;
    border: 1px solid rgba(255, 215, 0, 0.25);

    background: linear-gradient(
        135deg,
        #1a1a1a,
        #000
    );

    color: #ffd700;
    font-weight: 800;
    letter-spacing: .4px;
    cursor: pointer;

    transition: all .25s ease;
}

/* Hover */
.client-snackbar button:hover {
    background: linear-gradient(
        135deg,
        #ffd700,
        #c9a300
    );
    color: #111;
    transform: translateY(-1px);
    box-shadow: 0 8px 22px rgba(255, 215, 0, .35);
}

/* ===============================
   Animation
================================ */
@keyframes slideIn {
    from {
        transform: translateX(120%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
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
    <div class="space"></div>


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

 <script>
        let persons = 1;

        function updatePersonsUI() {
            const countEl = document.getElementById('persons-count');
            const inputEl = document.getElementById('persons-input');

            if (countEl) countEl.innerText = persons;
            if (inputEl) inputEl.value = persons;
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {




            /* ===============================
                Scanner State
            =============================== */
            let isSearching = false;
            let buffer = '';
            let scanTimer = null;

            const MIN_LENGTH = 4;
            const MAX_LENGTH = 5;
            const SCAN_TIMEOUT = 8000; // 8 ثانية

            /* ===============================
                Safe Audio Loader
            =============================== */
            function safeAudio(src) {
                const audio = new Audio(src);
                audio.onerror = () => console.warn('Audio not found:', src);
                return audio;
            }

            const sounds = {
                start: safeAudio('/sounds/start.mp3'),
                success: safeAudio('/sounds/success.mp3'),
                entry: safeAudio('/sounds/entry.mp3'),
                error: safeAudio('/sounds/error.mp3'),
                cancel: safeAudio('/sounds/cancel.mp3'),
            };

            /* ===============================
                Helpers
            =============================== */
            function resetScan(reason = '') {
                buffer = '';
                clearTimeout(scanTimer);
                scanTimer = null;
                isSearching = false;

                if (reason === 'timeout' || reason === 'invalid') {
                    sounds.cancel.play();
                }
            }


            function startTimeout() {
                clearTimeout(scanTimer);
                scanTimer = setTimeout(() => {
                    resetScan('timeout');
                }, SCAN_TIMEOUT);
            }

            function isTypingInField(e) {
                const el = e.target;

                return (
                    el.tagName === 'INPUT' ||
                    el.tagName === 'TEXTAREA' ||
                    el.isContentEditable ||
                    el.closest('.client-snackbar')
                );
            }

            function closeClientSnackbar() {
                document.querySelector('.client-snackbar')?.remove();
                persons = 1;
                updatePersonsUI();
            }

            /* ===============================
                Keyboard Listener (Scanner)
            =============================== */
            document.addEventListener('keydown', (e) => {

                // ❌ تجاهل أي كتابة أثناء البحث
                if (isSearching) return;

                if (isTypingInField(e)) return;

                if (e.key === 'Escape') {
                    closeClientSnackbar();
                    resetScan();
                    return;
                }

                // ⛔ أرقام فقط
                if (!/^[0-9]$/.test(e.key)) {
                    resetScan('invalid'); // ⛔ أي زر غير رقم = مسح فوري
                    return;
                }

                if (buffer.length === 0) {
                    startTimeout();
                    sounds.start.play();
                }

                buffer += e.key;

                if (buffer.length === MIN_LENGTH || buffer.length === MAX_LENGTH) {
                    isSearching = true; // 🔒 قفل الإدخال
                    searchClientById(buffer);
                    resetScan();
                    return;
                }

                if (buffer.length > MAX_LENGTH) {
                    resetScan('invalid');
                }
            });


            /* ===============================
                API Search
            =============================== */
            async function searchClientById(id) {
                try {
                    const res = await fetch(`{{ route('clients.search.id') }}?query=${id}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    const data = await res.json();

                    // ❌ مفيش عميل
                    if (!data || !data.length) {
                        closeClientSnackbar(); // 🔥 اقفل أي Snackbar
                        resetScan('invalid'); // 🔥 امسح البفر فورًا
                        sounds.error.play();
                        showClientSnackbar(null, '❌ لا يوجد عميل بهذا الكود');
                        return;
                    }

                    const client = data[0];

                    // ✅ عنده جلسة
                    if (client.active_session_id) {
                        window.location.href = `{{ url('/sessions') }}/${client.active_session_id}`;
                        sounds.entry.play();
                        return;
                    }

                    // ✅ عميل طبيعي
                    persons = 1;
                    updatePersonsUI();
                    showClientSnackbar(client);
                    sounds.success.play();

                } catch (err) {
                    console.error(err);
                    resetScan('invalid');
                    sounds.error.play();
                } finally {
                    isSearching = false; // 🔓 فتح الإدخال تاني
                }
            }


            document.addEventListener('DOMContentLoaded', () => {
                const hallEl = document.getElementById('hallSelect');
                const startNowBtn = document.getElementById('startNowBtn');

                hallEl.addEventListener('change', async () => {
                    if (!hallEl.value) {
                        startNowBtn.disabled = true;
                        return;
                    }

                    // حساب التقدير والتحقق من الحجز
                    await fetchEstimate();
                });

            });

        });
    </script>

    <script>
        function showClientSnackbar(client, errorMsg = null) {
            persons = 1;
            updatePersonsUI();

            document.querySelector('.client-snackbar')?.remove();

            const box = document.createElement('div');
            box.className = 'client-snackbar';

            if (!client) {
                box.innerHTML = `<p>${errorMsg}</p>`;
                document.body.appendChild(box);
                setTimeout(() => box.remove(), 2500);
                return;
            }

            box.innerHTML = `
        <h4>👤 ${client.name}</h4>
        <p>📞 ${client.phone}</p>
        <p>ID: ${client.id}</p>

        <form method="POST" action="{{ route('session.justStart') }}">
            @csrf
            <input type="hidden" name="name" value="${client.name}">
            <input type="hidden" name="phone" value="${client.phone}">
            <div class="persons-counter">
                <button type="button" class="counter-btn" data-action="minus">➖</button>
                <span id="persons-count">1</span>
                <button type="button" class="counter-btn" data-action="plus">➕</button>
            </div>
            <input type="hidden" name="persons" id="persons-input" value="1">
            <input type="hidden" name="age" value="${client.age ?? ''}">
            <input type="hidden" name="specialization_id" value="${client.specialization_id ?? ''}">
            <input type="hidden" name="education_stage_id" value="${client.education_stage_id ?? ''}">
            
            <!-- الزر الرئيسي -->
            <button type="submit" autofocus>🚀 بدء الجلسة</button>

              <div class="d-flex gap-2" style="flex-wrap:wrap;">

                        <!-- زر يفتح المودال — لا يرسل الفورم -->
                        <button type="button" id="openPrivateBtn" class="btn-submit"
                            style="background: linear-gradient(135deg,#7b61ff,#5e3bff); flex:1; min-width:140px;"
                            data-bs-toggle="modal" data-bs-target="#startBookingModal">
                            🔒 بدء جلسة خاصة
                        </button>
                    </div>
        </form>
    `;

            document.body.appendChild(box);

            box.querySelector('button').focus();

            setTimeout(async () => {
                try {
                    const clientId = client.id;
                    const personsCount = persons;
                    const startTime = new Date().toISOString().slice(0, 19).replace('T',
                        ' ');

                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    const response = await fetch("{{ route('new-session.store') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({
                            client_id: clientId,
                            persons: personsCount,
                            start_time: startTime
                        })
                    });

                    if (!response.ok) {
                        const text = await response.text();
                        console.error('خطأ من السيرفر:', text);
                        return;
                    }

                    const data = await response.json();

                    if (data.success) {
                        console.log('تم تخزين الجلسة تلقائيًا بعد 80 ثانية');
                    } else {
                        console.error('خطأ في تخزين الجلسة:', data);
                    }

                } catch (err) {
                    console.error('حدث خطأ عند الاتصال بالسيرفر:', err);
                }
            }, 2000);


            // ==============================
            // التعامل مع العدادات داخل Snackbar
            // ==============================
            // ==============================
            // التعامل مع العدادات داخل Snackbar
            // ==============================
            const counterMinus = box.querySelector('[data-action="minus"]');
            const counterPlus = box.querySelector('[data-action="plus"]');
            const personsCountEl = box.querySelector('#persons-count');
            const personsInputEl = box.querySelector('#persons-input');

            function updateModalPersons() {
                const modalPersonsEl = document.getElementById('personsDisplayInModal');
                const modalPersonsInput = document.getElementById('modal_persons');
                modalPersonsEl.textContent = persons;
                modalPersonsInput.value = persons;
            }

            counterMinus.addEventListener('click', () => {
                if (persons > 1) persons--;
                personsCountEl.innerText = persons;
                personsInputEl.value = persons;

                updateModalPersons(); // 🔹 يحدث المودال مباشرة
            });

            counterPlus.addEventListener('click', () => {
                persons++;
                personsCountEl.innerText = persons;
                personsInputEl.value = persons;

                updateModalPersons(); // 🔹 يحدث المودال مباشرة
            });

        }

        // زر "بدء جلسة خاصة"
        const privateBtn = box.querySelector('#openPrivateBtn');

        privateBtn.addEventListener('click', () => {
            const modalPhone = document.getElementById('modal_phone');
            const modalName = document.getElementById('modal_name');
            const modalClientId = document.getElementById('modal_client_id');
            const modalPersons = document.getElementById('modal_persons');
            const personsDisplayEl = document.getElementById('personsDisplayInModal');

            if (modalPhone) modalPhone.value = client.phone || '';
            if (modalName) modalName.value = client.name || '';
            if (modalClientId) modalClientId.value = client.id || '';
            if (modalPersons) modalPersons.value = persons || '1';
            if (personsDisplayEl) personsDisplayEl.textContent = persons || '1';

            // إعادة تعيين حالة UI للمودال
            if (estimateBanner) estimateBanner.style.display = 'none';
            if (ongoingWarning) ongoingWarning.style.display = 'none';
            if (estimateAmount) estimateAmount.textContent = '';
            if (estimatePerHour) estimatePerHour.textContent = '';
            if (estimateMessage) estimateMessage.textContent = 'اختَر القاعة لاظهار التقدير (المدة: ساعة واحدة)';
            if (startNowBtn) startNowBtn.disabled = true;

            // فتح المودال برمجياً
            const modalEl = document.getElementById('startBookingModal');
            if (modalEl) {
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            }
        });

        const personsDisplayEl = document.getElementById('personsDisplayInModal');
        const hallEl = document.getElementById('hallSelect');
        const estimateBanner = document.getElementById('estimateBanner');
        const ongoingWarning = document.getElementById('ongoingWarning');
        const estimateAmount = document.getElementById('estimateAmount');
        const estimatePerHour = document.getElementById('estimatePerHour');
        const estimateMessage = document.getElementById('estimateMessage');
        const startNowBtn = document.getElementById('startNowBtn');

        $('#startBookingModal').on('shown.bs.modal', function() {
            const pDisplay = document.getElementById('personsDisplayInModal');
            if (pDisplay) pDisplay.textContent = persons || '1';

            // إذا كانت القاعة محددة مسبقًا
            const hSelect = document.getElementById('hallSelect');
            if (hSelect && hSelect.value) {
                fetchEstimate?.();
            }
        });

        // ==============================
        // تحديث عداد الأشخاص في المودال أثناء تعديلهم في Snackbar
        // ==============================
        function updateModalPersons() {
            const modalPersonsEl = document.getElementById('personsDisplayInModal');
            const modalPersonsInput = document.getElementById('modal_persons');
            if (modalPersonsEl) modalPersonsEl.textContent = persons;
            if (modalPersonsInput) modalPersonsInput.value = persons;
        }

        async function checkOngoing(hallId) {
            if (!hallId) return { ongoing: false };
            try {
                const url = "{{ route('bookings.check_ongoing') }}?hall_id=" + encodeURIComponent(hallId);
                const resp = await fetch(url, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                if (!resp.ok) return { error: 'خطأ في التحقق من القاعة' };
                return await resp.json();
            } catch (err) {
                console.error('[checkOngoing] ', err);
                return { error: 'خطأ في الاتصال بالخادم' };
            }
        }

        async function fetchEstimate() {
            const hallSelectEl = document.getElementById('hallSelect');
            const modalPersonsInput = document.getElementById('modal_persons');
            const startBtn = document.getElementById('startNowBtn');
            const banner = document.getElementById('estimateBanner');
            const msg = document.getElementById('estimateMessage');
            const amountEl = document.getElementById('estimateAmount');
            const perHourEl = document.getElementById('estimatePerHour');
            const warnBox = document.getElementById('ongoingWarning');
            const warnText = document.getElementById('ongoingText');

            const hallId = hallSelectEl?.value;
            const attendees = Number(modalPersonsInput?.value || 1);

            if (!hallId || attendees < 1) {
                if (startBtn) startBtn.disabled = true;
                if (banner) banner.style.display = 'none';
                return;
            }

            // عرض Loading
            if (banner) banner.style.display = 'block';
            if (msg) msg.textContent = 'جارِ الفحص والحساب...';
            if (warnBox) warnBox.style.display = 'none';
            if (startBtn) startBtn.disabled = true;

            // تحقق من الحجز الجاري
            const ongoingResp = await checkOngoing(hallId);
            if (ongoingResp && ongoingResp.ongoing) {
                if (warnBox) warnBox.style.display = 'block';
                if (warnText) warnText.textContent = ongoingResp.message || 'القاعة محجوزة حالياً.';
                if (banner) banner.style.display = 'none';
                if (startBtn) startBtn.disabled = true;
                return;
            }

            // طلب التقدير من السيرفر
            try {
                const params = new URLSearchParams({
                    hall_id: hallId,
                    attendees: attendees,
                    duration_minutes: 60
                });
                const resp = await fetch("{{ route('bookings.estimate') }}?" + params.toString());
                const data = await resp.json();

                if (data && data.success) {
                    if (msg) msg.textContent = `التقدير (المدة: ساعة واحدة)`;
                    if (amountEl) amountEl.textContent = `${data.estimated_formatted} ${data.currency || ''}`;
                    if (perHourEl) perHourEl.textContent = `سعر الساعة: ${data.per_hour_formatted || ''} ${data.currency || ''}`;
                    if (startBtn) startBtn.disabled = false; // ✅ تفعيل الزر
                } else {
                    if (msg) msg.textContent = data.error || 'خطأ في الحساب';
                    if (startBtn) startBtn.disabled = true;
                }
            } catch (err) {
                console.error(err);
                if (msg) msg.textContent = 'خطأ في الاتصال';
                if (startBtn) startBtn.disabled = true;
            }
        }

        counterMinus.addEventListener('click', () => {
            if (persons > 1) persons--;
            personsCountEl.innerText = persons;
            personsInputEl.value = persons;

            updateModalPersons(); // 🔹 يحدث المودال مباشرة
        });

        counterPlus.addEventListener('click', () => {
            persons++;
            personsCountEl.innerText = persons;
            personsInputEl.value = persons;

            updateModalPersons(); // 🔹 يحدث المودال مباشرة
        });

        const startBookingForm = document.getElementById('startBookingForm');
        startBookingForm?.addEventListener('submit', function(e) {
            const mPhone = document.getElementById('modal_phone');
            const mName = document.getElementById('modal_name');
            // تحقق من البيانات
            if (!mPhone?.value || !mName?.value) {
                e.preventDefault();
                alert('مطلوب: اسم العميل ورقم الهاتف قبل بدء الجلسة.');
                return false;
            }

            const mPersons = document.getElementById('modal_persons');
            if (mPersons && !mPersons.value) {
                mPersons.value = '1';
            }
        });
    </script>
    @include('session.modal.start-booking')
@yield('scripts')

</body>
