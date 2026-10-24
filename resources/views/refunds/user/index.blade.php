@extends('layouts.app_page')

@section('content')
    <div class="drafts-wrapper">

        <!-- فورم إضافة طلب ارتجاع -->
        <div class="draft-form">
            <h2>🔄 طلب ارتجاع جديد</h2>
            <p class="subtitle">المبلغ سيُخصم فورياً من شيفتك الحالي ويظل معلقاً حتى يؤكده المدير.</p>

            @if($openShift)
                <div class="shift-badge-info">
                    <span>🟢 شيفت مفتوح (#{{ $openShift->id }})</span>
                    <span>إجمالي المصروفات: {{ number_format($openShift->total_expense, 2) }} ج.م</span>
                </div>
            @else
                <div class="shift-badge-warning">
                    ⚠️ تنبيه: لا يوجد شيفت مفتوح حالياً. افتح شيفت لتسجيل الارتجاع.
                </div>
            @endif

            <form action="{{ route('refund-requests.store') }}" method="POST" id="refundForm">
                @csrf

                <!-- أزرار التبديل بين الاختيار من فواتير الشيفت والإدخال اليدوي -->
                <div class="mode-tabs">
                    <button type="button" class="tab-btn active" id="tabFromInvoice" onclick="switchRefundMode('invoice')">
                        🧾 اختيار من فواتير الشيفت (الأساسي)
                    </button>
                    <button type="button" class="tab-btn" id="tabManual" onclick="switchRefundMode('manual')">
                        ✍️ إدخال يدوي بدون فاتورة
                    </button>
                </div>

                <!-- 1. سكشن الاختيار من فواتير الشيفت الحالي -->
                <div id="invoiceSection" class="form-group">
                    <label>اختر الفاتورة الصادرة في شيفتك الحالي <span class="required">*</span></label>
                    <select name="invoice_id" id="shiftInvoiceSelect" class="styled-select" onchange="onInvoiceSelected(this)">
                        <option value="">-- اضغط لاختيار فاتورة من هذا الشيفت --</option>
                        @foreach($shiftInvoices as $inv)
                            <option value="{{ $inv->id }}"
                                    data-total="{{ $inv->total }}"
                                    data-number="{{ $inv->invoice_number ?? $inv->id }}"
                                    data-client="{{ $inv->client?->name ?? 'عميل نقدي' }}"
                                    data-payment="{{ $inv->shift_payment_type ?? 'cash' }}"
                                    data-items="{{ $inv->items_summary }}"
                                    {{ old('invoice_id') == $inv->id ? 'selected' : '' }}>
                                #{{ $inv->invoice_number ?? $inv->id }} | {{ $inv->client?->name ?? 'عميل نقدي' }} — {{ number_format($inv->total, 2) }} ج.م ({{ $inv->items_summary }})
                            </option>
                        @endforeach
                    </select>

                    @if($shiftInvoices->isEmpty())
                        <div class="empty-invoices-hint">
                            ℹ️ لم يتم إنشاء فواتير في شيفتك المفتوح حالياً بعد. يمكنك التبديل إلى <strong>الإدخال اليدوي</strong> أدناه.
                        </div>
                    @endif

                    <!-- كارت معاينة الفاتورة المختارة -->
                    <div id="invoicePreviewCard" class="invoice-preview-card" style="display: none;">
                        <div class="preview-header">
                            <span class="preview-badge" id="previewBadgeNum">فاتورة #</span>
                            <span class="preview-total" id="previewBadgeTotal">0.00 ج.م</span>
                        </div>
                        <div class="preview-grid">
                            <div><strong>👤 العميل:</strong> <span id="previewClientName">—</span></div>
                            <div><strong>🛍️ الأصناف:</strong> <span id="previewItemsList">—</span></div>
                        </div>
                    </div>
                </div>

                <!-- 2. حقل رقم الفاتورة في حالة الإدخال اليدوي -->
                <div id="manualInvoiceGroup" class="form-group" style="display: none;">
                    <label>رقم الفاتورة (اختياري)</label>
                    <input type="text" name="invoice_number" id="invoiceNumberInput" placeholder="مثال: INV-1002 أو رقم ID" value="{{ old('invoice_number') }}">
                </div>

                <!-- 3. المبلغ وطريقة الدفع في صف واحد -->
                <div class="form-row">
                    <div class="form-group col-half">
                        <label>المبلغ المسترجع (ج.م) <span class="required">*</span></label>
                        <input type="number" step="0.01" name="amount" id="amountInput" placeholder="0.00" value="{{ old('amount') }}" required min="0.01">
                        <small class="helper-text" id="amountHelper">يتملى تلقائياً من الفاتورة ويمكنك تعديله لو الارتجاع جزئي</small>
                        @error('amount')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group col-half">
                        <label>طريقة دفع المرتجع</label>
                        <select name="payment_type" id="paymentTypeSelect" class="styled-select">
                            <option value="cash" {{ old('payment_type') == 'cash' ? 'selected' : '' }}>💵 كاش (من درج الشيفت)</option>
                            <option value="digital" {{ old('payment_type') == 'digital' ? 'selected' : '' }}>💳 ديجيتال (فودافون كاش / فيزا)</option>
                        </select>
                    </div>
                </div>

                <!-- 4. سبب الارتجاع -->
                <div class="form-group">
                    <label>سبب الارتجاع (ملاحظة تفصيلية إلزامية) <span class="required">*</span></label>
                    <textarea name="notes" id="notesInput" rows="3" placeholder="اكتب هنا سبب الارتجاع بالتفصيل ليظهر للمدير للموافقة عليه..." required>{{ old('notes') }}</textarea>
                    @error('notes')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn-submit" {{ !$openShift ? 'disabled' : '' }}>
                    📤 إرسال طلب الارتجاع وخصمه من الشيفت
                </button>
            </form>
        </div>

        <!-- قائمة طلبات الارتجاع السابقة -->
        <div class="draft-list">
            <div class="list-header-row">
                <h3>📋 طلبات الارتجاع الخاصة بي</h3>
                <div class="stats-mini">
                    <span class="badge-mini pending">⏳ معلق: {{ $stats['pending'] }}</span>
                    <span class="badge-mini approved">✅ مؤكد: {{ $stats['approved'] }}</span>
                </div>
            </div>

            <div class="cards">
                @forelse($refundRequests as $req)
                    <div class="card-content status-{{ $req->status }}">
                        <div class="card-top">
                            <h4>💰 {{ number_format($req->amount, 2) }} ج.م</h4>
                            <span class="status-pill {{ $req->status }}">
                                @if($req->isPending())
                                    ⏳ معلق (مخصوم)
                                @elseif($req->isApproved())
                                    ✅ مؤكد من الإدارة
                                @elseif($req->isRejected())
                                    ❌ مرفوض (تم إلغاء الخصم)
                                @endif
                            </span>
                        </div>

                        <p class="notes-text"><strong>السبب:</strong> {{ $req->notes }}</p>

                        <div class="card-meta">
                            <span>💳 {{ $req->payment_type === 'digital' ? 'ديجيتال' : 'كاش' }}</span>
                            @if($req->invoice)
                                <span>🧾 فاتورة: #{{ $req->invoice->invoice_number ?? $req->invoice->id }}</span>
                            @endif
                            <span>🕒 {{ $req->created_at->diffForHumans() }}</span>
                        </div>

                        @if($req->isRejected() && $req->rejection_reason)
                            <div class="rejection-box">
                                <strong>سبب الرفض من المدير:</strong> {{ $req->rejection_reason }}
                            </div>
                        @endif

                        @if($req->isApproved())
                            <div class="approval-box">
                                أكده: {{ $req->approvedBy->name ?? 'المدير' }} في {{ $req->approved_at?->format('Y-m-d H:i') }}
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="empty">🚀 لا توجد طلبات ارتجاع مسجلة لك حتى الآن</p>
                @endforelse
            </div>

            <div class="mt-3">
                {{ $refundRequests->links() }}
            </div>
        </div>

    </div>
@endsection

@section('style')
    <style>
        :root {
            --bg-color: #0e0e11;
            --card-bg: #16161a;
            --accent: #ffb84d;
            --accent-light: #ffe6a3;
            --text-main: #f5f5f5;
            --text-dim: #bfbfbf;
            --border-light: rgba(255, 255, 255, 0.08);
            --radius: 14px;
        }

        html, body {
            box-sizing: border-box;
        }

        *, *::before, *::after {
            box-sizing: inherit;
        }

        body {
            font-family: "Cairo", sans-serif;
            margin: 0;
            padding: 40px;
            background: var(--bg-color);
            color: var(--text-main);
            -webkit-font-smoothing: antialiased;
        }

        .drafts-wrapper {
            max-width: 1050px;
            margin: 18px auto;
            display: grid;
            gap: 28px;
            grid-template-columns: 1fr 480px;
        }

        @media (max-width: 1024px) {
            .drafts-wrapper {
                grid-template-columns: 1fr;
                padding: 0 12px;
                gap: 20px;
            }
        }

        .draft-form {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 24px;
            border: 1px solid var(--border-light);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
        }

        .draft-form h2 {
            margin: 0 0 6px;
            color: var(--accent);
            font-size: 20px;
            font-weight: 700;
        }

        .draft-form .subtitle {
            font-size: 13px;
            color: var(--text-dim);
            margin-bottom: 16px;
        }

        .shift-badge-info {
            background: rgba(40, 167, 69, 0.15);
            border: 1px solid rgba(40, 167, 69, 0.3);
            color: #75b798;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
            display: flex;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .shift-badge-warning {
            background: rgba(255, 193, 7, 0.15);
            border: 1px solid rgba(255, 193, 7, 0.3);
            color: #ffc107;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            margin-bottom: 6px;
            color: #ddd;
        }

        .form-group label .required {
            color: #ff6b6b;
        }

        .form-group input,
        .form-group textarea,
        .styled-select {
            width: 100%;
            background: #1f1f24;
            border: 1px solid var(--border-light);
            color: var(--text-main);
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .styled-select:focus {
            border-color: var(--accent);
        }

        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #d49a3d, #ffb84d);
            color: #111;
            font-weight: 700;
            padding: 12px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 15px;
            font-family: inherit;
            margin-top: 10px;
            transition: all 0.2s;
        }

        .btn-submit:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(255, 184, 77, 0.3);
        }

        .btn-submit:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .draft-list {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 24px;
            border: 1px solid var(--border-light);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
        }

        .list-header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .list-header-row h3 {
            margin: 0;
            font-size: 18px;
            color: #f1f1f1;
        }

        .stats-mini {
            display: flex;
            gap: 6px;
        }

        .badge-mini {
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 6px;
            font-weight: 600;
        }

        .badge-mini.pending {
            background: rgba(255, 193, 7, 0.15);
            color: #ffc107;
        }

        .badge-mini.approved {
            background: rgba(40, 167, 69, 0.15);
            color: #75b798;
        }

        .cards {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .card-content {
            background: #1c1c22;
            border: 1px solid var(--border-light);
            border-radius: 10px;
            padding: 14px;
            position: relative;
        }

        .card-content.status-pending {
            border-right: 4px solid #ffc107;
        }

        .card-content.status-approved {
            border-right: 4px solid #28a745;
        }

        .card-content.status-rejected {
            border-right: 4px solid #dc3545;
        }

        .card-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .card-top h4 {
            margin: 0;
            color: #4ade80;
            font-size: 16px;
        }

        .status-pill {
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 600;
        }

        .status-pill.pending {
            background: rgba(255, 193, 7, 0.2);
            color: #ffd15c;
        }

        .status-pill.approved {
            background: rgba(40, 167, 69, 0.2);
            color: #8ef58a;
        }

        .status-pill.rejected {
            background: rgba(220, 53, 69, 0.2);
            color: #ff7676;
        }

        .notes-text {
            font-size: 13px;
            color: #e0e0e0;
            margin: 0 0 8px;
            line-height: 1.5;
        }

        .card-meta {
            display: flex;
            gap: 12px;
            font-size: 11px;
            color: #888;
            flex-wrap: wrap;
        }

        .rejection-box {
            margin-top: 8px;
            padding: 6px 10px;
            background: rgba(220, 53, 69, 0.1);
            border-radius: 6px;
            font-size: 12px;
            color: #ff8b8b;
        }

        .approval-box {
            margin-top: 8px;
            padding: 6px 10px;
            background: rgba(40, 167, 69, 0.1);
            border-radius: 6px;
            font-size: 11px;
            color: #92e698;
        }

        .empty {
            text-align: center;
            color: #777;
            padding: 30px;
            font-size: 14px;
        }

        /* تنسيقات التبويبات والمعاينة التلقائية للفواتير */
        .mode-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
            background: #111115;
            padding: 4px;
            border-radius: 10px;
            border: 1px solid var(--border-light);
        }

        .tab-btn {
            flex: 1;
            background: transparent;
            border: none;
            color: var(--text-dim);
            padding: 8px 12px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
        }

        .tab-btn.active {
            background: var(--accent);
            color: #111;
            box-shadow: 0 2px 8px rgba(255, 184, 77, 0.25);
        }

        .form-row {
            display: flex;
            gap: 12px;
        }

        .col-half {
            flex: 1;
            margin-bottom: 14px;
        }

        .helper-text {
            display: block;
            font-size: 11px;
            color: #aaa;
            margin-top: 4px;
        }

        .empty-invoices-hint {
            margin-top: 6px;
            padding: 8px 12px;
            background: rgba(255, 193, 7, 0.1);
            border-radius: 6px;
            color: #ffc107;
            font-size: 12px;
        }

        .invoice-preview-card {
            margin-top: 10px;
            padding: 12px 14px;
            background: #1c1c24;
            border: 1px solid rgba(255, 184, 77, 0.35);
            border-radius: 10px;
            animation: fadeIn 0.2s ease;
        }

        .preview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 1px dashed rgba(255, 255, 255, 0.1);
        }

        .preview-badge {
            font-weight: 700;
            color: var(--accent);
            font-size: 14px;
        }

        .preview-total {
            font-weight: 700;
            color: #4ade80;
            font-size: 15px;
        }

        .preview-grid {
            display: flex;
            flex-direction: column;
            gap: 4px;
            font-size: 12px;
            color: #ccc;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    <script>
        function onInvoiceSelected(select) {
            const selected = select.options[select.selectedIndex];
            const preview = document.getElementById('invoicePreviewCard');
            if (!selected || !selected.value) {
                if (preview) preview.style.display = 'none';
                return;
            }

            const total = selected.dataset.total || '0';
            const num = selected.dataset.number || selected.value;
            const client = selected.dataset.client || 'عميل نقدي';
            const payment = selected.dataset.payment || 'cash';
            const items = selected.dataset.items || '—';

            // ملء المبلغ
            const amountInput = document.getElementById('amountInput');
            if (amountInput) {
                amountInput.value = parseFloat(total).toFixed(2);
            }

            // تحديد طريقة الدفع
            const paySelect = document.getElementById('paymentTypeSelect');
            if (paySelect) {
                paySelect.value = (payment === 'digital') ? 'digital' : 'cash';
            }

            // إظهار كارت المعاينة
            const bNum = document.getElementById('previewBadgeNum');
            const bTot = document.getElementById('previewBadgeTotal');
            const cName = document.getElementById('previewClientName');
            const iList = document.getElementById('previewItemsList');

            if (bNum) bNum.textContent = 'فاتورة #' + num;
            if (bTot) bTot.textContent = parseFloat(total).toFixed(2) + ' ج.م';
            if (cName) cName.textContent = client;
            if (iList) iList.textContent = items;
            if (preview) preview.style.display = 'block';

            // اقتراح الملاحظة
            const notesInput = document.getElementById('notesInput');
            if (notesInput && (!notesInput.value.trim() || notesInput.value.startsWith('ارتجاع فاتورة #'))) {
                notesInput.value = 'ارتجاع فاتورة #' + num + (client !== 'عميل نقدي' ? ' (' + client + ')' : '') + ' — سبب الارتجاع: ';
                notesInput.focus();
            }
        }

        function switchRefundMode(mode) {
            const tabInvoice = document.getElementById('tabFromInvoice');
            const tabManual = document.getElementById('tabManual');
            const invoiceSection = document.getElementById('invoiceSection');
            const manualGroup = document.getElementById('manualInvoiceGroup');
            const select = document.getElementById('shiftInvoiceSelect');

            if (mode === 'invoice') {
                tabInvoice?.classList.add('active');
                tabManual?.classList.remove('active');
                if (invoiceSection) invoiceSection.style.display = 'block';
                if (manualGroup) manualGroup.style.display = 'none';
                if (select && select.value) {
                    onInvoiceSelected(select);
                }
            } else {
                tabManual?.classList.add('active');
                tabInvoice?.classList.remove('active');
                if (invoiceSection) invoiceSection.style.display = 'none';
                if (manualGroup) manualGroup.style.display = 'block';
                const preview = document.getElementById('invoicePreviewCard');
                if (preview) preview.style.display = 'none';
                if (select) {
                    select.value = '';
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const select = document.getElementById('shiftInvoiceSelect');
            if (select && select.value) {
                onInvoiceSelected(select);
            }
        });
    </script>
@endsection
