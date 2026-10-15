@extends('layouts.app_page')

@section('title', 'تعديل فاتورة')

@section('content')
    <div class="subscription-container">
        <div class="card">
            <div class="card-header">
                <h2>تعديل فاتورة رقم: {{ $invoice->invoice_number }}</h2>
                <span class="badge" id="total-display">الإجمالي: {{ $invoice->total }} ج</span>
            </div>
            <form id="invoice-update-form">
                @csrf

                <div id="items-container">
                    @foreach ($invoice->items->where('item_type', 'product') as $index => $item)
                        <div class="box item-row" data-index="{{ $index }}">
                            <div class="row-layout">
                                <div class="field-group search-field">
                                    <label>المنتج</label>
                                    <input type="text" class="product-search-input" placeholder="ابحث عن منتج..."
                                        value="{{ $item->name }}" autocomplete="off">
                                    <input type="hidden" name="items[{{ $index }}][product_id]"
                                        class="product-id-hidden" value="{{ $item->product_id }}">
                                    <div class="search-results-dropdown"></div>
                                </div>

                                <div class="field-group qty-field">
                                    <label>العدد</label>
                                    <div class="qty-control">
                                        <button type="button" class="qty-btn minus">-</button>
                                        <input type="number" name="items[{{ $index }}][qty]" class="qty-input"
                                            value="{{ $item->qty }}" min="1">
                                        <button type="button" class="qty-btn plus">+</button>
                                    </div>
                                </div>

                                <div class="field-group price-field">
                                    <label>السعر</label>
                                    <span class="unit-price">{{ $item->price }}</span> ج
                                </div>

                                <button type="button" class="remove-item-btn">×</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </form>

            <button type="button" id="add-new-item" class="btn-outline">+ إضافة منتج جديد</button>

            <div class="form-actions mt-4">
                <button type="button" id="submit-update" class="btn w-100">إتمام التعديل وحفظ التغييرات</button>
            </div>
        </div>
    </div>
@endsection

@section('style')
    <style>
        body {
            background: #0f0f0f;
            font-family: "Cairo", sans-serif;
            color: #f5f5f5;
        }

        .subscription-container {
            max-width: 850px;
            margin: 30px auto;
            padding: 20px;
        }

        .card {
            background: #1a1a1a;
            border-radius: 18px;
            padding: 25px;
            border: 1px solid rgba(255, 184, 77, 0.1);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 184, 77, 0.1);
            margin-bottom: 20px;
            padding-bottom: 15px;
        }

        .card-header h2 {
            color: #ffb84d;
            font-size: 20px;
        }

        .box {
            background: #111;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 15px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            position: relative;
        }

        .row-layout {
            display: flex;
            align-items: flex-end;
            gap: 15px;
            flex-wrap: wrap;
        }

        .field-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .search-field {
            flex: 2;
            position: relative;
            min-width: 200px;
        }

        .qty-field {
            flex: 1;
            min-width: 120px;
        }

        .price-field {
            min-width: 80px;
            text-align: center;
            color: #ffb84d;
            font-weight: bold;
        }

        label {
            font-size: 12px;
            color: #888;
            margin-right: 5px;
        }

        input {
            background: #1a1a1a;
            border: 1px solid #333;
            color: white;
            padding: 8px;
            border-radius: 8px;
            outline: none;
        }

        input:focus {
            border-color: #ffb84d;
        }

        /* Quantity Controls */
        .qty-control {
            display: flex;
            align-items: center;
            background: #1a1a1a;
            border-radius: 8px;
            border: 1px solid #333;
            overflow: hidden;
        }

        .qty-btn {
            background: #333;
            border: none;
            color: white;
            width: 30px;
            height: 35px;
            cursor: pointer;
        }

        .qty-btn:hover {
            background: #ffb84d;
            color: black;
        }

        .qty-input {
            width: 40px;
            text-align: center;
            border: none !important;
            padding: 0;
        }

        /* Remove Button */
        .remove-item-btn {
            position: absolute;
            top: -10px;
            left: -10px;
            background: #e74c3c;
            color: white;
            border: none;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            cursor: pointer;
            font-weight: bold;
        }

        /* Search Results */
        .search-results-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            left: 0;
            background: #222;
            border: 1px solid #444;
            z-index: 100;
            border-radius: 0 0 8px 8px;
            display: none;
            max-height: 200px;
            overflow-y: auto;
        }

        .result-item {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #333;
        }

        .result-item:hover {
            background: #ffb84d;
            color: black;
        }

        .btn-outline {
            background: transparent;
            border: 1px dashed #ffb84d;
            color: #ffb84d;
            width: 100%;
            padding: 12px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .btn {
            background: linear-gradient(90deg, #ffb84d, #a86f68);
            color: black;
            border: none;
            padding: 15px;
            border-radius: 12px;
            font-weight: bold;
            cursor: pointer;
        }

        .badge {
            background: rgba(255, 184, 77, 0.2);
            color: #ffb84d;
            padding: 8px 15px;
            border-radius: 10px;
        }
    </style>
@endsection

@section('scripts')
    @php
        $oldItems = [];
        foreach ($invoice->items->where('item_type', 'product') as $i) {
            $oldItems[$i->product_id] = $i->qty;
        }
    @endphp

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            let rowCounter = document.querySelectorAll('.item-row').length;

            function choosePayment(type) {

                document.getElementById('payment_type').value = type;

                const modal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
                modal.hide();

                sendUpdate(type);
            }

            function sendUpdate(paymentType = null) {

                const items = [];

                document.querySelectorAll('.item-row').forEach(row => {

                    const productId = row.querySelector('.product-id-hidden').value;
                    const qty = row.querySelector('.qty-input').value;

                    if (productId) {
                        items.push({
                            product_id: productId,
                            qty: qty
                        });
                    }
                });

                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                fetch(`{{ route('invoices.update', $invoice->id) }}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            items,
                            payment_type: paymentType
                        })
                    })

                    .then(res => res.json())
                    .then(data => {
                        alert(data.message);
                        window.location.reload();
                    });
            }

            function calculateTotal() {
                let total = 0;

                document.querySelectorAll('.item-row').forEach(row => {
                    const price = parseFloat(row.querySelector('.unit-price').dataset.price || 0);
                    const qty = parseInt(row.querySelector('.qty-input').value || 0);
                    total += price * qty;
                });

                document.getElementById('total-display').innerText =
                    `الإجمالي: ${total.toFixed(2)} ج`;
            }

            // ==============================
            // ADD NEW ROW
            // ==============================
            document.getElementById('add-new-item').addEventListener('click', function() {

                const container = document.getElementById('items-container');

                const newRow = document.createElement('div');
                newRow.className = 'box item-row';
                newRow.innerHTML = `
            <div class="row-layout">
                <div class="field-group search-field">
                    <label>المنتج</label>
                    <input type="text" class="product-search-input"
                           placeholder="ابحث عن منتج..." autocomplete="off">
                    <input type="hidden"
                           name="items[${rowCounter}][product_id]"
                           class="product-id-hidden">
                    <div class="search-results-dropdown"></div>
                </div>

                <div class="field-group qty-field">
                    <label>العدد</label>
                    <div class="qty-control">
                        <button type="button" class="qty-btn minus">-</button>
                        <input type="number"
                               name="items[${rowCounter}][qty]"
                               class="qty-input" value="1" min="1">
                        <button type="button" class="qty-btn plus">+</button>
                    </div>
                </div>

                <div class="field-group price-field">
                    <label>السعر</label>
                    <span class="unit-price" data-price="0">0</span> ج
                </div>

                <button type="button" class="remove-item-btn">×</button>
            </div>
        `;

                container.appendChild(newRow);
                rowCounter++;
            });

            // ==============================
            // EVENT DELEGATION
            // ==============================
            document.addEventListener('click', function(e) {

                // DELETE ROW
                if (e.target.classList.contains('remove-item-btn')) {
                    e.target.closest('.item-row').remove();
                    calculateTotal();
                }

                // PLUS / MINUS
                if (e.target.classList.contains('qty-btn')) {
                    const input = e.target.parentElement.querySelector('.qty-input');

                    if (e.target.classList.contains('plus')) {
                        input.value = parseInt(input.value) + 1;
                    }

                    if (e.target.classList.contains('minus') && input.value > 1) {
                        input.value = parseInt(input.value) - 1;
                    }

                    calculateTotal();
                }

                // SELECT PRODUCT
                if (e.target.classList.contains('result-item')) {

                    const row = e.target.closest('.item-row');
                    const name = e.target.dataset.name;
                    const id = e.target.dataset.id;
                    const price = parseFloat(e.target.dataset.price);

                    row.querySelector('.product-search-input').value = name;
                    row.querySelector('.product-id-hidden').value = id;

                    const priceSpan = row.querySelector('.unit-price');
                    priceSpan.innerText = price;
                    priceSpan.dataset.price = price;

                    row.querySelector('.search-results-dropdown').style.display = 'none';

                    calculateTotal();
                }
            });

            // ==============================
            // LIVE SEARCH
            // ==============================
            document.addEventListener('input', function(e) {

                if (e.target.classList.contains('product-search-input')) {

                    const query = e.target.value.trim();
                    const row = e.target.closest('.item-row');
                    const dropdown = row.querySelector('.search-results-dropdown');

                    row.querySelector('.product-id-hidden').value = '';
                    row.querySelector('.unit-price').innerText = '0';
                    row.querySelector('.unit-price').dataset.price = 0;

                    if (query.length < 1) { // الآن يبدأ من أول حرف
                        resultsDiv.style.display = "none";
                        return;
                    }


                    fetch(`/products/searchid?query=${query}`)
                        .then(res => res.json())
                        .then(data => {

                            dropdown.innerHTML = '';

                            data.forEach(product => {


                                const div = document.createElement('div');
                                div.className = 'result-item';
                                div.dataset.id = product.id;
                                div.dataset.price = product.price;
                                div.dataset.name = product.name;

                                div.innerText = `${product.name} (${product.price} ج)`;

                                dropdown.appendChild(div);
                            });

                            dropdown.style.display = 'block';
                        });
                }

                if (e.target.classList.contains('qty-input')) {
                    calculateTotal();
                }
            });

            // ==============================
            // SUBMIT
            // ==============================
            // نضع الدالة خارج DOMContentLoaded أو نربطها بـ window لتكون مرئية من الـ HTML
            window.processPayment = function(type) {
                const modalElement = document.getElementById('paymentModal');
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) modal.hide();

                // إرسال التعديل مع طريقة الدفع المختارة
                sendUpdate(type);
            };

            // داخل الـ Script الأصلي، تأكد من تعديل الـ Submit:
            document.getElementById('submit-update').addEventListener('click', function() {
                const oldItems = @json($oldItems);
                const currentProductIds = Array.from(document.querySelectorAll('.product-id-hidden')).map(
                    i => i.value);

                let stockReduced = false;
                document.querySelectorAll('.item-row').forEach(row => {
                    const productId = row.querySelector('.product-id-hidden').value;
                    const qty = parseInt(row.querySelector('.qty-input').value || 0);
                    if (productId && oldItems[productId] !== undefined && qty < oldItems[
                            productId]) {
                        stockReduced = true;
                    }
                });
                const deletedProducts = Object.keys(oldItems).filter(id => !currentProductIds.includes(id));
                if (deletedProducts.length > 0) stockReduced = true;

                if (stockReduced) {
                    // عرض مودال بدل confirm
                    const modalEl = new bootstrap.Modal(document.getElementById('returnStockModal'));
                    modalEl.show();

                    document.getElementById('returnStockYes').onclick = function() {
                        modalEl.hide();
                        sendUpdate(true); // نعم، إعادة المخزون
                    };

                    document.getElementById('returnStockNo').onclick = function() {
                        modalEl.hide();
                        sendUpdate(false); // لا، عدم إعادة المخزون
                    };
                } else {
                    sendUpdate(true); // لا تغييرات على الكمية، افتراضيًا نعم
                }
            });

            function sendUpdate(returnStock = true) {
                const items = [];
                document.querySelectorAll('.item-row').forEach(row => {
                    const productId = row.querySelector('.product-id-hidden').value;
                    const qty = row.querySelector('.qty-input').value;
                    if (productId) items.push({
                        product_id: productId,
                        qty
                    });
                });

                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const submitBtn = document.getElementById('submit-update');
                submitBtn.disabled = true;
                submitBtn.innerText = 'جاري الحفظ...';

                fetch(`{{ route('invoices.update', $invoice->id) }}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            items: items,
                            return_stock: returnStock
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert(data.message || 'تم التعديل بنجاح');
                            window.location.href = "{{ route('invoice.index') }}";
                        } else {
                            alert(data.message || 'حدث خطأ');
                            submitBtn.disabled = false;
                            submitBtn.innerText = 'إتمام التعديل وحفظ التغييرات';
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('حدث خطأ أثناء التحديث');
                        submitBtn.disabled = false;
                        submitBtn.innerText = 'إتمام التعديل وحفظ التغييرات';
                    });
            }

            calculateTotal();
        });
    </script>
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true" style="direction: rtl;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background: #1a1a1a; border: 1px solid #ffb84d; color: white;">
                <div class="modal-header">
                    <h5 class="modal-title">توجد زيادة بقيمة <span id="diff-amount">0</span> ج</h5>
                </div>
                <div class="modal-body text-center">
                    <p>اختر طريقة دفع فرق السعر:</p>
                    <div class="d-flex gap-3 justify-content-center mt-3">
                        <button type="button" class="btn" onclick="processPayment('cash')"
                            style="background: #27ae60; color: white; flex: 1;">كاش</button>
                        <button type="button" class="btn" onclick="processPayment('digital')"
                            style="background: #2980b9; color: white; flex: 1;">محفظة</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal لإعادة الخامات -->
    <div class="modal fade" id="returnStockModal" tabindex="-1" aria-hidden="true" style="direction: rtl;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background: #1a1a1a; border: 1px solid #ffb84d; color: white;">
                <div class="modal-header">
                    <h5 class="modal-title">تم حذف أو تقليل كمية منتج</h5>
                </div>
                <div class="modal-body text-center">
                    <p>هل تريد إعادة الخامات للمخزن؟</p>
                    <div class="d-flex gap-3 justify-content-center mt-3">
                        <button type="button" class="btn" id="returnStockYes"
                            style="background: #27ae60; color: white; flex:1;">نعم</button>
                        <button type="button" class="btn" id="returnStockNo"
                            style="background: #c0392b; color: white; flex:1;">لا</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
