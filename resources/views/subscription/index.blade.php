@extends('layouts.app')
@section('title', 'إدارة الاشتراكات')

<style>
  /* body */
body {
    font-family: "Tahoma", sans-serif;
    background: #1c1c1f; /* خلفية داكنة زي صفحة الحجوزات */
    margin: 0;
    padding: 0;
    color: #f5f5f5;
}

/* زر الإضافة مع اللمعة */
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
    font-weight: 600;
    color: #1a1a1a;
    box-shadow: 0 6px 18px rgba(0, 0, 0, .3);
    transition: transform .28s ease, box-shadow .28s ease, background .28s;
    margin: 20px auto;
    position: relative;
    overflow: hidden;
}

#addButton::before {
    content: "";
    position: absolute;
    inset: -120% -30%;
    background: linear-gradient(120deg, transparent 35%, rgba(255,255,255,.35) 50%, transparent 65%);
    transform: translateX(-100%);
    transition: transform .6s ease;
    pointer-events: none;
}

#addButton:hover {
    transform: translateY(-4px) scale(1.03);
    background: #ffd97a;
    box-shadow: 0 14px 30px rgba(0,0,0,.45);
}

#addButton:hover::before {
    transform: translateX(100%);
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
    font-size: 15px;
    outline: none;
    background: #1c1c1f;
    color: #f5f5f5;
    transition: box-shadow .22s ease, border-color .22s;
}

.search-box input:focus {
    border-color: #ffb84d;
    box-shadow: 0 0 8px rgba(255,184,77,0.28);
}

/* الفلاتر */
.filters-box {
    display: flex;
    justify-content: flex-end;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 18px;
    align-items: center;
}

.filters-box label.form-label {
    color: #ffffff !important;
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
    margin-top: 12px;
    background: #1a1a1d;
    color: #f5f5f5;
}

thead {
    background: #333;
}

thead th {
    padding: 14px 12px;
    text-align: center;
    color: #ffd97a;
    font-size: 15px;
}

tbody tr {
    border-bottom: 1px solid rgba(255,255,255,0.05);
    text-align: center;
    transition: background .18s ease, transform .18s ease;
    background: #1c1c1f;
}

tbody td {
    padding: 12px 10px;
    text-align: center;
    color: #f5f5f5;
}

tbody tr:hover {
    transform: translateY(-3px);
    background: rgba(255,184,77,0.08);
    box-shadow: 0 6px 18px rgba(0,0,0,0.15);
    cursor: pointer;
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
    background: rgba(0,184,123,.15);
    color: #0bd97f;
}

.badge.bg-secondary {
    background: rgba(255,255,255,.15);
    color: #f5f5f5;
}

/* موبايل */
@media (max-width:768px) {
    table, thead, tbody, th, td, tr {
        display: block;
        width: 100%;
    }

    thead { display: none; }

    tbody tr {
        margin-bottom: 15px;
        border: 1px solid #333;
        border-radius: 10px;
        padding: 12px;
        background: #1c1c1f;
        box-shadow: 0 6px 18px rgba(0,0,0,0.3);
        transform: none;
        animation: cardIn .28s ease both;
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
        left: auto;
        color: #aaa;
        font-weight: 600;
    }
}

@keyframes cardIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
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
            // عناصر البحث والتصفية
            let searchBox = document.getElementById("searchBox");
            let statusFilters = document.querySelectorAll(".status-filter");
            let planFilters = document.querySelectorAll(".plan-filter");
            let tableBody = document.getElementById("subsTable");

            // المودال
            let renewModal = new bootstrap.Modal(document.getElementById("renewModal"));
            let renewForm = document.getElementById("renewForm");
            let renewClient = document.getElementById("renewClient");
            let renewPlan = document.getElementById("renewPlan");
            let renewRoute = "{{ route('subscriptions.renew', ['subscription' => ':id']) }}";

            // الراوت الخاص بالعرض
            let showRoute = @json(route('subscriptions.show', ['id' => ':id']));

            // فورمات التاريخ
            function formatDateTime(dateStr) {
                let d = new Date(dateStr);
                let options = {
                    year: "numeric",
                    month: "short",
                    day: "2-digit",
                };
                return d.toLocaleString("en-US", options);
            }

            // الفetch
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

            // ربط الكلاينت show
            function attachRowClick() {
                document.querySelectorAll(".subscription-row").forEach(row => {
                    row.addEventListener("click", function() {
                        let id = this.dataset.id;
                        window.location.href = showRoute.replace(':id', id);
                    });
                });
            }

            // تشغيل البحث أول مرة
            searchBox.addEventListener("keyup", fetchSubs);
            statusFilters.forEach(cb => cb.addEventListener("change", fetchSubs));
            planFilters.forEach(cb => cb.addEventListener("change", fetchSubs));

            fetchSubs();
        });
    </script>


    <!-- ✅ Modal تجديد الاشتراك -->
    <div class="modal fade animate__animated animate__fadeInDown" id="renewModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 shadow">

                <!-- الهيدر -->
                <div class="modal-header bg-success text-white rounded-top-4">
                    <h5 class="modal-title">🔄 تجديد الاشتراك</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- الفورم -->
                <form id="renewForm" method="POST" class="p-4">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">👤 العميل</label>
                            <input type="text" id="renewClient" class="form-control form-control-lg" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">📦 الخطة الحالية</label>
                            <input type="text" id="renewPlan" class="form-control form-control-lg" readonly>
                        </div>

                        <div class="col-12">
                            <label for="plan_id" class="form-label">🔄 اختيار خطة أخرى (اختياري)</label>
                            <select name="plan_id" id="plan_id" class="form-select form-select-lg">
                                <option value="">-- نفس الخطة --</option>
                                @foreach ($plans as $plan)
                                    <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- الأزرار -->
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-success btn-lg px-5 fw-bold">✅ إتمام التجديد</button>
                        <button type="button" class="btn btn-outline-secondary btn-lg px-4 ms-2"
                            data-bs-dismiss="modal">إلغاء</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@endsection
