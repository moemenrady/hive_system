@extends('layouts.app_page')

@section('content')
    @php
        $actionLabels = [
            'new_subscription' => 'اشتراك جديد',
            'renew_subscription' => 'تجديد اشتراك',
            'end_session' => 'إنهاء جلسة',
            'session_prepaid' => 'جلسة مدفوعة مسبقاً',
            'separate_sale' => 'عملية بيع منفصلة',
            'add_booking' => 'إضافة حجز',
            'end_booking' => 'إنهاء حجز',
            'expense_note' => 'إضافة مصروف',
        ];

        if ($shift) {
            // حساب الإيرادات حسب طريقة الدفع
            $cashAmount = $shift->actions->where('payment_type', 'cash')->sum('amount');
            // نعتبر أي دفع غير الكاش (مثل wallet, visa) يندرج تحت المحافظ/الإلكتروني، أو نحدد المحفظة نصاً
            $walletAmount = $shift->actions->where('payment_type', 'digital')->sum('amount');

            // حساب عدد الفواتير المطلوبة
            $separateSalesCount = $shift->actions->where('action_type', 'separate_sale')->count();
            $endSessionCount = $shift->actions->where('action_type', 'end_session')->count();
            // الفواتير التي تمتلك ID أو بناءً على العمليات
            $totalInvoicesCount = $shift->actions->whereNotNull('invoice_id')->count();
        }
    @endphp

    <div class="shift-wrapper">

        {{-- Alerts --}}
        @if (session('success'))
            <script>
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: "{{ session('success') }}",
                    showConfirmButton: false,
                    timer: 3500,
                    timerProgressBar: true,
                    background: '#222',
                    color: '#fff'
                });
            </script>
        @endif

        @if (session('error'))
            <script>
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: "{{ session('error') }}",
                    showConfirmButton: false,
                    timer: 3500,
                    timerProgressBar: true,
                    background: '#222',
                    color: '#fff'
                });
            </script>
        @endif

        @if (!$shift)
            <div class="form-container no-shift animate__animated animate__zoomIn">
                <div class="empty-state-icon">🌙</div>
                <h2 class="page-title">لم تبدأ أي شيفت بعد</h2>
                <p class="text-muted" style="margin-bottom: 20px;">ابدأ الشيفت الآن لتتمكن من تسجيل المبيعات والمصروفات.</p>
                <form action="{{ route('shift.start') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-start-shift pulse-animation">🚀 بدء الشيفت الآن</button>
                </form>
            </div>
        @else
            <div class="header-section animate__animated animate__fadeInDown">
                <h2 class="page-title">📊 لوحة تحكم الشيفت الحالي</h2>
            </div>

            <div class="form-container animate__animated animate__fadeInUp shift-summary">
                <div class="summary-grid">

                    <div class="summary-item stat-card">
                        <div class="stat-icon">⏰</div>
                        <div class="summary-label">وقت البدء</div>
                        <div class="summary-value">{{ $shift->start_time->format('h:i A') }}</div>
                    </div>

                    <div class="summary-item stat-card">
                        <div class="stat-icon">💰</div>
                        <div class="summary-label">إجمالي الإيرادات</div>
                        <div class="summary-value text-success">{{ number_format($shift->total_amount, 2) }} ج.م</div>

                        <div class="payment-breakdown">
                            <span class="badge badge-cash">💵 كاش: {{ number_format($cashAmount, 2) }}</span>
                            <span class="badge badge-wallet">📱 محفظة: {{ number_format($walletAmount, 2) }}</span>
                        </div>
                    </div>

                    <div class="summary-item stat-card">
                        <div class="stat-icon">📉</div>
                        <div class="summary-label">إجمالي المصروفات</div>
                        <div class="summary-value text-danger">{{ number_format($shift->total_expense, 2) }} ج.م</div>
                    </div>

                    <div class="summary-item stat-card highlight-card">
                        <div class="stat-icon">💎</div>
                        <div class="summary-label">الصافي</div>
                        <div class="summary-value">{{ number_format($shift->net_profit, 2) }} ج.م</div>
                    </div>
                </div>

                <div class="invoices-stats-container">
                    <div class="invoice-trigger" onclick="toggleInvoices()">
                        <div class="trigger-content">
                            <span class="icon">🧾</span>
                            <span class="text">عدد الفواتير المُصدرة بالشيفت: <strong>{{ $totalInvoicesCount }}</strong>
                                فاتورة</span>
                        </div>
                        <span class="arrow-icon" id="invoiceArrow">▼</span>
                    </div>

                    <div class="invoice-details-content" id="invoiceDetails">
                        <div class="invoice-cards">
                            <div class="inv-card">
                                <h5>🛍️ مبيعات منفصلة</h5>
                                <h2>{{ $separateSalesCount }}</h2>
                            </div>
                            <div class="inv-card">
                                <h5>🎮 إنهاء جلسات</h5>
                                <h2>{{ $endSessionCount }}</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="divider">

                <form action="{{ route('shift.end') }}" method="POST" id="endShiftForm" class="end-form text-center">
                    @csrf
                    <button type="button" onclick="confirmEndShift()" class="btn-end-shift">⛔ إنهاء الشيفت بأمان</button>
                </form>
            </div>

            <div class="header-section animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
                <h3 class="page-title" style="font-size: 20px;">📜 سجل العمليات خلال الشيفت</h3>
            </div>

            <div class="form-container animate__animated animate__fadeInUp actions-wrapper" style="animation-delay: 0.3s;">
                <div class="table-responsive custom-scrollbar">
                    <table class="actions-table">
                        <thead>
                            <tr>
                                <th>نوع العملية</th>
                                <th>طريقة الدفع</th>
                                <th>الإيراد</th>
                                <th>المصروف</th>
                                <th>الوصف</th>
                                <th>الوقت</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($shift->actions as $action)
                                <tr class="table-row-hover">
                                    <td>
                                        <span class="action-type-badge">
                                            {{ $actionLabels[$action->action_type] ?? $action->action_type }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($action->amount > 0)
                                            <span
                                                class="pay-type {{ $action->payment_type == 'cash' ? 'type-cash' : 'type-wallet' }}">
                                                {{ $action->payment_type == 'cash' ? 'كاش' : ($action->payment_type == 'digital' ? 'محفظة' : $action->payment_type ?? '-') }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-success font-weight-bold">
                                        {{ $action->amount > 0 ? number_format($action->amount, 2) : '-' }}</td>
                                    <td class="text-danger font-weight-bold">
                                        {{ $action->expense_amount ? number_format($action->expense_amount, 2) : '-' }}
                                    </td>
                                    <td class="td-notes" title="{{ $action->notes }}">{{ $action->notes ?? '-' }}</td>
                                    <td class="text-muted">{{ $action->created_at->format('h:i A') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="empty-table">
                                        <div class="empty-box">📭 لا توجد أي عمليات مسجلة في هذا الشيفت حتى الآن.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <style>
        /* المتغيرات الأساسية */
        :root {
            --bg-dark: #0a0a16;
            --card-bg: rgba(20, 20, 35, 0.7);
            --border-color: rgba(255, 255, 255, 0.08);
            --accent-primary: #00c2ff;
            --accent-secondary: #7b5cff;
            --success: #00e676;
            --danger: #ff416c;
            --text-main: #ffffff;
            --text-muted: #9ba1a6;
            --radius-lg: 20px;
            --radius-md: 12px;
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        body {
            font-family: "Cairo", sans-serif;
            background: radial-gradient(circle at top right, var(--bg-dark) 0%, #000000 100%);
            color: var(--text-main);
            padding: 20px;
            min-height: 100vh;
            direction: rtl;
        }

        /* حاويات الأساسية */
        .shift-wrapper {
            max-width: 1100px;
            margin: 0 auto;
        }

        .form-container {
            background: var(--card-bg);
            border-radius: var(--radius-lg);
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(15px);
            border: 1px solid var(--border-color);
            margin-bottom: 25px;
        }

        .no-shift {
            text-align: center;
            padding: 60px 20px;
            max-width: 500px;
            margin: 10vh auto;
        }

        .empty-state-icon {
            font-size: 60px;
            margin-bottom: 15px;
            animation: float 3s ease-in-out infinite;
        }

        /* العناوين */
        .page-title {
            text-align: center;
            font-weight: 800;
            background: linear-gradient(90deg, var(--accent-secondary), var(--accent-primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
            display: inline-block;
            width: 100%;
        }

        /* شبكة الإحصائيات */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.03);
            border-radius: var(--radius-md);
            padding: 20px;
            text-align: center;
            border: 1px solid transparent;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .highlight-card {
            background: linear-gradient(145deg, rgba(123, 92, 255, 0.1), rgba(0, 194, 255, 0.1));
            border: 1px solid rgba(0, 194, 255, 0.3);
        }

        .stat-icon {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .summary-label {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 8px;
            font-weight: 600;
        }

        .summary-value {
            font-size: 22px;
            font-weight: 800;
        }

        /* تفاصيل الدفع */
        .payment-breakdown {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 12px;
            flex-wrap: wrap;
        }

        .badge {
            font-size: 11px;
            padding: 5px 10px;
            border-radius: 30px;
            font-weight: 700;
        }

        .badge-cash {
            background: rgba(0, 230, 118, 0.15);
            color: var(--success);
            border: 1px solid rgba(0, 230, 118, 0.3);
        }

        .badge-wallet {
            background: rgba(0, 194, 255, 0.15);
            color: var(--accent-primary);
            border: 1px solid rgba(0, 194, 255, 0.3);
        }

        /* إحصائيات الفواتير (Accordion) */
        .invoices-stats-container {
            margin: 25px 0;
            background: rgba(0, 0, 0, 0.2);
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .invoice-trigger {
            padding: 18px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.02);
            transition: var(--transition);
        }

        .invoice-trigger:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .trigger-content {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 16px;
        }

        .trigger-content strong {
            color: var(--accent-primary);
            font-size: 18px;
        }

        .arrow-icon {
            font-size: 12px;
            color: var(--text-muted);
            transition: transform 0.3s ease;
        }

        .arrow-icon.rotated {
            transform: rotate(180deg);
        }

        .invoice-details-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s cubic-bezier(0, 1, 0, 1);
            background: rgba(0, 0, 0, 0.3);
        }

        .invoice-details-content.expanded {
            max-height: 200px;
            /* Adjust if content grows */
        }

        .invoice-cards {
            display: flex;
            justify-content: center;
            gap: 20px;
            padding: 20px;
        }

        .inv-card {
            background: rgba(255, 255, 255, 0.04);
            padding: 15px 25px;
            border-radius: 10px;
            text-align: center;
            flex: 1;
            max-width: 200px;
            border: 1px dashed var(--border-color);
        }

        .inv-card h5 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: var(--text-muted);
        }

        .inv-card h2 {
            margin: 0;
            color: var(--text-main);
            font-size: 24px;
        }

        /* الأزرار */
        .btn-start-shift,
        .btn-end-shift {
            border: none;
            padding: 15px 30px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 16px;
            cursor: pointer;
            transition: var(--transition);
            color: #fff;
            font-family: inherit;
        }

        .btn-start-shift {
            background: linear-gradient(135deg, var(--accent-secondary), var(--accent-primary));
            width: 100%;
            max-width: 300px;
        }

        .btn-end-shift {
            background: linear-gradient(135deg, var(--danger), #ff4b2b);
            min-width: 200px;
        }

        .btn-start-shift:hover,
        .btn-end-shift:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }

        .pulse-animation {
            animation: pulse 2s infinite;
        }

        /* الجدول */
        .table-responsive {
            overflow-x: auto;
            border-radius: var(--radius-md);
        }

        .actions-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            white-space: nowrap;
        }

        .actions-table th {
            background: rgba(0, 0, 0, 0.4);
            color: var(--text-muted);
            font-weight: 600;
            padding: 15px;
            text-align: right;
            border-bottom: 2px solid var(--border-color);
        }

        .actions-table td {
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            vertical-align: middle;
        }

        .table-row-hover:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        .action-type-badge {
            background: rgba(255, 255, 255, 0.08);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
        }

        .pay-type {
            font-size: 12px;
            font-weight: bold;
            padding: 4px 8px;
            border-radius: 4px;
        }

        .type-cash {
            background: rgba(0, 230, 118, 0.1);
            color: var(--success);
        }

        .type-wallet {
            background: rgba(0, 194, 255, 0.1);
            color: var(--accent-primary);
        }

        .td-notes {
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: var(--text-muted);
        }

        .empty-table {
            text-align: center;
            padding: 40px !important;
        }

        .empty-box {
            color: var(--text-muted);
            font-size: 15px;
        }

        .divider {
            border-top: 1px solid var(--border-color);
            margin: 30px 0;
            opacity: 0.5;
        }

        /* Helpers */
        .text-success {
            color: var(--success) !important;
        }

        .text-danger {
            color: var(--danger) !important;
        }

        .text-muted {
            color: var(--text-muted) !important;
        }

        .font-weight-bold {
            font-weight: 700;
        }

        .text-center {
            text-align: center;
        }

        /* Animations */
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(0, 194, 255, 0.4);
            }

            70% {
                box-shadow: 0 0 0 15px rgba(0, 194, 255, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(0, 194, 255, 0);
            }
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        /* التجاوب مع الجوال */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .form-container {
                padding: 20px 15px;
            }

            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }

            .invoice-cards {
                flex-direction: column;
                gap: 10px;
            }

            .inv-card {
                max-width: 100%;
            }

            .invoice-details-content.expanded {
                max-height: 300px;
            }
        }

        @media (max-width: 480px) {
            .summary-grid {
                grid-template-columns: 1fr;
            }

            .trigger-content {
                font-size: 14px;
            }

            .actions-table th,
            .actions-table td {
                padding: 12px 10px;
            }
        }

        /* Custom Scrollbar for table */
        .custom-scrollbar::-webkit-scrollbar {
            height: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.2);
        }
    </style>

    <script>
        // دالة لطي وفتح تفاصيل الفواتير (Accordion)
        function toggleInvoices() {
            const content = document.getElementById('invoiceDetails');
            const arrow = document.getElementById('invoiceArrow');

            content.classList.toggle('expanded');
            arrow.classList.toggle('rotated');
        }

        // دالة تأكيد إنهاء الشيفت بـ SweetAlert بدلاً من Confirm العادي
        function confirmEndShift() {
            Swal.fire({
                title: 'إنهاء الشيفت؟',
                text: "هل أنت متأكد من رغبتك في إغلاق الشيفت الحالي؟ لا يمكن التراجع عن هذه الخطوة.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff416c',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'نعم، قم بالإنهاء',
                cancelButtonText: 'إلغاء',
                background: '#1a1a2e',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('endShiftForm').submit();
                }
            })
        }
    </script>
@endsection
