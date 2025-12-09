@extends('layouts.app_page')

@section('title', 'تفاصيل الجلسة')

@section('content')
    @php
        $purchasesArray = $purchases
            ->map(function ($purchase) {
                return [
                    'product_id' => $purchase->product->id,
                    'name' => $purchase->product->name,
                    'qty' => $purchase->quantity,
                    'price' => $purchase->product->price,
                    'cost' => $purchase->product->cost,
                ];
            })
            ->toArray();

    @endphp

    <div class="subscription-container">
        {{-- زر الرجوع --}}

        <div class="card">

            <!-- الهيدر -->
            <div class="card-header">
                <h2>📋 تفاصيل الجلسة</h2>
                <span class="badge">#{{ $session->id }}</span>
            </div>

            <!-- بيانات العميل -->
            <div class="section">
                <h3>👤 بيانات العميل</h3>
                <div class="client-info">
                    <span>🆔 {{ $session->client->id }}</span>
                    <span>👤 {{ $session->client->name }}</span>
                    <span>📞 {{ $session->client->phone }}</span>
                    <a href="{{ route('clients.edit', $session->client->id) }}" class="edit-btn" title="تعديل بيانات العميل">✏️
                        تعديل</a>
                </div>

            </div>

            <!-- بيانات الجلسة -->
            <div class="section">
                <h3>🕒 بيانات الجلسة</h3>
                <div class="booking-time">
                    <div class="time-item highlight">
                        <span class="label">موعد البدء🚀 :</span>
                        <span class="value">
                            {{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }}
                        </span>
                    </div>
                    <div class="time-item duration">
                        <span class="label">عدد الساعات⏱️ :</span>
                        <span class="value">{{ $hours }}</span>
                    </div>
                    @if ($isFullDay)
                        <p><strong>🌞 يوم كامل</strong></p>
                    @endif
                    <div class="time-item">
                        <span class="label">عدد الأفراد👤👤 :</span>
                        <span class="value"> {{ $session->persons }}</span>
                    </div>

                    @if (Auth::user()->role === 'admin')
                        <div style="margin-top:12px;">
                            <button id="enterEdit" class="edit-btn"
                                style="background:transparent; border:0; cursor:pointer; padding:6px 8px; border-radius:8px;">
                                <span id="editIcon">📅</span> <span id="editText">عدل الموعد</span>
                            </button>
                        </div>
                    @endif

                    <div class="inline-edit-row" style="margin-top:8px;">
                        <form id="inlineEditForm"
                            style="display:none; gap:8px; align-items:center; transition:all .18s ease; margin-top:6px;">
                            @csrf
                            @method('PUT')
                            <input id="start_time_inline" name="start_time" type="datetime-local"
                                value="{{ \Carbon\Carbon::parse($session->start_time)->format('Y-m-d\TH:i') }}"
                                style="padding:8px 10px; border-radius:8px; border:1px solid #ddd; min-width:220px;">
                            <div style="display:flex; gap:8px; align-items:center;">
                                <button type="submit" id="saveInlineEdit" class="btn"
                                    style="background:#28a745; color:#fff; padding:8px 12px; border-radius:8px; border:0;">
                                    ✅ حفظ
                                </button>
                                <button type="button" id="cancelInlineEdit" class="btn"
                                    style="background:#f0f0f0; color:#333; padding:8px 12px; border-radius:8px; border:0;">
                                    إلغاء
                                </button>
                            </div>
                        </form>
                    </div>

                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            const displayRow = document.getElementById('displayRow');
                            const enterEdit = document.getElementById('enterEdit');
                            const inlineForm = document.getElementById('inlineEditForm');
                            const cancelBtn = document.getElementById('cancelInlineEdit');
                            const startInput = document.getElementById('start_time_inline');

                            function openInline() {
                                inlineForm.style.display = 'flex';
                                displayRow.style.display = 'none';
                                startInput.focus();
                            }

                            function closeInline() {
                                inlineForm.style.display = 'none';
                                displayRow.style.display = 'flex';
                            }

                            enterEdit.addEventListener('click', openInline);
                            cancelBtn.addEventListener('click', e => {
                                e.preventDefault();
                                closeInline();
                            });

                            inlineForm.addEventListener('submit', async e => {
                                e.preventDefault();

                                const url = "{{ route('sessions.updateStartTime', $session->id) }}";
                                const fd = new FormData(inlineForm);
                                fd.append('_method', 'PUT');

                                try {
                                    const resp = await fetch(url, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')
                                                ?.value || '',
                                            'X-Requested-With': 'XMLHttpRequest',
                                            'Accept': 'application/json'
                                        },
                                        body: fd
                                    });

                                    const data = await resp.json();

                                    showSnackbar(data.message || 'تم', data.status === 'success' ? 'success' : 'error');

                                    if (data.status === 'success') {
                                        // ندي مهلة بسيطة عشان المستخدم يشوف الرسالة ثم نعمل ريفريش
                                        setTimeout(() => window.location.reload(), 1000);
                                    }
                                } catch (err) {
                                    showSnackbar('حدث خطأ أثناء الاتصال بالسيرفر.', 'error');
                                }
                            });

                            function showSnackbar(message, type = 'success') {
                                const existing = document.querySelector('.snackbar.temp-js');
                                if (existing) existing.remove();

                                let el = document.createElement('div');
                                el.className = 'snackbar temp-js ' + (type === 'error' ? 'error' : 'success');
                                el.style.cssText =
                                    "position:fixed;bottom:20px;right:20px;padding:12px 18px;border-radius:8px;color:#fff;font-weight:600;z-index:99999;transition:all .3s ease;";
                                el.style.background = type === 'error' ? '#e74c3c' : '#28a745';
                                el.innerText = message;
                                document.body.appendChild(el);

                                setTimeout(() => el.style.opacity = 1, 100);
                                setTimeout(() => el.remove(), 3000);
                            }
                        });
                    </script>
                </div>
            </div>

            <!-- المشتريات -->
            <div class="section">
                <h3>🛒 المشتريات</h3>
                <div class="box selected-products" id="openPurchasesModal" style="cursor:pointer;">
                    @forelse ($purchases as $purchase)
                        <div class="purchase-row" data-purchase-id="{{ $purchase->id }}">
                            <p>{{ $purchase->product->name }} × {{ $purchase->quantity }}</p>
                        </div>
                    @empty
                        <p>لا يوجد مشتريات</p>
                    @endforelse
                </div>


                <div class="products-list">

                    @foreach ($importantProducts as $importantProduct)
                        <form class="invoiceForm" action="{{ route('session.purchase.store', $session->id) }}"
                            method="POST">
                            @csrf
                            <input type="hidden" name="items" class="itemsInput">
                            <button type="submit" class="product-item" data-id="{{ $importantProduct->product_id }}">
                                {{ $importantProduct->name }}
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
            <!-- الإجمالي -->
            <div class="section">
                <h2>💰 الحساب</h2>
                <div class="box">
                    {{-- <p><strong>إجمالي قبل الخصم:</strong> <span class="price">{{ $total }}</span> جنيه</p> --}}

                    <div class="discount-box">
                        {{-- <label><input type="radio" name="discount_type" value="amount" form="checkoutForm" checked>
                                مبلغ</label> --}}
                        {{-- <label><input type="radio" name="discount_type" value="percent" form="checkoutForm"> نسبة
                                %</label>
                            <input type="number" step="0.01" name="discount_value" form="checkoutForm"
                                placeholder="قيمة الخصم">
                            <input type="text" name="discount_reason" form="checkoutForm"
                                placeholder="سبب الخصم (اختياري)"> --}}
                      <p>
    <strong>سعر الساعات:</strong>

    @if ($session->isPayed)
        <span style="text-decoration: line-through; opacity: 0.6; margin-right: 5px;">
            {{ $hours_price }} جنيه
        </span>

        {{-- علامة صح --}}
        <span style="
            color: #2ecc71;
            font-weight: bold;
            font-size: 18px;
            vertical-align: middle;
        ">
            ✔
        </span>

    @else
        {{ $hours_price }} جنيه
    @endif
</p>

                        <p><strong>سعر المشتريات:</strong> {{ $products_price }} جنيه</p>
                        <p style="font-size: 1.5rem; color: green; font-weight: bold;">
                        <p style="font-size: 1.5rem; color: green; font-weight: bold;">
                            <strong id="final_total_preview">{{ $finalTotal }}</strong> جنيه
                        </p>

                        </p>
                    </div>

                </div>
            </div>

            <!-- الأزرار -->
            <div class="form-btn">
                <a id="addPurchasesBtn" href="{{ route('purchases.create', $session->id) }}" class="btn">➕ إضافة
                    مشتريات</a>

                <form id="checkoutForm" action="{{ route('sessions.checkout', $session->id) }}" method="POST"
                    style="display:inline;">
                    @csrf
                    <input type="hidden" name="hours" value="{{ $hours }}">
                    <input type="hidden" name="hourly_rate" value="{{ $hours > 0 ? $hours_price / $hours : 0 }}">
                    <input type="hidden" name="isPayed" value="{{ $session->isPayed }}">
                    <button type="submit" class="btn btn-danger">إنهاء الحساب</button>
                </form>
            </div>

            <!-- حساب منفصل -->
            @if ($session->persons > 1)
                <div class="form-btn">
                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#splitSessionModal">
                        🔀 حساب منفصل
                    </button>
                </div>
            @endif
        </div>
    </div>


    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const openBtn = document.getElementById('openEditInline');
            const box = document.getElementById('inlineEditBox');
            const cancelBtn = document.getElementById('cancelInlineEdit');
            const form = document.getElementById('inlineEditForm');

            // الحماية لو العناصر مش موجودة (غير أدمن)
            if (!form) return;

            // فتح الصندوق
            if (openBtn) {
                openBtn.addEventListener('click', function() {
                    box.style.display = 'flex';
                });
            }

            // إغلاق
            if (cancelBtn) {
                cancelBtn.addEventListener('click', function() {
                    box.style.display = 'none';
                });
            }

            // إغلاق عند الضغط خارج المحتوى
            if (box) {
                box.addEventListener('click', function(e) {
                    if (e.target === box) box.style.display = 'none';
                });
            }

            // دالة تنسيق تُظهر التاريخ بطريقة أبسط (اختياري)
            function formatLocalReadable(isoString) {
                try {
                    const dt = new Date(isoString);
                    if (isNaN(dt)) return isoString;
                    // مثال تنسيق: 2025-09-21 10:30 AM
                    let hours = dt.getHours();
                    const minutes = String(dt.getMinutes()).padStart(2, '0');
                    const ampm = hours >= 12 ? 'م' : 'ص';
                    hours = ((hours + 11) % 12) + 1;
                    const Y = dt.getFullYear();
                    const M = String(dt.getMonth() + 1).padStart(2, '0');
                    const D = String(dt.getDate()).padStart(2, '0');
                    return `${Y}-${M}-${D} ${hours}:${minutes} ${ampm}`;
                } catch (e) {
                    return isoString;
                }
            }

            // إرسال AJAX (PUT عبر _method)
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                const url = "{{ route('sessions.updateStartTime', $session->id) }}";
                const fd = new FormData(form);
                // Laravel expects _method = PUT when using POST
                fd.append('_method', 'PUT');

                try {
                    const resp = await fetch(url, {
                        method: 'POST', // نرسل POST مع _method=PUT
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')
                                ?.value || '',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: fd,
                        credentials: 'same-origin'
                    });

                    const ct = resp.headers.get('content-type') || '';
                    let data;
                    if (ct.indexOf('application/json') !== -1) {
                        data = await resp.json();
                    } else {
                        data = {
                            message: await resp.text()
                        };
                    }

                    if (resp.ok) {
                        // اغلاق الصندوق
                        if (box) box.style.display = 'none';

                        // تحديث العنصر الذي يعرض الموعد فورياً
                        const displayEl = document.getElementById('display-start-time');
                        if (displayEl) {
                            if (data.start_time) {
                                // نتوقع start_time بصيغة ISO من السيرفر
                                displayEl.textContent = formatLocalReadable(data.start_time);
                            } else {
                                // fallback: استخدم قيمة الـ input
                                const val = document.getElementById('start_time_inline')?.value;
                                if (val) {
                                    // قيمة الـ input تكون مثل "YYYY-MM-DDThh:mm"
                                    const dt = new Date(val);
                                    if (!isNaN(dt)) displayEl.textContent = formatLocalReadable(dt
                                        .toISOString());
                                    else displayEl.textContent = val;
                                }
                            }
                        }

                        // عرض snackbar نجاح
                        showSnackbar(data.message || '✅ تم تعديل الموعد بنجاح', 'success');
                    } else {
                        // فشل: عرض رسالة واضحة
                        // إذا كان السيرفر أرسل رسائل تحقق أو HTML نعرض الرسالة النصية
                        const msg = data?.message || `حدث خطأ (كود ${resp.status})`;
                        showSnackbar(msg, 'error');
                    }
                } catch (err) {
                    console.error(err);
                    showSnackbar('حدث خطأ أثناء الاتصال بالسيرفر.', 'error');
                }
            });

            // دالة snackbar بسيطة (لو عندك واحدة بدّل الاستدعاء)
            function showSnackbar(message, type = 'success') {
                // تحقق لو فيه snackbar موجود شبيه فشيله أول
                const existing = document.querySelector('.snackbar.temp-js');
                if (existing) existing.remove();

                let el = document.createElement('div');
                el.className = 'snackbar temp-js show ' + (type === 'error' ? 'error' : 'success');
                el.style.zIndex = 99999;
                el.innerHTML = `<span>${message}</span>`;
                document.body.appendChild(el);

                // عرض/إخفاء أنيميشن
                setTimeout(() => el.classList.add('show'), 10);
                setTimeout(() => el.classList.remove('show'), 2600);
                setTimeout(() => el.remove(), 3000);
            }
        });
    </script>


    <script>
        document.addEventListener("DOMContentLoaded", () => {

            const checkoutForm = document.getElementById('checkoutForm');

            const hours = @json($hours ?? 0);
            const purchasesCount = @json($session->purchases->count() ?? 0);
            const purchases = @json($purchasesArray);

            // إضافة hidden input للمشتريات
            let purchasesInput = document.createElement('input');
            purchasesInput.type = 'hidden';
            purchasesInput.name = 'purchases';
            purchasesInput.value = JSON.stringify(purchases);
            checkoutForm.appendChild(purchasesInput);

            checkoutForm.addEventListener('submit', function(e) {
                e.preventDefault(); // وقف الإرسال الأول

                // 1) حالة الجلسة الفارغة: ساعات 0 ومشتريات 0
                if (hours === 0 && purchasesCount === 0) {
                    const confirmDelete = confirm(
                        "⚠️ الجلسة لا يوجد بها محتويات حتى الآن. هل تريد حذفها نهائيًا؟");

                    if (confirmDelete) {
                        let form = document.createElement('form');
                        form.method = 'POST';
                        form.action = "{{ route('sessions.deleteEmpty', $session->id) }}";

                        let token = document.createElement('input');
                        token.type = 'hidden';
                        token.name = '_token';
                        token.value = "{{ csrf_token() }}";
                        form.appendChild(token);

                        let method = document.createElement('input');
                        method.type = 'hidden';
                        method.name = '_method';
                        method.value = 'DELETE';
                        form.appendChild(method);

                        document.body.appendChild(form);
                        form.submit();
                    }
                    return; // وقف هنا
                }

                // 2) لو الجلسة مش فاضية → نفتح مودال الدفع
                const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
                modal.show();
            });
        });

        // 3) اختيار طريقة الدفع
        function choosePayment(type) {
            document.getElementById('payment_type').value = type;

            // ابعت الفورم الحقيقي
            document.getElementById('checkoutForm').submit();
        }
    </script>

    <script>
        document.querySelectorAll(".invoiceForm").forEach(form => {
            form.addEventListener("submit", function(e) {
                e.preventDefault();
                let button = form.querySelector(".product-item");
                let id = button.getAttribute("data-id");
                let item = [{
                    id: parseInt(id),
                    qty: 1
                }];
                form.querySelector(".itemsInput").value = JSON.stringify(item);
                form.submit();
            });
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const btn = document.querySelector(".calendar-btn");
            const input = document.querySelector(".calendar-input");
            const saveBtn = document.querySelector(".save-time-btn");

            btn.addEventListener("click", () => {
                input.style.display = "block";
                saveBtn.style.display = "inline-block";
                input.focus();
            });
        });
    </script>


    <script>
        window.addEventListener("pageshow", function(event) {
            if (event.persisted || performance.getEntriesByType("navigation")[0].type === "back_forward") {
                // هنا تمنع ظهور المودال
                const modal = document.getElementById("splitSessionModal");
                if (modal) {
                    modal.classList.remove("show");
                    modal.style.display = "none";
                    modal.setAttribute("aria-hidden", "true");
                    modal.removeAttribute("aria-modal");

                    // لو Bootstrap 5 بيستعمل backdrop
                    const backdrop = document.querySelector(".modal-backdrop");
                    if (backdrop) {
                        backdrop.remove();
                    }
                    document.body.classList.remove("modal-open");
                    document.body.style.overflow = "auto";
                }
            }
        });
    </script>

    {{-- استدعاء المودال من ملف خارجي --}}
    @include('session.modal.split_persons')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // قيم من السيرفر (عدل أسماء المتغيرات حسب ما في Blade)
            const totalHoursPrice = Number(@json($hours_price ?? 0)); // إجمالي سعر الساعات للجلسة
            const sessionPersons = Number(@json($session->persons ?? 1)); // إجمالي عدد الأفراد في الجلسة
            // purchasesArray: [{product_id, name, qty, price, cost}, ...]
            const purchases = @json($purchasesArray ?? []);

            // عناصر DOM داخل المودال
            const splitForm = document.querySelector('#splitSessionModal form');
            const splitPersonsInput = splitForm.querySelector('input[name="split_persons"]');
            const itemsInputs = Array.from(splitForm.querySelectorAll('input[name^="items"]'));
            const splitPriceValueEl = document.getElementById('splitPriceValue');
            const splitItemsValueEl = document.getElementById('splitItemsValue');
            const splitHoursValueEl = document.getElementById('splitHoursValue');
            const submitBtn = splitForm.querySelector('button[type="submit"]');

            // safety: لو الفورم أو العنصر مش موجود نتوقف
            if (!splitForm || !splitPersonsInput) return;

            // بناء خريطة productId => price, maxQty
            const priceMap = {};
            purchases.forEach(p => {
                priceMap[String(p.product_id)] = {
                    price: Number(p.price || 0),
                    maxQty: Number(p.qty || 0)
                };
            });

            // دالة تنسيق الأرقام
            function fmt(n) {
                return Number(n || 0).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            // دالة حساب حصة الساعات للمجموعة المنفصلة
            function calcHoursShare(splitPersons) {
                if (!sessionPersons || sessionPersons <= 0) return 0;
                // نفترض: حصة الساعات = (totalHoursPrice / sessionPersons) * splitPersons
                const perPerson = totalHoursPrice / sessionPersons;
                return perPerson * splitPersons;
            }

            // دالة حساب مجموع المشتريات المختارة في الفورم
            function calcSelectedItems() {
                let sum = 0;
                // كل input name مثل items[<product_id>]
                itemsInputs.forEach(inp => {
                    const name = inp.getAttribute('name'); // items[12]
                    const matches = name.match(/items\[(\d+)\]/);
                    if (!matches) return;
                    const productId = matches[1];
                    const qty = Number(inp.value || 0);
                    const info = priceMap[productId];
                    if (!info) return;
                    // safety: clamp qty
                    const clamped = Math.max(0, Math.min(qty, info.maxQty));
                    sum += clamped * info.price;
                });
                return sum;
            }

            // تحقق من صلاحية الإدخالات (عدد الأفراد، الكميات)
            function validateInputs() {
                const splitPersons = Number(splitPersonsInput.value || 0);
                if (!Number.isFinite(splitPersons) || splitPersons < 1 || splitPersons >= sessionPersons) {
                    return {
                        ok: false,
                        message: `عدد الأفراد يجب أن يكون بين 1 و ${sessionPersons - 1}`
                    };
                }
                // تحقق كميات المشتريات
                for (let inp of itemsInputs) {
                    const name = inp.getAttribute('name');
                    const productId = (name.match(/items\[(\d+)\]/) || [])[1];
                    const info = priceMap[productId];
                    if (!info) continue;
                    const qty = Number(inp.value || 0);
                    if (!Number.isFinite(qty) || qty < 0) {
                        return {
                            ok: false,
                            message: 'الرجاء إدخال أعداد صحيحة للمشتريات'
                        };
                    }
                    if (qty > info.maxQty) {
                        return {
                            ok: false,
                            message: `الكمية للمُنتج ${productId} لا يمكن أن تتجاوز ${info.maxQty}`
                        };
                    }
                }
                return {
                    ok: true
                };
            }

            // الدالة الأساسية التي تحدّث الواجهة
            function refresh() {
                const valid = validateInputs();
                if (!valid.ok) {
                    // تعطيل زر الإرسال وعرض رسالة قصيرة (يمكن تحسين الواجهة لاحقًا)
                    if (submitBtn) submitBtn.disabled = true;
                    splitPriceValueEl.textContent = 'خطأ في الإدخال';
                    splitItemsValueEl.textContent = '-';
                    splitHoursValueEl.textContent = '-';
                    return;
                }
                if (submitBtn) submitBtn.disabled = false;

                const splitPersons = Number(splitPersonsInput.value || 0);
                const hoursShare = calcHoursShare(splitPersons);
                const itemsSum = calcSelectedItems();
                const total = hoursShare + itemsSum;

                splitHoursValueEl.textContent = `${fmt(hoursShare)} جنيه`;
                splitItemsValueEl.textContent = `${fmt(itemsSum)} جنيه`;
                splitPriceValueEl.textContent = `${fmt(total)} جنيه`;

                // ضع قيمة مخفية في الفورم لتُرسَل للسيرفر (مثلاً amount) — سيتم إنشاؤها أو تحديثها
                let existingHidden = splitForm.querySelector('input[name="split_total_amount"]');
                if (!existingHidden) {
                    existingHidden = document.createElement('input');
                    existingHidden.type = 'hidden';
                    existingHidden.name = 'split_total_amount';
                    splitForm.appendChild(existingHidden);
                }
                existingHidden.value = total.toFixed(2);

                // أيضًا نُحدّث حقل المشتريات (لو محتاجين إرسال التفاصيل)
                let itemsHidden = splitForm.querySelector('input[name="split_items_summary"]');
                if (!itemsHidden) {
                    itemsHidden = document.createElement('input');
                    itemsHidden.type = 'hidden';
                    itemsHidden.name = 'split_items_summary';
                    splitForm.appendChild(itemsHidden);
                }
                // نبني ملخّص: {productId: qty, ...} فقط للتي qty>0
                const summary = {};
                itemsInputs.forEach(inp => {
                    const matches = inp.name.match(/items\[(\d+)\]/);
                    if (!matches) return;
                    const pid = matches[1];
                    const qty = Number(inp.value || 0);
                    if (qty > 0) summary[pid] = qty;
                });
                itemsHidden.value = JSON.stringify(summary);
            }

            // ربط الأحداث
            splitPersonsInput.addEventListener('input', refresh);
            itemsInputs.forEach(inp => {
                // اجازه إدخال أرقام سالبة؟ نمنعها فورًا
                inp.addEventListener('input', () => {
                    // نلقي نظرة سريعة على max
                    const name = inp.name;
                    const pid = (name.match(/items\[(\d+)\]/) || [])[1];
                    const info = priceMap[pid];
                    let v = Number(inp.value || 0);
                    if (!Number.isFinite(v)) v = 0;
                    if (info) {
                        if (v < 0) v = 0;
                        if (v > info.maxQty) v = info.maxQty;
                    }
                    // نكتب القيمة المصحّحة (بهذا نمنع القيم غير المسموح بها)
                    inp.value = v;
                    refresh();
                });
                // نستخدم change لتحديث عند الخروج من الحقل أيضاً
                inp.addEventListener('change', refresh);
            });

            // تهيئة: إذا ما في قيمة في الحقل نضع قيمة افتراضية 1 (لو تريد)
            if (!splitPersonsInput.value) {
                // لا نفرض قيمة؛ نترك المستخدم يحدد. لكن لو تحب تفعل التعليق التالي:
                // splitPersonsInput.value = 1;
            }

            // أول تشغيل
            refresh();

        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let selectedProducts = []; // المنتجات المختارة مؤقتاً

            // إنشاء الـ Snackbar container
            let snackbar = document.createElement("div");
            snackbar.id = "selectedProductsSnackbar";
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
            confirmBtn.className = "btn btn-success btn-sm";
            snackbar.appendChild(confirmBtn);

            function updateSnackbarUI() {
                list.innerHTML = "";
                if (selectedProducts.length === 0) {
                    snackbar.style.display = "none";
                    return;
                }

                selectedProducts.forEach(p => {
                    const prodName = document.querySelector(`.product-item[data-id="${p.product_id}"]`)
                        .textContent;
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

            // التعامل مع أزرار المنتجات
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

            confirmBtn.addEventListener("click", function() {
                if (selectedProducts.length === 0) return;

                // نستخدم أول فورم كـ مرجع (كلها نفس الأكشن)
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
                    const row = this.closest('[data-id]');
                    const id = row.dataset.id;
                    removedPurchases.push(id); // نحفظه في مصفوفة
                    row.remove();
                });
            });

            // 💾 حفظ التعديلات
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                let formData = new FormData(form);
                formData.append('removed', JSON.stringify(removedPurchases)); // نضيف المنتجات المحذوفة

                fetch("{{ route('sessionPurchases.update', $session->id ?? 1) }}", {
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

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const splitForm = document.querySelector("#splitSessionModal form");

            splitForm.addEventListener("submit", function(e) {
                e.preventDefault(); // وقف الإرسال

                // افتح مودال اختيار الدفع
                const splitPayModal = new bootstrap.Modal(document.getElementById("splitPaymentModal"));
                splitPayModal.show();
            });
        });

        // اختيار نوع الدفع
        function chooseSplitPayment(type) {
            document.getElementById("split_payment_type").value = type;

            // إرسال الفورم بعد الاختيار
            document.querySelector("#splitSessionModal form").submit();
        }
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {

            const checkoutForm = document.getElementById('checkoutForm');

            checkoutForm.addEventListener('submit', function(e) {
                e.preventDefault(); // وقف الإرسال الأول

                // تحقق من حالة الجلسة
                const hours = @json($hours ?? 0);
                const purchasesCount = @json($session->purchases->count() ?? 0);

                // 1️⃣ الجلسة فاضية → حذف
                if (hours === 0 && purchasesCount === 0) {
                    const confirmDelete = confirm("⚠️ الجلسة لا يوجد بها ساعات أو مشتريات. هل تريد حذفها؟");
                    if (confirmDelete) {
                        let form = document.createElement('form');
                        form.method = 'POST';
                        form.action = "{{ route('sessions.deleteEmpty', $session->id) }}";

                        form.innerHTML = `
                    @csrf
                    <input type="hidden" name="_method" value="DELETE">
                `;

                        document.body.appendChild(form);
                        form.submit();
                    }
                    return;
                }

                // 2️⃣ الجلسة مش فاضية → افتح مودال الدفع
                const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
                modal.show();
            });
        });

        // 3️⃣ عند الضغط على كاش/ديجيتال
        function choosePayment(type) {
            document.getElementById('payment_type').value = type;
            document.getElementById('checkoutForm').submit(); // إعادة إرسال الفورم الحقيقي
        }
    </script>

    {{-- 🟢 مودال تعديل المشتريات --}}
    <div class="modal fade" id="purchasesModal" tabindex="-1" aria-labelledby="purchasesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px;">
                <div class="modal-header">
                    <h5 class="modal-title">🛒 تعديل المشتريات</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updatePurchasesForm">
                        @csrf

                        <div id="purchaseItemsContainer">
                            @forelse ($purchases as $purchase)
                                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2"
                                    data-id="{{ $purchase->id }}">
                                    <span class="fw-bold">{{ $purchase->product->name }}</span>

                                    <div class="d-flex align-items-center">
                                        <button type="button"
                                            class="btn btn-outline-secondary btn-sm decrease">-</button>
                                        <input type="number" class="form-control mx-2 text-center quantity-input"
                                            name="quantities[{{ $purchase->id }}]" value="{{ $purchase->quantity }}"
                                            min="1" style="width:70px;">
                                        <button type="button"
                                            class="btn btn-outline-secondary btn-sm increase">+</button>
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

    <!-- مودال اختيار طريقة الدفع -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="text-align:center; padding:20px;">

                <h4>💰 اختر طريقة الدفع</h4>

                <input type="hidden" id="payment_type" form="checkoutForm" name="payment_type">

                <div class="d-flex justify-content-around mt-4">
                    <button class="btn btn-success" onclick="choosePayment('cash')">💵 كاش</button>
                    <button class="btn btn-primary" onclick="choosePayment('digital')">💳 ديجيتال</button>
                </div>

            </div>
        </div>
    </div>


    <!-- Modal الدفع للحساب المنفصل -->
    <div class="modal fade" id="splitPaymentModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">اختر طريقة الدفع</h5>
                </div>

                <div class="modal-body text-center">
                    <button class="btn btn-success w-100 mb-2" onclick="chooseSplitPayment('cash')">💵 كاش</button>
                    <button class="btn btn-primary w-100" onclick="chooseSplitPayment('digital')">💳 ديجيتال</button>
                </div>

            </div>
        </div>
    </div>

@endsection



@section('style')
    <style>
        /* ==========================
           Unified responsive stylesheet (Dark Hive Theme)
           Desktop & Mobile (merged)
           ========================== */

        /* ===== Variables & reset ===== */
        :root {
            --card-max-width: 980px;
            --body-padding-desktop: 40px;
            --body-padding-mobile: 12px;
            --base-font: "Cairo", sans-serif;
            --muted: #9a9a9a;
            --accent: #ffb84d;
            --accent-dark: #d49a3d;
            --badge-bg: #ffb84d;
            --text-main: #f1f1f1;
            --text-dim: #c5c5c5;
            --border-light: rgba(255, 255, 255, 0.1);
            --bg-dark: #0e0e11;
            --card-bg: #16161a;
        }

        /* ===== Reset ===== */
        html,
        body {
            box-sizing: border-box;
        }

        *,
        *::before,
        *::after {
            box-sizing: inherit;
        }

        /* ===== Base body ===== */
        body {
            font-family: var(--base-font);
            margin: 0;
            padding: var(--body-padding-desktop);
            background: var(--bg-dark);
            color: var(--text-main);
            -webkit-font-smoothing: antialiased;
        }

        /* ===== Container & card ===== */
        .subscription-container {
            max-width: var(--card-max-width);
            margin: 24px auto;
            padding: 18px;
            box-sizing: border-box;
        }

        .card {
            background: var(--card-bg);
            border-radius: 18px;
            padding: 28px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.45);
            border: 1px solid var(--border-light);
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .card:active {
            transform: translateY(1px);
        }

        /* old layout */
        .session-container {
            width: 85%;
            max-width: 750px;
            margin: 30px auto;
            background: #1d1d22;
            padding: 25px;
            border-radius: 18px;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.5);
            animation: fadeInUp 0.8s ease;
        }

        /* ===== Header ===== */
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
        }

        .card-header h2 {
            font-size: 20px;
            margin: 0;
            letter-spacing: .2px;
            color: var(--accent);
        }

        .badge {
            background: var(--accent-dark);
            color: #000;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 700;
        }

        /* ===== Sections & boxes ===== */
        .section {
            margin-bottom: 12px;
        }

        .section h3 {
            color: var(--accent);
            font-size: 16px;
            margin: 0 0 10px 0;
        }

        .box {
            background: #1c1c20;
            padding: 14px;
            border-radius: 12px;
            margin-bottom: 10px;
            border: 1px solid var(--border-light);
        }

        .box p {
            margin: 6px 0;
            font-size: 15px;
            line-height: 1.5;
            color: var(--text-dim);
        }

        strong {
            font-weight: 700;
            color: var(--text-main);
        }

        /* زرار حساب منفصل */
        .btn-info[data-bs-target="#splitSessionModal"] {
            background: linear-gradient(135deg, var(--accent), #d4a043);
            color: var(--btn-text);
            border: none;
            border-radius: 25px;
            padding: 10px 20px;
            font-weight: 600;
            font-size: 15px;
            box-shadow: 0 4px 15px rgba(255, 184, 77, 0.25);
            transition: all 0.3s ease;
            margin: 20px 0;
            /* يبعد عن العناصر حوليه */
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-info[data-bs-target="#splitSessionModal"]:hover {
            background: linear-gradient(135deg, #ffd97a, #ffb84d);
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(255, 184, 77, 0.4);
        }

        /* ===== Highlight / Prices ===== */
        .highlight-box {
            background: #1c1c20;
            border-left: 6px solid var(--accent);
            padding: 12px 15px;
            border-radius: 14px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: bold;
            font-size: 15px;
            color: var(--text-main);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.4);
        }

        .final-price {
            background: rgba(76, 175, 80, 0.15);
            border-left: 6px solid #4caf50;
            color: #8ef58a;
            font-size: 18px;
            font-weight: 800;
        }

        .full-day {
            background: rgba(255, 204, 0, 0.15);
            border-left: 6px solid #ffcc00;
            color: #ffe88a;
            font-weight: 700;
        }

        /* ===== Purchases / product items ===== */
        .selected-products .purchase-row {
            padding: 8px 10px;
            border-radius: 8px;
            margin-bottom: 6px;
        }

        .selected-products span {
            background: rgba(255, 184, 77, 0.15);
            color: var(--accent-light);
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 14px;
            animation: zoomIn 0.4s ease;
        }

        .products-list {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 14px;
            margin: 20px 0;
        }

        .product-item {
            background: #1a1a1f;
            border: 1px solid var(--border-light);
            border-radius: 12px;
            padding: 12px 14px;
            min-width: 120px;
            min-height: 70px;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-main);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.4);
            transition: all 0.25s ease;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        /* Hover effect */
        .product-item:hover {
            transform: translateY(-4px) scale(1.03);
            border-color: var(--accent);
            box-shadow: 0 6px 18px rgba(255, 184, 77, 0.25);
            color: var(--accent);
        }

        /* ===== Buttons ===== */
        .btn {
            display: inline-block;
            background: var(--accent);
            color: #000;
            padding: 10px 14px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 15px;
            border: 0;
            cursor: pointer;
            transition: transform .14s ease, background .14s ease;
            min-height: 44px;
            text-align: center;
        }

        .btn:hover {
            transform: translateY(-3px);
            background: var(--accent-light);
        }

        .btn.btn-danger {
            background: #e74c3c;
            color: #fff;
        }

        .btn.btn-info {
            background: #3498db;
            color: #fff;
        }

        /* save/end buttons */
        .save-btn {
            background: #27ae60;
            border: none;
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 15px;
            margin-bottom: 15px;
            width: 30%;
            color: #fff;
        }

        .save-btn:hover {
            background: #2ecc71;
            transform: scale(1.05);
        }

        .end-btn {
            background: #e74c3c;
            border: none;
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 16px;
            color: #fff;
            cursor: pointer;
            transition: 0.3s;
        }

        .end-btn:hover {
            background: #c0392b;
            transform: scale(1.05);
        }

        /* edit button */
        .edit-btn {
            display: inline-block;
            padding: 12px 18px;
            background: #1f1f25;
            color: var(--accent);
            font-weight: 700;
            font-size: 15px;
            border-radius: 14px;
            text-decoration: none;
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.5);
            transition: all 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .edit-btn::after {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 184, 77, 0.2);
            transform: skewX(-20deg);
            transition: left 0.4s ease;
        }

        .edit-btn:hover::after {
            left: 100%;
        }

        .edit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 20px rgba(255, 184, 77, 0.25);
        }

        /* split button */
        .split-btn {
            background: #3498db;
            color: #fff;
            font-weight: bold;
            border: none;
            border-radius: 12px;
            padding: 10px 20px;
            cursor: pointer;
            transition: 0.3s;
        }

        .split-btn:hover {
            background: #2980b9;
            transform: scale(1.05);
        }

        /* ===== Modal ===== */
        .modal-content {
            background: #1a1a1f;
            border: 1px solid var(--border-light);
            color: var(--text-main);
            border-radius: 20px !important;
        }

        .modal-header {
            border-bottom: 1px solid var(--border-light);
            background: #202025;
            color: var(--accent);
            border-radius: 20px 20px 0 0 !important;
        }

        .modal-title {
            font-weight: bold;
            font-size: 18px;
        }

        .modal-body label {
            font-weight: bold;
            color: var(--text-main);
        }

        .modal-footer {
            border-top: none;
        }

        .modal {
            display: none;
        }

        .modal.show {
            display: block;
        }

        /* inline edit box */
        #inlineEditBox {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 18px;
        }

        #inlineEditBox .inner {
            background: #1c1c21;
            padding: 18px;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
            max-width: 480px;
            width: 100%;
        }

        /* ===== Snackbar ===== */
        .snackbar {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #222;
            color: #fff;
            padding: 12px 18px;
            border-radius: 10px;
            font-size: 14px;
            z-index: 9999;
            opacity: 0;
            transform: translateX(120%);
            transition: opacity 0.4s ease, transform 0.4s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .snackbar.show {
            opacity: 1;
            transform: translateX(0);
        }

        .snackbar.success {
            background: #27ae60;
        }

        .snackbar.error {
            background: #c0392b;
        }

        /* ===== Misc helpers ===== */
        small.text-muted {
            color: var(--text-dim);
            font-size: 13px;
        }

        #conflictWarning {
            color: #e74c3c;
            font-weight: 700;
            margin-top: 8px;
        }

        .selected-products p,
        .purchase-row p {
            margin: 0;
            padding: 6px 0;
        }

        .result-item {
            cursor: pointer;
            padding: 8px;
        }

        /* ===== Animations ===== */
        @keyframes wiggle {

            0%,
            100% {
                transform: rotate(0);
            }

            25% {
                transform: rotate(-3deg);
            }

            75% {
                transform: rotate(3deg);
            }
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

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes zoomIn {
            from {
                transform: scale(0.8);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* ===== Responsive ===== */
        @media (max-width:420px) {
            body {
                padding: var(--body-padding-mobile);
            }

            .subscription-container {
                padding: 8px;
                margin: 12px auto;
            }

            .card {
                padding: 16px;
                border-radius: 14px;
            }

            .card-header h2 {
                font-size: 18px;
            }

            .box {
                padding: 12px;
            }

            .badge {
                padding: 6px 10px;
                font-size: 13px;
            }

            .box p {
                font-size: 15px;
            }

            .form-btn .btn,
            .action-btns .btn {
                width: 100%;
                display: block;
                margin-bottom: 8px;
            }

            #inlineEditBox>div {
                width: 92% !important;
            }
        }

        @media (max-width:768px) {
            .btn {
                padding: 12px 16px;
                font-size: 16px;
                border-radius: 14px;
            }

            .subscription-container {
                padding-left: 16px;
                padding-right: 16px;
            }
        }

        @media (min-width:1200px) {
            .card {
                padding: 36px;
                border-radius: 22px;
            }

            .card-header h2 {
                font-size: 22px;
            }

            .box p {
                font-size: 16px;
            }

            .subscription-container {
                padding-left: 24px;
                padding-right: 24px;
            }
        }

        @media (min-width:768px) {
            .subscription-container {
                padding-left: 24px;
                padding-right: 24px;
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
        }

        /* مودال Dark + Gold */
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
