@extends('layouts.app_page')

@section('content')
    @php
        $actionLabels = [
            'new_subscription' => 'اشتراك جديد',
            'renew_subscription' => 'تجديد اشتراك',
            'end_session' => 'إنهاء جلسة',
            'separate_sale' => 'عملية بيع منفصلة',
            'add_booking' => 'إضافة حجز',
            'end_booking' => 'إنهاء حجز',
            'expense_note' => 'إضافة مصروف',
        ];
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
            <div class="form-container no-shift animate__animated animate__fadeInUp" style="text-align:center;">
                <h2 class="page-title">لم تبدأ أي شيفت بعد</h2>
                <form action="{{ route('shift.start') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-start-shift">🚀 بدء الشيفت</button>
                </form>
            </div>
        @else
            <h2 class="page-title">الشيفت الحالي</h2>

            <div class="form-container animate__animated animate__fadeInUp shift-summary">
                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="summary-label">وقت البدء</div>
                        <div class="summary-value">{{ $shift->start_time }}</div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">إجمالي الإيرادات</div>
                        <div class="summary-value">{{ number_format($shift->total_amount, 2) }} ج.م</div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">إجمالي المصروفات</div>
                        <div class="summary-value">{{ number_format($shift->total_expense, 2) }} ج.م</div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">الصافي</div>
                        <div class="summary-value">{{ number_format($shift->net_profit, 2) }} ج.م</div>
                    </div>
                </div>

                <form action="{{ route('shift.end') }}" method="POST"
                    onsubmit="return confirm('هل أنت متأكد من إنهاء الشيفت؟');" class="end-form">
                    @csrf
                    <button type="submit" class="btn-end-shift">⛔ إنهاء الشيفت</button>
                </form>
            </div>

            <h3 class="page-title">العمليات خلال الشيفت</h3>

            <div class="form-container animate__animated animate__fadeInUp actions-wrapper">
                <div class="table-responsive">
                    <table class="actions-table">
                        <thead>
                            <tr>
                                <th>العملية</th>
                                <th>المبلغ</th>
                                <th>المصروف</th>
                                <th>الوصف</th>
                                <th>التاريخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($shift->actions as $action)
                                <tr>
                                    <td>{{ $actionLabels[$action->action_type] ?? $action->action_type }}</td>
                                    <td>{{ $action->amount > 0 ? number_format($action->amount, 2) : '-' }}</td>
                                    <td>{{ $action->expense_amount ? number_format($action->expense_amount, 2) : '-' }}
                                    </td>
                                    <td class="td-notes">{{ $action->notes ?? '-' }}</td>
                                    <td>{{ $action->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align:center;">لا توجد عمليات مسجلة</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <style>
        :root {
            --bg-dark: radial-gradient(circle at top right, #101028 0%, #070710 100%);
            --card-dark: rgba(255, 255, 255, 0.05);
            --accent: linear-gradient(135deg, #7b5cff, #00c2ff);
            --danger: linear-gradient(135deg, #ff416c, #ff4b2b);
            --text-light: #fff;
            --text-muted: #aaa;
            --blur: blur(20px);
            --radius: 18px;
        }

        body {
            font-family: "Cairo", sans-serif;
            background: var(--bg-dark);
            color: var(--text-light);
            padding: 32px;
            backdrop-filter: var(--blur);
        }

        .page-title {
            font-size: 24px;
            text-align: center;
            font-weight: 700;
            background: linear-gradient(90deg, #a88beb, #00c2ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 24px;
        }

        .form-container {
            background: var(--card-dark);
            border-radius: var(--radius);
            padding: 24px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.4);
            backdrop-filter: var(--blur);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 18px;
        }

        .summary-item {
            background: rgba(255, 255, 255, 0.06);
            border-radius: 14px;
            padding: 14px;
            text-align: center;
            transition: 0.3s;
        }

        .summary-item:hover {
            background: rgba(255, 255, 255, 0.12);
        }

        .summary-label {
            font-size: 13px;
            color: var(--text-muted);
        }

        .summary-value {
            font-size: 16px;
            font-weight: 700;
            color: #fff;
        }

        .btn-start-shift,
        .btn-end-shift {
            border: none;
            padding: 14px 22px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            color: #fff;
        }

        .btn-start-shift {
            background: var(--accent);
            width: 100%;
            max-width: 280px;
        }

        .btn-end-shift {
            background: var(--danger);
        }

        .btn-start-shift:hover,
        .btn-end-shift:hover {
            transform: scale(1.03);
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.2);
        }

        .actions-table {
            width: 100%;
            border-collapse: collapse;
            color: #fff;
            font-size: 14px;
        }

        .actions-table th {
            text-align: center;
            padding: 12px;
            background: rgba(255, 255, 255, 0.08);
            color: #bbb;
        }

        .actions-table td {
            text-align: center;
            padding: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .td-notes {
            color: #ccc;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        @media (max-width: 900px) {
            body {
                padding: 16px;
            }

            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
@endsection
