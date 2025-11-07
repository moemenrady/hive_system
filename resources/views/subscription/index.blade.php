@extends('layouts.app')
@section('title', 'إدارة الاشتراكات')

<style>
    /* 🌑 الستايل الداكن بتقسيمة الصفحة الفاتحة */
    body {
        font-family: "Tahoma", sans-serif;
        background: #1c1c1f;
        color: #f5f5f5;
        margin: 0;
        padding: 0;
    }

    /* زر الإضافة */
    #addButton {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        background: #ffb84d;
        font-size: 48px;
        font-weight: bold;
        color: #1a1a1a;
        box-shadow: 0 4px 12px rgba(0, 0, 0, .3);
        transition: 0.3s;
        margin: 20px auto;
    }

    #addButton:hover {
        background: #ffd97a;
        transform: scale(1.05);
    }

    /* الحاوية العامة */
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 30px 20px;
    }

    /* مربع البحث */
    .search-box {
        margin: 20px auto;
        text-align: center;
    }

    .search-box input {
        padding: 14px 20px;
        width: 450px;
        max-width: 100%;
        border-radius: 25px;
        border: 1px solid #555;
        background: #1c1c1f;
        color: #f5f5f5;
        font-size: 15px;
        outline: none;
        transition: 0.3s;
    }

    .search-box input:focus {
        border-color: #ffb84d;
        box-shadow: 0 0 8px rgba(255, 184, 77, 0.25);
    }

    /* الفلاتر */
    .filters-box {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 25px;
        align-items: flex-start;
    }

    .filters-box label.form-label {
        color: #f5f5f5;
        font-weight: 600;
    }

    .form-check-lg .form-check-input {
        width: 1.4em;
        height: 1.4em;
        margin-top: .1em;
    }

    .form-check-lg .form-check-label {
        font-size: 1em;
        margin-right: .4em;
        color: #f5f5f5;
    }

    /* الجدول */
    table {
        width: 100%;
        border-collapse: collapse;
        border-radius: 12px;
        overflow: hidden;
        margin-top: 20px;
        background: #1a1a1d;
    }

    thead {
        background: #333;
    }

    thead th {
        padding: 16px 20px;
        text-align: center;
        font-size: 15px;
        font-weight: bold;
        color: #ffd97a;
    }

    tbody tr {
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        text-align: center;
        transition: background 0.2s;
    }

    tbody tr:hover {
        background: rgba(255, 184, 77, 0.08);
        cursor: pointer;
    }

    tbody td {
        padding: 14px 18px;
        font-size: 15px;
        color: #f5f5f5;
    }

    /* badge */
    .badge {
        padding: 6px 10px;
        border-radius: 10px;
        display: inline-block;
        font-size: 13px;
        font-weight: 600;
    }

    .badge.bg-success {
        background: rgba(0, 184, 123, .15);
        color: #0bd97f;
    }

    .badge.bg-secondary {
        background: rgba(255, 255, 255, .15);
        color: #f5f5f5;
    }

    /* موبايل */
    @media (max-width: 768px) {
        .filters-box {
            flex-direction: column;
            align-items: flex-start;
        }

        .search-box input {
            width: 100%;
        }

        table,
        thead,
        tbody,
        th,
        td,
        tr {
            display: block;
            width: 100%;
        }

        thead {
            display: none;
        }

        tbody tr {
            margin-bottom: 15px;
            border: 1px solid #333;
            border-radius: 10px;
            padding: 10px;
            background: #1c1c1f;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.3);
        }

        tbody td {
            text-align: right;
            padding: 8px 10px;
            position: relative;
            font-size: 14px;
        }

        tbody td::before {
            content: attr(data-label);
            position: absolute;
            left: 10px;
            font-weight: 600;
            color: #bbb;
        }
    }
</style>

@section('content')
    <div class="container">
        {{-- زرار الإضافة --}}
        <a href="{{ route('subscriptions.create') }}" id="addButton">+</a>

        {{-- البحث --}}
        <div class="search-box">
            <input type="text" id="searchBox" placeholder="🔍 بحث (اسم العميل / الهاتف / ID)">
        </div>

        {{-- الفلاتر --}}
        <div class="filters-box">
            {{-- الحالة --}}
            <div>
                <label class="form-label d-block mb-2">⚡ الحالة</label>
                <div class="d-flex gap-3">
                    <div class="form-check form-check-lg">
                        <input class="form-check-input status-filter" type="checkbox" value="1" id="statusActive">
                        <label class="form-check-label" for="statusActive">فعال</label>
                    </div>
                    <div class="form-check form-check-lg">
                        <input class="form-check-input status-filter" type="checkbox" value="0" id="statusEnded">
                        <label class="form-check-label" for="statusEnded">منتهي</label>
                    </div>
                </div>
            </div>

            {{-- الخطط --}}
            <div>
                <label class="form-label d-block mb-2">📦 الخطط</label>
                <div class="d-flex flex-wrap gap-3">
                    @foreach ($plans as $plan)
                        <div class="form-check form-check-lg">
                            <input class="form-check-input plan-filter" type="checkbox" value="{{ $plan->id }}"
                                id="plan{{ $plan->id }}">
                            <label class="form-check-label" for="plan{{ $plan->id }}">{{ $plan->name }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- الجدول --}}
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>العميل</th>
                    <th>الهاتف</th>
                    <th>الخطة</th>
                    <th>من</th>
                    <th>إلى</th>
                    <th>المتبقي</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody id="subsTable">
                <tr>
                    <td colspan="9" class="text-center p-3">⏳ جاري التحميل...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let searchBox = document.getElementById("searchBox");
            let statusFilters = document.querySelectorAll(".status-filter");
            let planFilters = document.querySelectorAll(".plan-filter");
            let tableBody = document.getElementById("subsTable");
            let showRoute = @json(route('subscriptions.show', ['id' => ':id']));

            function formatDateTime(dateStr) {
                let d = new Date(dateStr);
                let options = {
                    year: "numeric",
                    month: "short",
                    day: "2-digit"
                };
                return d.toLocaleString("en-US", options);
            }

            function fetchSubs() {
                let q = searchBox.value;
                let statuses = Array.from(statusFilters).filter(c => c.checked).map(c => c.value);
                let plans = Array.from(planFilters).filter(c => c.checked).map(c => c.value);

                let params = new URLSearchParams({
                    q
                });
                statuses.forEach(s => params.append("statuses[]", s));
                plans.forEach(p => params.append("plans[]", p));

                fetch("{{ route('subscriptions.ajaxSearch') }}?" + params.toString())
                    .then(res => res.json())
                    .then(data => {
                        tableBody.innerHTML = "";
                        if (data.length === 0) {
                            tableBody.innerHTML =
                                `<tr><td colspan="9" class="text-center p-3">❌ لا توجد نتائج</td></tr>`;
                        } else {
                            data.forEach(s => {
                                tableBody.innerHTML += `
                        <tr class="subscription-row" data-id="${s.id}" style="cursor: pointer;">
                            <td>${s.id}</td>
                            <td>${s.client_name}</td>
                            <td>${s.client_phone}</td>
                            <td>${s.plan_name}</td>
                            <td>${formatDateTime(s.start_date) ?? '-'}</td>
                            <td>${formatDateTime(s.end_date) ?? '-'}</td>
                            <td>${s.remaining_visits}</td>
                            <td>
                                ${s.is_active === "فعال"
                                    ? `<span class="badge bg-success">فعال</span>`
                                    : `<span class="badge bg-secondary">منتهي</span>`}
                            </td>
                        </tr>`;
                            });
                            attachRowClick();
                        }
                    });
            }

            function attachRowClick() {
                document.querySelectorAll(".subscription-row").forEach(row => {
                    row.addEventListener("click", function() {
                        let id = this.dataset.id;
                        window.location.href = showRoute.replace(':id', id);
                    });
                });
            }

            searchBox.addEventListener("keyup", fetchSubs);
            statusFilters.forEach(cb => cb.addEventListener("change", fetchSubs));
            planFilters.forEach(cb => cb.addEventListener("change", fetchSubs));

            fetchSubs();
        });
    </script>
@endsection
