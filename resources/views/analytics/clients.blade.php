@extends('layouts.analytics')

@section('title', 'تحليل العملاء')
@section('subtitle', 'سلوك – تفاعل – زيارات')

{{-- ================== STATS ================== --}}
@section('stats')
<div class="stats-grid">

    <div class="card">
        <div class="label">إجمالي العملاء</div>
        <div class="num">{{ $totalClients }}</div>
    </div>

    <div class="card">
        <div class="label">عملاء جدد اليوم</div>
        <div class="num">{{ $clientsLastDay }}</div>
    </div>

    <div class="card">
        <div class="label">آخر أسبوع</div>
        <div class="num">{{ $clientsLastWeek }}</div>
    </div>

    <div class="card">
        <div class="label">آخر شهر</div>
        <div class="num">{{ $clientsLastMonth }}</div>
    </div>

    <div class="card">
        <div class="label">إجمالي الزيارات</div>
        <div class="num">{{ $totalVisits }}</div>
    </div>

    <div class="card">
        <div class="label">متوسط زيارات / عميل</div>
        <div class="num">{{ $avgVisitsPerClient }}</div>
    </div>

</div>
@endsection

{{-- ================== CONTENT ================== --}}
@section('content')

{{-- ===== Date Filter ===== --}}
<div class="glass-box date-filter mb-4">

    <div class="filter-header">
        <h5>تصفية حسب التاريخ</h5>
    </div>

    <div class="filter-body" style="display:block">

        {{-- Quick Filters --}}
        <div class="quick-filters">
            <button onclick="applyQuick('today')"     class="{{ request('from')==now()->toDateString() && request('to')==now()->toDateString() ? 'active-btn':'' }}">اليوم</button>
            <button onclick="applyQuick('yesterday')" class="{{ request('from')==now()->subDay()->toDateString() ? 'active-btn':'' }}">أمس</button>
            <button onclick="applyQuick('7')"         class="{{ request('from')==now()->subDays(7)->toDateString() ? 'active-btn':'' }}">آخر 7 أيام</button>
            <button onclick="applyQuick('30')"        class="{{ request('from')==now()->subDays(30)->toDateString() ? 'active-btn':'' }}">آخر 30 يوم</button>
            <button onclick="applyQuick('this_month')"class="{{ request('from')==now()->startOfMonth()->toDateString() ? 'active-btn':'' }}">هذا الشهر</button>
            <button onclick="applyQuick('last_month')"class="{{ request('from')==now()->subMonth()->startOfMonth()->toDateString() ? 'active-btn':'' }}">الشهر السابق</button>
        </div>

        {{-- Range --}}
        <div class="range-box mt-3">
            <label>اختر الفترة</label>

            <div class="date-display" onclick="openCalendar()">
                <div class="date-icon">📅</div>
                <div class="date-text" id="dateText">
                    {{ $from }} — {{ $to }}
                </div>
                <div class="pulse"></div>
            </div>

            <input type="text" id="dateRange" class="flatpickr-hidden"
                value="{{ $from }} to {{ $to }}">
        </div>
    </div>
</div>

{{-- ===== KPI Period ===== --}}
<div class="stats-grid mb-4">
    <div class="card highlight">
        <div class="label">عدد العملاء في الفترة</div>
        <div class="num">{{ $clientsInRange }}</div>
        <div class="muted">من {{ $from }} إلى {{ $to }}</div>
    </div>
</div>
{{-- ===== Top Clients ===== --}}
<div class="content-row">
    <div class="card top-clients-card">
        <div class="card-title">🏆 أعلى العملاء زيارة</div>

        <ul class="top-clients-list">
            @foreach($topClients as $index => $c)
                <li class="top-client-item rank-{{ $index + 1 }}">
                    <div class="rank-badge">
                        {{ $index + 1 }}
                    </div>

                    <div class="client-info">
                        <div class="client-name">
                            {{ $c->name }}
                        </div>
                        <div class="client-visits">
                            {{ $c->invoices_count }} زيارة
                        </div>
                    </div>

                    <div class="glow"></div>
                </li>
            @endforeach
        </ul>
    </div>
</div>

{{-- ===== Charts ===== --}}
<div class="content-row">
    <div class="card">
        <div style="font-weight:700">توزيع الأعمار</div>
        <canvas id="ageChart"></canvas>
    </div>

    <div class="card">
        <div style="font-weight:700">المرحلة التعليمية</div>
        <canvas id="educationChart"></canvas>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
function formatDate(d){
    return d.toISOString().split('T')[0];
}

function sendFilter(from,to){
    window.location = `{{ route('analytics.clients') }}?from=${from}&to=${to}`;
}

function applyQuick(type){
    let from = new Date(), to = new Date();

    switch(type){
        case 'today': break;
        case 'yesterday':
            from.setDate(from.getDate()-1);
            to = new Date(from);
            break;
        case '7':
            from.setDate(from.getDate()-7);
            break;
        case '30':
            from.setDate(from.getDate()-30);
            break;
        case 'this_month':
            from = new Date(to.getFullYear(), to.getMonth(), 1);
            break;
        case 'last_month':
            from = new Date(to.getFullYear(), to.getMonth()-1, 1);
            to   = new Date(to.getFullYear(), to.getMonth(), 0);
            break;
    }
    sendFilter(formatDate(from), formatDate(to));
}

flatpickr("#dateRange",{
    mode:"range",
    dateFormat:"Y-m-d",
    onChange(d){
        if(d.length===2){
            sendFilter(formatDate(d[0]),formatDate(d[1]));
        }
    }
});

function openCalendar(){
    document.getElementById("dateRange")._flatpickr.open();
}
</script>

<script>
new Chart(document.getElementById('ageChart'),{
    type:'doughnut',
    data:{
        labels:['<18','18-25','25-35','35+'],
        datasets:[{ data:@json(array_values($ageStats)) }]
    }
});
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
        /* ===== Top Clients Leaderboard ===== */
.top-clients-card {
    position: relative;
    overflow: hidden;
}

.card-title {
    font-weight: 800;
    margin-bottom: 12px;
    font-size: 15px;
    background: linear-gradient(90deg, #F8E0C1, #D9B1AB);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.top-clients-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.top-client-item {
    position: relative;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    border-radius: 14px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.06);
    overflow: hidden;
    transition: all .3s ease;
}

.top-client-item:hover {
    transform: translateY(-2px) scale(1.01);
}

/* الرقم */
.rank-badge {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: grid;
    place-items: center;
    font-weight: 900;
    font-size: 14px;
    color: #000;
    background: linear-gradient(135deg, #F8E0C1, #D9B1AB);
    box-shadow: 0 4px 12px rgba(217,177,171,0.35);
}

/* اسم العميل */
.client-info {
    flex: 1;
}

.client-name {
    font-weight: 700;
    font-size: 14px;
}

.client-visits {
    font-size: 12px;
    color: var(--muted);
}

/* توهّج */
.top-client-item .glow {
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 30% 50%, rgba(248,224,193,0.18), transparent 60%);
    opacity: 0;
    transition: opacity .3s ease;
    pointer-events: none;
}

.top-client-item:hover .glow {
    opacity: 1;
}

/* ===== تمييز حسب الترتيب ===== */
.rank-1 {
    background: linear-gradient(135deg, rgba(248,224,193,0.18), rgba(217,177,171,0.08));
    border-color: rgba(248,224,193,0.4);
    box-shadow: 0 0 25px rgba(248,224,193,0.25);
}

.rank-2 {
    background: linear-gradient(135deg, rgba(217,177,171,0.18), rgba(255,255,255,0.04));
    box-shadow: 0 0 18px rgba(217,177,171,0.18);
}

.rank-3 {
    background: rgba(255,255,255,0.05);
}

/* الباقي */
.rank-4,
.rank-5 {
    opacity: 0.85;
}

    </style>


@endsection
