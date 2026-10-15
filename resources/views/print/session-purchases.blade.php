<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طباعة طلبات الجلسة #{{ $session->id }}</title>
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
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 5px 0;
            /* ألوان الثيم محاكاة بالأسود والأبيض للطباعة */
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
        }

        .header p {
            margin: 3px 0;
            font-size: 12px;
        }

        .info-section {
            margin-bottom: 15px;
            font-size: 13px;
            font-weight: 600;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
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
            width: 40%;
        }

        .totals {
            margin-top: 10px;
            border-top: 2px dashed #000;
            padding-top: 10px;
        }

        .totals .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 16px;
            font-weight: 700;
        }

        .notes-section {
            margin-top: 15px;
            padding: 8px;
            border: 1px dotted #000;
            font-size: 12px;
            border-radius: 4px;
        }

        .warning-box {
            margin-top: 20px;
            padding: 10px;
            background-color: #000;
            color: #fff;
            text-align: center;
            font-size: 15px;
            font-weight: 700;
            border-radius: 6px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 11px;
            color: #555;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }

        /* أزرار التحكم (لا تظهر في الطباعة) */
        .no-print {
            text-align: center;
            margin: 20px 0;
        }

        .print-btn {
            background: #ffb84d;
            /* لون الثيم */
            color: #000;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
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
        <button class="print-btn" onclick="window.print()">🖨️ طباعة الإيصال</button>
    </div>

    <div class="ticket">
        <div class="header">
            <h1>Hive Work Space</h1>
            <p>طلب إضافة مشتريات (أوردر داخلي)</p>
            <p>التاريخ: {{ now()->format('Y-m-d h:i A') }}</p>
        </div>

        <div class="info-section">
            <div class="info-row">
                <span>رقم الجلسة:</span>
                <span>#{{ $session->id }}</span>
            </div>
            <div class="info-row">
                <span>اسم العميل:</span>
                <span>{{ $session->client->name ?? 'عميل غير مسجل' }}</span>
            </div>
        </div>

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

        <div class="totals">
            <div class="total-row">
                <span>إجمالي الأوردر:</span>
                <span>{{ $total }} ج.م</span>
            </div>
        </div>


        <div class="warning-box">
            ⚠️ الدفع عند إنهاء الجلسة
        </div>

        <div class="footer">
            <p>تم استخراج هذا الإيصال كطلب داخلي</p>
            <p>شكراً لزيارتكم Hive Work Space</p>
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500); // تأخير بسيط لضمان تحميل الخطوط
        }
    </script>
</body>

</html>
