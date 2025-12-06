@extends('layouts.analytics')
@section('title', 'التحليل المالي')

@section('content')

    <div class="analytics-header">
        <div>
            <div class="page-title">التحليل المالي المتقدم</div>
            <div class="sub-muted">لوحة العائدات · الإحصاءات المتقدمة</div>
        </div>
    </div>
    <div class="glass-box date-filter mb-4">

        <div class="filter-header">
            <h5>تصفية حسب التاريخ</h5>
            <!-- إزالة أيقونة الكالندر -->
        </div>

        <!-- خلي الجسم دايمًا ظاهر -->
        <div id="dateFilterBody" class="filter-body" style="display: block;">

            {{-- SHORTCUTS --}}
            <div class="quick-filters">
                <button onclick="applyQuick('today')"
                    class="{{ request('from') == now()->toDateString() && request('to') == now()->toDateString() ? 'active-btn' : '' }}">
                    اليوم
                </button>

                <button onclick="applyQuick('yesterday')"
                    class="{{ request('from') == now()->subDay()->toDateString() && request('to') == now()->subDay()->toDateString() ? 'active-btn' : '' }}">
                    أمس
                </button>

                <button onclick="applyQuick('7')"
                    class="{{ request('from') == now()->subDays(7)->toDateString() && request('to') == now()->toDateString() ? 'active-btn' : '' }}">
                    آخر 7 أيام
                </button>

                <button onclick="applyQuick('30')"
                    class="{{ request('from') == now()->subDays(30)->toDateString() && request('to') == now()->toDateString() ? 'active-btn' : '' }}">
                    آخر 30 يوم
                </button>

                <button onclick="applyQuick('this_month')"
                    class="{{ request('from') == now()->startOfMonth()->toDateString() && request('to') == now()->toDateString() ? 'active-btn' : '' }}">
                    هذا الشهر
                </button>

                <button onclick="applyQuick('last_month')"
                    class="{{ request('from') == now()->subMonth()->startOfMonth()->toDateString() && request('to') == now()->subMonth()->endOfMonth()->toDateString() ? 'active-btn' : '' }}">
                    الشهر السابق
                </button>
            </div>

            <div class="range-box mt-3">
                <label>اختر الفترة</label>

                <!-- شكل Custom للعرض -->
                <div class="date-display" onclick="openCalendar()">
                    <div class="date-icon">📅</div>
                    <div class="date-text" id="dateText">
                        {{ request('from') && request('to') ? request('from') . ' — ' . request('to') : 'من – إلى' }}
                    </div>
                    <div class="pulse"></div>
                </div>

                <!-- الحقل الحقيقي الذي يشغل flatpickr -->
                <input type="text" id="dateRange" class="flatpickr-hidden"
                    value="{{ request('from') && request('to') ? request('from') . ' to ' . request('to') : '' }}">

                <!-- hidden inputs -->
                <input type="hidden" id="fromDateHidden" name="from" value="{{ request('from') }}">
                <input type="hidden" id="toDateHidden" name="to" value="{{ request('to') }}">
            </div>





        </div>
    </div>


    {{-- ======= STAT CARDS ======= --}}
    <div class="stats-grid">

        <div class="card">
            <div class="label">إجمالي الدخل</div>
            <div class="num text-success" style="cursor:pointer" onclick="goToIncomeDetails()">
                {{ number_format($totalIncome, 2) }} جنيه
            </div>

        </div>

        <div class="card">
            <div class="label">إجمالي المصاريف</div>
            <div class="num text-danger">
                {{ number_format($totalExpenses, 2) }} جنيه
            </div>
        </div>
        <div class="card">
            <div class="label">إجمالي تكلفة المنتجات</div>
            <div class="num text-danger">
                {{ number_format($productInvoiceItems, 2) }} جنيه
            </div>
        </div>
        <div class="card">
            <div class="label">صافي الربح</div>
            <div class="num {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                {{ number_format($netProfit, 2) }} جنيه
            </div>
        </div>

        <div class="card">
            <div class="label">الهامش الربحي</div>
            <div class="num">{{ $profitMargin }}%</div>
        </div>

        <div class="card">
            <div class="label">نسبة النمو هذا الشهر</div>
            <div class="num {{ $growthRate >= 0 ? 'text-success' : 'text-danger' }}">
                {{ $growthRate }}%
            </div>
        </div>

        <div class="card">
            <div class="label">أفضل يوم دخل</div>
            <div class="num">
                @if ($topIncomeDay)
                    {{ $topIncomeDay->day }} — {{ number_format($topIncomeDay->sum, 2) }} جنيه
                @else
                    لا يوجد بيانات
                @endif
            </div>
        </div>

        <div class="card">
            <div class="label">أعلى خدمة جابت دخل</div>
            <div class="num">
                @if ($topService)
                    @php
                        $serviceTypes = [
                            'session' => 'الجلسات',
                            'booking' => 'الحجوزات',
                            'subscription' => 'الاشتراكات',
                            'product' => 'المبيعات',
                            'deposit' => 'المقدم',
                        ];
                        $serviceName = $serviceTypes[$topService->item_type] ?? $topService->item_type;
                    @endphp
                    {{ $serviceName }} — {{ number_format($topService->sum, 2) }} جنيه
                @else
                    لا يوجد بيانات
                @endif
            </div>
        </div>


    </div>


    {{-- ======= TREND & TABLE ======= --}}
    <div class="content-row">

        {{-- LEFT: Trend Chart placeholder --}}
        <div class="glass-box">
            <h5 class="mb-3">منحنى الدخل خلال آخر 30 يوم</h5>
            <div class="chart-placeholder">📈 سيتم إضافة الرسم هنا قريبًا</div>
        </div>

        {{-- RIGHT: Monthly Comparison --}}
        <div class="glass-box">
            <h5 class="mb-3">مقارنة بين الشهور</h5>

            <table class="analytics-table">
                <thead>
                    <tr>
                        <th>الفترة</th>
                        <th>القيمة</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>هذا الشهر</td>
                        <td>{{ number_format($thisMonth, 2) }} جنيه</td>
                    </tr>
                    <tr>
                        <td>الشهر السابق</td>
                        <td>{{ number_format($lastMonth, 2) }} جنيه</td>
                    </tr>
                    <tr>
                        <td>الفرق</td>
                        <td class="{{ $growthRate >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $growthRate }}%
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>

    </div>

    <script>
        // فتح الكالندر عند الضغط على الـ container
        function openCalendar() {
            document.getElementById("dateRange")._flatpickr.open();
        }

        // دالة عامة لتنسيق التاريخ
        function formatDate(date) {
            let d = new Date(date);
            let month = '' + (d.getMonth() + 1);
            let day = '' + d.getDate();
            const year = d.getFullYear();

            if (month.length < 2) month = '0' + month;
            if (day.length < 2) day = '0' + day;

            return [year, month, day].join('-');
        }

        // إرسال الفلترة
        function sendFilter(from, to) {
            window.location = `{{ route('analytics.money') }}?from=${from}&to=${to}`;
        }

        // اختصارات الأيام
        function applyQuick(type) {
            let from, to = new Date();

            switch (type) {
                case 'today':
                    from = to;
                    break;
                case 'yesterday':
                    from = new Date();
                    from.setDate(from.getDate() - 1);
                    to = from;
                    break;
                case '7':
                    from = new Date();
                    from.setDate(from.getDate() - 7);
                    break;
                case '30':
                    from = new Date();
                    from.setDate(from.getDate() - 30);
                    break;
                case 'this_month':
                    from = new Date(to.getFullYear(), to.getMonth(), 1);
                    break;
                case 'last_month':
                    from = new Date(to.getFullYear(), to.getMonth() - 1, 1);
                    to = new Date(to.getFullYear(), to.getMonth(), 0);
                    break;
            }

            sendFilter(formatDate(from), formatDate(to));
        }

        // تهيئة الكالندر الرئيسي (مرّة واحدة فقط)
        flatpickr("#dateRange", {
            mode: "range",
            dateFormat: "Y-m-d",
            onChange: function(selectedDates) {
                if (selectedDates.length === 2) {
                    const from = formatDate(selectedDates[0]);
                    const to = formatDate(selectedDates[1]);

                    // تحديث UI
                    document.getElementById("dateText").innerText = `${from} — ${to}`;

                    // تحديث hidden inputs
                    document.getElementById("fromDateHidden").value = from;
                    document.getElementById("toDateHidden").value = to;

                    // إرسال الفلترة
                    sendFilter(from, to);
                }
            }
        });
    </script>
    <script>
        function goToIncomeDetails() {
            const from = document.getElementById("fromDateHidden").value;
            const to = document.getElementById("toDateHidden").value;

            const url = `{{ route('analytics.totalIncomeAndProfit') }}?from=${from}&to=${to}`;

            window.location = url;
        }
    </script>


@endsection

@section('style')

    <style>
        .date-filter {
            padding: 20px;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(12px);
            border-radius: 18px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: 0.3s ease;
        }

        .filter-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .filter-header h5 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }

        .filter-icon {
            cursor: pointer;
            font-size: 22px;
            transition: 0.3s;
        }

        .filter-icon:hover {
            transform: rotate(10deg) scale(1.1);
        }

        .filter-body {
            display: none;
            margin-top: 15px;
            animation: slideDown 0.4s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* زرار غير Active */
        .quick-filters button {
            padding: 7px 15px;
            border: none;
            border-radius: 10px;
            background: #000;
            /* أسود */
            color: #fff;
            /* أبيض */
            cursor: pointer;
            transition: 0.3s;
            font-weight: 500;
        }

        /* Hover للزرار غير Active */
        .quick-filters button:not(.active-btn):hover {
            background: #222;
            /* غامق أكتر */
            transform: translateY(-2px);
        }

        /* الزرار الـ Active */
        .quick-filters button.active-btn {
            background: #ffc478;
            /* أزرق */
            color: #fff;
            transform: scale(1.05);
        }

        .date-display {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #ffffff;
            padding: 12px 16px;
            border-radius: 14px;
            border: 1px solid #e4e4e4;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.04);
            position: relative;
            transition: 0.2s ease;
        }

        .date-display:hover {
            border-color: #007bff;
            box-shadow: 0 5px 14px rgba(0, 123, 255, 0.1);
        }

        .date-icon {
            font-size: 20px;
        }

        .date-text {
            font-size: 15px;
            color: #444;
            letter-spacing: 0.5px;
        }

        /* Pulse animation */
        .pulse {
            width: 8px;
            height: 8px;
            background: #007bff;
            border-radius: 50%;
            position: absolute;
            right: 14px;
            animation: pulseAnim 1.6s infinite ease-out;
        }

        @keyframes pulseAnim {
            0% {
                transform: scale(0.8);
                opacity: 0.7;
            }

            100% {
                transform: scale(1.8);
                opacity: 0;
            }
        }

        /* أخفي input الخاص بـ flatpickr */
        .flatpickr-hidden {
            opacity: 0;
            height: 0;
            position: absolute;
            pointer-events: none;
        }
    </style>


@endsection
