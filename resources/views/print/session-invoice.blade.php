<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة إنهاء الجلسة #{{ $session->id }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        /* =========================================
           إعدادات الطباعة الحرارية (Thermal Printer)
        ========================================= */
        * {
            box-sizing: border-box;
            font-family: 'Cairo', sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: #000;
            font-size: 13px;
        }

        .ticket {
            width: 80mm;
            /* العرض القياسي للطابعات الحرارية */
            max-width: 100%;
            margin: 20px auto;
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 5px 0;
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
        }

        .header p {
            margin: 3px 0;
            font-size: 13px;
            font-weight: 600;
        }

        /* تنسيق الأقسام (بيانات العميل - الوقت) */
        .section-box {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #aaa;
        }

        .section-title {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 8px;
            text-align: center;
            background-color: #000;
            color: #fff;
            padding: 4px;
            border-radius: 4px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            font-size: 13px;
            font-weight: 600;
        }

        /* الجدول */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            text-align: center;
            padding: 6px 2px;
            border-bottom: 1px solid #ddd;
            font-size: 12px;
        }

        th {
            font-weight: 700;
            border-bottom: 2px solid #000;
            border-top: 2px solid #000;
        }

        td.item-name {
            text-align: right;
            width: 45%;
        }

        /* الإجماليات */
        .totals {
            margin-top: 10px;
            border-top: 2px dashed #000;
            padding-top: 10px;
        }

        .totals .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .totals .grand-total {
            font-size: 18px;
            font-weight: 700;
            border-top: 2px solid #000;
            padding-top: 8px;
            margin-top: 5px;
        }

        .payment-status {
            margin-top: 15px;
            padding: 10px;
            text-align: center;
            font-size: 16px;
            font-weight: 700;
            border-radius: 6px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            border: 2px solid #000;
        }

        .status-paid {
            background-color: #000;
            color: #fff;
        }

        .status-unpaid {
            background-color: #fff;
            color: #000;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #555;
            border-top: 1px dashed #000;
            padding-top: 10px;
            font-weight: 600;
        }

        /* أزرار التحكم (لا تظهر في الطباعة) */
        .no-print {
            text-align: center;
            margin: 20px 0;
        }

        .print-btn {
            background: #ffb84d;
            color: #000;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* =========================================
           إخفاء العناصر غير المرغوب فيها عند الطباعة
        ========================================= */
        @media print {
            body {
                background: #fff;
            }

            .ticket {
                margin: 0;
                box-shadow: none;
                border-radius: 0;
                width: 100%;
                padding: 0;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <div class="no-print">
        <button class="print-btn" onclick="window.print()">🖨️ طباعة الفاتورة</button>
    </div>

    <div class="ticket">
        <div class="header">
            <h1>Hive Work Space</h1>
            <p>فاتورة إنهاء جلسة</p>
            <p>تاريخ الإصدار: {{ now()->format('Y-m-d h:i A') }}</p>
        </div>

        <div class="section-box">
            <div class="section-title">👤 بيانات العميل</div>
            <div class="info-row">
                <span>رقم الجلسة:</span>
                <span>#{{ $session->id }}</span>
            </div>
            <div class="info-row">
                <span>الاسم:</span>
                <span>{{ $session->client->name ?? 'عميل غير مسجل' }}</span>
            </div>
            <div class="info-row">
                <span>رقم الهاتف:</span>
                <span>{{ $session->client->phone ?? '---' }}</span>
            </div>
            <div class="info-row">
                <span>عدد الأفراد:</span>
                <span>{{ $session->persons }} فرد</span>
            </div>
        </div>

        @php
            $startTime = \Carbon\Carbon::parse($session->start_time);
            $endTime = $session->end_time ? \Carbon\Carbon::parse($session->end_time) : now();

            // حساب الفرق الساعات والدقائق
            $duration = $startTime->diff($endTime);
            $durationString = $duration->format('%h ساعة و %i دقيقة');
        @endphp

        <div class="section-box">
            <div class="section-title">⏱️ تفاصيل الوقت</div>
            <div class="info-row">
                <span>وقت الدخول:</span>
                <span>{{ $startTime->format('h:i A') }}</span>
            </div>
            <div class="info-row">
                <span>وقت الخروج:</span>
                <span>{{ $session->end_time ? $endTime->format('h:i A') : '---' }}</span>
            </div>
            <div class="info-row" style="margin-top: 5px; font-weight: 700;">
                <span>المدة المستغرقة:</span>
                <span>{{ $durationString }}</span>
            </div>
        </div>

        @if ($purchases->count() > 0)
            <div class="section-title">🛒 المشتريات</div>
            <table>
                <thead>
                    <tr>
                        <th class="item-name">الصنف</th>
                        <th>الكمية</th>
                        <th>السعر</th>
                        <th>الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchases as $item)
                        <tr>
                            <td class="item-name">{{ $item->product->name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $item->product->price }} ج</td>
                            <td>{{ $item->quantity * $item->product->price }} ج</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="section-box" style="text-align: center; font-weight: bold;">
                لا يوجد مشتريات مسجلة لهذه الجلسة
            </div>
        @endif

        <div class="totals">
            <div class="total-row">
                <span>إجمالي المشتريات:</span>
                <span>{{ $totalPurchases }} ج.م</span>
            </div>

            <div class="total-row grand-total">
                <span>الإجمالي المطلوب:</span>
                <span>{{ $totalPurchases }} ج.م</span>
            </div>
        </div>

        <div class="payment-status {{ $session->isPayed ? 'status-paid' : 'status-unpaid' }}">
            {{ $session->isPayed ? '✅ تم الدفع (خالصة)' : '⚠️ بانتظار الدفع' }}
        </div>

        <div class="footer">
            <p>سعدنا بتواجدك معنا في Hive Work Space</p>
            <p>نتمنى رؤيتك قريباً 💛</p>
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 800); // تأخير بسيط لضمان تحميل الخطوط بشكل كامل قبل استدعاء نافذة الطباعة
        }
    </script>
</body>

</html>
