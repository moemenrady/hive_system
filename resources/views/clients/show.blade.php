@extends('layouts.app_page')

@section('title', "تفاصيل العميل — {$client->name}")

@section('content')
    <div class="client-container">

        <div class="card">
            <div class="card-header">
                <h2>👥 بيانات العميل</h2>
                <span class="badge">#{{ $client->id }}</span>
            </div>

            <div class="header-left">
                <a href="{{ route('clients.edit', $client->id) }}" class="btn edit-btn" title="تعديل بيانات العميل">
                    <span class="edit-ico" aria-hidden="true">✏️</span>
                    <span class="edit-txt">تعديل</span>
                </a>

                <!-- زر المشاركة الجديد عبر الواتساب -->
                <button type="button" id="shareWhatsappBtn" class="btn whatsapp-btn" title="مشاركة الكارت عبر واتساب">
                    <span class="whatsapp-ico">💬</span>
                    <span class="whatsapp-txt">مشاركة الكارت</span>
                </button>

                <style>
                    .whatsapp-btn {
                        position: relative;
                        overflow: hidden;

                        display: inline-flex;
                        align-items: center;
                        gap: 10px;

                        padding: 12px 22px;
                        border-radius: 16px;

                        background: #ffffff;
                        color: #111;

                        border: 1px solid rgba(255, 255, 255, 0.25);

                        font-weight: 700;
                        font-size: 15px;

                        backdrop-filter: blur(12px);

                        transition:
                            transform 0.35s ease,
                            box-shadow 0.35s ease,
                            background 0.4s ease,
                            color 0.35s ease;

                        box-shadow:
                            0 8px 25px rgba(0, 0, 0, 0.12),
                            inset 0 1px 0 rgba(255, 255, 255, 0.8);

                        cursor: pointer;
                    }

                    /* لمعة متحركة */
                    .whatsapp-btn::before {
                        content: "";
                        position: absolute;
                        top: -120%;
                        left: -40%;
                        width: 60%;
                        height: 300%;

                        background: linear-gradient(120deg,
                                transparent,
                                rgba(255, 255, 255, 0.7),
                                transparent);

                        transform: rotate(20deg);
                        transition: 0.7s ease;
                    }

                    .whatsapp-btn:hover::before {
                        left: 140%;
                    }

                    .whatsapp-btn:hover {
                        background: linear-gradient(135deg, #8b5e1a, #c6922b, #6f4713);
                        color: #fff;

                        transform: translateY(-4px) scale(1.03);

                        box-shadow:
                            0 15px 35px rgba(198, 146, 43, 0.45),
                            0 0 18px rgba(255, 196, 72, 0.35);
                    }

                    .whatsapp-btn:active {
                        transform: scale(0.96);
                    }

                    .whatsapp-ico {
                        font-size: 18px;
                        transition: transform 0.35s ease;
                    }

                    .whatsapp-btn:hover .whatsapp-ico {
                        transform: rotate(-8deg) scale(1.15);
                    }

                    .whatsapp-txt {
                        letter-spacing: 0.3px;
                    }
                </style>
            </div>

            <div class="section client-main">
                <div class="box client-info">

                    <div class="row">
                        <div class="col">
                            <label class="lbl">اسم العميل</label>
                            <p class="value">{{ $client->name }}</p>
                        </div>

                        <div class="col">
                            <label class="lbl">رقم الهاتف</label>
                            <p class="value">{{ $client->phone }}</p>
                        </div>
                    </div>

                    <!-- الباركود -->
                    <div class="barcode-wrapper">
                        <svg id="clientBarcode"></svg>
                    </div>
                    <div class="blacklist-card">

                        @if ($client->activeBlackList)
                            <div class="blacklist-header danger">
                                <div>
                                    <span class="status-dot"></span>
                                    <span>العميل موجود بالقائمة السوداء</span>
                                </div>

                                <form action="{{ route('black-list.destroy', $client->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')

                                    <button class="blacklist-remove-btn">
                                        ✅ إزالة الحظر
                                    </button>
                                </form>
                            </div>

                            <div class="blacklist-body">

                                <div class="blacklist-item">
                                    <span>🚫 سبب الحظر</span>
                                    <strong>
                                        {{ $client->activeBlackList->reason }}
                                    </strong>
                                </div>

                                <div class="blacklist-grid">

                                    <div>
                                        <small>تاريخ الحظر</small>

                                        <p>
                                            {{ $client->activeBlackList->created_at->format('Y-m-d h:i A') }}
                                        </p>
                                    </div>

                                    <div>
                                        <small>بواسطة</small>

                                        <p>
                                            {{ optional($client->activeBlackList->user)->name }}
                                        </p>
                                    </div>

                                </div>

                            </div>
                        @else
                            <div class="blacklist-header success">

                                <div>
                                    <span class="status-dot green"></span>
                                    <span>العميل غير محظور</span>
                                </div>

                                <button class="blacklist-add-btn" data-bs-toggle="modal" data-bs-target="#blackListModal">

                                    🚫 إضافة للقائمة السوداء
                                </button>

                            </div>
                        @endif

                    </div>
                </div>
            </div>
            <div id="hiddenCaptureCard" class="premium-capture-card">
                <div class="brand-header">
                    <span class="brand-logo">HIVE</span>
                    <span class="brand-slogan">يسعدنا دائماً وجودك معنا ✨</span>
                </div>

                <div class="premium-divider"></div>

                <div class="client-details">
                    <div class="detail-item">
                        <span class="detail-label">الاسم:</span>
                        <span class="detail-val">{{ $client->name }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">رقم الهاتف:</span>
                        <span class="detail-val">{{ $client->phone }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">رقم العميل (ID):</span>
                        <span class="detail-val">#{{ $client->id }}</span>
                    </div>
                </div>

                <div class="capture-barcode-wrapper">
                    <!-- سيتم نسخ الباركود الأصلي هنا عبر الجافاسكريبت تلقائياً -->
                    <div id="barcodeTarget"></div>
                </div>
            </div>
            <div class="section statuses">
                <h3>📌 حالة الحساب</h3>
                <div class="box flex-grid">
                    {{-- SUBSCRIPTION --}}
                    <div class="status-card">
                        <label class="d-check">
                            <input type="checkbox" {{ $subscription ? 'checked' : '' }} disabled>
                            <span>مشترك</span>
                        </label>

                        @if ($subscription)
                            <p class="small">الحالة: <strong>{{ $subscription->is_active ? 'فعال' : 'منتهي' }}</strong>
                            </p>
                            <div class="actions">
                                <a href="{{ route('subscriptions.show', $subscription->id) }}" class="btn small">🔍 تفاصيل
                                    الاشتراك</a>
                            </div>
                        @else
                            <p class="small muted">غير مشترك</p>
                        @endif
                    </div>

                    {{-- BOOKINGS --}}
                    <div class="status-card">
                        <label class="d-check">
                            <input type="checkbox" {{ $bookings->count() ? 'checked' : '' }} disabled>
                            <span>حاجز</span>
                        </label>

                        @if ($bookings->count())
                            <p class="small">حجوزات حالية: <strong>{{ $bookings->count() }}</strong></p>
                            <div class="actions">
                                <a href="{{ route('client.bookings', $client->id) }}" class="btn small">🔍 تفاصيل
                                    الحجوزات</a>
                            </div>
                        @else
                            <p class="small muted">غير حاجز</p>
                        @endif
                    </div>

                    {{-- SESSIONS --}}
                    <div class="status-card">
                        <label class="d-check">
                            <input type="checkbox" {{ $activeSession ? 'checked' : '' }} disabled>
                            <span>يوجد جلسة</span>
                        </label>

                        @if ($activeSession)
                            <p class="small">تبدأ:
                                {{ \Carbon\Carbon::parse($activeSession->start_time)->format('Y-m-d H:i') }}</p>
                            <div class="actions">
                                <a href="{{ route('session.show', $activeSession->id) }}" class="btn small">🔍 فتح
                                    الجلسة</a>
                            </div>
                        @else
                            <p class="small muted">لا يوجد جلسة نشطة</p>
                        @endif
                    </div>

                    {{-- FINANCIALS --}}
                    <div class="status-card">
                        <label class="d-check">
                            <input type="checkbox" {{ $invoicesTotal > 0 ? 'checked' : '' }} disabled>
                            <span>التعاملات المالية</span>
                        </label>

                        <p class="small">المجموع: <strong>{{ number_format($invoicesTotal, 2) }} جنيه</strong></p>
                        <div class="actions">
                            <a href="{{ route('client.invoices', $client->id) }}" class="btn small">🔍 تفاصيل الفواتير</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick view -->
            <div class="section">
                <h3>🗂️ نظرة سريعة</h3>
                <div class="box">
                    <div class="quick-grid">
                        <div>
                            <h4>أحدث ٣ حجوزات</h4>
                            @if ($bookings->count())
                                <ul class="mini-list">
                                    @foreach ($bookings->take(3) as $b)
                                        <li>{{ $b->title }} — {{ $b->status }} —
                                            {{ \Carbon\Carbon::parse($b->start_at)->format('Y-m-d H:i') }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="muted">لا توجد حجوزات حالية</p>
                            @endif
                        </div>

                        <div>
                            <h4>الجلسات الأخيرة</h4>
                            @if ($recentSessions->count())
                                <ul class="mini-list">
                                    @foreach ($recentSessions->take(3) as $s)
                                        <li>بدء: {{ \Carbon\Carbon::parse($s->start_time)->format('Y-m-d H:i') }} — حالة:
                                            {{ $s->status }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="muted">لا توجد جلسات</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- استدعاء مكتبة html2canvas لتوليد الصورة -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            JsBarcode("#clientBarcode", "{{ $client->id }}", {
                format: "CODE128",
                lineColor: "#000000",
                width: 2,
                height: 60,
                displayValue: true,
                fontSize: 18,
                background: "#ffffff",
                margin: 10
            });

        });
    </script>
    <script>
        document.getElementById('shareWhatsappBtn').addEventListener('click', async function() {
            // 1. نسخ الباركود المولد بـ JsBarcode إلى الكارت المخفي
            const originalSvg = document.getElementById('clientBarcode');
            const targetDiv = document.getElementById('barcodeTarget');

            if (originalSvg) {
                targetDiv.innerHTML = originalSvg.outerHTML;
            }

            // تلميح للمستخدم
            this.innerText = 'جاري تحضير الكارت...';

            // 2. تحويل عنصر الـ HTML المخفي إلى صورة عبر html2canvas
            const captureArea = document.getElementById('hiddenCaptureCard');

            try {
                const canvas = await html2canvas(captureArea, {
                    scale: 2, // جودة أعلى للصورة
                    useCORS: true
                });

                // 3. تحويل الكانفاس إلى Blob ونسخه إلى الـ Clipboard (الحافظة)
                canvas.toBlob(async function(blob) {
                    try {
                        const item = new ClipboardItem({
                            "image/png": blob
                        });
                        await navigator.clipboard.write([item]);

                        // 4. إعداد الرسالة النصية والتوجه للواتساب
                        const clientPhone = "{{ $client->phone }}";
                        // تعديل الرقم ليتناسب مع الصيغة الدولية إذا لزم الأمر
                        let formattedPhone = clientPhone.trim();
                        if (formattedPhone.startsWith('0')) {
                            formattedPhone = '2' + formattedPhone; // صيغة مصر كمثال، عدلها حسب بلدك
                        }

                        const textMessage = encodeURIComponent(
                            "أهلاً بك في Hive! ✨"
                        );
                        const whatsappUrl = `https://wa.me/${formattedPhone}?text=${textMessage}`;

                        // فتح الواتساب في نافذة جديدة
                        window.open(whatsappUrl, '_blank');
                    } catch (err) {
                        alert(
                            "عذراً، حدث خطأ أثناء نسخ الصورة تلقائياً. تأكد من إعطاء الصلاحية للمتصفح."
                        );
                        console.error(err);
                    }
                }, 'image/png');

            } catch (error) {
                console.error("خطأ في توليد الصورة:", error);
            } finally {
                // إعادة نص الزر لطبيعته
                this.innerHTML =
                    '<span class="whatsapp-ico">💬</span><span class="whatsapp-txt">مشاركة الكارت</span>';
            }
        });
    </script>
    <div class="modal fade" id="blackListModal" tabindex="-1">

        <div class="modal-dialog modal-dialog-centered">

            <form action="{{ route('black-list.store',  $client->id) }}" method="POST" class="modal-content">

                @csrf

                <div class="modal-header">

                    <h5>
                        🚫 إضافة العميل للقائمة السوداء
                    </h5>

                </div>

                <div class="modal-body">

                    <label class="mb-2">
                        سبب الحظر
                    </label>

                    <textarea name="reason" rows="5" class="form-control" required placeholder="اكتب سبب الحظر..."></textarea>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">

                        إلغاء

                    </button>

                    <button class="btn btn-danger">

                        🚫 حظر العميل

                    </button>

                </div>

            </form>

        </div>

    </div>
@endsection

@section('style')


    <style>
        body {
            background: #121212;
            font-family: "Tahoma", sans-serif;
            color: #fff;
        }

        .client-container {
            max-width: 960px;
            margin: 40px auto;
            padding: 20px;
        }

        .card {
            background: #1e1e1e;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.5);
            padding: 26px;
            animation: fadeInUp .6s ease;
            color: #fff;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #333;
            margin-bottom: 16px;
            padding-bottom: 10px;
        }

        .card-header h2 {
            font-size: 24px;
            color: #ffb84d;
            margin: 0;
        }

        .badge {
            background: #ffb84d;
            color: #000;
            padding: 6px 12px;
            border-radius: 30px;
            font-weight: bold;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.2);
        }

        .section h3 {
            color: #ffb84d;
            font-size: 20px;
            margin-bottom: 10px;
        }

        .box {
            background: #2a2a2a;
            padding: 14px 18px;
            border-radius: 12px;
            box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.3);
            margin-bottom: 18px;
            font-size: 15px;
            line-height: 1.6;
        }

        .flex-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
        }

        .status-card {
            background: #1e1e1e;
            padding: 12px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.5);
            display: flex;
            flex-direction: column;
            gap: 8px;
            min-height: 110px;
        }

        .d-check {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }

        .d-check input {
            transform: scale(1.1);
            margin-right: 6px;
        }

        .actions {
            margin-top: 6px;
        }

        .btn.small {
            display: inline-block;
            background: #ffb84d;
            color: #000;
            padding: 8px 12px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            transition: transform .18s;
        }

        .btn.small:hover {
            transform: translateY(-3px);
            background: #fdc36c;
        }

        .muted {
            color: #888;
        }

        .value {
            font-size: 16px;
            font-weight: 700;
            margin-top: 6px;
            color: #fff;
        }

        .row {
            display: flex;
            gap: 20px;
        }

        .col {
            flex: 1;
        }

        .quick-grid {
            display: flex;
            gap: 18px;
        }

        .mini-list {
            list-style: none;
            padding-left: 0;
            margin: 0;
            font-size: 14px;
            color: #fff;
        }

        .mini-list li {
            margin-bottom: 6px;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width:800px) {
            .flex-grid {
                grid-template-columns: 1fr;
            }

            .quick-grid {
                flex-direction: column;
            }
        }

        .barcode-wrapper {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .barcode-wrapper svg {
            background: #fff;
            padding: 14px;
            border-radius: 16px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, .35);
            max-width: 100%;
        }
    </style>
    <style>
        /* تنسيق زر الواتساب */
        .whatsapp-btn {
            background-color: #128C7E;
            color: #fff;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-weight: bold;
            transition: background 0.3s;
        }

        .whatsapp-btn:hover {
            background-color: #075E54;
        }

        /* ==================== ستكايل كارت التصوير (أسود وذهبي محروق) ==================== */
        .premium-capture-card {
            position: absolute;
            top: -9999px;
            /* مخفي عن عين المستخدم ولكنه متاح للمكتبة لتصويره */
            left: -9999px;
            width: 400px;
            background: linear-gradient(135deg, #111111 0%, #1a1a1a 100%);
            border: 3px solid #8e6f3e;
            /* ذهبي محروق */
            border-radius: 15px;
            padding: 25px;
            font-family: 'Cairo', sans-serif;
            direction: rtl;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .brand-header {
            text-align: center;
            margin-bottom: 15px;
        }

        .brand-logo {
            display: block;
            font-size: 32px;
            font-weight: 900;
            color: #bfa36f;
            /* ذهبي مطفي/محروق بريميوم */
            letter-spacing: 3px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
        }

        .brand-slogan {
            font-size: 14px;
            color: #d4c5a9;
            font-style: italic;
        }

        .premium-divider {
            height: 1px;
            background: linear-gradient(to left, transparent, #8e6f3e, transparent);
            margin: 15px 0;
        }

        .client-details {
            margin-bottom: 20px;
        }

        .detail-item {
            margin-bottom: 10px;
            font-size: 16px;
            color: #e5dac3;
            display: flex;
            justify-content: flex-start;
            gap: 10px;
        }

        .detail-label {
            color: #8e6f3e;
            /* ذهبي غامق */
            font-weight: bold;
        }

        .capture-barcode-wrapper {
            background: #fff;
            /* الباركود يفضل خلفية بيضاء ليقرأه الاسكانر بسهولة */
            padding: 15px;
            border-radius: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
            border: 2px solid #8e6f3e;
        }

        .capture-barcode-wrapper svg {
            width: 100% !important;
            height: auto !important;
        }

        /*==========================
     BLACK LIST
    ===========================*/

        .blacklist-card {

            margin-top: 25px;

            border-radius: 18px;

            overflow: hidden;

            background: #181818;

            border: 1px solid rgba(255, 255, 255, .08);

            box-shadow: 0 15px 35px rgba(0, 0, 0, .25);

        }

        .blacklist-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            padding: 18px 20px;

            gap: 15px;

            flex-wrap: wrap;

        }

        .blacklist-header.success {

            background: linear-gradient(135deg, #18331d, #224829);

        }

        .blacklist-header.danger {

            background: linear-gradient(135deg, #511616, #742020);

        }

        .status-dot {

            width: 12px;

            height: 12px;

            display: inline-block;

            border-radius: 50%;

            background: #ff3c3c;

            margin-left: 8px;

            box-shadow: 0 0 10px red;

        }

        .status-dot.green {

            background: #39d353;

            box-shadow: 0 0 10px #39d353;

        }

        .blacklist-header span {

            color: #fff;

            font-size: 17px;

            font-weight: bold;

        }

        .blacklist-body {

            padding: 22px;

        }

        .blacklist-item {

            display: flex;

            flex-direction: column;

            gap: 8px;

            margin-bottom: 20px;

        }

        .blacklist-item span {

            color: #999;

            font-size: 14px;

        }

        .blacklist-item strong {

            color: #ff9d9d;

            font-size: 18px;

            line-height: 1.7;

        }

        .blacklist-grid {

            display: grid;

            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));

            gap: 18px;

        }

        .blacklist-grid div {

            background: #202020;

            border-radius: 12px;

            padding: 15px;

        }

        .blacklist-grid small {

            color: #999;

        }

        .blacklist-grid p {

            margin-top: 8px;

            font-weight: bold;

            color: white;

        }

        .blacklist-add-btn {

            border: none;

            background: #dc3545;

            color: white;

            padding: 12px 18px;

            border-radius: 10px;

            font-weight: bold;

            transition: .25s;

        }

        .blacklist-add-btn:hover {

            transform: translateY(-3px);

            background: #b51f2f;

        }

        .blacklist-remove-btn {

            border: none;

            background: #28a745;

            color: white;

            padding: 12px 18px;

            border-radius: 10px;

            font-weight: bold;

            transition: .25s;

        }

        .blacklist-remove-btn:hover {

            transform: translateY(-3px);

            background: #208738;

        }

        /*=====================
    Responsive
    ======================*/

        @media(max-width:768px) {

            .blacklist-header {

                flex-direction: column;

                align-items: flex-start;

            }

            .blacklist-add-btn,
            .blacklist-remove-btn {

                width: 100%;

            }

            .blacklist-grid {

                grid-template-columns: 1fr;

            }

            .blacklist-item strong {

                font-size: 16px;

            }

        }
    </style>
@endsection
