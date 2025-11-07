@extends('layouts.app_page')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

@section('title', 'عرض الفاتورة')

@section('content')
    <div class="subscription-container">
        <div class="card">
            {{-- رأس الفاتورة --}}
            <div class="card-header">
                <h2>فاتورة رقم: {{ $invoice->invoice_number }}</h2>
                <span class="badge">{{ $invoice->created_at->format('d/m/Y') }}</span>
            </div>
            {{-- بيانات العميل إن لم تكن مشتريات فقط --}}
            @if (!in_array($invoiceType, ['product']))
                <div class="section">
                    <div class="box">
                        <p><strong>العميل:</strong> {{ $invoice->client->name ?? 'غير معروف' }}
                            {{ $invoice->client->id ?? '-' }}</p>
                    </div>
                </div>
            @endif
            {{-- حسب نوع الفاتورة --}}
            @if (in_array($invoiceType, ['product', 'mixed']))
                <div class="section">
                    <h3>المشتريات</h3>
                    @foreach ($groupedItems['product'] as $item)
                        <div class="box">
                            <p>اسم المنتج: {{ $item->name }}</p>
                            <p>الكمية: {{ $item->qty }}</p>
                            <p>سعر الوحدة: {{ $item->price }} ج</p>
                            <p>الإجمالي: {{ $item->total }} ج</p>
                        </div>
                    @endforeach
                </div>
            @endif

            @if (in_array($invoiceType, ['subscription', 'mixed']))
                <div class="section">
                    <h3>فاتورة اشتراك</h3>
                    @foreach ($groupedItems['subscription'] as $item)
                        <div class="box">
                            <p>اسم الخطة: {{ $item->name }}</p>
                            <p>السعر: {{ $item->price }} ج</p>
                        </div>
                    @endforeach
                </div>
            @endif

            @if (in_array($invoiceType, ['booking', 'mixed']))
                <div class="section">
                    <h3>جلسة خاصة / حجز</h3>
                    @foreach ($groupedItems['booking'] as $item)
                        <div class="box">
                            <p>القاعة: {{ $bookingData->hall->name }} </p>

                            <p>سعر الساعة: {{ $hourlyRate }} ج</p>
                            <p id="duration-text-{{ $loop->index }}">مدة الجلسة: ...</p>
                            <p>الإجمالي: {{ $item->total }} ج</p>
                        </div>

                        <script>
                            (function() {
                                const minutes = {{ $actualDurationMinutes ?? 0 }};

                                function formatDurationArabic(minutes) {
                                    if (minutes < 60) {
                                        return `${minutes} دقيقة`;
                                    }
                                    const hours = Math.floor(minutes / 60);
                                    const remainingMinutes = minutes % 60;
                                    let hourText = '';
                                    if (hours === 1) hourText = 'ساعة';
                                    else if (hours === 2) hourText = 'ساعتين';
                                    else hourText = `${hours} ساعات`;

                                    let minuteText = '';
                                    if (remainingMinutes === 15) {
                                        minuteText = 'وربع';
                                    } else if (remainingMinutes === 30) {
                                        minuteText = 'ونصف';
                                    } else if (remainingMinutes === 45) {
                                        minuteText = 'إلا ربع';
                                        // نزيد ساعة لأن "إلا ربع" يعني قبل الساعة التالية بربع
                                        if (hours === 1) hourText = 'ساعتين إلا ربع';
                                        else hourText = `${hours + 1} ساعات إلا ربع`;
                                        return hourText;
                                    } else if (remainingMinutes > 0) {
                                        minuteText = `و${remainingMinutes} دقيقة`;
                                    }
                                    return `${hourText} ${minuteText}`.trim();
                                }

                                const formatted = formatDurationArabic(minutes);
                                document.getElementById("duration-text-{{ $loop->index }}").textContent =
                                    `مدة الجلسة: ${formatted}`;
                            })
                            ();
                        </script>
                    @endforeach
                </div>
                @if ($groupedItems['deposit']->isNotEmpty())
                    <div class="section">
                        <h3>💰 الدفعات المقدمة</h3>

                        @foreach ($groupedItems['deposit'] as $item)
                            <div class="box deposit-box">
                                <p>المبلغ المدفوع مقدمًا: <strong>{{ number_format($item->total, 2) }} ج</strong></p>
                                @if (!empty($item->notes))
                                    <p>ملاحظات: {{ $item->notes }}</p>
                                @endif
                                <p>تاريخ الدفع: {{ $item->created_at->format('Y-m-d H:i') }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- @if ($isHasPurchase == true)
                    <div class="section">
                        <h3>🛒 المشتريات</h3>
                        <div class="purchase-list">
                            <div class="purchase-header">
                                <span>المنتج</span>
                                <span>الكمية</span>
                                <span>السعر</span>
                                <span>الإجمالي</span>
                            </div>
                            @foreach ($groupedItems['product'] as $item)
                                <div class="purchase-row">
                                    <span>{{ $item->name }}</span>
                                    <span>{{ $item->qty }}</span>
                                    <span>{{ number_format($item->price, 2) }} ج</span>
                                    <span>{{ number_format($item->total, 2) }} ج</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif --}}

            @endif

            @if (in_array($invoiceType, ['session', 'mixed']))
                <div class="section">
                    <h3>جلسة</h3>
                    @foreach ($groupedItems['session'] as $item)
                        <div class="box">
                            <p>سعر الساعة: {{ $item->price }} ج</p>
                            <p>عدد الساعات: {{ $item->qty }}</p>
                            <p>الإجمالي: {{ $item->total }} ج</p>
                        </div>
                    @endforeach
                </div>
                {{-- @if ($isHasPurchase == true)
                    <div class="section">
                        <h3>🛒 المشتريات</h3>
                        <div class="purchase-list">
                            <div class="purchase-header">
                                <span>المنتج</span>
                                <span>الكمية</span>
                                <span>السعر</span>
                                <span>الإجمالي</span>
                            </div>
                            @foreach ($groupedItems['product'] as $item)
                                <div class="purchase-row">
                                    <span>{{ $item->name }}</span>
                                    <span>{{ $item->qty }}</span>
                                    <span>{{ number_format($item->price, 2) }} ج</span>
                                    <span>{{ number_format($item->total, 2) }} ج</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif --}}


            @endif
            {{-- 🛒 قسم المشتريات الموحد --}}
            @if ($purchaseItems->isNotEmpty())
                <div class="section">
                    <h3>🛒 المشتريات</h3>
                    <div class="purchase-list">
                        <div class="purchase-header">
                            <span>المنتج</span>
                            <span>الكمية</span>
                            <span>السعر</span>
                            <span>الإجمالي</span>
                        </div>
                        @foreach ($purchaseItems as $item)
                            <div class="purchase-row">
                                <span>{{ $item->name }}</span>
                                <span>{{ $item->qty }}</span>
                                <span>{{ number_format($item->price, 2) }} ج</span>
                                <span>{{ number_format($item->total, 2) }} ج</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($invoiceType === 'deposit')
                <div class="section">
                    <h3>مقدم الحجز</h3>
                    <div class="box">
                        <p>اسم العميل: {{ $extraData['client_name'] }}</p>
                        <p>موعد الحجز: {{ $extraData['booking_date'] }}</p>
                        @foreach ($groupedItems['deposit'] as $item)
                            <p>المبلغ المدفوع: {{ $item->total }} ج</p>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- السعر الكلي --}}
            <div class="section">
                <h3>الإجمالي الكلي</h3>
                <div class="box price">{{ $totalAmount }} ج</div>
            </div>

            <div class="section">
                <p style="text-align:center; margin-top:20px;">خلينا نشوفك تاني 😊</p>
            </div>
        </div>
    </div>
@endsection



@section('style')


    <style>
        /* 🌌 ثيم Hive المظلم للفواتير */
        body {
            background: #0f0f0f;
            font-family: "Cairo", "Tahoma", sans-serif;
            color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .subscription-container {
            max-width: 850px;
            margin: 50px auto;
            padding: 25px;
        }

        /* 🧾 الكارت الرئيسي */
        .card {
            background: #1a1a1a;
            border-radius: 18px;
            box-shadow: 0 6px 20px rgba(255, 184, 77, 0.08);
            padding: 30px;
            animation: fadeInUp 0.6s ease;
            border: 1px solid rgba(255, 184, 77, 0.08);
        }

        /* 🔹 رأس الفاتورة */
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 184, 77, 0.15);
            margin-bottom: 20px;
            padding-bottom: 10px;
        }

        .card-header h2 {
            font-size: 24px;
            color: #ffb84d;
            /* الذهبي المحروق */
            margin: 0;
            font-weight: 700;
        }

        .badge {
            background: linear-gradient(90deg, #ffb84d, #a86f68);
            color: #000;
            padding: 6px 15px;
            border-radius: 25px;
            font-weight: bold;
            font-size: 13px;
        }

        /* 🧩 العناوين */
        .section h3 {
            color: #ffb84d;
            font-size: 19px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        /* 📦 الصناديق */
        .box {
            background: #111;
            color: #f5f5f5;
            padding: 10px 15px;
            border-radius: 12px;
            border: 1px solid rgba(255, 184, 77, 0.1);
            margin-bottom: 8px;
            box-shadow: inset 0 0 10px rgba(255, 184, 77, 0.03);
            font-size: 13px;
            line-height: 1.8;
        }

        .price {
            font-weight: bold;
            font-size: 20px;
            color: #ffb84d;
        }

        /* 💰 الدفعات المقدمة */
        .deposit-box {
            background: #1e1e1e;
            border: 1px solid rgba(255, 184, 77, 0.15);
        }

        /* 🛒 جدول المشتريات */
        .purchase-list {
            background: #161616;
            border-radius: 10px;
            box-shadow: inset 0 0 10px rgba(255, 184, 77, 0.03);
            padding: 10px;
            font-size: 13px;
        }

        .purchase-header,
        .purchase-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            align-items: center;
            text-align: center;
            padding: 8px 0;
        }

        .purchase-header {
            color: #ffb84d;
            font-weight: bold;
            border-bottom: 1px solid rgba(255, 184, 77, 0.15);
        }

        .purchase-row {
            color: #ddd;
            border-bottom: 1px dashed rgba(255, 184, 77, 0.08);
        }

        .purchase-row:last-child {
            border-bottom: none;
        }

        /* 🧠 تأثير لطيف عند التحميل */
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

        /* 📱 موبايل */
        @media (max-width: 600px) {

            .purchase-header,
            .purchase-row {
                grid-template-columns: 1.5fr 0.8fr 0.8fr 0.8fr;
                font-size: 12px;
            }

            .card {
                padding: 20px;
            }
        }

        /* 🔘 الأزرار */
        .btn {
            background: linear-gradient(90deg, #ffb84d, #a86f68);
            color: #000;
            border: none;
            border-radius: 10px;
            padding: 10px 25px;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn:hover {
            transform: scale(1.05);
            background: linear-gradient(90deg, #a86f68, #ffb84d);
        }

        /* ✨ تنبيهات */
        .alert {
            background: rgba(255, 184, 77, 0.1);
            border: 1px solid rgba(255, 184, 77, 0.3);
            color: #ffb84d;
            border-radius: 8px;
            padding: 10px;
            font-size: 14px;
        }
    </style>
@endsection
