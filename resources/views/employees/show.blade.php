@extends('layouts.app_page')

@section('title', 'كشف حساب: ' . $employee->name)

@section('style')
    <style>
        /* ألوان العمليات للتمييز البصري */
        .tx-purchase {
            color: #0dcaf0;
            background: rgba(13, 202, 240, 0.1);
            border-inline-start: 4px solid #0dcaf0;
        }

        .tx-advance {
            color: #ffb84d;
            background: rgba(255, 184, 77, 0.1);
            border-inline-start: 4px solid #ffb84d;
        }

        .tx-deduction {
            color: #ef4444;
            background: rgba(239, 68, 68, 0.1);
            border-inline-start: 4px solid #ef4444;
        }

        .tx-bonus {
            color: #a855f7;
            background: rgba(168, 85, 247, 0.1);
            border-inline-start: 4px solid #a855f7;
        }

        .icon-box {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 1.1rem;
        }

        /* كروت الإحصائيات */
        .stat-card {
            background: #1a1a1d;
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 1rem;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }
    </style>
@endsection

@section('content')
    <div class="container mx-auto p-2 md:p-4 max-w-5xl">

        <!-- معلومات الموظف -->
        <div class="card p-4 border-0 mb-4 animate__animated animate__fadeInDown"
            style="background: linear-gradient(145deg, #1a1a1d, #25252a); border-radius: 16px;">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-dark rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 60px; height: 60px; border: 2px solid var(--accent);">
                        <i class="fas fa-user-tie fs-3 text-warning"></i>
                    </div>
                    <div>
                        <h4 class="text-white mb-1">{{ $employee->name }}</h4>
                        <p class="text-dim mb-0"><i class="fas fa-phone-alt me-1 small"></i> {{ $employee->phone }}</p>
                    </div>
                </div>
                <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-outline-warning rounded-pill px-4">
                    <i class="fas fa-edit me-1"></i> تعديل البيانات
                </a>
            </div>
        </div>

        <!-- لوحة الإحصائيات المالية (المرتب والصافي) -->
        <div class="row g-3 mb-5 animate__animated animate__fadeInUp">
            <div class="col-6 col-md-3">
                <div class="stat-card text-center h-100">
                    <div class="text-dim small mb-2"><i class="fas fa-wallet me-1"></i> الراتب الأساسي</div>
                    <div class="h4 text-white mb-0">{{ number_format($employee->salary) }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card text-center h-100" style="border-bottom: 3px solid #a855f7;">
                    <div class="text-purple small mb-2"><i class="fas fa-gift me-1"></i> إجمالي المكافآت</div>
                    <div class="h4 mb-0" style="color: #a855f7;">+ {{ number_format($totalBonuses) }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card text-center h-100" style="border-bottom: 3px solid #ef4444;">
                    <div class="text-danger small mb-2"><i class="fas fa-minus-circle me-1"></i> إجمالي الخصومات</div>
                    <div class="h4 text-danger mb-0">- {{ number_format($totalDiscounts) }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card text-center h-100"
                    style="border-bottom: 3px solid #10b981; background: rgba(16, 185, 129, 0.05);">
                    <div class="text-success small mb-2"><i class="fas fa-money-bill-wave me-1"></i> صافي الشهر</div>
                    <div class="h4 text-success fw-bold mb-0">{{ number_format($netSalary) }}</div>
                </div>
            </div>
        </div>

        <!-- تفاصيل الشهر الحالي -->
        <div class="d-flex justify-content-between align-items-center mt-5 mb-4 animate__animated animate__fadeInLeft">
            <div class="title" style="font-size: 1.2rem;">
                <i class="fas fa-calendar-check me-2 text-warning"></i> حركات شهر {{ now()->translatedFormat('F Y') }}
            </div>
            <span class="badge bg-secondary rounded-pill px-3 py-2">{{ $currentTransactions->count() }} عملية</span>
        </div>

        @php
            $groupedByDay = $currentTransactions->groupBy(fn($date) => $date->created_at->format('Y-m-d'));
        @endphp

        <div class="space-y-4 mb-5">
            @forelse($groupedByDay as $day => $txs)
                <div class="animate__animated animate__fadeInUp">
                    <!-- عنوان اليوم -->
                    <div class="d-flex align-items-center gap-2 mb-2 px-2">
                        <i class="far fa-clock text-dim"></i>
                        <span
                            class="text-white-50 small fw-bold">{{ \Carbon\Carbon::parse($day)->translatedFormat('l, d F Y') }}</span>
                    </div>

                    <!-- قائمة عمليات اليوم -->
                    <div class="card bg-transparent border-0 space-y-2">
                        @foreach ($txs as $tx)
                            @php
                                // ضبط المتغيرات بناءً على نوع العملية
                                if ($tx->type == 'purchase') {
                                    $txClass = 'tx-purchase';
                                    $icon = 'fa-shopping-cart';
                                    $label = 'مشتريات';
                                    $sign = '-';
                                } elseif ($tx->type == 'advance') {
                                    $txClass = 'tx-advance';
                                    $icon = 'fa-hand-holding-usd';
                                    $label = 'سلفة مالية';
                                    $sign = '-';
                                } elseif ($tx->type == 'deduction') {
                                    $txClass = 'tx-deduction';
                                    $icon = 'fa-gavel';
                                    $label = 'خصم إداري';
                                    $sign = '-';
                                } else {
                                    $txClass = 'tx-bonus';
                                    $icon = 'fa-star';
                                    $label = 'مكافأة';
                                    $sign = '+';
                                }
                            @endphp

                            <div class="d-flex align-items-center justify-content-between p-3 rounded-3 shadow-sm {{ $txClass }}"
                                style="background-color: #16161a;">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-box" style="background: currentColor; opacity: 0.8;">
                                        <i class="fas {{ $icon }} text-dark"></i>
                                    </div>
                                    <div>
                                        <div class="text-white fw-bold fs-6">{{ $label }}</div>
                                        <div class="text-dim small">{{ $tx->notes ?? 'بدون ملاحظات' }}</div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold font-monospace fs-5" style="color: currentColor;">
                                        {{ $sign }} {{ number_format($tx->amount, 2) }} <span
                                            class="small fs-6">ج.م</span>
                                    </div>
                                    <div class="text-dim small" style="font-size: 0.75rem;">
                                        {{ $tx->created_at->format('h:i A') }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-center p-5 card border-0 text-dim bg-dark rounded-4"
                    style="border: 1px dashed rgba(255,255,255,0.1) !important;">
                    <i class="fas fa-box-open fs-1 mb-3 text-secondary"></i>
                    <h5>لا توجد مسحوبات أو حركات لهذا الشهر حتى الآن</h5>
                </div>
            @endforelse
        </div>

        <!-- السجل التاريخي (الشهور السابقة) -->
        @if ($history->count() > 0)
            <div class="title mt-5 mb-4" style="font-size: 1.2rem;">
                <i class="fas fa-history me-2 text-dim"></i> السجل التاريخي (الشهور السابقة)
            </div>

            <div class="accordion border-0 space-y-3" id="historyAccordion">
                @foreach ($history as $month => $txs)
                    @if ($month != now()->format('Y-m'))
                        @php
                            $monthBonuses = $txs->where('type', 'bonus')->sum('amount');
                            $monthDiscounts = $txs
                                ->whereIn('type', ['advance', 'purchase', 'deduction'])
                                ->sum('amount');
                        @endphp
                        <div
                            class="accordion-item bg-dark border border-secondary border-opacity-25 rounded-4 overflow-hidden mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed shadow-none text-white p-3 p-md-4" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#month-{{ \Str::slug($month) }}"
                                    style="background: #1a1a1d !important;">
                                    <div
                                        class="d-flex justify-content-between align-items-center w-100 me-3 flex-wrap gap-2">
                                        <span class="fw-bold"><i class="far fa-calendar-alt text-warning me-2"></i> شهر
                                            {{ $month }}</span>
                                        <div class="d-flex gap-2">
                                            <span
                                                class="badge bg-purple bg-opacity-10 text-purple border border-purple border-opacity-25">+
                                                {{ number_format($monthBonuses) }} ج.م</span>
                                            <span
                                                class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">-
                                                {{ number_format($monthDiscounts) }} ج.م</span>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="month-{{ \Str::slug($month) }}" class="accordion-collapse collapse"
                                data-bs-parent="#historyAccordion">
                                <div class="accordion-body p-0">
                                    <!-- قائمة عمليات الشهور القديمة بنفس ستايل الشهر الحالي لكن مصغرة -->
                                    <div class="list-group list-group-flush border-top border-secondary border-opacity-25">
                                        @foreach ($txs as $tx)
                                            @php
                                                if ($tx->type == 'purchase') {
                                                    $color = '#0dcaf0';
                                                    $label = 'مشتريات';
                                                    $sign = '-';
                                                } elseif ($tx->type == 'advance') {
                                                    $color = '#ffb84d';
                                                    $label = 'سلفة';
                                                    $sign = '-';
                                                } elseif ($tx->type == 'deduction') {
                                                    $color = '#ef4444';
                                                    $label = 'خصم';
                                                    $sign = '-';
                                                } else {
                                                    $color = '#a855f7';
                                                    $label = 'مكافأة';
                                                    $sign = '+';
                                                }
                                            @endphp
                                            <div
                                                class="list-group-item bg-transparent text-white d-flex justify-content-between align-items-center py-3 border-bottom border-secondary border-opacity-10">
                                                <div>
                                                    <span class="badge me-2"
                                                        style="background-color: {{ $color }}20; color: {{ $color }}; border: 1px solid {{ $color }}50;">{{ $label }}</span>
                                                    <span
                                                        class="text-dim small">{{ $tx->created_at->format('Y-m-d') }}</span>
                                                </div>
                                                <div class="font-monospace fw-bold" style="color: {{ $color }};">
                                                    {{ $sign }} {{ number_format($tx->amount) }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
@endsection
