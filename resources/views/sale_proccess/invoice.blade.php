<!-- resources/views/sale_proccess/invoice.blade.php -->
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta charset="UTF-8">
    <title>إتمام الفاتورة</title>
    <style>
        :root {
            --bg-start: #fdfdfd;
            --bg-end: #bdbdbd;
            --card-bg: #ffffff;
            --muted: #888;
            --accent1: #ff416c;
            --accent2: #ff4b2b;
            --pill-bg: #cfcfcf;
            --success: #2e8b57;
            --card-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
        }

        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: "Tahoma", sans-serif;
            -webkit-font-smoothing: antialiased;
            background: linear-gradient(to bottom, var(--bg-start), var(--bg-end));
            color: #222;
        }

        .page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 18px;
            box-sizing: border-box;
            gap: 18px;
            align-items: center;
        }

        h2.page-title {
            margin: 0;
            padding: 6px 12px;
            font-size: 20px;
            text-align: center;
        }

        .back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #e5c6c3;
            border-radius: 50%;
            padding: 13px;
            cursor: pointer;
            transition: transform 0.2s;
            z-index: 2000;
            border: none;
            font-weight: bold;
        }

        .invoice-container {
            width: 100%;
            max-width: 1100px;
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            padding: 20px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .invoice-header {
            display: flex;
            gap: 12px;
            align-items: center;
            justify-content: space-between;
        }

        .summary {
            background: var(--pill-bg);
            padding: 10px 22px;
            border-radius: 25px;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        /* الجدول للديسكتوب */
        .table-wrapper {
            width: 100%;
            border-radius: 8px;
            border: 1px solid rgba(0, 0, 0, 0.06);
        }

        table.invoice-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.invoice-table th {
            padding: 12px 10px;
            background: #fafafa;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }

        table.invoice-table td {
            padding: 12px 10px;
            text-align: center;
            border-bottom: 1px solid rgba(0, 0, 0, 0.03);
        }

        td.total {
            color: var(--success);
            font-weight: 700;
        }

        /* الكروت للموبايل */
        .cards-wrapper {
            display: none;
            width: 100%;
            gap: 12px;
        }

        .item-card {
            display: flex;
            gap: 12px;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(180deg, #fff, #fafafa);
            border-radius: 12px;
            padding: 12px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(0, 0, 0, 0.04);
        }

        .item-badge {
            background: rgba(0, 0, 0, 0.06);
            border-radius: 10px;
            padding: 8px;
            font-weight: 700;
        }

        .item-name {
            font-weight: 800;
            font-size: 15px;
        }

        .item-total {
            color: var(--success);
            font-weight: 800;
        }

        /* قسم الإجماليات والخصم الاحترافي */
        .totals-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 16px 20px;
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            width: 100%;
            max-width: 350px;
            align-self: flex-end;
            border: 1px solid rgba(0, 0, 0, 0.04);
            box-sizing: border-box;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 15px;
            font-weight: bold;
            color: #444;
        }

        .discount-row {
            color: var(--accent1);
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .discount-controls {
            display: flex;
            width: 100%;
            gap: 8px;
        }

        .discount-select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
            font-size: 14px;
            outline: none;
            background: #fff;
            flex: 1;
            transition: 0.2s;
        }

        .discount-select:focus {
            border-color: var(--accent1);
        }

        .discount-input {
            width: 80px;
            padding: 8px 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            text-align: center;
            font-family: inherit;
            font-size: 15px;
            font-weight: bold;
            color: var(--accent1);
            transition: all 0.2s;
            outline: none;
        }

        .discount-input:focus {
            border-color: var(--accent1);
            box-shadow: 0 0 0 3px rgba(255, 65, 108, 0.15);
        }

        .discount-reason {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
            font-size: 13px;
            box-sizing: border-box;
            transition: 0.2s;
        }

        .discount-reason:focus {
            border-color: var(--accent1);
            outline: none;
        }

        .net-total-row {
            font-size: 18px;
            font-weight: 900;
            color: var(--success);
            border-top: 1px dashed #ccc;
            padding-top: 12px;
            margin-top: 4px;
        }

        /* الأزرار */
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
        }

        button {
            border: none;
            border-radius: 25px;
            padding: 12px 22px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.25s;
        }

        .btn-cancel {
            background: #e7b4b4;
            color: white;
        }

        .btn-done {
            background: linear-gradient(135deg, var(--accent1), var(--accent2));
            color: white;
            font-size: 16px;
            font-weight: 800;
            border-radius: 50px;
            padding: 13px 26px;
            box-shadow: 0 0 15px rgba(255, 65, 108, 0.6);
        }

        .btn-done:hover {
            transform: scale(1.03);
            box-shadow: 0 0 25px rgba(255, 65, 108, 0.9);
        }

        .sticky-actions-mobile {
            display: none;
        }

        /* المودلز */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 3000;
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 20px;
            width: 90%;
            max-width: 400px;
            text-align: center;
        }

        .payment-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 15px;
        }

        .pay-btn {
            padding: 20px;
            border: 2px solid #eee;
            border-radius: 15px;
            cursor: pointer;
            transition: 0.2s;
            font-weight: bold;
        }

        .pay-btn:hover {
            border-color: var(--accent1);
            background: #fff5f6;
        }

        .btn-close-modal {
            margin-top: 20px;
            background: none;
            color: #888;
            text-decoration: underline;
        }

        @media (max-width: 640px) {

            .table-wrapper,
            .action-bar {
                display: none;
            }

            .cards-wrapper {
                display: grid;
                grid-template-columns: 1fr;
                padding-bottom: 15px;
            }

            .totals-section {
                max-width: 100%;
                margin-bottom: 80px;
                align-self: center;
            }

            .sticky-actions-mobile {
                display: flex;
                position: fixed;
                left: 0;
                right: 0;
                bottom: 0;
                padding: 10px;
                gap: 8px;
                justify-content: center;
                background: white;
                box-shadow: 0 -8px 24px rgba(0, 0, 0, 0.08);
                z-index: 60;
            }

            .sticky-actions-mobile .btn-done {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="page">
        <h2 class="page-title">إتمام عملية البيع</h2>

        <form action="{{ route('main.create') }}">
            <button class="back-btn">الرئيسية</button>
        </form>

        <div class="invoice-container">
            @php
                $grandTotal = 0;
                foreach ($items as $item) {
                    $grandTotal += $item['total'];
                }
            @endphp

            <div class="invoice-header">
                <div class="header-left">
                    <div class="summary">
                        الصافي: <span style="margin-right:8px;" id="headerTotalValue">{{ $grandTotal }} ج.م</span>
                    </div>
                </div>
            </div>

            <!-- جدول الديسكتوب -->
            <div class="table-wrapper">
                <table class="invoice-table">
                    <thead>
                        <tr>
                            <th>عدد</th>
                            <th>المنتج</th>
                            <th>سعر الوحدة</th>
                            <th>المجموع</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td>{{ $item['qty'] }}</td>
                                <td>{{ $item['name'] }}</td>
                                <td>{{ $item['price'] ?? '-' }}</td>
                                <td class="total">{{ $item['total'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- كروت الموبايل -->
            <div class="cards-wrapper">
                @foreach ($items as $item)
                    <div class="item-card">
                        <div style="display:flex; gap: 10px; align-items:center;">
                            <div class="item-badge">{{ $item['qty'] }}</div>
                            <div class="item-name">{{ $item['name'] }}</div>
                        </div>
                        <div style="text-align: left;">
                            <div style="font-weight: 700;">{{ $item['price'] ?? '-' }}</div>
                            <div class="item-total">{{ $item['total'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <form id="invoiceForm">
                @csrf
                <input type="hidden" name="client_id" value="{{ $client->id ?? '' }}">
                <input type="hidden" name="notes" value="عملية بيع منفصلة">

                @foreach ($items as $index => $item)
                    <input type="hidden" name="items[{{ $index }}][item_type]" value="product">
                    <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item['id'] }}">
                    <input type="hidden" name="items[{{ $index }}][qty]" value="{{ $item['qty'] }}">
                @endforeach

                <!-- قسم الخصم والإجماليات -->
                <div style="width: 100%; margin-top: 10px; text-align: right;">
                    <label for="hiveNote"
                        style="font-weight: 800; font-size: 14px; color: var(--accent1); margin-bottom: 5px; display: block;">
                        🐝 Hive Special Notes:
                    </label>
                    <textarea id="hiveNote" name="note" rows="2"
                        style="width: 100%; border: 1px solid #ddd; border-radius: 8px; padding: 10px; font-family: inherit; font-size: 14px; resize: none; outline: none; box-sizing: border-box; transition: 0.3s;"
                        placeholder="اكتب ملاحظة مميزة للعميل لتطبع على الفاتورة..."></textarea>
                </div>
                <div class="totals-section">
                    <div class="total-row">
                        <span>الإجمالي قبل الخصم:</span>
                        <span>{{ $grandTotal }} ج.م</span>
                    </div>

                    {{-- ✅ التحقق من الصلاحيات هنا (إظهار الخصم للمدير والمشرف فقط) --}}
                    @if (auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('supervisor')))
                        <div class="total-row discount-row">
                            <div style="display:flex; justify-content:space-between; width:100%;">
                                <span>إضافة خصم:</span>
                            </div>
                            <div class="discount-controls">
                                <select name="discount_type" id="discountType" class="discount-select">
                                    <option value="fixed">مبلغ ثابت (ج.م)</option>
                                    <option value="percentage">نسبة مئوية (%)</option>
                                </select>
                                <input type="number" id="discountInput" name="discount_value" value="0"
                                    min="0" step="0.5" class="discount-input" placeholder="0">
                            </div>
                            <input type="text" name="discount_reason" placeholder="سبب الخصم (اختياري)"
                                class="discount-reason">
                        </div>
                    @else
                        {{-- إذا لم يكن يمتلك صلاحية، نرسل القيم افتراضياً بـ 0 --}}
                        <input type="hidden" name="discount_value" value="0">
                        <input type="hidden" name="discount_type" value="fixed">
                    @endif

                    <div class="total-row net-total-row">
                        <span>الصافي المطلوب:</span>
                        <span id="netTotalDisplay">{{ $grandTotal }} ج.م</span>
                    </div>
                </div>

                <!-- أزرار الديسكتوب -->
                <div class="action-bar">
                    <button type="button" class="btn-cancel"
                        onclick="window.location.replace('{{ route('sale_proccess.create') }}')">إلغاء</button>
                    <button type="submit" class="btn-done">إتمام الدفع والفاتورة</button>
                </div>
            </form>

            <!-- فورم الطباعة -->
            <form id="printForm" action="{{ route('invoices.print') }}" method="POST" target="_blank"
                style="display:none;">
                @csrf
                <input type="hidden" name="discount" id="printDiscountInput" value="0">
                <input type="hidden" name="note" id="printNoteInput" value="">
                @foreach ($items as $index => $item)
                    <input type="hidden" name="items[{{ $index }}][qty]" value="{{ $item['qty'] }}">
                    <input type="hidden" name="items[{{ $index }}][name]" value="{{ $item['name'] }}">
                    <input type="hidden" name="items[{{ $index }}][price]"
                        value="{{ $item['price'] ?? '' }}">
                @endforeach
            </form>

            <!-- شريط الموبايل -->
            <div class="sticky-actions-mobile">
                <button type="button" class="btn-cancel"
                    onclick="window.location.replace('{{ route('sale_proccess.create') }}')">إلغاء</button>
                <button type="submit" form="invoiceForm" class="btn-done">إتمام الفاتورة</button>
            </div>
        </div>
    </div>

    <!-- المودلز الخاصة بالدفع والخطأ -->
    <div id="paymentModal" class="modal-overlay">
        <div class="modal-content">
            <div style="font-size: 1.2rem; font-weight: 800;">اختر طريقة الدفع</div>
            <div class="payment-options">
                <div class="pay-btn" onclick="submitWithPayment('cash')">
                    <div style="font-size: 30px;">💵</div> كاش
                </div>
                <div class="pay-btn" onclick="submitWithPayment('digital')">
                    <div style="font-size: 30px;">📱</div> محفظة
                </div>
            </div>
            <button type="button" class="btn-close-modal" onclick="closePaymentModal()">إغلاق وتعديل
                الفاتورة</button>
        </div>
    </div>

    <div id="errorModal" class="modal-overlay">
        <div class="modal-content" style="border:2px solid #ff4b2b;">
            <div style="font-size:22px; color:#ff4b2b; font-weight:bold;">⚠️ تنبيه</div>
            <p id="errorText" style="margin-top:15px; font-weight:bold;"></p>
            <button onclick="closeErrorModal()" class="btn-close-modal" style="color:#ff4b2b;">إغلاق</button>
        </div>
    </div>

    <script>
        const baseTotal = {{ $grandTotal }};
        const discountInput = document.getElementById('discountInput');
        const discountType = document.getElementById('discountType');
        const netTotalDisplay = document.getElementById('netTotalDisplay');
        const headerTotalValue = document.getElementById('headerTotalValue');
        const printDiscountInput = document.getElementById('printDiscountInput');

        // ✅ دالة احتساب الخصم ديناميكياً (نسبة أو مبلغ ثابت)
        function calculateDiscount() {
            if (!discountInput) return; // للعملاء بدون صلاحيات

            let val = parseFloat(discountInput.value) || 0;
            let type = discountType.value;
            let discountAmount = 0;

            if (val < 0) {
                val = 0;
                discountInput.value = '';
            }

            if (type === 'percentage') {
                if (val > 100) {
                    val = 100;
                    discountInput.value = 100;
                }
                discountAmount = (baseTotal * val) / 100;
            } else {
                if (val > baseTotal) {
                    val = baseTotal;
                    discountInput.value = baseTotal;
                }
                discountAmount = val;
            }

            let netTotal = baseTotal - discountAmount;
            let formattedNet = Number(netTotal.toFixed(2));

            // تحديث الواجهة
            netTotalDisplay.innerText = formattedNet + ' ج.م';
            headerTotalValue.innerText = formattedNet + ' ج.م';

            // نرسل قيمة الخصم الفعلية كأموال للطباعة
            if (printDiscountInput) printDiscountInput.value = discountAmount;
        }

        if (discountInput) {
            discountInput.addEventListener('input', calculateDiscount);
            discountType.addEventListener('change', calculateDiscount);
        }

        // --- دوال الإرسال والمودال ---
        function closePaymentModal() {
            document.getElementById('paymentModal').style.display = 'none';
        }

        function closeErrorModal() {
            document.getElementById('errorModal').style.display = 'none';
        }

        function showErrorModal(message) {
            document.getElementById('errorText').innerText = message;
            document.getElementById('errorModal').style.display = 'flex';
        }

        document.getElementById('invoiceForm').addEventListener('submit', function(e) {
            e.preventDefault();
            document.getElementById('paymentModal').style.display = 'flex';
        });

        async function submitWithPayment(type) {
            document.getElementById('paymentModal').style.display = 'none';
            const form = document.getElementById('invoiceForm');
            const doneBtns = document.querySelectorAll('.btn-done');

            const fd = new FormData(form);
            fd.append('payment_type', type);

            try {
                doneBtns.forEach(btn => {
                    btn.disabled = true;
                    btn.innerText = "جاري الحفظ...";
                });

                let response = await fetch("{{ route('invoices.store') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: fd
                });

                const ct = response.headers.get('content-type') || '';
                let data = ct.includes('application/json') ? await response.json() : {
                    message: await response.text()
                };

                if (response.ok) {
                    const invoiceId = data.invoice ? (data.invoice.id ?? null) : null;
                    if (confirm('✅ تم إنشاء الفاتورة بنجاح. هل تريد طباعتها الآن؟')) {
                        if (invoiceId) {
                            const invInput = document.createElement('input');
                            invInput.type = 'hidden';
                            invInput.name = 'invoice_id';
                            invInput.value = invoiceId;
                            document.getElementById('printForm').appendChild(invInput);
                        }
                        // نقل الملاحظة إلى فورم الطباعة
                        const noteValue = document.getElementById('hiveNote') ? document.getElementById('hiveNote')
                            .value : '';
                        document.getElementById('printNoteInput').value = noteValue;

                        // ثم أمر الإرسال الموجود مسبقاً
                        document.getElementById('printForm').submit();
                    }
                    setTimeout(() => {
                        window.location.href = "{{ route('main.create') }}";
                    }, 1000);
                } else {
                    throw new Error(data.message || 'حدث خطأ في السيرفر');
                }
            } catch (err) {
                console.error(err);
                showErrorModal(err.message);
                doneBtns.forEach(btn => {
                    btn.disabled = false;
                    btn.innerText = "إتمام الفاتورة";
                });
            }
        }

        // اختصار لوحة المفاتيح
        document.addEventListener('keydown', function(e) {
            if (e.key !== 'Enter' || e.repeat) return;
            const active = document.activeElement;
            if (!active) return;
            if (active.tagName === 'TEXTAREA' || active.isContentEditable || active.id === 'discountInput') return;
            e.preventDefault();
            const doneBtn = document.querySelector('.btn-done:not([disabled])');
            if (doneBtn) doneBtn.click();
        });
    </script>
</body>

</html>
