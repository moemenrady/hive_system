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

            <form action="{{ route('refund-requests.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>المبلغ المسترجع (ج.م) <span class="required">*</span></label>
                    <input type="number" step="0.01" name="amount" placeholder="مثال: 50.00" value="{{ old('amount') }}" required min="0.01">
                    @error('amount')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label>طريقة الدفع للمرتجع</label>
                    <select name="payment_type" class="styled-select">
                        <option value="cash" {{ old('payment_type') == 'cash' ? 'selected' : '' }}>💵 كاش (خرج من الدرج)</option>
                        <option value="digital" {{ old('payment_type') == 'digital' ? 'selected' : '' }}>💳 ديجيتال (فودافون كاش / فيزا)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>رقم الفاتورة (اختياري)</label>
                    <input type="text" name="invoice_number" placeholder="مثال: INV-1002 أو رقم ID" value="{{ old('invoice_number') }}">
                </div>

                <div class="form-group">
                    <label>سبب الارتجاع (ملاحظة تفصيلية) <span class="required">*</span></label>
                    <textarea name="notes" rows="3" placeholder="سجل هنا بالتفصيل سبب الارتجاع (مثال: طلب العميل إلغاء الطلب قبل التجهيز، مشروب خطأ، إلخ)..." required>{{ old('notes') }}</textarea>
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
    </style>
@endsection
