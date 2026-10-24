@extends('layouts.app_page')

@section('title', 'تفاصيل الحجز')

@section('content')
    <div class="subscription-container">
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
        <div class="card">
            <!-- الهيدر -->
            <div class="card-header">
                <h2>📋 تفاصيل الحجز</h2>
                <span class="badge">#{{ $booking->id }}</span>
            </div>

            <!-- بيانات العميل -->
            <div class="section">
                <h3>👤 بيانات العميل</h3>
                <div class="client-info">
                    <span>🆔 {{ $booking->client->id }}</span>
                    <span>👤 {{ $booking->client->name }}</span>
                    <span>📞 {{ $booking->client->phone }}</span>
                    <a href="{{ route('clients.edit', $booking->client->id) }}" class="edit-btn" title="تعديل بيانات العميل">✏️
                        تعديل</a>
                </div>
            </div>

            <div class="section">
                <h3>📅 تفاصيل الحجز</h3>
                <div class="box">
                    <p><strong>قاعة:</strong> {{ $booking->hall->name }}</p>
                    <p><strong>الحضور:</strong> {{ $booking->attendees }} فرد</p>
                    <div class="booking-time">
                        @php
                            $statuses = [
                                'scheduled' => '⏳ لم يبدأ بعد',
                                'due' => '📌 موعده الآن',
                                'in_progress' => '▶️ جاري',
                                'finished' => '✅ منتهي',
                                'cancelled' => '❌ ملغي',
                            ];
                        @endphp
                        <div class="time-item">
                            <span class="label">من:</span>
                            <span
                                class="value">{{ \Carbon\Carbon::parse($booking->start_at)->format('Y-m-d h:i A') }}</span>
                        </div>

                        <div class="time-item">
                            <span class="label">إلى:</span>
                            <span
                                class="value">{{ \Carbon\Carbon::parse($booking->end_at)->format('Y-m-d h:i A') }}</span>
                        </div>

                        @if (in_array($booking->status, ['in_progress', 'finished']) && $booking->real_start_at)
                            <div class="time-item highlight">
                                <span class="label">🚀 بداية فعلية:</span>
                                <span class="value">
                                    {{ \Carbon\Carbon::parse($booking->real_start_at)->format('Y-m-d h:i A') }}
                                </span>
                            </div>
                        @endif

                        @if ($booking->real_end_at)
                            <div class="time-item">
                                <span class="label">🏁 نهاية فعلية:</span>
                                <span
                                    class="value">{{ \Carbon\Carbon::parse($booking->real_end_at)->format('Y-m-d h:i A') }}</span>
                            </div>
                        @endif

                        @if (!empty($actual_duration))
                            @php
                                $totalMin = intval($actual_duration);
                                $hours = intdiv($totalMin, 60);
                                $mins = $totalMin % 60;
                                if ($hours > 0) {
                                    $display = $hours . ' س ' . ($mins > 0 ? $mins . ' د' : '');
                                } else {
                                    $display = $mins . ' د';
                                }
                            @endphp
                            <div class="time-item duration">
                                <span class="label">⏱️ المدة:</span>
                                <span class="value">{{ $display }}</span>
                                <span class="status">{{ $statuses[$booking->status] ?? $booking->status }}</span>
                            </div>
                        @endif
                    </div>


                    @php
                        $statuses = [
                            'scheduled' => '⏳ لم يبدأ بعد',
                            'due' => '📌 موعده الآن',
                            'in_progress' => '▶️ جاري',
                            'finished' => '✅ منتهي',
                            'cancelled' => '❌ ملغي',
                        ];
                    @endphp
                </div>
            </div>

            <!-- المشتريات -->
            <div class="section">
                <h3>🛒 المشتريات</h3>
                <button id="openPurchasesModal" class="box selected-products purchases-btn" type="button">
                    @forelse ($purchases as $purchase)
                        <p data-id="{{ $purchase->product_id }}">
                            {{ $purchase->product->name }} × {{ $purchase->quantity }}
                        </p>
                    @empty
                        <p>لا يوجد مشتريات</p>
                    @endforelse
                </button>

                @if ($booking->status === 'in_progress')
                    <div class="products-list">

                        @foreach ($importantProducts as $importantProduct)
                            <form class="invoiceForm" action="{{ route('booking.purchase.store', $booking->id) }}"
                                method="POST">
                                @csrf
                                <input type="hidden" name="items" class="itemsInput">
                                <button type="submit" class="product-item" data-id="{{ $importantProduct->product_id }}">
                                    {{ $importantProduct->name }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                @endif
            </div>
            <h3>💰 الحساب</h3>
            <div class="box">
                <p><strong>إجمالي متوقع:</strong>
                    <span class="price" style="color:green;">{{ number_format($booking->estimated_total, 2) }}
                        جنيه</span>
                </p>

                <p><strong>سعر الساعة:</strong> {{ number_format($bookingHourPrice, 2) }} جنيه</p>

                <p><strong>سعر الساعات:</strong>
                    <span class="price-hours">{{ number_format($hours_total ?? 0, 2) }} جنيه</span>
                </p>

                <p><strong>سعر المشتريات:</strong>
                    <span class="price-purchases">{{ number_format($purchases_total ?? 0, 2) }} جنيه</span>
                </p>

                <p><strong>الإجمالي الفعلي حتى الآن:</strong>
                    <span class="price" style="color:red; font-weight:800;">
                        {{ number_format($combined_actual ?? 0, 2) }} جنيه
                    </span>
                </p>

                <p><strong>الدفعة المقدمة:</strong>
                    @if (($deposit_paid ?? 0) > 0)
                        ✅ {{ number_format($deposit_paid, 2) }} جنيه
                    @else
                        ❌ 0.00 جنيه
                    @endif
                </p>

                <p><strong>المتبقي للدفع:</strong></p>
                <div
                    style="
                          background: #e8fbe8;
                          border: 2px solid #38a169;
                          color: #166534;
                          padding: 10px 15px;
                          border-radius: 10px;
                          font-size: 1.2em;
                          font-weight: bold;
                          display: inline-block;
                          margin-top: 5px;
                      ">
                    {{ number_format(max($remaining ?? 0, 0), 2) }} جنيه
                    @if (!empty($remaining_label ?? null))
                        <span style="color:#2f855a; font-style:italic; font-weight: normal; margin-left:8px;">
                            {{ $remaining_label }}
                        </span>
                    @endif
                </div>


            </div>
            <!-- الأزرار حسب الحالة -->
            <div class="actions">
                @if (in_array($booking->status, ['scheduled', 'due']))
                    <a href="{{ route('bookings.edit', $booking) }}" class="btn yellow">✏️ تعديل الميعاد</a>
                    <form id="cancelForm-{{ $booking->id }}" action="{{ route('bookings.cancel', $booking) }}"
                        method="POST" style="display:inline;">
                        @csrf

                        @php
                            // اليوم/الشهر مع الساعة بنظام 12 ساعة (AM/PM)
                            $startFormatted = \Carbon\Carbon::parse($booking->start_at)->format('j/n h:i A');
                            $endFormatted = \Carbon\Carbon::parse($booking->end_at)->format('j/n h:i A');
                        @endphp

                        <button type="button" class="btn red"
                            onclick="confirmCancelSimple({{ $booking->id }}, '{{ addslashes($booking->client->name) }}', '{{ $startFormatted }}', '{{ $endFormatted }}')">
                            ❌ إلغاء الحجز
                        </button>
                    </form>

                    <script>
                        function confirmCancelSimple(id, clientName, startFormatted, endFormatted) {
                            const message =
                                `هل أنت متأكد من إلغاء الحجز للعميل: ${clientName}\nمن: ${startFormatted}\nإلى: ${endFormatted}\n\n(سيتم استرجاع أي مقدم إذا وُجد)`;
                            if (confirm(message)) {
                                document.getElementById(`cancelForm-${id}`).submit();
                            }
                        }
                    </script>

                    <form action="{{ route('bookings.start', $booking) }}" method="POST" style="display:inline">
                        @csrf
                        <button class="btn green" type="submit">✅ بدء الحجز</button>
                    </form>
                @elseif($booking->status === 'in_progress')
                    <a href="{{ route('booking.purchases.create', $booking->id) }}" class="btn">➕ إضافة مشتريات</a>

                    <form id="checkoutForm" action="{{ route('booking.checkout', $booking->id) }}" method="POST"
                        style="display:inline;">
                        @csrf
                        <input type="hidden" name="booking" value="{{ $booking->id }}">
                        <input type="hidden" name="hours_total" id="hours_total"
                            value="{{ number_format($hours_total ?? 0, 2, '.', '') }}">
                        <input type="hidden" name="purchases_json" id="purchases_json" value="">
                        <input type="hidden" name="deposit_paid" id="deposit_paid"
                            value="{{ number_format($deposit_paid ?? 0, 2, '.', '') }}">
                        <input type="hidden" name="hourly_rate" id="hourly_rate"
                            value="{{ number_format($bookingHourPrice ?? 0, 2, '.', '') }}">
                        <input type="hidden" name="remaining" id="remaining"
                            value="{{ number_format($remaining ?? 0, 2, '.', '') }}">
                        <input type="hidden" name="payment_type" id="payment_type" value="cash">

                        <button type="submit" class="btn btn-danger">إنهاء الحساب</button>
                    </form>
                @elseif($booking->status === 'finished')
                    {{-- <a href="{{ route('invoices.show', $booking->id) }}" class="btn">🧾 عرض الفاتورة</a> --}}
                @elseif($booking->status === 'cancelled')
                    <span class="badge">هذا الحجز ملغي</span>
                @endif
            </div>


        </div>
    </div>

    <script>
        document.querySelectorAll(".invoiceForm").forEach(form => {
            form.addEventListener("submit", function(e) {
                e.preventDefault();
                let button = form.querySelector(".product-item");
                if (!button) return;
                let id = button.getAttribute("data-id");
                let item = [{
                    id: parseInt(id),
                    qty: 1
                }];
                form.querySelector(".itemsInput").value = JSON.stringify(item);
                form.submit();
            });
        });

        // تحديث الحقول المخفية قبل إرسال checkoutForm
        document.getElementById('checkoutForm')?.addEventListener('submit', function() {
            const hoursSpan = document.querySelector('.price-hours');
            const purchasesSpan = document.querySelector('.price-purchases');

            if (hoursSpan) {
                const hours = hoursSpan.textContent.replace(/[^\d.-]/g, '').trim();
                document.getElementById('hours_total').value = hours || 0;
            }
            if (purchasesSpan) {
                const purchases = purchasesSpan.textContent.replace(/[^\d.-]/g, '').trim();
                const pInput = document.getElementById('purchases_total');
                if (pInput) pInput.value = purchases || 0;
            }
            // deposit_paid يأتي من السيرفر كقيمة افتراضية، لا حاجة لتغييره هنا
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let selectedProducts = []; // المنتجات المختارة مؤقتاً

            // إنشاء الـ Snackbar container
            let snackbar = document.createElement("div");
            snackbar.id = "bookingProductsSnackbar";
            snackbar.style.position = "fixed";
            snackbar.style.bottom = "20px";
            snackbar.style.right = "20px";
            snackbar.style.background = "#333";
            snackbar.style.color = "#fff";
            snackbar.style.padding = "15px";
            snackbar.style.borderRadius = "12px";
            snackbar.style.boxShadow = "0 4px 12px rgba(0,0,0,0.3)";
            snackbar.style.zIndex = "99999";
            snackbar.style.display = "none";
            snackbar.style.minWidth = "250px";
            snackbar.style.maxHeight = "60vh";
            snackbar.style.overflowY = "auto";
            document.body.appendChild(snackbar);

            // زر مسح الكل
            let clearBtn = document.createElement("span");
            clearBtn.textContent = "❌";
            clearBtn.style.cursor = "pointer";
            clearBtn.style.float = "right";
            clearBtn.style.marginBottom = "10px";
            snackbar.appendChild(clearBtn);

            clearBtn.addEventListener("click", () => {
                selectedProducts = [];
                updateSnackbarUI();
            });

            let list = document.createElement("div");
            list.id = "selectedProductsList";
            snackbar.appendChild(list);

            let confirmBtn = document.createElement("button");
            confirmBtn.textContent = "✅ تأكيد المشتريات";
            confirmBtn.style.marginTop = "10px";
            confirmBtn.className = "btn btn-success btn-sm w-100";
            snackbar.appendChild(confirmBtn);

            // تحديث واجهة الـ Snackbar
            function updateSnackbarUI() {
                list.innerHTML = "";
                if (selectedProducts.length === 0) {
                    snackbar.style.display = "none";
                    return;
                }

                selectedProducts.forEach(p => {
                    const prodName = document.querySelector(`.product-item[data-id="${p.product_id}"]`)
                        ?.textContent || "منتج";
                    const div = document.createElement("div");
                    div.style.display = "flex";
                    div.style.justifyContent = "space-between";
                    div.style.alignItems = "center";
                    div.style.marginBottom = "5px";

                    let nameSpan = document.createElement("span");
                    nameSpan.textContent = `${prodName} × ${p.qty}`;

                    let minusBtn = document.createElement("button");
                    minusBtn.textContent = "➖";
                    minusBtn.className = "btn btn-sm btn-warning";
                    minusBtn.style.marginLeft = "10px";

                    minusBtn.addEventListener("click", () => {
                        if (p.qty > 1) {
                            p.qty -= 1;
                        } else {
                            selectedProducts = selectedProducts.filter(item => item.product_id !== p
                                .product_id);
                        }
                        updateSnackbarUI();
                    });

                    div.appendChild(nameSpan);
                    div.appendChild(minusBtn);
                    list.appendChild(div);
                });

                snackbar.style.display = "block";
            }

            // التعامل مع أزرار المنتجات (داخل صفحة الحجز)
            document.querySelectorAll(".product-item").forEach(btn => {
                btn.addEventListener("click", function(e) {
                    e.preventDefault();
                    const id = parseInt(this.dataset.id);
                    const existing = selectedProducts.find(p => p.product_id === id);
                    if (existing) {
                        existing.qty += 1;
                    } else {
                        selectedProducts.push({
                            product_id: id,
                            qty: 1
                        });
                    }
                    updateSnackbarUI();
                });
            });

            // عند الضغط على تأكيد المشتريات
            confirmBtn.addEventListener("click", function() {
                if (selectedProducts.length === 0) return;

                // نستخدم أول فورم كـ مرجع (كلها نفس الـ action)
                const firstForm = document.querySelector(".invoiceForm");
                if (!firstForm) return;

                const allItems = selectedProducts.map(p => ({
                    id: p.product_id,
                    qty: p.qty
                }));

                firstForm.querySelector(".itemsInput").value = JSON.stringify(allItems);
                firstForm.submit();

                // فضي المصفوفة
                selectedProducts = [];
                updateSnackbarUI();
            });
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const checkoutForm = document.getElementById('checkoutForm');

            if (checkoutForm) {
                checkoutForm.addEventListener('submit', function(e) {
                    // منع الإرسال المؤقت عشان نجمع البيانات
                    e.preventDefault();

                    // اجمع المشتريات المعروضة داخل div.selected-products
                    const purchaseLines = document.querySelectorAll('.selected-products p[data-id]');
                    const purchases = [];

                    purchaseLines.forEach(line => {
                        const id = parseInt(line.dataset.id);
                        const match = line.textContent.match(/×\s*(\d+)/);
                        if (id && match) {
                            const qty = parseInt(match[1]);
                            purchases.push({
                                id,
                                qty
                            });
                        }
                    });



                    // حفظها في input hidden كـ JSON
                    document.getElementById('purchases_json').value = JSON.stringify(purchases);

                    // تحديث ساعات العمل
                    const hoursSpan = document.querySelector('.price-hours');
                    if (hoursSpan) {
                        const hours = hoursSpan.textContent.replace(/[^\d.-]/g, '').trim();
                        document.getElementById('hours_total').value = hours || 0;
                    }

                    // خلاص أرسل الفورم
                    checkoutForm.submit();
                });
            }
        });
    </script>
  <div class="modal fade" id="purchasesModal" tabindex="-1" aria-labelledby="purchasesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px;">
            <div class="modal-header text-center position-relative">
                <h5 class="modal-title w-100 fw-bold" style="font-size: 1.3rem;">تعديل المشتريات</h5>
                <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="updatePurchasesForm">
                    @csrf

                    <div id="purchaseItemsContainer">
                        @forelse ($purchases as $purchase)
                            <div class="d-flex align-items-center justify-content-between mb-3 purchase-item pb-2"
                                data-id="{{ $purchase->id }}">
                                <span class="fw-bold" style="color:#c59d5f">{{ $purchase->product->name }}</span>

                                <div class="d-flex align-items-center">
                                    <button type="button" class="btn btn-outline-secondary btn-sm decrease">-</button>
                                    <input type="number" class="form-control mx-2 text-center quantity-input"
                                           name="quantities[{{ $purchase->id }}]" value="{{ $purchase->quantity }}"
                                           min="1" style="width:70px;">
                                    <button type="button" class="btn btn-outline-secondary btn-sm increase">+</button>
                                </div>

                                <button type="button" class="btn btn-danger btn-sm remove-purchase">❌</button>
                            </div>
                        @empty
                            <p class="text-muted text-center">لا يوجد مشتريات</p>
                        @endforelse
                    </div>

                    <div id="purchasesAlert" class="alert d-none mt-3"></div>

                    <button type="submit" class="btn btn-primary w-100 mt-3">💾 حفظ التعديلات</button>
                </form>
            </div>
        </div>
    </div>
</div>

      <script>
        document.addEventListener('DOMContentLoaded', function() {

            const modalTrigger = document.getElementById('openPurchasesModal');
            const form = document.getElementById('updatePurchasesForm');
            const alertBox = document.getElementById('purchasesAlert');
            let removedPurchases = [];

            // فتح المودال
            modalTrigger.addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('purchasesModal'));
                modal.show();
            });

            // أزرار + و -
            document.querySelectorAll('.increase').forEach(btn => {
                btn.addEventListener('click', function() {
                    let input = this.parentNode.querySelector('.quantity-input');
                    input.value = parseInt(input.value) + 1;
                });
            });

            document.querySelectorAll('.decrease').forEach(btn => {
                btn.addEventListener('click', function() {
                    let input = this.parentNode.querySelector('.quantity-input');
                    if (parseInt(input.value) > 1) input.value = parseInt(input.value) - 1;
                });
            });

            // ❌ حذف المنتج
            document.querySelectorAll('.remove-purchase').forEach(btn => {
                btn.addEventListener('click', function() {
                    const row = this.closest('[data-id]'); // استخدم data-id
                    const productId = row.dataset.id; // اقرأ من data-id
                    removedPurchases.push(productId);
                    row.remove(); // امسح العنصر من الواجهة
                });
            });



            // 💾 حفظ التعديلات
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                let formData = new FormData(form);
                formData.append('removed', JSON.stringify(removedPurchases));

                fetch("{{ route('booking.purchases.update', $booking->id) }}", {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alertBox.className = 'alert alert-success';
                            alertBox.textContent = '✅ تم تحديث المشتريات بنجاح';
                            alertBox.classList.remove('d-none');
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            throw new Error(data.message || 'حدث خطأ أثناء الحفظ');
                        }
                    })
                    .catch(err => {
                        alertBox.className = 'alert alert-danger';
                        alertBox.textContent = '❌ ' + err.message;
                        alertBox.classList.remove('d-none');
                    });
            });
        });
    </script>
@endsection

@section('style')
    <style>
        body {
            background: #121212;
            /* دارك */
            color: #fff;
            font-family: "Tahoma", sans-serif;
        }

        /* ===== Snackbar ===== */
        .snackbar {
            background: #333;
            color: #fff;
        }

        .snackbar.success {
            background: #28a745;
        }

        .snackbar.error {
            background: #dc3545;
        }

        .subscription-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }

        .card {
            background: #1e1e1e;
            /* كارد غامق */
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.5);
            padding: 30px;
            animation: fadeInUp 0.6s ease;
            color: #fff;
            /* كل الكلام جوه أبيض */
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }

        .card-header h2 {
            font-size: 26px;
            margin: 0;
            color: #ffd700;
            /* الذهب للنص */
        }

        .badge {
            background: #ffd700;
            /* الذهب */
            color: #000;
            padding: 6px 15px;
            border-radius: 30px;
            font-weight: bold;
        }

        .section h3 {
            color: #ffd700;
            /* عناوين الأقسام بالذهبي */
            font-size: 20px;
            margin-bottom: 10px;
        }

        .box {
            background: #2a2a2a;
            /* صندوق الكارد غامق شويه */
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            color: #fff;
            /* نصوص بيضاء */
        }

        .price {
            font-weight: bold;
            font-size: 18px;
            color: #ffd700;
            /* أسعار بالذهبي */
        }

        /* الأزرار */
        .btn {
            border: none;
            padding: 12px 18px;
            border-radius: 12px;
            font-weight: bold;
            cursor: pointer;
            transition: .3s;
            font-size: 15px;
            color: #fff;
        }

        .btn.yellow {
            background: #ffd700;
            color: #000;
            border: 1px solid #e6c200;
        }

        .btn.green {
            background: #4caf50;
        }

        .btn.red,
        .btn.btn-danger {
            background: #f44336;
        }

        /* المنتجات المختارة */
        .selected-products span {
            background: #ffd700;
            color: #000;
        }

        /* الكروت */
        .product-item {
            background: #2a2a2a;
            border: 1px solid #444;
            border-radius: 12px;
            padding: 12px 14px;
            min-width: 120px;
            min-height: 70px;
            font-size: 14px;
            font-weight: 600;
            color: #fff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
            transition: all 0.25s ease;
            cursor: pointer;

            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .product-item:hover {
            transform: translateY(-4px) scale(1.03);
            border-color: #ffd700;
            box-shadow: 0 6px 16px rgba(255, 215, 0, 0.4);
            color: #ffd700;
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

        .client-info {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            background: #131313;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 10px 14px;
            font-size: 15px;
            color: #ffffff;
            font-weight: 500;
        }

        .client-info span {
            display: flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
        }

        .client-info .edit-btn {
            background: #007bff;
            color: #fff;
            text-decoration: none;
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 14px;
            transition: 0.2s;
            white-space: nowrap;
        }

        .client-info .edit-btn:hover {
            background: #0056b3;
        }

        .booking-time {
            display: flex;
            flex-wrap: wrap;
            align-items: stretch;
            justify-content: center;
            gap: 8px;
            background: linear-gradient(135deg, #1e1e1e, #272727);
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            font-family: "Cairo", sans-serif;
        }

        .time-item {
            background: #1e1e1e;
            border-radius: 10px;
            padding: 8px 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1 1 120px;
            min-width: 110px;
            text-align: center;
            box-shadow: inset 0 0 3px rgba(0, 0, 0, 0.05);
        }

        .time-item .label {
            font-weight: 600;
            color: #047857;
            font-size: 13px;
            margin-bottom: 3px;
        }

        .time-item .value {
            font-size: 14px;
            color: #eff6ff;
            font-weight: 500;
        }

        .time-item.highlight {
            background: #1f2937;
            border: 1px solid #6ee7b7;
        }

        .time-item.duration {
            background: #1f2937;
            border: 1px solid #93c5fd;
        }

        .purchases-btn {
            display: block;
            width: 100%;
            text-align: start;
            background: #1f2937;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 12px 14px;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            font-family: "Cairo", sans-serif;
        }

        .purchases-btn:hover {
            background: #28313f;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            transform: translateY(-1px);
        }

        .purchases-btn p {
            margin: 0;
            padding: 4px 0;
            color: #f8fafc;
            font-size: 15px;
        }

        .purchases-btn p:first-child {
            margin-top: 2px;
        }

        .purchases-btn:active {
            transform: scale(0.98);
            background: #f1f5f9;
        }/* مودال Dark + Gold */
.modal-content {
    background-color: #1a1a1a !important; 
    color: #f1f1f1;
    border: none;
}

.modal-header {
    border-bottom: 1px solid #333;
}

.modal-title {
    color: #c59d5f !important;
}

.quantity-input {
    background-color: #2a2a2a;
    border: 1px solid #444;
    color: #fff;
}

.quantity-input:focus {
    background-color: #2a2a2a;
    border-color: #c59d5f;
    box-shadow: 0 0 0 0.2rem rgba(197, 157, 95, 0.25);
}

.btn-outline-secondary {
    color: #c59d5f;
    border-color: #c59d5f;
}

.btn-outline-secondary:hover {
    background-color: #c59d5f;
    color: #1a1a1a;
}

.remove-purchase {
    background-color: transparent;
    border: 1px solid #bb4444 !important;
}

.remove-purchase:hover {
    background-color: #bb4444 !important;
    color: white;
}

.btn-primary {
    background-color: #c59d5f !important;
    border-color: #c59d5f !important;
    color: #1a1a1a !important;
}

.btn-primary:hover {
    background-color: #a98246 !important;
    border-color: #a98246 !important;
}

.purchase-item {
    border-bottom: 1px solid #333 !important;
}


    </style>
@endsection
