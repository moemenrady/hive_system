<!-- Modal حساب منفصل -->
<div class="modal fade animate__animated animate__zoomIn" id="splitSessionModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-lg bg-dark text-light border-0">

            <!-- الهيدر -->
            <div class="modal-header rounded-top-4"
                style="background: linear-gradient(135deg, var(--accent), #d4a043); border-bottom: none;">
                <h5 class="modal-title fw-bold text-dark">🔀 حساب منفصل</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <!-- البودي -->
            <form action="{{ route('sessions.split') }}" method="POST" class="p-4">
                @csrf
                <input type="hidden" name="payment_type" id="split_payment_type"> <!-- هنا -->
                <input type="hidden" name="session_id" value="{{ $session->id }}">
                <input type="hidden" name="hours" value="{{ $hours }}">

                <!-- عدد الأفراد -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">👥 عدد الأفراد في الجلسة</label>
                    <input type="number" class="form-control form-control-lg bg-dark text-light border-secondary"
                        value="{{ $session->persons }}" readonly>
                </div>

                <!-- الأفراد اللي هيتحاسبوا -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">👤 عدد الأفراد اللي هيدفعوا حساب منفصل</label>
                    <input type="number" name="split_persons"
                        class="form-control form-control-lg bg-dark text-light border-secondary" min="1"
                        max="{{ $session->persons - 1 }}" required>
                </div>

                <!-- المشتريات -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">🛒 اختر المشتريات الخاصة بيهم</label>
                    <div class="list-group bg-transparent">
                        @foreach ($session->purchases as $purchase)
                            <div
                                class="d-flex align-items-center mb-2 p-2 bg-dark-subtle border border-secondary rounded-3">
                                <div class="flex-grow-1">
                                    <strong>{{ $purchase->product->name }}</strong>
                                    <span class="text-muted small">({{ $purchase->quantity }} × {{ $purchase->price }}
                                        جنيه)</span>
                                </div>
                                <div style="width:120px">
                                    <input type="number" name="items[{{ $purchase->product->id }}]"
                                        class="form-control bg-dark text-light border-secondary" min="0"
                                        max="{{ $purchase->quantity }}" value="0" placeholder="العدد">
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <small class="text-muted d-block mt-2">💡 لا يمكنك إدخال عدد أكبر من الكمية المتاحة في
                        الجلسة.</small>
                </div>

                <!-- عرض السعر -->
                <div id="splitPriceBox" class="split-price-box mt-4 p-3 rounded-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-muted">الحساب المنفصل — ملخص</div>
                            <div class="h5 mb-0" id="splitPriceValue">0.00 جنيه</div>
                        </div>
                        <div class="text-end">
                            <div class="small text-muted">المشتريات</div>
                            <div id="splitItemsValue">0.00 جنيه</div>
                            <div class="small text-muted mt-1">حصة الساعات</div>
                            <div id="splitHoursValue">0.00 جنيه</div>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">💡 يتم الحساب فورًا عند تعديل عدد الأفراد أو عدد المشتريات.</small>
                    </div>
                </div>

                <!-- زر التأكيد -->
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-submit primary px-5 py-3">
                        ✅ تأكيد الحساب المنفصل
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    :root {
        --accent: #d4a043;
        --btn-primary-start: #e0e0e0;
        --btn-primary-end: #9a9a9a;
        --btn-text: #fff;
    }

    /* مودال */
    .modal-content {
        backdrop-filter: blur(12px);
        background: rgba(18, 18, 18, 0.9);
    }

    /* صناديق */
    .form-control {
        border-radius: 10px;
        transition: all 0.2s ease;
    }

    .form-control:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(212, 160, 67, 0.25);
    }

    /* صندوق السعر */
    .split-price-box {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
        border-left: 4px solid var(--accent);
        color: #f0f0f0;
        transition: all .2s ease;
    }

    .split-price-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(212, 160, 67, 0.15);
    }

    #splitPriceValue {
        color: var(--accent);
        font-weight: 800;
    }

    /* النصوص داخل ملخص الحساب */
    #splitPriceBox .small.text-muted,
    #splitPriceBox .small.text-muted.mt-1,
    #splitPriceBox .text-muted {
        color: #ffffff !important;
        opacity: 0.9;
    }
</style>
