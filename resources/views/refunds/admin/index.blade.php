@extends('layouts.app_page')

@section('content')
    <div class="refunds-admin-wrapper">
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h2 class="page-title">🔄 إدارة طلبات الارتجاع للموظفين</h2>
                    <p class="text-secondary small mb-0">متابعة واعتماد طلبات الارتجاع المخصومة من شفتات الشباب</p>
                </div>
                <div class="header-badges">
                    <span class="stat-pill pending">⏳ معلق: {{ $counts['pending'] }} ({{ number_format($counts['pending_amount'], 2) }} ج.م)</span>
                    <span class="stat-pill approved">✅ معتمد: {{ $counts['approved'] }}</span>
                </div>
            </div>

            <!-- شريط الفلترة والأقسام -->
            <div class="filter-card mt-3">
                <form action="{{ route('admin.refund-requests.index') }}" method="GET" class="filter-form">
                    <div class="filter-tabs">
                        <a href="{{ route('admin.refund-requests.index') }}" class="tab-btn {{ !request('status') ? 'active' : '' }}">
                            الكل ({{ $counts['all'] }})
                        </a>
                        <a href="{{ route('admin.refund-requests.index', ['status' => 'pending']) }}" class="tab-btn pending {{ request('status') === 'pending' ? 'active' : '' }}">
                            ⏳ معلقة ({{ $counts['pending'] }})
                        </a>
                        <a href="{{ route('admin.refund-requests.index', ['status' => 'approved']) }}" class="tab-btn approved {{ request('status') === 'approved' ? 'active' : '' }}">
                            ✅ معتمدة ({{ $counts['approved'] }})
                        </a>
                        <a href="{{ route('admin.refund-requests.index', ['status' => 'rejected']) }}" class="tab-btn rejected {{ request('status') === 'rejected' ? 'active' : '' }}">
                            ❌ مرفوضة ({{ $counts['rejected'] }})
                        </a>
                    </div>

                    <div class="filter-inputs">
                        @if(request('status'))
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="بحث بالموظف، الملاحظة، رقم الفاتورة..." class="search-input">
                        <input type="date" name="from" value="{{ request('from') }}" class="date-input" title="من تاريخ">
                        <input type="date" name="to" value="{{ request('to') }}" class="date-input" title="إلى تاريخ">
                        <button type="submit" class="btn-filter">🔍 بحث</button>
                        @if(request()->anyFilled(['q', 'from', 'to', 'status']))
                            <a href="{{ route('admin.refund-requests.index') }}" class="btn-reset">إعادة ضبط</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="timeline-container mt-4">
            @forelse ($refundRequests as $req)
                <div class="refund-card status-{{ $req->status }}">
                    <div class="refund-main">
                        <div class="refund-top-row">
                            <div class="emp-info">
                                <span class="emp-avatar">👤</span>
                                <div>
                                    <span class="emp-name">{{ $req->user->name ?? 'موظف غير معروف' }}</span>
                                    <span class="refund-time">🕒 {{ $req->created_at->format('Y-m-d g:i A') }} ({{ $req->created_at->diffForHumans() }})</span>
                                </div>
                            </div>
                            <div class="amount-badge">
                                <span class="amount-num">{{ number_format($req->amount, 2) }}</span>
                                <span class="currency">ج.م</span>
                            </div>
                        </div>

                        <div class="refund-body">
                            <div class="reason-box">
                                <strong>سبب الارتجاع:</strong>
                                <p class="mb-0">{{ $req->notes }}</p>
                            </div>

                            <div class="meta-row">
                                <span class="meta-item">
                                    💵 طريقة الدفع: <strong>{{ $req->payment_type === 'digital' ? 'ديجيتال' : 'كاش' }}</strong>
                                </span>
                                @if($req->shift_id)
                                    <span class="meta-item">
                                        ⏱️ شيفت #{{ $req->shift_id }}
                                    </span>
                                @endif
                                @if($req->invoice)
                                    <span class="meta-item">
                                        🧾 فاتورة: #{{ $req->invoice->invoice_number ?? $req->invoice->id }}
                                    </span>
                                @endif
                                <span class="status-badge {{ $req->status }}">
                                    @if($req->isPending())
                                        ⏳ معلق بانتظار التأكيد (مخصوم من شيفت الموظف)
                                    @elseif($req->isApproved())
                                        ✅ معتمد ومؤكد من: {{ $req->approvedBy->name ?? 'المدير' }}
                                    @elseif($req->isRejected())
                                        ❌ مرفوض (تم إلغاء الخصم)
                                    @endif
                                </span>
                            </div>

                            @if($req->isRejected() && $req->rejection_reason)
                                <div class="rejection-alert">
                                    <strong>سبب الرفض:</strong> {{ $req->rejection_reason }}
                                </div>
                            @endif
                        </div>

                        <!-- أزرار الإجراءات للمدير -->
                        @if($req->isPending())
                            <div class="actions-row">
                                <form action="{{ route('admin.refund-requests.approve', $req->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من تأكيد واعتماد طلب الارتجاع بمبلغ {{ number_format($req->amount, 2) }} ج.م؟')">
                                    @csrf
                                    <button type="submit" class="btn-action approve">
                                        <i class="fas fa-check"></i> تأكيد واعتماد الارتجاع
                                    </button>
                                </form>

                                <button type="button" class="btn-action reject" onclick="openRejectModal('{{ $req->id }}', '{{ number_format($req->amount, 2) }}', '{{ addslashes($req->user->name ?? 'الموظف') }}')">
                                    <i class="fas fa-times"></i> رفض وإلغاء الخصم
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <p>🚀 لا توجد طلبات ارتجاع مطابقة للمعايير المحددة</p>
                </div>
            @endforelse

            <div class="pagination-wrapper mt-3">
                {{ $refundRequests->links() }}
            </div>
        </div>
    </div>

    <!-- مودال كتابة سبب الرفض -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0" style="background: #1c1c22; color: #f5f5f5;">
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title text-danger font-weight-bold">❌ رفض طلب الارتجاع</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="rejectForm" method="POST" action="">
                    @csrf
                    <div class="modal-body">
                        <p id="rejectModalText" class="text-light small mb-3"></p>
                        <div class="form-group mb-2">
                            <label class="form-label text-light small">سبب الرفض (سيظهر للموظف ويتم إلغاء الخصم من شيفته):</label>
                            <textarea name="rejection_reason" class="form-control bg-dark text-white border-secondary" rows="3" placeholder="اكتب سبب رفض طلب الارتجاع..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-secondary">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-danger btn-sm">تأكيد الرفض وإلغاء الخصم</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openRejectModal(id, amount, employeeName) {
            const form = document.getElementById('rejectForm');
            form.action = "{{ url('admin/refund-requests') }}/" + id + "/reject";
            document.getElementById('rejectModalText').innerHTML = `أنت على وشك رفض طلب الارتجاع بقيمة <strong>${amount} ج.م</strong> المقدم من <strong>${employeeName}</strong>. سيتم إلغاء الخصم من الشيفت فوراً.`;
            const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
            modal.show();
        }
    </script>
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

        body {
            font-family: "Cairo", sans-serif;
            background: var(--bg-color);
            color: var(--text-main);
            padding: 30px 15px;
        }

        .refunds-admin-wrapper {
            max-width: 1100px;
            margin: 0 auto;
        }

        .page-title {
            font-size: 22px;
            color: var(--accent);
            font-weight: 700;
            margin: 0;
        }

        .header-badges {
            display: flex;
            gap: 8px;
        }

        .stat-pill {
            font-size: 13px;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 20px;
        }

        .stat-pill.pending {
            background: rgba(255, 193, 7, 0.15);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }

        .stat-pill.approved {
            background: rgba(40, 167, 69, 0.15);
            color: #75b798;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        .filter-card {
            background: var(--card-bg);
            border: 1px solid var(--border-light);
            border-radius: var(--radius);
            padding: 16px;
        }

        .filter-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .tab-btn {
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            color: #aaa;
            background: #1c1c22;
            border: 1px solid var(--border-light);
            transition: all 0.2s;
        }

        .tab-btn:hover {
            color: #fff;
            border-color: #555;
        }

        .tab-btn.active {
            background: var(--accent);
            color: #111;
            border-color: var(--accent);
        }

        .tab-btn.pending.active {
            background: #ffc107;
            color: #111;
        }

        .tab-btn.approved.active {
            background: #28a745;
            color: #fff;
        }

        .tab-btn.rejected.active {
            background: #dc3545;
            color: #fff;
        }

        .filter-inputs {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .search-input {
            flex: 1;
            min-width: 220px;
            background: #1c1c22;
            border: 1px solid var(--border-light);
            color: #fff;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
        }

        .date-input {
            background: #1c1c22;
            border: 1px solid var(--border-light);
            color: #fff;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
        }

        .btn-filter {
            background: var(--accent);
            color: #111;
            font-weight: 700;
            border: none;
            padding: 8px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
        }

        .btn-reset {
            background: #333;
            color: #ccc;
            border: none;
            padding: 8px 14px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
        }

        .timeline-container {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .refund-card {
            background: var(--card-bg);
            border: 1px solid var(--border-light);
            border-radius: var(--radius);
            padding: 18px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .refund-card.status-pending {
            border-right: 5px solid #ffc107;
        }

        .refund-card.status-approved {
            border-right: 5px solid #28a745;
        }

        .refund-card.status-rejected {
            border-right: 5px solid #dc3545;
        }

        .refund-top-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .emp-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .emp-avatar {
            font-size: 22px;
            background: #25252e;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .emp-name {
            display: block;
            font-weight: 700;
            font-size: 15px;
            color: #fff;
        }

        .refund-time {
            font-size: 12px;
            color: #888;
        }

        .amount-badge {
            background: rgba(74, 222, 128, 0.15);
            border: 1px solid rgba(74, 222, 128, 0.3);
            color: #4ade80;
            padding: 6px 14px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 17px;
        }

        .amount-badge .currency {
            font-size: 12px;
            margin-right: 2px;
        }

        .reason-box {
            background: #1c1c22;
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 10px;
            font-size: 13.5px;
            color: #ddd;
            line-height: 1.5;
        }

        .reason-box strong {
            color: var(--accent);
            display: block;
            margin-bottom: 4px;
        }

        .meta-row {
            display: flex;
            gap: 14px;
            font-size: 12px;
            color: #999;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 8px;
        }

        .status-badge {
            padding: 3px 10px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 11px;
        }

        .status-badge.pending {
            background: rgba(255, 193, 7, 0.2);
            color: #ffd15c;
        }

        .status-badge.approved {
            background: rgba(40, 167, 69, 0.2);
            color: #8ef58a;
        }

        .status-badge.rejected {
            background: rgba(220, 53, 69, 0.2);
            color: #ff7676;
        }

        .rejection-alert {
            background: rgba(220, 53, 69, 0.15);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #ff8b8b;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 12px;
            margin-top: 8px;
        }

        .actions-row {
            display: flex;
            gap: 10px;
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px solid var(--border-light);
        }

        .btn-action {
            padding: 8px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-action.approve {
            background: #28a745;
            color: #fff;
        }

        .btn-action.approve:hover {
            background: #218838;
            transform: translateY(-1px);
        }

        .btn-action.reject {
            background: rgba(220, 53, 69, 0.2);
            color: #ff7676;
            border: 1px solid rgba(220, 53, 69, 0.4);
        }

        .btn-action.reject:hover {
            background: #dc3545;
            color: #fff;
            transform: translateY(-1px);
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #777;
            font-size: 15px;
            background: var(--card-bg);
            border-radius: var(--radius);
        }
    </style>
@endsection
