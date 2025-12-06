@extends('layouts.analytics')
@section('title', 'تفاصيل الدخل والربح')

@section('content')
    <style>
        /* ---------- Layout ---------- */
        .layout-row {
            display: flex;
            gap: 32px;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 40px;
        }

        .column-vertical {
            display: flex;
            flex-direction: column;
            gap: 26px;
        }

        .cinema-card {
            width: 420px;
            min-width: 300px;
            border-radius: 18px;
            padding: 28px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.02), rgba(255, 255, 255, 0.01));
            border: 1px solid var(--glass-border);
            box-shadow: 0 18px 50px rgba(2, 6, 23, 0.6);
            position: relative;
        }

        .float {
            animation: floatSlow 6.5s ease-in-out infinite;
        }

        @keyframes floatSlow {
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

        .big-num {
            font-weight: 900;
            font-size: 36px;
            line-height: 1;
            background: var(--accent-grad);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-top: 6px;
        }

        .income-list {
            margin-top: 14px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .income-item {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .income-item .bar {
            width: 120px;
            height: 8px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.06);
            overflow: hidden;
        }

        .income-item .bar i {
            height: 100%;
            display: block;
            border-radius: 999px;
            background: linear-gradient(90deg, #F8E0C1, #D9B1AB);
        }

        .profit-row {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
    </style>

    {{-- ====================== ROW 1: Income + Expenses ====================== --}}
    <div class="layout-row">

        {{-- -------- Income Card -------- --}}
        <div class="cinema-card float">
            <div style="font-weight:800; font-size:19px; margin-bottom:14px;">تفصيل الدخل حسب نوع الخدمة</div>

            @php
                $totalIncomeSum = array_sum($incomeDetails);
                if ($totalIncomeSum <= 0) {
                    $totalIncomeSum = 1;
                }
            @endphp

            <div class="income-list">
                @foreach ($incomeDetails as $label => $value)
                    @php
                        $num = (float) $value;
                        $pct = round(($num / $totalIncomeSum) * 100, 1);
                        $barWidth = max(4, $pct);
                    @endphp

                    <div class="income-item">
                        <div class="left">
                            <div style="font-weight:700">{{ $label }}</div>
                            <div class="muted-sm">{{ number_format($num, 2) }} ج.م · <small>{{ $pct }}%</small></div>
                        </div>
                        <div class="bar">
                            <i style="width: {{ $barWidth }}%;"></i>
                        </div>
                    </div>
                @endforeach
            </div>

            <div style="margin-top:20px; border-top:1px solid rgba(255,255,255,0.05); padding-top:12px;">
                <div class="muted-sm">إجمالي الدخل</div>
                <div class="big-num">{{ number_format($totalIncome, 2) }} ج.م</div>
            </div>
        </div>


        {{-- -------- Expenses Card -------- --}}
        <div class="cinema-card float">
            <div style="font-weight:800; font-size:19px; margin-bottom:14px;">المصروفات</div>

            <div class="income-list">
                @foreach ($expenseList as $expense)
                    @php
                        $pct = ($expense['total'] / max($totalExpensesWithoutProducts, 1)) * 100;
                        $barWidth = max(4, $pct);
                    @endphp

                    <div class="income-item">
                        <div>
                            <div style="font-weight:700">{{ $expense['name'] }}</div>
                            <div class="muted-sm">{{ number_format($expense['total'], 2) }} ج.م · {{ round($pct, 1) }}%</div>
                        </div>
                        <div class="bar">
                            <i style="width: {{ $barWidth }}%"></i>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="profit-row">
                <div class="muted-sm">إجمالي مصروف وهوالك</div>
                <div class="big-num">{{ number_format($totalExpensesWithoutProducts, 2) }} ج.م</div>
            </div>

            <div class="profit-row">
                <div class="muted-sm">اجمالي تكلفة المنتجات</div>
                <div class="big-num">{{ number_format($productInvoiceItems, 2) }} ج.م</div>
            </div>

            <div class="profit-row">
                <div class="muted-sm">الأجمالي</div>
                <div class="big-num">{{ number_format($totalExpenses, 2) }} ج.م</div>
            </div>
        </div>

    </div>



    {{-- ====================== ROW 2: Profit Summary ====================== --}}
    <div class="layout-row">

        <div class="column-vertical">

            <div class="cinema-card float">
                <div style="font-weight:800; font-size:19px;">صافي ربح المبيعات</div>
                <div class="big-num">{{ number_format($netProfitOfProducts, 2) }} ج.م</div>
            </div>

            <div class="cinema-card float">
                <div style="font-weight:800; font-size:19px;">صافي ربح الخدمات</div>
                <div class="big-num">{{ number_format($netProfitWithoutProducts, 2) }} ج.م</div>
            </div>

            <div class="cinema-card float">
                <div style="font-weight:800; font-size:19px;">صافي ربح الخدمات والمبيعات</div>
                <div class="big-num">{{ number_format($netProfit, 2) }} ج.م</div>
            </div>

        </div>

    </div>

@endsection
