<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>إيصال Hive</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 5mm;
        }

        body {
            font-family: "Tahoma", sans-serif;
            font-size: 14px;
            color: #000;
            direction: rtl;
            margin: 0;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
        }

        .header h2 {
            margin: 0;
            font-size: 20px;
            letter-spacing: 1px;
            font-weight: 900;
        }

        .header p {
            margin: 2px 0;
            font-size: 12px;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        .items th,
        .items td {
            padding: 5px 2px;
            text-align: center;
        }

        .items th {
            border-bottom: 1px dashed #000;
            font-size: 13px;
        }

        .items td {
            font-size: 12px;
        }

        .total {
            border-top: 2px dashed #000;
            margin-top: 5px;
            padding-top: 5px;
            text-align: center;
            font-size: 15px;
            font-weight: bold;
        }

        /* 🐝 تنسيق قسم الملاحظات الخاص بـ Hive */
        .hive-notes {
            margin-top: 12px;
            padding: 8px;
            border: 1px solid #000;
            border-radius: 6px;
            background-color: #fafafa;
            text-align: center;
        }

        .hive-notes-title {
            font-size: 12px;
            font-weight: 900;
            margin-bottom: 4px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
            display: inline-block;
        }

        .hive-notes-content {
            font-size: 13px;
            font-weight: bold;
            white-space: pre-wrap;
        }

        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 12px;
            border-top: 1px dashed #000;
            padding-top: 8px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Hive 🐝</h2>
        <p>مكانك المثالي للعمل والإبداع 💡</p>
        <p>استمتع بكل لحظة هنا!</p>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>العدد</th>
                <th>المنتج</th>
                <th>المجموع</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach ($items as $item)
                @php $grandTotal += $item['total']; @endphp
                <tr>
                    <td>{{ $item['qty'] }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['total'] }} ج.م</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        الإجمالي: {{ $grandTotal }} ج.م
    </div>

    @if (isset($note) && !empty(trim($note)))
        <div class="hive-notes">
            <div class="hive-notes-title">Hive Special Notes</div>
            <div class="hive-notes-content">{{ $note }}</div>
        </div>
    @endif

    <div class="footer">
        شكرًا لزيارتك Hive 💼<br>
        نتطلع لرؤيتك مرة أخرى!
    </div>

    <script>
        // إعطاء المتصفح لحظة لتحميل الخطوط ثم الطباعة
        setTimeout(() => {
            window.print();
        }, 300);
    </script>
</body>

</html>
