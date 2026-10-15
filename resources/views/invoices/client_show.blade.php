@extends('layouts.app_page')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

@section('title', 'عرض الفاتورة')

@section('content')
    <div class="invoice-container">
        <div class="card">
            {{-- رأس الفاتورة --}}
            <div class="card-header">
                <div class="header-info">
                    <h2>فاتورة رقم: <span>{{ $invoice->invoice_number }}</span></h2>
                    <span class="badge">{{ $invoice->created_at->format('d/m/Y') }}</span>
                </div>
                <div class="header-actions">
                    <button onclick="printInvoice()" class="btn btn-print">
                        🖨 طباعة
                    </button>
                    @if (Auth::check() && Auth::user()->role === 'admin')
                        <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn btn-edit">
                            ✏️ تعديل
                        </a>
                    @endif
                </div>
            </div>

            {{-- بيانات العميل --}}
            @if (!in_array($invoiceType, ['product']))
                <div class="section client-info">
                    <div class="box">
                        <p><strong>👤 العميل:</strong> {{ $invoice->client->name ?? 'غير معروف' }}
                            <span class="client-id">({{ $invoice->client->id ?? '-' }})</span>
                        </p>
                    </div>
                </div>
            @endif

            {{-- المشتريات الموحدة --}}
            @if ($purchaseItems->isNotEmpty())
                <div class="section">
                    <h3>🛒 المشتريات</h3>
                    <div class="table-responsive">
                        <div class="table-header">
                            <span>المنتج</span>
                            <span>الكمية</span>
                            <span>السعر</span>
                            <span>الإجمالي</span>
                        </div>
                        @foreach ($purchaseItems as $item)
                            <div class="table-row">
                                <span>{{ $item->name }}</span>
                                <span>{{ $item->qty }}</span>
                                <span>{{ number_format($item->price, 2) }} ج</span>
                                <span><strong>{{ number_format($item->total, 2) }} ج</strong></span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- الاشتراكات --}}
            @if (in_array($invoiceType, ['subscription', 'mixed']) && $groupedItems['subscription']->isNotEmpty())
                <div class="section">
                    <h3>🎟 فاتورة اشتراك</h3>
                    @foreach ($groupedItems['subscription'] as $item)
                        <div class="box flex-box">
                            <p><strong>اسم الخطة:</strong> {{ $item->name }}</p>
                            <p><strong>السعر:</strong> {{ number_format($item->price, 2) }} ج</p>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- الحجوزات --}}
            @if (in_array($invoiceType, ['booking', 'mixed']) && $groupedItems['booking']->isNotEmpty())
                <div class="section">
                    <h3>📅 جلسة خاصة / حجز</h3>
                    @foreach ($groupedItems['booking'] as $item)
                        <div class="box">
                            <p><strong>القاعة:</strong> {{ $bookingData->hall->name ?? 'غير محدد' }} </p>
                            <p><strong>سعر الساعة:</strong> {{ number_format($hourlyRate ?? 0, 2) }} ج</p>
                            <p id="duration-text-{{ $loop->index }}" class="duration-highlight">مدة الجلسة: جاري الحساب...
                            </p>
                            <p class="item-total"><strong>الإجمالي:</strong> {{ number_format($item->total, 2) }} ج</p>
                        </div>

                        <script>
                            (function() {
                                const minutes = {{ $actualDurationMinutes ?? 0 }};

                                function formatDurationArabic(minutes) {
                                    if (minutes <= 0) return 'لم تبدأ بعد أو لم تحدد';
                                    if (minutes < 60) return `${minutes} دقيقة`;

                                    const hours = Math.floor(minutes / 60);
                                    const remainingMinutes = minutes % 60;

                                    let hourText = hours === 1 ? 'ساعة' : hours === 2 ? 'ساعتين' : `${hours} ساعات`;
                                    let minuteText = '';

                                    if (remainingMinutes === 15) minuteText = 'وربع';
                                    else if (remainingMinutes === 30) minuteText = 'ونصف';
                                    else if (remainingMinutes === 45) {
                                        hourText = hours === 1 ? 'ساعتين إلا ربع' : `${hours + 1} ساعات إلا ربع`;
                                        return hourText;
                                    } else if (remainingMinutes > 0) {
                                        minuteText = `و ${remainingMinutes} دقيقة`;
                                    }
                                    return `${hourText} ${minuteText}`.trim();
                                }
                                document.getElementById("duration-text-{{ $loop->index }}").innerHTML =
                                    `<strong>مدة الجلسة:</strong> ${formatDurationArabic(minutes)}`;
                            })
                            ();
                        </script>
                    @endforeach
                </div>
            @endif

            {{-- الجلسات --}}
            @if (in_array($invoiceType, ['session', 'mixed']) && $groupedItems['session']->isNotEmpty())
                <div class="section">
                    <h3>🎮 جلسة</h3>
                    @foreach ($groupedItems['session'] as $item)
                        <div class="box flex-box">
                            <p><strong>سعر الساعة:</strong> {{ number_format($item->price, 2) }} ج</p>
                            <p><strong>عدد الساعات:</strong> {{ $item->qty }}</p>
                            <p class="item-total"><strong>الإجمالي:</strong> {{ number_format($item->total, 2) }} ج</p>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- الدفعات المقدمة والمقدم --}}
            @if ($groupedItems['deposit']->isNotEmpty())
                <div class="section">
                    <h3>💰 الدفعات المقدمة (العربون)</h3>
                    @if ($invoiceType === 'deposit')
                        <div class="box">
                            <p><strong>اسم العميل:</strong> {{ $extraData['client_name'] ?? 'غير معروف' }}</p>
                            <p><strong>موعد الحجز:</strong> {{ $extraData['booking_date'] ?? '-' }}</p>
                        </div>
                    @endif

                    @foreach ($groupedItems['deposit'] as $item)
                        <div class="box deposit-box">
                            <div class="flex-box">
                                <p><strong>المبلغ المدفوع:</strong> {{ number_format($item->total, 2) }} ج</p>
                                <p class="date-text">📅 {{ $item->created_at->format('Y-m-d h:i A') }}</p>
                            </div>
                            @if (!empty($item->notes))
                                <p class="notes">📝 ملاحظات: {{ $item->notes }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- ملخص الفاتورة (الإجمالي والخصم) --}}
            <div class="section summary-section">
                <div class="summary-box">
                    {{-- عرض المجموع قبل الخصم لو فيه خصم --}}
                    @if (isset($invoice->discount_amount) && $invoice->discount_amount > 0)
                        <div class="summary-row subtotal">
                            <span>المجموع الفرعي:</span>
                            <span>{{ number_format($invoice->subtotal ?? $totalAmount, 2) }} ج</span>
                        </div>
                        <div class="summary-row discount">
                            <span>الخصم
                                @if ($invoice->discount_reason)
                                    <small class="discount-reason">({{ $invoice->discount_reason }})</small>
                                @endif
                                :
                            </span>
                            <span>- {{ number_format($invoice->discount_amount, 2) }} ج</span>
                        </div>
                    @endif

                    {{-- الإجمالي النهائي --}}
                    <div class="summary-row final-total">
                        <span>الإجمالي الكلي:</span>
                        <span>{{ number_format($invoice->total ?? $totalAmount - ($invoice->discount_amount ?? 0), 2) }}
                            ج</span>
                    </div>
                </div>
            </div>

            <div class="footer-msg">
                <p>خلينا نشوفك تاني 😊</p>
            </div>
        </div>
    </div>

    <script>
        function printInvoice() {
            const content = document.querySelector(".card").innerHTML;
            const printWindow = window.open('', '', 'width=600,height=800');

            printWindow.document.write(`
                <html dir="rtl">
                <head>
                    <title>طباعة الفاتورة - ${'{{ $invoice->invoice_number }}'}</title>
                    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
                    <style>
                        body {
                            font-family: 'Cairo', sans-serif;
                            direction: rtl;
                            color: #000;
                            padding: 20px;
                            background: #fff;
                        }
                        .header-actions { display: none; } /* إخفاء الأزرار عند الطباعة */
                        .header-info h2 { font-size: 20px; margin: 0 0 10px 0; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
                        .badge { background: #eee; padding: 5px 10px; border-radius: 5px; font-size: 14px; }
                        .section { margin-bottom: 20px; }
                        h3 { font-size: 16px; color: #333; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 10px; }
                        .box { padding: 10px; margin-bottom: 10px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; }
                        .flex-box { display: flex; justify-content: space-between; flex-wrap: wrap; }
                        .table-responsive { width: 100%; }
                        .table-header, .table-row { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; padding: 8px; font-size: 14px; text-align: center; }
                        .table-header { font-weight: bold; background: #f0f0f0; border-bottom: 2px solid #ccc; }
                        .table-row { border-bottom: 1px dashed #ccc; }
                        .summary-box { border: 2px solid #000; padding: 15px; border-radius: 8px; margin-top: 20px; background: #fafafa; }
                        .summary-row { display: flex; justify-content: space-between; font-size: 16px; margin-bottom: 8px; }
                        .summary-row.discount { color: #d9534f; }
                        .summary-row.final-total { font-weight: bold; font-size: 20px; border-top: 1px solid #ccc; padding-top: 10px; margin-top: 10px; }
                        .footer-msg { text-align: center; margin-top: 30px; font-weight: bold; font-size: 16px; }
                    </style>
                </head>
                <body>
                    ${content}
                    <script>
                        window.onload = function() {
                            window.print();
                            window.close();
                        };
                    <\/script>
                </body>
                </html>
            `);
            printWindow.document.close();
        }
    </script>
@endsection

@section('style')
    <style>
        /* 🌌 ثيم Hive المظلم والمطور */
        body {
            background-color: #0f0f0f;
            font-family: "Cairo", "Tahoma", sans-serif;
            color: #f5f5f5;
            margin: 0;
            padding: 0;
            direction: rtl;
        }

        .invoice-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }

        .card {
            background: #1a1a1a;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
            padding: 30px;
            border: 1px solid rgba(255, 184, 77, 0.1);
            animation: fadeIn 0.5s ease-in-out;
        }

        /* 🔹 رأس الفاتورة */
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            border-bottom: 1px solid rgba(255, 184, 77, 0.2);
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .header-info h2 {
            font-size: 22px;
            color: #fff;
            margin: 0 0 8px 0;
        }

        .header-info h2 span {
            color: #ffb84d;
        }

        .badge {
            background: rgba(255, 184, 77, 0.15);
            color: #ffb84d;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            border: 1px solid rgba(255, 184, 77, 0.3);
        }

        .header-actions {
            display: flex;
            gap: 10px;
        }

        /* 🔘 الأزرار */
        .btn {
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: bold;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            border: none;
        }

        .btn-print {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-print:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .btn-edit {
            background: linear-gradient(135deg, #ffb84d, #f59e0b);
            color: #111;
        }

        .btn-edit:hover {
            box-shadow: 0 4px 15px rgba(255, 184, 77, 0.4);
            transform: translateY(-2px);
        }

        /* 🧩 العناوين والصناديق */
        .section {
            margin-bottom: 25px;
        }

        .section h3 {
            color: #ffb84d;
            font-size: 18px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .box {
            background: #242424;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #333;
            margin-bottom: 10px;
            transition: background 0.3s ease;
        }

        .box:hover {
            background: #2a2a2a;
        }

        .box p {
            margin: 5px 0;
            font-size: 14px;
            color: #ddd;
        }

        .flex-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .item-total {
            color: #ffb84d;
            font-size: 15px;
        }

        .deposit-box {
            border-right: 4px solid #10b981;
        }

        /* 🛒 الجداول والمشتريات */
        .table-responsive {
            background: #242424;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #333;
        }

        .table-header,
        .table-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            text-align: center;
            padding: 12px;
            font-size: 14px;
        }

        .table-header {
            background: rgba(255, 184, 77, 0.1);
            color: #ffb84d;
            font-weight: 700;
            border-bottom: 1px solid #444;
        }

        .table-row {
            color: #ccc;
            border-bottom: 1px solid #333;
        }

        .table-row:last-child {
            border-bottom: none;
        }

        /* 🧾 ملخص الفاتورة */
        .summary-box {
            background: linear-gradient(145deg, #1f1f1f, #181818);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid rgba(255, 184, 77, 0.2);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            font-size: 15px;
            color: #bbb;
        }

        .summary-row.discount {
            color: #ef4444;
        }

        .discount-reason {
            font-size: 12px;
            opacity: 0.8;
            margin-right: 5px;
        }

        .summary-row.final-total {
            font-size: 22px;
            color: #ffb84d;
            font-weight: bold;
            border-top: 1px dashed #444;
            margin-top: 10px;
            padding-top: 15px;
        }

        .footer-msg {
            text-align: center;
            margin-top: 30px;
            color: #888;
            font-size: 15px;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* 📱 الموبايل (Responsive) */
        @media (max-width: 768px) {
            .invoice-container {
                padding: 10px;
                margin: 20px auto;
            }

            .card {
                padding: 20px;
            }

            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .header-actions {
                width: 100%;
                justify-content: stretch;
            }

            .btn {
                flex: 1;
            }

            .table-header,
            .table-row {
                grid-template-columns: 1.5fr 1fr 1fr 1fr;
                font-size: 12px;
                padding: 10px 5px;
            }

            .summary-row {
                font-size: 14px;
            }

            .summary-row.final-total {
                font-size: 18px;
            }
        }
    </style>
@endsection
