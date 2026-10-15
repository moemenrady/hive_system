@extends('layouts.app_page')

@section('title', 'التحليلات الشاملة والذكاء التشغيلي | Hive Analytics')

@section('style')
<style>
    :root {
        --hive-gold: #ffb84d;
        --hive-gold-light: #ffe6a3;
        --hive-gold-dark: #b87a14;
        --hive-card-bg: rgba(22, 22, 26, 0.85);
        --hive-border: rgba(255, 184, 77, 0.2);
        --hive-border-subtle: rgba(255, 255, 255, 0.08);
        --hive-text: #f5f5f5;
        --hive-muted: #9e9ea7;
        --hive-green: #10b981;
        --hive-red: #ef4444;
        --hive-blue: #38bdf8;
        --hive-purple: #c084fc;
    }

    .analytics-container {
        max-width: 1480px;
        margin: 0 auto;
        padding: 15px 20px 80px 20px;
    }

    /* Header styling */
    .dashboard-header {
        background: linear-gradient(135deg, rgba(26, 26, 32, 0.95), rgba(16, 16, 20, 0.85));
        border: 1px solid var(--hive-border);
        border-radius: 18px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        position: relative;
        overflow: hidden;
    }

    .dashboard-header::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 184, 77, 0.05) 0%, transparent 60%);
        pointer-events: none;
    }

    .dashboard-title {
        font-size: 26px;
        font-weight: 800;
        color: #fff;
        margin: 0 0 6px 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .dashboard-title i {
        color: var(--hive-gold);
        filter: drop-shadow(0 0 8px rgba(255, 184, 77, 0.6));
    }

    .dashboard-subtitle {
        color: var(--hive-muted);
        font-size: 14px;
        margin: 0;
    }

    /* Filters Bar */
    .filter-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
        justify-content: space-between;
        margin-top: 18px;
        padding-top: 18px;
        border-top: 1px solid var(--hive-border-subtle);
    }

    .period-btn-group {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .period-btn {
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid var(--hive-border-subtle);
        color: var(--hive-muted);
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.25s ease;
    }

    .period-btn:hover {
        background: rgba(255, 184, 77, 0.15);
        border-color: var(--hive-gold);
        color: #fff;
    }

    .period-btn.active {
        background: linear-gradient(135deg, var(--hive-gold), #e69d2e);
        border-color: var(--hive-gold);
        color: #111;
        box-shadow: 0 0 14px rgba(255, 184, 77, 0.4);
    }

    .date-filter-form {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .date-input {
        background: rgba(14, 14, 18, 0.9);
        border: 1px solid var(--hive-border-subtle);
        color: #fff;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 13px;
        outline: none;
        transition: border-color 0.2s;
    }

    .date-input:focus {
        border-color: var(--hive-gold);
    }

    .btn-filter-submit {
        background: var(--hive-gold);
        border: none;
        color: #111;
        padding: 6px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-filter-submit:hover {
        background: var(--hive-gold-light);
        box-shadow: 0 0 10px rgba(255, 184, 77, 0.5);
    }

    /* Section Titles */
    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 35px 0 18px 0;
        padding-bottom: 10px;
        border-bottom: 2px solid rgba(255, 184, 77, 0.15);
    }

    .section-title {
        font-size: 19px;
        font-weight: 800;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
    }

    .section-title i {
        color: var(--hive-gold);
    }

    /* Metric Cards Grid */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .kpi-card {
        background: var(--hive-card-bg);
        border: 1px solid var(--hive-border-subtle);
        border-radius: 14px;
        padding: 18px;
        position: relative;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
        overflow: hidden;
    }

    .kpi-card:hover {
        transform: translateY(-3px);
        border-color: var(--hive-border);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.45);
    }

    .kpi-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .kpi-label {
        font-size: 13px;
        font-weight: 600;
        color: var(--hive-muted);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .kpi-icon-wrap {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        background: rgba(255, 184, 77, 0.08);
        color: var(--hive-gold);
    }

    .kpi-icon-wrap.green { background: rgba(16, 185, 129, 0.1); color: var(--hive-green); }
    .kpi-icon-wrap.blue { background: rgba(56, 189, 248, 0.1); color: var(--hive-blue); }
    .kpi-icon-wrap.purple { background: rgba(192, 132, 252, 0.1); color: var(--hive-purple); }
    .kpi-icon-wrap.red { background: rgba(239, 68, 68, 0.1); color: var(--hive-red); }

    .kpi-value-row {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
    }

    .kpi-value {
        font-size: 24px;
        font-weight: 800;
        color: #fff;
        line-height: 1.2;
    }

    .kpi-unit {
        font-size: 12px;
        color: var(--hive-muted);
        margin-right: 4px;
        font-weight: normal;
    }

    .kpi-subtext {
        font-size: 12px;
        color: var(--hive-muted);
        margin-top: 8px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* THE MANDATORY `!` EXPLAINER BADGE */
    .explainer-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: rgba(255, 184, 77, 0.15);
        color: var(--hive-gold);
        border: 1px solid rgba(255, 184, 77, 0.4);
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
        padding: 0;
        margin-right: 6px;
        transition: all 0.2s ease;
        vertical-align: middle;
        flex-shrink: 0;
    }

    .explainer-btn:hover {
        background: var(--hive-gold);
        color: #111;
        box-shadow: 0 0 10px rgba(255, 184, 77, 0.7);
        transform: scale(1.15);
    }

    /* Charts Row */
    .charts-row {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 18px;
        margin-bottom: 24px;
    }

    @media (max-width: 1024px) {
        .charts-row {
            grid-template-columns: 1fr;
        }
    }

    .chart-card {
        background: var(--hive-card-bg);
        border: 1px solid var(--hive-border-subtle);
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .chart-title {
        font-size: 15px;
        font-weight: 700;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
    }

    .chart-container-rel {
        position: relative;
        height: 310px;
        width: 100%;
    }

    /* Tables */
    .table-card {
        background: var(--hive-card-bg);
        border: 1px solid var(--hive-border-subtle);
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        margin-bottom: 24px;
        overflow: hidden;
    }

    .custom-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        color: var(--hive-text);
    }

    .custom-table thead th {
        background: rgba(255, 255, 255, 0.03);
        color: var(--hive-gold-light);
        font-weight: 700;
        padding: 12px 14px;
        text-align: right;
        border-bottom: 1px solid var(--hive-border);
        white-space: nowrap;
    }

    .custom-table tbody td {
        padding: 12px 14px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        vertical-align: middle;
    }

    .custom-table tbody tr:hover {
        background: rgba(255, 184, 77, 0.03);
    }

    .badge-status {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
    }

    .badge-status.success { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
    .badge-status.warning { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
    .badge-status.danger { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }

    /* Modal / Popover Explainer Styling */
    .explainer-modal-content {
        background: #141418 !important;
        border: 1px solid var(--hive-gold) !important;
        border-radius: 20px !important;
        color: #fff !important;
        box-shadow: 0 0 35px rgba(255, 184, 77, 0.25) !important;
    }

    .explainer-modal-header {
        border-bottom: 1px solid rgba(255, 184, 77, 0.2) !important;
        padding: 18px 24px !important;
    }

    .explainer-block {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 14px;
    }

    .explainer-block-title {
        font-size: 13px;
        font-weight: 700;
        color: var(--hive-gold);
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .explainer-block-text {
        font-size: 13px;
        line-height: 1.7;
        color: #e2e8f0;
        margin: 0;
    }

    .calc-formula {
        background: #090a0d;
        border-left: 3px solid var(--hive-gold);
        padding: 8px 12px;
        border-radius: 6px;
        font-family: monospace;
        color: #38bdf8;
        direction: ltr;
        text-align: left;
        margin-top: 6px;
        font-size: 12px;
    }

    .table-responsive {
        max-height: 480px;
        overflow-y: auto;
    }

    /* Scrollbar */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    ::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.2);
    }
    ::-webkit-scrollbar-thumb {
        background: rgba(255, 184, 77, 0.3);
        border-radius: 3px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: var(--hive-gold);
    }
</style>
@endsection

@section('content')
<div class="analytics-container">

    {{-- DASHBOARD HEADER --}}
    <div class="dashboard-header">
        <div class="dashboard-title">
            <i class="fa-solid fa-chart-line"></i>
            <span>لوحة التحليلات الشاملة والذكاء التشغيلي</span>
            <span class="badge" style="background: rgba(255, 184, 77, 0.2); color: var(--hive-gold); border: 1px solid var(--hive-gold); font-size: 12px; font-weight: normal; margin-right: 8px;">
                نظام هايف 360°
            </span>
        </div>
        <p class="dashboard-subtitle">
            تقرير تفاعلي موحد يربط حركة الجلسات، مبيعات المنتجات، استهلاك الخامات، الاشتراكات، والسيولة المالية برؤية إدارة احترافية.
        </p>

        {{-- FILTERS BAR --}}
        <div class="filter-bar">
            <div class="period-btn-group">
                <a href="{{ route('analytics.comprehensive', ['period' => 'today']) }}" class="period-btn {{ $period === 'today' ? 'active' : '' }}">اليوم</a>
                <a href="{{ route('analytics.comprehensive', ['period' => 'yesterday']) }}" class="period-btn {{ $period === 'yesterday' ? 'active' : '' }}">أمس</a>
                <a href="{{ route('analytics.comprehensive', ['period' => 'this_week']) }}" class="period-btn {{ $period === 'this_week' ? 'active' : '' }}">هذا الأسبوع</a>
                <a href="{{ route('analytics.comprehensive', ['period' => 'this_month']) }}" class="period-btn {{ $period === 'this_month' ? 'active' : '' }}">هذا الشهر</a>
                <a href="{{ route('analytics.comprehensive', ['period' => 'last_30_days']) }}" class="period-btn {{ $period === 'last_30_days' ? 'active' : '' }}">آخر 30 يوماً</a>
                <a href="{{ route('analytics.comprehensive', ['period' => 'this_year']) }}" class="period-btn {{ $period === 'this_year' ? 'active' : '' }}">هذا العام</a>
                <a href="{{ route('analytics.comprehensive', ['period' => 'all']) }}" class="period-btn {{ $period === 'all' ? 'active' : '' }}">كل الأوقات</a>
            </div>

            <form action="{{ route('analytics.comprehensive') }}" method="GET" class="date-filter-form">
                <input type="hidden" name="period" value="custom">
                <span style="font-size: 12px; color: var(--hive-muted);">تخصيص:</span>
                <input type="date" name="from" value="{{ $from }}" class="date-input" required title="من تاريخ">
                <span style="color: var(--hive-muted);">-</span>
                <input type="date" name="to" value="{{ $to }}" class="date-input" required title="إلى تاريخ">
                <button type="submit" class="btn-filter-submit">
                    <i class="fa-solid fa-filter"></i> تطبيق
                </button>
            </form>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- SECTION 1: FINANCIAL OVERVIEW --}}
    {{-- ======================================================== --}}
    <div class="section-header">
        <h2 class="section-title">
            <i class="fa-solid fa-sack-dollar"></i>
            <span>المؤشرات المالية الكلية وتدفق الأرباح</span>
        </h2>
        <span class="badge" style="background: rgba(255,255,255,0.05); color: var(--hive-muted); font-size: 12px;">
            الفترة: {{ $from ? $from . ' إلى ' . $to : 'كل البيانات' }}
        </span>
    </div>

    <div class="kpi-grid">
        {{-- Total Revenue --}}
        <div class="kpi-card">
            <div class="kpi-top">
                <span class="kpi-label">
                    <button type="button" class="explainer-btn" onclick="openExplainer('revenue')" title="شرح مفصل">!</button>
                    إجمالي الإيرادات الكلية
                </span>
                <div class="kpi-icon-wrap"><i class="fa-solid fa-coins"></i></div>
            </div>
            <div class="kpi-value-row">
                <div class="kpi-value">{{ number_format($totalRevenue, 2) }} <span class="kpi-unit">ج.م</span></div>
            </div>
            <div class="kpi-subtext">
                <span>فواتير مصدرة: {{ number_format($invoicesCount) }}</span>
                <span style="color: var(--hive-gold);">شامل كل الخدمات</span>
            </div>
        </div>

        {{-- Net Profit --}}
        <div class="kpi-card">
            <div class="kpi-top">
                <span class="kpi-label">
                    <button type="button" class="explainer-btn" onclick="openExplainer('net_profit')" title="شرح مفصل">!</button>
                    صافي الربح الفعلي
                </span>
                <div class="kpi-icon-wrap {{ $netProfit >= 0 ? 'green' : 'red' }}">
                    <i class="fa-solid fa-chart-line-up"></i>
                </div>
            </div>
            <div class="kpi-value-row">
                <div class="kpi-value" style="color: {{ $netProfit >= 0 ? 'var(--hive-green)' : 'var(--hive-red)' }};">
                    {{ number_format($netProfit, 2) }} <span class="kpi-unit">ج.م</span>
                </div>
            </div>
            <div class="kpi-subtext">
                <span>هامش الربح: %{{ $netProfitMargin }}</span>
                <span style="color: {{ $netProfit >= 0 ? 'var(--hive-green)' : 'var(--hive-red)' }};">
                    {{ $netProfit >= 0 ? 'فائض نقدي متاح' : 'عجز تشغيلي' }}
                </span>
            </div>
        </div>

        {{-- COGS --}}
        <div class="kpi-card">
            <div class="kpi-top">
                <span class="kpi-label">
                    <button type="button" class="explainer-btn" onclick="openExplainer('cogs')" title="شرح مفصل">!</button>
                    تكلفة البضاعة المباعة (COGS)
                </span>
                <div class="kpi-icon-wrap purple"><i class="fa-solid fa-boxes-packing"></i></div>
            </div>
            <div class="kpi-value-row">
                <div class="kpi-value">{{ number_format($cogs, 2) }} <span class="kpi-unit">ج.م</span></div>
            </div>
            <div class="kpi-subtext">
                <span>المشروبات والمنتجات</span>
                <span>ربح المنتجات: {{ number_format($productRevenue - $cogs, 2) }} ج.م</span>
            </div>
        </div>

        {{-- Operating Expenses --}}
        <div class="kpi-card">
            <div class="kpi-top">
                <span class="kpi-label">
                    <button type="button" class="explainer-btn" onclick="openExplainer('expenses')" title="شرح مفصل">!</button>
                    المصروفات التشغيلية
                </span>
                <div class="kpi-icon-wrap red"><i class="fa-solid fa-receipt"></i></div>
            </div>
            <div class="kpi-value-row">
                <div class="kpi-value">{{ number_format($totalExpenses, 2) }} <span class="kpi-unit">ج.م</span></div>
            </div>
            <div class="kpi-subtext">
                <span>نثريات، فواتير وصيانة</span>
                <span>إجمالي الخصومات: {{ number_format($totalDiscounts, 2) }} ج.م</span>
            </div>
        </div>

        {{-- Average Order Value --}}
        <div class="kpi-card">
            <div class="kpi-top">
                <span class="kpi-label">
                    <button type="button" class="explainer-btn" onclick="openExplainer('aov')" title="شرح مفصل">!</button>
                    متوسط الفاتورة (AOV)
                </span>
                <div class="kpi-icon-wrap blue"><i class="fa-solid fa-file-invoice-dollar"></i></div>
            </div>
            <div class="kpi-value-row">
                <div class="kpi-value">{{ number_format($avgInvoiceValue, 2) }} <span class="kpi-unit">ج.م</span></div>
            </div>
            <div class="kpi-subtext">
                <span>لكل عميل / فاتورة</span>
                <span>عدد الفواتير: {{ $invoicesCount }}</span>
            </div>
        </div>
    </div>

    {{-- CHARTS ROW 1: REVENUE TIMELINE & STREAM DISTRIBUTION --}}
    <div class="charts-row">
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">
                    <button type="button" class="explainer-btn" onclick="openExplainer('timeline_chart')" title="شرح الرسم">!</button>
                    <i class="fa-solid fa-chart-area" style="color: var(--hive-gold);"></i>
                    <span>حركة الإيرادات وصافي الأرباح اليومية</span>
                </h3>
                <span style="font-size: 12px; color: var(--hive-muted);">تطور السيولة عبر الفترة</span>
            </div>
            <div class="chart-container-rel">
                <canvas id="timelineChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">
                    <button type="button" class="explainer-btn" onclick="openExplainer('revenue_sources_chart')" title="شرح الرسم">!</button>
                    <i class="fa-solid fa-chart-pie" style="color: var(--hive-gold);"></i>
                    <span>توزيع مصادر الدخل الكلي</span>
                </h3>
                <span style="font-size: 12px; color: var(--hive-muted);">نسب المساهمة</span>
            </div>
            <div class="chart-container-rel">
                <canvas id="revenueSourcesChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- SECTION 2: SESSIONS & WORKSPACE INTELLIGENCE --}}
    {{-- ======================================================== --}}
    <div class="section-header">
        <h2 class="section-title">
            <i class="fa-solid fa-chair"></i>
            <span>تحليلات الجلسات ومساحة العمل (Coworking Sessions)</span>
        </h2>
        <span class="badge" style="background: rgba(255, 184, 77, 0.1); color: var(--hive-gold); border: 1px solid var(--hive-border); font-size: 12px;">
            جلسات نشطة الآن: {{ $activeSessionsNow }}
        </span>
    </div>

    <div class="kpi-grid">
        {{-- Total Sessions --}}
        <div class="kpi-card">
            <div class="kpi-top">
                <span class="kpi-label">
                    <button type="button" class="explainer-btn" onclick="openExplainer('total_sessions')" title="شرح مفصل">!</button>
                    إجمالي الجلسات بالفترة
                </span>
                <div class="kpi-icon-wrap"><i class="fa-solid fa-users"></i></div>
            </div>
            <div class="kpi-value-row">
                <div class="kpi-value">{{ number_format($totalSessions) }} <span class="kpi-unit">جلسة</span></div>
            </div>
            <div class="kpi-subtext">
                <span>مكتملة: {{ $closedSessions }}</span>
                <span style="color: var(--hive-gold);">نشطة الآن: {{ $activeSessionsNow }}</span>
            </div>
        </div>

        {{-- Total Persons --}}
        <div class="kpi-card">
            <div class="kpi-top">
                <span class="kpi-label">
                    <button type="button" class="explainer-btn" onclick="openExplainer('total_persons')" title="شرح مفصل">!</button>
                    إجمالي الأفراد المستضافين
                </span>
                <div class="kpi-icon-wrap blue"><i class="fa-solid fa-user-group"></i></div>
            </div>
            <div class="kpi-value-row">
                <div class="kpi-value">{{ number_format($totalPersons) }} <span class="kpi-unit">فرد</span></div>
            </div>
            <div class="kpi-subtext">
                <span>متوسط الأفراد بالجلسة:</span>
                <span style="color: var(--hive-blue); font-weight: 700;">{{ $avgPersonsPerSession }} فرد/جلسة</span>
            </div>
        </div>

        {{-- Average Duration --}}
        <div class="kpi-card">
            <div class="kpi-top">
                <span class="kpi-label">
                    <button type="button" class="explainer-btn" onclick="openExplainer('avg_duration')" title="شرح مفصل">!</button>
                    متوسط مدة الجلسة
                </span>
                <div class="kpi-icon-wrap purple"><i class="fa-solid fa-stopwatch"></i></div>
            </div>
            <div class="kpi-value-row">
                <div class="kpi-value" style="font-size: 21px;">{{ $avgDurationFormatted }}</div>
            </div>
            <div class="kpi-subtext">
                <span>إجمالي الدقائق:</span>
                <span>{{ $avgDurationMinutes }} دقيقة</span>
            </div>
        </div>

        {{-- Beverage Attachment Rate --}}
        <div class="kpi-card">
            <div class="kpi-top">
                <span class="kpi-label">
                    <button type="button" class="explainer-btn" onclick="openExplainer('attachment_rate')" title="شرح مفصل">!</button>
                    معدل طلب المشروبات
                </span>
                <div class="kpi-icon-wrap green"><i class="fa-solid fa-mug-hot"></i></div>
            </div>
            <div class="kpi-value-row">
                <div class="kpi-value" style="color: var(--hive-green);">%{{ $beverageAttachmentRate }}</div>
            </div>
            <div class="kpi-subtext">
                <span>جلسات طلبت مشروبات:</span>
                <span style="color: var(--hive-green); font-weight: 700;">{{ $sessionsWithPurchasesCount }} من {{ $totalSessions }}</span>
            </div>
        </div>

        {{-- Session Avg Spend --}}
        <div class="kpi-card">
            <div class="kpi-top">
                <span class="kpi-label">
                    <button type="button" class="explainer-btn" onclick="openExplainer('session_spend')" title="شرح مفصل">!</button>
                    متوسط إنفاق الجلسة
                </span>
                <div class="kpi-icon-wrap"><i class="fa-solid fa-hand-holding-dollar"></i></div>
            </div>
            <div class="kpi-value-row">
                <div class="kpi-value">{{ number_format($avgSpendPerSession, 2) }} <span class="kpi-unit">ج.م</span></div>
            </div>
            <div class="kpi-subtext">
                <span>متوسط إنفاق الفرد:</span>
                <span style="color: var(--hive-gold); font-weight: 700;">{{ number_format($avgSpendPerPerson, 2) }} ج.م</span>
            </div>
        </div>
    </div>

    {{-- SESSIONS BREAKDOWN & PEAK HOURS CHART --}}
    <div class="charts-row">
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">
                    <button type="button" class="explainer-btn" onclick="openExplainer('peak_hours')" title="شرح الرسم">!</button>
                    <i class="fa-solid fa-clock" style="color: var(--hive-gold);"></i>
                    <span>توزيع ساعات الذروة للجلسات (24 ساعة)</span>
                </h3>
                <span style="font-size: 12px; color: var(--hive-muted);">توقيت بدء الجلسات وجدولة الشفتات</span>
            </div>
            <div class="chart-container-rel">
                <canvas id="peakHoursChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">
                    <button type="button" class="explainer-btn" onclick="openExplainer('session_revenue_split')" title="شرح الرسم">!</button>
                    <i class="fa-solid fa-scale-balanced" style="color: var(--hive-gold);"></i>
                    <span>هيكل إيرادات الجلسة</span>
                </h3>
                <span style="font-size: 12px; color: var(--hive-muted);">رسوم الوقت مقابل المشروبات</span>
            </div>
            <div class="chart-container-rel">
                <canvas id="sessionSplitChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- SECTION 3: PRODUCTS & INVENTORY ANALYTICS --}}
    {{-- ======================================================== --}}
    <div class="section-header">
        <h2 class="section-title">
            <i class="fa-solid fa-coffee"></i>
            <span>تحليلات المنتجات والمبيعات والمخزن (Products & Stock)</span>
        </h2>
        <span class="badge" style="background: rgba(255,255,255,0.05); color: var(--hive-gold); font-size: 12px;">
            أصناف المنيو: {{ $totalProductCatalog }} (مصنع: {{ $producedProductsCount }} | جاهز: {{ $packagedProductsCount }})
        </span>
    </div>

    <div class="kpi-grid">
        {{-- Total Units Sold --}}
        <div class="kpi-card">
            <div class="kpi-top">
                <span class="kpi-label">
                    <button type="button" class="explainer-btn" onclick="openExplainer('units_sold')" title="شرح مفصل">!</button>
                    إجمالي القطع المباعة
                </span>
                <div class="kpi-icon-wrap"><i class="fa-solid fa-basket-shopping"></i></div>
            </div>
            <div class="kpi-value-row">
                <div class="kpi-value">{{ number_format($totalUnitsSold) }} <span class="kpi-unit">وحدة</span></div>
            </div>
            <div class="kpi-subtext">
                <span>إيراد المبيعات:</span>
                <span style="color: var(--hive-gold); font-weight: 700;">{{ number_format($productRevenue, 2) }} ج.م</span>
            </div>
        </div>

        {{-- Inventory Valuation Cost --}}
        <div class="kpi-card">
            <div class="kpi-top">
                <span class="kpi-label">
                    <button type="button" class="explainer-btn" onclick="openExplainer('inv_cost')" title="شرح مفصل">!</button>
                    قيمة المخزون الحالي بالتكلفة
                </span>
                <div class="kpi-icon-wrap purple"><i class="fa-solid fa-warehouse"></i></div>
            </div>
            <div class="kpi-value-row">
                <div class="kpi-value">{{ number_format($inventoryTotalCost, 2) }} <span class="kpi-unit">ج.م</span></div>
            </div>
            <div class="kpi-subtext">
                <span>رأس المال الراكد بالبضاعة</span>
                <span>تكلفة الشراء الفعلية</span>
            </div>
        </div>

        {{-- Inventory Potential Retail --}}
        <div class="kpi-card">
            <div class="kpi-top">
                <span class="kpi-label">
                    <button type="button" class="explainer-btn" onclick="openExplainer('inv_retail')" title="شرح مفصل">!</button>
                    القيمة البيعية المتوقعة للمخزون
                </span>
                <div class="kpi-icon-wrap green"><i class="fa-solid fa-tags"></i></div>
            </div>
            <div class="kpi-value-row">
                <div class="kpi-value" style="color: var(--hive-green);">{{ number_format($inventoryTotalRetail, 2) }} <span class="kpi-unit">ج.م</span></div>
            </div>
            <div class="kpi-subtext">
                <span>الربح الكامن عند البيع:</span>
                <span style="color: var(--hive-green); font-weight: 700;">{{ number_format($inventoryPotentialProfit, 2) }} ج.م</span>
            </div>
        </div>

        {{-- Low Stock Products Alert --}}
        <div class="kpi-card">
            <div class="kpi-top">
                <span class="kpi-label">
                    <button type="button" class="explainer-btn" onclick="openExplainer('low_stock_products')" title="شرح مفصل">!</button>
                    منتجات في الحد الحرج
                </span>
                <div class="kpi-icon-wrap {{ $lowStockProducts->count() > 0 ? 'red' : 'green' }}">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
            <div class="kpi-value-row">
                <div class="kpi-value" style="color: {{ $lowStockProducts->count() > 0 ? 'var(--hive-red)' : 'var(--hive-green)' }};">
                    {{ $lowStockProducts->count() }} <span class="kpi-unit">صنف</span>
                </div>
            </div>
            <div class="kpi-subtext">
                <span>الرصيد أقل من الحد الأدنى</span>
                <span style="color: {{ $lowStockProducts->count() > 0 ? 'var(--hive-red)' : 'var(--hive-green)' }};">
                    {{ $lowStockProducts->count() > 0 ? 'يتطلب إعادة طلب' : 'المخزون آمن' }}
                </span>
            </div>
        </div>
    </div>

    {{-- TABLES: TOP SELLERS & MOST PROFITABLE --}}
    <div class="row g-3 mb-4">
        {{-- Top Selling Products --}}
        <div class="col-lg-6">
            <div class="table-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 style="font-size: 15px; font-weight: 700; color: #fff; margin: 0;">
                        <button type="button" class="explainer-btn" onclick="openExplainer('top_selling_table')" title="شرح الجدول">!</button>
                        <i class="fa-solid fa-trophy" style="color: var(--hive-gold);"></i>
                        أفضل 8 منتجات مبيعاً بالكمية
                    </h4>
                    <span style="font-size: 12px; color: var(--hive-muted);">الأعلى طلباً</span>
                </div>
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>المنتج</th>
                                <th>
                                    <button type="button" class="explainer-btn" onclick="openExplainer('col_qty')" style="width: 16px; height: 16px; font-size: 10px;">!</button>
                                    الكمية
                                </th>
                                <th>
                                    <button type="button" class="explainer-btn" onclick="openExplainer('col_rev')" style="width: 16px; height: 16px; font-size: 10px;">!</button>
                                    الإيراد
                                </th>
                                <th>
                                    <button type="button" class="explainer-btn" onclick="openExplainer('col_profit')" style="width: 16px; height: 16px; font-size: 10px;">!</button>
                                    الربح
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topSellingProducts as $item)
                                <tr>
                                    <td style="font-weight: 700;">{{ $item->name }}</td>
                                    <td><span class="badge" style="background: rgba(255, 184, 77, 0.15); color: var(--hive-gold);">{{ $item->total_qty }}</span></td>
                                    <td>{{ number_format($item->revenue, 2) }} ج.م</td>
                                    <td style="color: var(--hive-green); font-weight: 700;">{{ number_format($item->profit, 2) }} ج.م</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">لا توجد مبيعات في الفترة المحددة</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Most Profitable Products --}}
        <div class="col-lg-6">
            <div class="table-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 style="font-size: 15px; font-weight: 700; color: #fff; margin: 0;">
                        <button type="button" class="explainer-btn" onclick="openExplainer('top_profitable_table')" title="شرح الجدول">!</button>
                        <i class="fa-solid fa-gem" style="color: #38bdf8;"></i>
                        أعلى 8 منتجات تحقيقاً لصافي الأرباح
                    </h4>
                    <span style="font-size: 12px; color: var(--hive-muted);">عائد الربح الأكبر</span>
                </div>
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>المنتج</th>
                                <th>الكمية</th>
                                <th>الإيراد</th>
                                <th>
                                    <button type="button" class="explainer-btn" onclick="openExplainer('col_margin')" style="width: 16px; height: 16px; font-size: 10px;">!</button>
                                    صافي الربح
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProfitableProducts as $item)
                                <tr>
                                    <td style="font-weight: 700;">{{ $item->name }}</td>
                                    <td>{{ $item->total_qty }}</td>
                                    <td>{{ number_format($item->revenue, 2) }} ج.م</td>
                                    <td style="color: var(--hive-green); font-weight: 800;">
                                        {{ number_format($item->profit, 2) }} ج.م
                                        @if($item->revenue > 0)
                                            <small style="color: var(--hive-muted); font-size: 11px;">(%{{ round(($item->profit / $item->revenue) * 100, 1) }})</small>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">لا توجد بيانات ربحية</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- LOW STOCK PRODUCTS TABLE (IF ANY) --}}
    @if($lowStockProducts->count() > 0)
    <div class="table-card" style="border-color: rgba(239, 68, 68, 0.4);">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 style="font-size: 15px; font-weight: 700; color: #f87171; margin: 0;">
                <button type="button" class="explainer-btn" onclick="openExplainer('low_stock_products_table')" style="background: rgba(239, 68, 68, 0.2); color: #f87171; border-color: rgba(239, 68, 68, 0.5);">!</button>
                <i class="fa-solid fa-triangle-exclamation"></i>
                تنبيه النواقص: منتجات بلغت الحد الأدنى أو نفذت بالمخزن
            </h4>
            <span class="badge-status danger">{{ $lowStockProducts->count() }} صنف حرج</span>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>المنتج</th>
                        <th>القسم</th>
                        <th>الرصيد الفعلي الحالي</th>
                        <th>حد الأمان الأدنى</th>
                        <th>العجز المطلوب للشراء</th>
                        <th>حالة الصنف</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lowStockProducts as $p)
                        <tr>
                            <td style="font-weight: 700;">{{ $p->name }}</td>
                            <td>{{ $p->category->name ?? 'بدون قسم' }}</td>
                            <td style="color: {{ $p->quantity <= 0 ? 'var(--hive-red)' : 'var(--hive-gold)' }}; font-weight: 800;">
                                {{ $p->quantity }}
                            </td>
                            <td>{{ $p->min_quantity }}</td>
                            <td style="color: var(--hive-red); font-weight: 700;">
                                {{ max(0, $p->min_quantity - $p->quantity + 5) }} وحدة مقترحة
                            </td>
                            <td>
                                @if($p->quantity <= 0)
                                    <span class="badge-status danger">نفذ بالكامل (Out of Stock)</span>
                                @else
                                    <span class="badge-status warning">مخزون حرج (Low Stock)</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ======================================================== --}}
    {{-- SECTION 4: RAW MATERIALS & INGREDIENTS ANALYTICS --}}
    {{-- ======================================================== --}}
    <div class="section-header">
        <h2 class="section-title">
            <i class="fa-solid fa-wheat-awn"></i>
            <span>تحليلات الخامات ومستودع التشغيل (Ingredients & Raw Materials)</span>
        </h2>
        <span class="badge" style="background: rgba(255,255,255,0.05); color: var(--hive-gold); font-size: 12px;">
            إجمالي الخامات: {{ $totalIngredientsCount }} خامة
        </span>
    </div>

    <div class="kpi-grid">
        {{-- Total Raw Ingredients --}}
        <div class="kpi-card">
            <div class="kpi-top">
                <span class="kpi-label">
                    <button type="button" class="explainer-btn" onclick="openExplainer('total_ingredients')" title="شرح مفصل">!</button>
                    أصناف الخامات بالبار
                </span>
                <div class="kpi-icon-wrap"><i class="fa-solid fa-jar"></i></div>
            </div>
            <div class="kpi-value-row">
                <div class="kpi-value">{{ $totalIngredientsCount }} <span class="kpi-unit">خامة</span></div>
            </div>
            <div class="kpi-subtext">
                <span>مواد تحضير المشروبات</span>
                <span>بن، حليب، سيرب، أكواب...</span>
            </div>
        </div>

        {{-- Alert Raw Materials --}}
        <div class="kpi-card">
            <div class="kpi-top">
                <span class="kpi-label">
                    <button type="button" class="explainer-btn" onclick="openExplainer('alert_ingredients')" title="شرح مفصل">!</button>
                    خامات دون حد الأمان
                </span>
                <div class="kpi-icon-wrap {{ $lowStockIngredients->count() > 0 ? 'red' : 'green' }}">
                    <i class="fa-solid fa-bell"></i>
                </div>
            </div>
            <div class="kpi-value-row">
                <div class="kpi-value" style="color: {{ $lowStockIngredients->count() > 0 ? 'var(--hive-red)' : 'var(--hive-green)' }};">
                    {{ $lowStockIngredients->count() }} <span class="kpi-unit">خامة</span>
                </div>
            </div>
            <div class="kpi-subtext">
                <span>تنبيه فوري للمشتريات</span>
                <span style="color: {{ $lowStockIngredients->count() > 0 ? 'var(--hive-red)' : 'var(--hive-green)' }};">
                    {{ $lowStockIngredients->count() > 0 ? 'خطر توقف أصناف' : 'جميع الخامات متوفرة' }}
                </span>
            </div>
        </div>

        {{-- Total Outflow Movements --}}
        <div class="kpi-card">
            <div class="kpi-top">
                <span class="kpi-label">
                    <button type="button" class="explainer-btn" onclick="openExplainer('outflow_movements')" title="شرح مفصل">!</button>
                    حركات صرف واستهلاك الخامات
                </span>
                <div class="kpi-icon-wrap purple"><i class="fa-solid fa-arrow-right-from-bracket"></i></div>
            </div>
            <div class="kpi-value-row">
                <div class="kpi-value">{{ number_format($totalOutMovements) }} <span class="kpi-unit">حركة</span></div>
            </div>
            <div class="kpi-subtext">
                <span>خصم آلي وفوري بالوصفات</span>
                <span>حركات توريد (In): {{ $totalInMovements }}</span>
            </div>
        </div>
    </div>

    {{-- INGREDIENTS CONSUMPTION & INVENTORY STATUS TABLES --}}
    <div class="row g-3 mb-4">
        {{-- Top Consumed Ingredients --}}
        <div class="col-lg-6">
            <div class="table-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 style="font-size: 15px; font-weight: 700; color: #fff; margin: 0;">
                        <button type="button" class="explainer-btn" onclick="openExplainer('top_consumed_ingredients')" title="شرح الجدول">!</button>
                        <i class="fa-solid fa-fire-burner" style="color: var(--hive-gold);"></i>
                        أكثر 8 خامات استهلاكاً خلال الفترة
                    </h4>
                    <span style="font-size: 12px; color: var(--hive-muted);">سحب التشغيل</span>
                </div>
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>الخامة</th>
                                <th>الوحدة</th>
                                <th>
                                    <button type="button" class="explainer-btn" onclick="openExplainer('col_consumed_qty')" style="width: 16px; height: 16px; font-size: 10px;">!</button>
                                    إجمالي الكمية المصروفة
                                </th>
                                <th>عمليات الخصم</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topConsumedIngredients as $ing)
                                <tr>
                                    <td style="font-weight: 700;">{{ $ing->name }}</td>
                                    <td><span class="badge" style="background: rgba(255,255,255,0.06);">{{ $ing->unit_name ?: 'وحدة' }}</span></td>
                                    <td style="color: var(--hive-gold); font-weight: 800;">{{ number_format($ing->consumed_qty, 2) }}</td>
                                    <td>{{ $ing->op_count }} مرة</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">لا توجد حركات استهلاك مسجلة خلال الفترة</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- All Ingredients Stock Health Table --}}
        <div class="col-lg-6">
            <div class="table-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 style="font-size: 15px; font-weight: 700; color: #fff; margin: 0;">
                        <button type="button" class="explainer-btn" onclick="openExplainer('all_ingredients_health')" title="شرح الجدول">!</button>
                        <i class="fa-solid fa-list-check" style="color: #38bdf8;"></i>
                        حالة أرصدة الخامات وحدود التنبيه
                    </h4>
                    <span style="font-size: 12px; color: var(--hive-muted);">جرد المستودع</span>
                </div>
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>الخامة</th>
                                <th>الرصيد الفعلي</th>
                                <th>حد التنبيه</th>
                                <th>الوحدة</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allIngredients as $ing)
                                <tr>
                                    <td style="font-weight: 700;">{{ $ing->name }}</td>
                                    <td style="font-weight: 800; color: {{ $ing->stock <= $ing->alert_stock ? 'var(--hive-red)' : '#fff' }};">
                                        {{ $ing->stock }}
                                    </td>
                                    <td>{{ $ing->alert_stock }}</td>
                                    <td>{{ $ing->unit->name ?? 'وحدة' }}</td>
                                    <td>
                                        @if($ing->stock <= 0)
                                            <span class="badge-status danger">نفذ</span>
                                        @elseif($ing->stock <= $ing->alert_stock)
                                            <span class="badge-status warning">حرج</span>
                                        @else
                                            <span class="badge-status success">آمن</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">لا توجد خامات مسجلة</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- SECTION 5: SUBSCRIPTIONS & BOOKINGS ANALYTICS --}}
    {{-- ======================================================== --}}
    <div class="section-header">
        <h2 class="section-title">
            <i class="fa-solid fa-id-card"></i>
            <span>تحليلات الاشتراكات وحجوزات القاعات (Subscriptions & Halls)</span>
        </h2>
    </div>

    <div class="kpi-grid">
        {{-- Active Subscriptions --}}
        <div class="kpi-card">
            <div class="kpi-top">
                <span class="kpi-label">
                    <button type="button" class="explainer-btn" onclick="openExplainer('active_subs')" title="شرح مفصل">!</button>
                    الاشتراكات النشطة حالياً
                </span>
                <div class="kpi-icon-wrap"><i class="fa-solid fa-address-card"></i></div>
            </div>
            <div class="kpi-value-row">
                <div class="kpi-value">{{ $activeSubscriptions }} <span class="kpi-unit">عضو نشط</span></div>
            </div>
            <div class="kpi-subtext">
                <span>اشتراكات مسجلة بالفترة: {{ $totalSubscriptionsInPeriod }}</span>
                <span>إيراد: {{ number_format($subscriptionRevenue, 2) }} ج.م</span>
            </div>
        </div>

        {{-- Bookings & Events --}}
        <div class="kpi-card">
            <div class="kpi-top">
                <span class="kpi-label">
                    <button type="button" class="explainer-btn" onclick="openExplainer('bookings_kpi')" title="شرح مفصل">!</button>
                    حجوزات القاعات والمناسبات
                </span>
                <div class="kpi-icon-wrap blue"><i class="fa-solid fa-calendar-check"></i></div>
            </div>
            <div class="kpi-value-row">
                <div class="kpi-value">{{ $totalBookings }} <span class="kpi-unit">حجز</span></div>
            </div>
            <div class="kpi-subtext">
                <span>إجمالي الحضور: {{ $totalBookingAttendees }} فرد</span>
                <span>إيراد: {{ number_format($bookingRevenue, 2) }} ج.م</span>
            </div>
        </div>

        {{-- Top Hall --}}
        <div class="kpi-card">
            <div class="kpi-top">
                <span class="kpi-label">
                    <button type="button" class="explainer-btn" onclick="openExplainer('top_hall')" title="شرح مفصل">!</button>
                    القاعة الأكثر طلباً
                </span>
                <div class="kpi-icon-wrap purple"><i class="fa-solid fa-door-open"></i></div>
            </div>
            <div class="kpi-value-row">
                <div class="kpi-value" style="font-size: 20px;">{{ $topHall->name ?? 'لا توجد حجوزات' }}</div>
            </div>
            <div class="kpi-subtext">
                <span>عدد الحجوزات: {{ $topHall->count ?? 0 }}</span>
                <span>إيراد القاعة: {{ number_format($topHall->revenue ?? 0, 2) }} ج.م</span>
            </div>
        </div>
    </div>

</div>

{{-- ======================================================== --}}
{{-- UNIVERSAL INTERACTIVE EXPLAINER MODAL (!) --}}
{{-- ======================================================== --}}
<div class="modal fade" id="explainerModal" tabindex="-1" aria-labelledby="explainerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content explainer-modal-content">
            <div class="modal-header explainer-modal-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="explainer-btn" style="cursor: default; width: 28px; height: 28px; font-size: 14px;">!</span>
                    <h5 class="modal-title" id="explainerModalLabel" style="font-weight: 800; color: #fff; margin: 0;">
                        شرح وتحليل المؤشر
                    </h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                {{-- 1: What it measures --}}
                <div class="explainer-block">
                    <div class="explainer-block-title">
                        <i class="fa-solid fa-magnifying-glass-chart"></i>
                        <span>ما الذي يحلله هذا الرقم بالتحديد؟ (المفهوم الإداري)</span>
                    </div>
                    <p class="explainer-block-text" id="explainerWhat"></p>
                </div>

                {{-- 2: How it was calculated --}}
                <div class="explainer-block">
                    <div class="explainer-block-title">
                        <i class="fa-solid fa-calculator"></i>
                        <span>طريقة الحساب والمعادلة من قاعدة البيانات</span>
                    </div>
                    <p class="explainer-block-text" id="explainerHow"></p>
                    <div class="calc-formula" id="explainerFormula"></div>
                </div>

                {{-- 3: Business impact & decisions --}}
                <div class="explainer-block" style="border-color: rgba(255, 184, 77, 0.25); background: rgba(255, 184, 77, 0.03);">
                    <div class="explainer-block-title" style="color: var(--hive-gold-light);">
                        <i class="fa-solid fa-lightbulb"></i>
                        <span>الدلالة التشغيلية والقرار الإداري (خبير أنظمة الكافيهات 30 سنة)</span>
                    </div>
                    <p class="explainer-block-text" id="explainerWhy"></p>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid rgba(255,255,255,0.08);">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px; font-size: 13px;">إغلاق</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // ========================================================
    // 1️⃣ EXPLANATION DATA DICTIONARY (Every Single Metric Explained!)
    // ========================================================
    const explanations = {
        'revenue': {
            title: 'إجمالي الإيرادات الكلية (Total Gross Inflow)',
            what: 'يقيس هذا المؤشر القيمة المالية الإجمالية لكل التدفقات النقدية والمبيعات التي دخلت المنظومة خلال الفترة الزمنية المحددة، دون خصم أي تكلفة أو مصروف.',
            how: 'يتم احتسابه عبر تجميع مبالغ جميع بنود الفواتير في جدول invoice_items لجميع أنواع الخدمات (ساعات الجلسات + مبيعات المنتجات + اشتراكات الخطط + حجز القاعات ومقدماتها).',
            formula: 'Total Revenue = SUM(invoice_items.total) WHERE created_at BETWEEN from AND to',
            why: 'يعكس الحجم الكلي للتجارة ومستوى الإقبال العام. إذا كان الإيراد مرتفعاً ولكن الأرباح منخفضة، فهذا تنبيه فوري لوجود تضخم في المصاريف أو تكلفة بضاعة مرتفعة جداً بحاجة لضبط التسعير.'
        },
        'net_profit': {
            title: 'صافي الربح الفعلي الحقيقي (True Net Profit)',
            what: 'المؤشر الأهم على الإطلاق؛ يمثل الفائض المالي الصافي المتبقي في الخزينة بعد اقتطاع جميع تكاليف المشتريات المباشرة (COGS) والمصروفات التشغيلية (نثريات، كهرباء، رواتب، إيجار).',
            how: 'إجمالي الإيرادات مطروحاً منه مجموع تكلفة البضاعة المباعة مضافاً إليها جميع حركات المصروفات التشغيلية.',
            formula: 'Net Profit = Total Revenue - (COGS + Total Operating Expenses)',
            why: 'هذا هو الرقم الحقيقي الذي يحدد استدامة المنشأة وقدرتها على سداد التزاماتها وتوزيع أرباح على الملاك، وهو بوصلة اتخاذ قرارات التوسع أو ضغط النفقات.'
        },
        'cogs': {
            title: 'تكلفة البضاعة المباعة (Cost of Goods Sold - COGS)',
            what: 'يقيس التكلفة المباشرة للبضائع والمكونات التي استهلكها العملاء (تكلفة البن، السيرب، الحليب، السناكس المشتراة، العلب الجاهزة).',
            how: 'حاصل ضرب تكلفة كل منتج (cost) في الكمية المباعة منه (qty) لجميع المنتجات المباعة في الفترة.',
            formula: 'COGS = SUM(invoice_items.cost * invoice_items.qty) WHERE item_type = "product"',
            why: 'المعيار الصحي العالمي للكافيهات هو ألا تتجاوز نسبة الـ COGS من 25% إلى 32% من إجمالي مبيعات المنتجات. إذا تجاوزت ذلك، يعني وجود هدر بالبار، أو وصفات غير دقيقة، أو أسعار بيع غير مواكبة لارتفاع أسعار السوق.'
        },
        'expenses': {
            title: 'المصروفات التشغيلية (Operating Overhead Expenses)',
            what: 'مجموع النفقات العامة التي تم صرفها من الخزينة لتشغيل المنشأة ولا تتعلق مباشرة بشراء بضاعة مباعة (مثل: فواتير إنترنت، صيانة، نظافة، نثريات، مكافآت).',
            how: 'مجموع مبالغ القيود المسجلة في جدول expenses خلال الفترة المحددة.',
            formula: 'Total Expenses = SUM(expenses.amount) WHERE created_at BETWEEN from AND to',
            why: 'يساعد الإدارة في مراقبة بنود الإنفاق وتفادي تسرب السيولة النقدية في مصاريف يومية متفرقة غير مبررة.'
        },
        'aov': {
            title: 'متوسط قيمة الفاتورة (Average Order Value - AOV)',
            what: 'متوسط المبلغ الذي يدفعه العميل في كل زيارة أو فاتورة مكتملة.',
            how: 'إجمالي الإيرادات الكلية مقسوماً على عدد الفواتير المصدرة.',
            formula: 'AOV = Total Revenue / Total Invoices Count',
            why: 'رفع هذا الرقم هو أسرع وسيلة لمضاعفة الأرباح دون الحاجة لزيادة عدد الزوار؛ يتم تحسينه بتدريب الويترز والباريستا على البيع الإضافي (Upselling & Cross-selling) كعرض مشروب مميز أو طبق تحلية مع كل جلسة.'
        },
        'total_sessions': {
            title: 'إجمالي الجلسات ومعدل دوران المقاعد (Total Sessions)',
            what: 'عدد الجلسات الفردية والجماعية التي تم استقبالها وتشغيلها في مساحة العمل خلال الفترة.',
            how: 'عد جميع سجلات جدول sations في نطاق وقت البدء start_time المحدد.',
            formula: 'Total Sessions = COUNT(sations.id) WHERE start_time BETWEEN from AND to',
            why: 'يعكس معدل استخدام الطاقة الاستيعابية للكافيه ومساحة العمل؛ يستخدم لحساب نسبة إشغال الطاولات.'
        },
        'total_persons': {
            title: 'إجمالي الأفراد والزوار المستضافين (Total Hosted Guests)',
            what: 'العدد الفعلي للأشخاص المستضافين داخل جميع الجلسات (بما يشمل الجلسات الفردية والجماعية المكونة من عدة أفراد).',
            how: 'مجموع عمود persons في جدول sations.',
            formula: 'Total Persons = SUM(sations.persons) WHERE start_time BETWEEN from AND to',
            why: 'يظهر الحجم الحقيقي لحركة الأفراد (Foot Traffic) ويعد الأساس لحساب نصيب الفرد الواحد من الإيرادات والخدمات.'
        },
        'avg_duration': {
            title: 'متوسط مدة بقاء الجلسة (Average Dwell Time)',
            what: 'متوسط الوقت الزمني الذي يقضيه العميل جالساً داخل المكان من لحظة فتح الجلسة حتى السداد وإغلاقها.',
            how: 'متوسط الفرق بالدقائق بين وقت البدء start_time ووقت النهاية end_time للجلسات المكتملة.',
            formula: 'Avg Duration = AVG(TIMESTAMPDIFF(MINUTE, start_time, end_time))',
            why: 'في مساحات العمل، البقاء الأطول يعني إيراد وقت أكبر؛ ولكن يجب موازنته مع طلب المشروبات لضمان عدم شغل المقعد لفترات طويلة جداً بدون استهلاك إضافي.'
        },
        'attachment_rate': {
            title: 'معدل إرفاق المشروبات بالجلسات (F&B Attachment Rate)',
            what: 'النسبة المئوية للجلسات التي اشترى أصحابها مشروبات أو منتجات أثناء مكوثهم في مساحة العمل.',
            how: 'عدد الجلسات التي تحتوي على طلبات في جدول session_purchases مقسوماً على إجمالي عدد الجلسات مضروباً في 100.',
            formula: 'Attachment Rate = (Sessions With Purchases / Total Sessions) * 100',
            why: 'أهم مقياس لكفاءة قسم الضيافة والبار. إذا كانت النسبة أقل من 60%، فهذا يعني أن المكان يُستغل كمساحة جلوس فقط دون تحريك مبيعات البار، ويستدعي تقديم عروض ترحيبية وتنشيط الويترز.'
        },
        'session_spend': {
            title: 'متوسط إنفاق الجلسة والزائر (Session Yield)',
            what: 'العائد المالي الشامل المحقق من كل جلسة واحدة (يشمل رسوم الوقت + طلبات البار).',
            how: 'مجموع إيراد ساعات الجلسة + مبيعات مشروبات الجلسة مقسوماً على إجمالي الجلسات.',
            formula: 'Session Yield = (Session Time Revenue + Session Beverage Revenue) / Total Sessions',
            why: 'يحدد القيمة الربحية الحقيقية للمقعد (Revenue Per Available Seat Hour) ومقارنة جدوى تأجير الساعات مقابل مبيعات الكافيه.'
        },
        'peak_hours': {
            title: 'توزيع ساعات الذروة (Peak Hours Histogram)',
            what: 'رسم بياني يوضح أوقات تدفق العملاء ودخولهم على مدار الـ 24 ساعة من اليوم.',
            how: 'تجميع الجلسات حسب ساعة بدء الجلسة HOUR(start_time) وحساب التكرار لكل ساعة من 0 إلى 23.',
            formula: 'Peak = SELECT HOUR(start_time) as hr, COUNT(*) GROUP BY hr',
            why: 'يمنع هدر الرواتب وسوء إدارة العمالة؛ يتيح للمدير معرفة أوقات الضغط لجدولة أكبر عدد من الباريستا والخدمة، وتخفيض العمالة في فترات الركود الصباحية أو المتأخرة.'
        },
        'session_revenue_split': {
            title: 'هيكل إيرادات الجلسات (Time vs F&B Split)',
            what: 'المقارنة بين الدخل القادم من رسوم قضاء الوقت (ساعات المقاعد) مقابل الدخل القادم من استهلاك المشروبات والأطعمة.',
            how: 'مقارنة مجموع invoice_items من نوع session مع مجموع مبيعات المنتجات المرتبطة بالجلسات session_id.',
            formula: 'Split = Session Hours Total vs Session Purchases Total',
            why: 'يعكس النموذج الربحي للمكان، هل يميل أكثر لكونه مساحة عمل (Coworking Space) أو كافيه ومقهى ترفيهي.'
        },
        'units_sold': {
            title: 'إجمالي القطع والمشروبات المباعة (Total Volume)',
            what: 'عدد الأكواب والمشروبات والقطع الغذائية التي خرجت من البار للعملاء خلال الفترة.',
            how: 'مجموع عمود الكمية qty لجميع بنود المنتجات في الفواتير.',
            formula: 'Units Sold = SUM(invoice_items.qty) WHERE item_type = "product"',
            why: 'مؤشر الكثافة التشغيلية وسرعة دوران البضاعة داخل البار والمطبخ.'
        },
        'inv_cost': {
            title: 'تقييم المخزون بالتكلفة (Inventory Valuation - Cost)',
            what: 'إجمالي السيولة النقدية المجمدة حالياً على هيئة بضائع ومنتجات جاهزة على الرفوف.',
            how: 'مجموع حاصل ضرب سعر التكلفة cost في الكمية الحالية quantity لجميع المنتجات المسجلة.',
            formula: 'Inv Cost = SUM(products.cost * products.quantity)',
            why: 'حماية رأس المال من التجميد المفرط في بضائع بطيئة الحركة، والحفاظ على دورة كاش صحية.'
        },
        'inv_retail': {
            title: 'القيمة البيعية المتوقعة للمخزون (Inventory Retail Value)',
            what: 'القيمة النقدية المتوقع تحصيلها عند بيع كامل البضائع الموجودة حالياً بالمخزن بسعر المنيو.',
            how: 'مجموع حاصل ضرب سعر البيع price في الكمية الحالية quantity لجميع المنتجات.',
            formula: 'Inv Retail = SUM(products.price * products.quantity)',
            why: 'يوضح حجم الأرباح الكامنة المستقبلية (Potential Gross Margin) المحتجزة في المستودع.'
        },
        'low_stock_products': {
            title: 'مؤشر نواقص المنتجات (Low Stock Alert)',
            what: 'عدد أصناف المنتجات التي وصل رصيدها الفعلي إلى حد الطلب الأدنى أو نفذت تماماً.',
            how: 'عدد المنتجات التي يكون فيها عمود quantity أقل من أو يساوي min_quantity.',
            formula: 'Low Stock = COUNT(products) WHERE quantity <= min_quantity',
            why: 'حماية المبيعات من التوقف؛ نفاذ صنف رئيسي (مثل نوع بن معين أو مشروب مفضل) يؤدي لخسارة فورية للإيرادات واستياء الزبائن.'
        },
        'top_selling_table': {
            title: 'جدول أفضل المنتجات مبيعاً (Top Sellers Analysis)',
            what: 'ترتيب الأصناف التي تشهد أعلى إقبال من الزبائن من حيث عدد الوحدات المباعة وقيمة المبيعات.',
            how: 'تجميع بنود مبيعات المنتجات حسب اسم المنتج وحساب مجموع الكميات والإيرادات وتكاليفها.',
            formula: 'Top Sellers = GROUP BY product_id ORDER BY SUM(qty) DESC LIMIT 8',
            why: 'يساعد في الحفاظ على المنتجات النجمية (Stars) والتأكد من عدم نفاذ خاماتها مطلقاً، واستخدامها في حزم عروض ترويجية.'
        },
        'top_profitable_table': {
            title: 'جدول المنتجات الأعلى ربحاً (Profit Drivers)',
            what: 'قائمة بالأصناف التي تدر أكبر هامش ربح نقدي صافٍ للخزينة، وليس بالضرورة الأكثر مبيعاً.',
            how: 'حساب الفارق بين إجمالي إيراد المنتج وإجمالي تكلفة تحضيره (Revenue - Cost).',
            formula: 'Profit = SUM(total - cost * qty) ORDER BY Profit DESC LIMIT 8',
            why: 'قد يبيع صنف 100 قطعة بربح 5 جنيهات، بينما يبيع صنف آخر 20 قطعة بربح 50 جنيهاً! هذا الجدول يوجه الإدارة لتسليط الضوء على الأصناف الأكثر ربحية وتوجيه الزبائن إليها.'
        },
        'low_stock_products_table': {
            title: 'جدول تنبيه النواقص الحرجة (Stock Replenishment Table)',
            what: 'بيان تفصيلي بأسماء الأصناف التي توشك على النفاذ، رصيدها المتبقي، والكمية التعويضية المقترحة للشراء.',
            how: 'فلترة المنتجات حيث quantity <= min_quantity وترتيبها تصاعدياً حسب الأقل رصيداً.',
            formula: 'Deficit = min_quantity - quantity',
            why: 'يعتبر قائمة مشتريات فورية لمسؤول المشتريات للنزول للسوق والتوريد دون إضاعة الوقت.'
        },
        'total_ingredients': {
            title: 'أصناف الخامات والمواد الأولية (Raw Ingredients Master)',
            what: 'إجمالي المواد الخام الأساسية المستخدمة في وصفات التصنيع وإعداد المشروبات (بن، سيرب، صوصات، حليب، أكواب، شاليموه).',
            how: 'إجمالي عدد السجلات في جدول ingredients.',
            formula: 'Total Ingredients = COUNT(ingredients.id)',
            why: 'متابعة شجرة المواد الأولية والتأكد من تغطية جميع مكونات المنيو بوصفات قياسية.'
        },
        'alert_ingredients': {
            title: 'خامات دون حد الأمان (Critical Raw Materials)',
            what: 'المواد الخام التي شارف رصيدها في البار على النفاذ وتجاوزت حد التنبيه المحدد لها.',
            how: 'فلترة الخامات حيث stock <= alert_stock في جدول ingredients.',
            formula: 'Alert = COUNT(ingredients) WHERE stock <= alert_stock',
            why: 'نفاذ خامة واحدة قد يوقف قائمة كاملة من المشروبات (مثل نفاذ الحليب يوقف اللاتيه والكابتشينو والفلات وايت). هذا التنبيه يمنع الكوارث التشغيلية في ساعات الذروة.'
        },
        'outflow_movements': {
            title: 'حركات صرف واستهلاك الخامات (Material Outflow Tracking)',
            what: 'عدد عمليات الخصم التلقائي واليدوي التي تمت على مخزون الخامات بالمستودع والبار.',
            how: 'حساب عدد السجلات التي نوعها out في جدول ingredient_stocks خلال الفترة.',
            formula: 'Outflow = COUNT(ingredient_stocks.id) WHERE type = "out"',
            why: 'يؤكد أن نظام الخصم الآلي للوصفات يعمل بدقة وكفاءة مع كل فنجان أو مشروب يتم إعداده.'
        },
        'top_consumed_ingredients': {
            title: 'أكثر الخامات استهلاكاً ونفاذاً (Top Consumed Raw Materials)',
            what: 'ترتيب المواد الخام الأكثر سحباً بالكمية ووحدات القياس (جرام، ملي، قطعة) خلال فترة العمل.',
            how: 'تجميع حركات الصرف من جدول ingredient_stocks حسب الخامة وحساب مجموع amount.',
            formula: 'Consumed = SUM(amount) WHERE type = "out" GROUP BY ingredient_id ORDER BY Consumed DESC',
            why: 'يحدد للمدير المواد ذات الحجم الأكبر في الشراء للتفاوض مع الموردين على خصومات كمية والحصول على أسعار جملة أفضل.'
        },
        'all_ingredients_health': {
            title: 'الحالة الشاملة لجرد المستودع (Live Ingredient Inventory)',
            what: 'كشف جرد حي يوضح الرصيد المتوفر لكل خامة مع تصنيفها اللوني الفوري: آمن (أخضر)، حرج (أصفر)، نفذ (أحمر).',
            how: 'مقارنة stock الفعلي مع alert_stock لكل خامة في قاعدة البيانات.',
            formula: 'Health = Status evaluation based on stock vs alert_stock',
            why: 'يمكّن مدير الفرع من مراجعة الجرد الفعلي على الأرض ومطابقته مع رصيد السيستم لكشف أي عجز أو هدر أو سرقة فوراً.'
        },
        'active_subs': {
            title: 'الاشتراكات النشطة وأعضاء المجتمع (Community Memberships)',
            what: 'عدد باقات الاشتراكات السارية حالياً التي يحق لأصحابها الدخول واستخدام مساحة العمل.',
            how: 'حساب عدد الاشتراكات في جدول subscriptions حيث is_active = true.',
            formula: 'Active Subs = COUNT(subscriptions) WHERE is_active = true',
            why: 'الاشتراكات هي الدخل الدوري المضمون (Recurring Revenue)؛ زيادتها تقلل من اعتماد المنشأة على الزيارات العشوائية وتوفر سيولة ثابتة كل شهر.'
        },
        'bookings_kpi': {
            title: 'حجوزات القاعات والفعاليات (Event Halls Utilization)',
            what: 'عدد حجوزات قاعات الاجتماعات والتدريب وورش العمل المنفذة خلال الفترة وإجمالي الحضور.',
            how: 'عد السجلات في جدول bookings ومجموع الحضور attendees ومجموع الإيرادات.',
            formula: 'Bookings = COUNT(bookings) AND SUM(attendees)',
            why: 'القاعات تحقق أعلى عائد على المتر المربع في المنشأة؛ استغلالها الأمثل يرفع الدخل بشكل مضاعف مقارنة بالجلسات الفردية.'
        },
        'top_hall': {
            title: 'القاعة الأكثر طلباً وإشغالاً (Top Performing Room)',
            what: 'القاعة أو المساحة الخاصة التي حققت أعلى تكرار حجز وأعلى دخل مالي.',
            how: 'تجميع الحجوزات حسب hall_id وحساب التكرار ومجموع الإيراد واختيار الأعلى.',
            formula: 'Top Hall = GROUP BY hall_id ORDER BY COUNT(*) DESC LIMIT 1',
            why: 'يوضح نوعية الفعاليات المطلوبة في منطقتك (اجتماعات صغيرة أم ورش عمل كبيرة) ويساعد في تطوير تجهيزات القاعات الأكثر عائداً.'
        },
        'timeline_chart': {
            title: 'الرسم البياني لتطور الإيرادات والأرباح اليومية (Revenue & Profit Trend)',
            what: 'منحنى بياني زمني يوضح حركة صعود وهبوط المبيعات وصافي الأرباح يوماً بيوم طوال الفترة.',
            how: 'تجميع إيرادات الفواتير وتكاليفها ومصروفاتها لكل تاريخ يومي على حدة ورسم المنحنيين معاً.',
            formula: 'Daily Trend = For each date: Revenue vs Profit',
            why: 'يكشف التذبذبات الدورية (مثل انتعاش المبيعات في عطلة نهاية الأسبوع أو بداية الشهر) ويتيح قياس تأثير العروض الترويجية في أيام محددة.'
        },
        'revenue_sources_chart': {
            title: 'الرسم البياني لتوزيع مصادر الدخل (Revenue Streams Breakdown)',
            what: 'مخطط دائري يوضح الحصة النسبية لكل قطاع في المنشأة (جلسات، منتجات بار، اشتراكات باقات، قاعات).',
            how: 'نسبة مساهمة كل item_type من إجمالي إيرادات invoice_items.',
            formula: 'Percentage = (Service Revenue / Total Revenue) * 100',
            why: 'يضمن عدم الاعتماد على مصدر دخل أحادي وتوزيع المخاطر التشغيلية عبر تنويع الخدمات.'
        },
        'col_qty': {
            title: 'عمود الكمية المباعة (Units Sold Column)',
            what: 'عدد المرات التي تم بيع هذا الصنف فيها خلال الفترة المحددة.',
            how: 'مجموع عمود qty لبنود هذا المنتج في الفواتير.',
            formula: 'SUM(invoice_items.qty) for this product',
            why: 'يحدد درجة شعبية الصنف في المنيو.'
        },
        'col_rev': {
            title: 'عمود الإيراد الكلي للصنف (Item Revenue Column)',
            what: 'مجموع المبالغ المالية التي جناها هذا الصنف من العملاء قبل خصم تكلفته.',
            how: 'مجموع عمود total لهذا المنتج.',
            formula: 'SUM(invoice_items.total) for this product',
            why: 'يعكس حصة الصنف من التدفق النقدي.'
        },
        'col_profit': {
            title: 'عمود صافي الربح للصنف (Item Net Profit Column)',
            what: 'المكسب المالي الحقيقي الذي أضافه الصنف للخزينة بعد طرح تكلفة إنتاجه/شرائه.',
            how: 'إجمالي إيراد الصنف - (سعر التكلفة × الكمية المباعة).',
            formula: 'SUM(total - cost * qty)',
            why: 'العبرة بالربح وليس برقم المبيعات فقط!'
        },
        'col_margin': {
            title: 'هامش ربح المنتج (Product Profit Margin)',
            what: 'النسبة المئوية لربحية الصنف مقارنة بسعر بيعه.',
            how: '(صافي ربح الصنف ÷ إجمالي إيراده) × 100.',
            formula: '(Profit / Revenue) * 100',
            why: 'يوضح كفاءة تسعير الصنف، وإذا كان من الأصناف ذات الهامش المرتفع التي تستحق الترويج المكثف.'
        },
        'col_consumed_qty': {
            title: 'إجمالي الكمية المصروفة من الخامة (Consumed Raw Material Qty)',
            what: 'المقدار الإجمالي المستهلك من الخامة بوحدة القياس المعيارية (جرام، لتر، إلخ).',
            how: 'مجموع كميات الصرف المسجلة في جدول ingredient_stocks بنوع out.',
            formula: 'SUM(ingredient_stocks.amount) WHERE type = "out"',
            why: 'مقارنته مع المبيعات الفعلية يكشف أي تباين في نسب التحضير أو هدر الباريستا.'
        }
    };

    // Modal Trigger Function
    function openExplainer(key) {
        const item = explanations[key];
        if (!item) return;

        document.getElementById('explainerModalLabel').innerText = item.title;
        document.getElementById('explainerWhat').innerText = item.what;
        document.getElementById('explainerHow').innerText = item.how;
        document.getElementById('explainerFormula').innerText = item.formula;
        document.getElementById('explainerWhy').innerText = item.why;

        const modalEl = document.getElementById('explainerModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    // ========================================================
    // 2️⃣ CHART.JS INITIALIZATIONS
    // ========================================================
    document.addEventListener('DOMContentLoaded', function () {
        // Timeline Chart (Revenue & Profit)
        const timelineLabels = @json($timelineLabels);
        const timelineRevenue = @json($timelineRevenue);
        const timelineProfit = @json($timelineProfit);

        const ctxTimeline = document.getElementById('timelineChart').getContext('2d');
        new Chart(ctxTimeline, {
            type: 'line',
            data: {
                labels: timelineLabels,
                datasets: [
                    {
                        label: 'الإيرادات (ج.م)',
                        data: timelineRevenue,
                        borderColor: '#ffb84d',
                        backgroundColor: 'rgba(255, 184, 77, 0.12)',
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        pointBackgroundColor: '#ffb84d'
                    },
                    {
                        label: 'صافي الأرباح (ج.م)',
                        data: timelineProfit,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.08)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 2.5,
                        pointBackgroundColor: '#10b981'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { color: '#e2e8f0', font: { family: 'Cairo' } }
                    },
                    tooltip: {
                        rtl: true,
                        bodyFont: { family: 'Cairo' }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#9e9ea7', font: { family: 'Cairo' } }
                    },
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#9e9ea7', font: { family: 'Cairo' } }
                    }
                }
            }
        });

        // Revenue Sources Doughnut Chart
        const sessionHoursRev = {{ $sessionHoursRevenue }};
        const productRev = {{ $productRevenue }};
        const subscriptionRev = {{ $subscriptionRevenue }};
        const bookingRev = {{ $bookingRevenue }};

        const ctxSources = document.getElementById('revenueSourcesChart').getContext('2d');
        new Chart(ctxSources, {
            type: 'doughnut',
            data: {
                labels: ['الجلسات (الوقت)', 'المنتجات والبار', 'الاشتراكات', 'حجز القاعات'],
                datasets: [{
                    data: [sessionHoursRev, productRev, subscriptionRev, bookingRev],
                    backgroundColor: ['#ffb84d', '#38bdf8', '#c084fc', '#10b981'],
                    borderColor: '#16161a',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#e2e8f0', font: { family: 'Cairo', size: 12 } }
                    },
                    tooltip: {
                        rtl: true,
                        callbacks: {
                            label: function(ctx) {
                                const val = ctx.raw || 0;
                                return ' ' + ctx.label + ': ' + Number(val).toLocaleString() + ' ج.م';
                            }
                        }
                    }
                },
                cutout: '65%'
            }
        });

        // Peak Hours Bar Chart (24 Hours)
        const peakData = @json(array_values($peakHoursData));
        const hourLabels = [];
        for (let i = 0; i < 24; i++) {
            const h = i < 10 ? '0' + i : '' + i;
            hourLabels.push(h + ':00');
        }

        const ctxPeak = document.getElementById('peakHoursChart').getContext('2d');
        new Chart(ctxPeak, {
            type: 'bar',
            data: {
                labels: hourLabels,
                datasets: [{
                    label: 'عدد الجلسات التي بدأت',
                    data: peakData,
                    backgroundColor: 'rgba(255, 184, 77, 0.4)',
                    borderColor: '#ffb84d',
                    borderWidth: 1.5,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        rtl: true,
                        callbacks: {
                            label: function(ctx) {
                                return ' بدأ ' + ctx.raw + ' جلسة في هذه الساعة';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#9e9ea7', font: { family: 'Cairo', size: 11 } }
                    },
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#9e9ea7', stepSize: 1, precision: 0 }
                    }
                }
            }
        });

        // Session Revenue Split Chart (Time vs Beverage)
        const sessionTimeRev = {{ $sessionHoursRevenue }};
        const sessionDrinkRev = {{ $sessionBeverageRevenue }};

        const ctxSplit = document.getElementById('sessionSplitChart').getContext('2d');
        new Chart(ctxSplit, {
            type: 'doughnut',
            data: {
                labels: ['رسوم قضاء الوقت (الساعات)', 'طلبات المشروبات داخل الجلسة'],
                datasets: [{
                    data: [sessionTimeRev, sessionDrinkRev],
                    backgroundColor: ['#ffb84d', '#10b981'],
                    borderColor: '#16161a',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#e2e8f0', font: { family: 'Cairo', size: 12 } }
                    },
                    tooltip: {
                        rtl: true,
                        callbacks: {
                            label: function(ctx) {
                                const val = ctx.raw || 0;
                                return ' ' + ctx.label + ': ' + Number(val).toLocaleString() + ' ج.م';
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });
    });
</script>
@endsection
