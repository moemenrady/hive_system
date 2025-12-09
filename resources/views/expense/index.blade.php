@extends('layouts.app')

@section('page_title', 'قائمة الحجوزات')

@section('content')
    <style>
        /* زر الإضافة (لمعة) */
        #addButton {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            background: #ffcb9a;
            font-size: 48px;
            font-weight: 600;
            color: #000;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .12);
            transition: transform .28s ease, box-shadow .28s ease, background .28s;
            margin: 20px auto;
            position: relative;
            overflow: hidden;
        }

        #addButton::before {
            content: "";
            position: absolute;
            inset: -120% -30%;
            background: linear-gradient(120deg, transparent 35%, rgba(255, 255, 255, .65) 50%, transparent 65%);
            transform: translateX(-100%);
            transition: transform .6s ease;
            pointer-events: none;
        }

        #addButton:hover {
            transform: translateY(-4px) scale(1.03);
            background: #ffa94d;
            box-shadow: 0 14px 30px rgba(0, 0, 0, .18);
        }

        #addButton:hover::before {
            transform: translateX(100%);
        }

        /* البحث */
        .search-box {
            margin: 20px auto;
            text-align: center;
        }

        .search-box input {
            padding: 14px 20px;
            width: 450px;
            max-width: 100%;
            border-radius: 25px;
            border: 1px solid #ddd;
            font-size: 15px;
            outline: none;
            background: #fff;
            transition: box-shadow .22s ease, border-color .22s;
        }

        .search-box input:focus {
            border-color: #ffcb9a;
            box-shadow: 0 0 8px rgba(255, 170, 80, 0.28);
        }

        /* filters */
        .filters-box {
            display: flex;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 18px;
            align-items: center;
        }

        /* checkboxes */
        .form-check-lg .form-check-input {
            width: 1.4em;
            height: 1.4em;
            margin-top: .1em;
        }

        .form-check-lg .form-check-label {
            font-size: 1em;
            margin-right: .4em;
        }

        /* الجدول (الاجمالي) */
        table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 12px;
            overflow: hidden;
            margin-top: 12px;
            background: transparent;
        }

        thead {
            background: rgba(255, 224, 178, 0.85);
        }

        thead th {
            padding: 14px 12px;
            text-align: center;
            color: #444;
            font-size: 15px;
        }

        tbody {
            background: transparent;
        }

        /* tbody شفاف كما طلبت */
        tbody tr {
            border-bottom: 1px solid rgba(0, 0, 0, 0.03);
            text-align: center;
            transition: background .18s ease, transform .18s ease;
            background: transparent;
        }

        tbody td {
            padding: 12px 10px;
            text-align: center;
            color: #333;
        }

        /* hover effect on desktop rows */
        tbody tr:hover {
            transform: translateY(-3px);
            background: rgba(255, 247, 240, 0.6);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05);
            cursor: pointer;
        }

        /* badge styles */
        .badge {
            padding: 6px 10px;
            border-radius: 10px;
            display: inline-block;
            font-size: 13px;
        }

        .badge.scheduled {
            background: #fff4d6;
            color: #a46e00;
        }

        .badge.due {
            background: #ffe8e0;
            color: #9b3b20;
        }

        .badge.in_progress {
            background: #dbf7e9;
            color: #11683e;
        }

        .badge.finished {
            background: #e9eef7;
            color: #1f3f7a;
        }

        .badge.cancelled {
            background: #f5e9ee;
            color: #8a3350;
        }

        /* card style on small screens (when table elements become block) */
        @media (max-width:768px) {

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
                border: 1px solid #eee;
                border-radius: 10px;
                padding: 12px;
                background: #fff;
                box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
                transform: none;
                /* no lift on mobile for consistency */
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
                left: 12px;
                color: #666;
            }
        }

        /* subtle animations */
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes cardIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .row-animate {
            animation: fadeUp .32s ease both;
        }

        /* accessibility - reduced motion */
        @media (prefers-reduced-motion: reduce) {

            #addButton,
            #addButton::before,
            .row-animate {
                transition: none;
                animation: none;
                transform: none !important;
            }
        }

        /* nice spacing for container */
        .container h1 {
            margin-bottom: 8px;
        }

        /* small helper for no-results */
        .no-results {
            text-align: center;
            color: #999;
            padding: 18px 0;
        }
    </style>

    <div class="container">

        <a href="{{ route('expenses.create') }}" id="addButton" title="إضافة مصروف">+</a>



        <div class="filters-box">

            <div>
                <label class="form-label d-block mb-2">أنواع المصروف</label>
                <select id="expenseTypeFilter" class="form-select" style="min-width:160px;">
                    <option value="">كل الأنواع</option>
                    @foreach ($expenseTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- تقويم من إلى -->
            <div>
                <label class="form-label d-block mb-2">الفترة</label>
                <input type="text" id="dateRange" class="form-control" style="min-width: 200px;"
                    placeholder="اختر الفترة">
                <input type="hidden" id="fromDateHidden">
                <input type="hidden" id="toDateHidden">
            </div>


        </div>

        <table aria-describedby="expense-list">
            <thead>
                <tr>
                    <th>النوع</th>
                    <th>المبلغ</th>
                    <th>الملاحظة</th>
                    <th>أضيف بواسطة</th>
                    <th>التاريخ</th>
                </tr>
            </thead>
            <tbody id="expenseTable">
                <tr>
                    <td colspan="5" class="text-center p-3">⏳ جاري التحميل...</td>
                </tr>
            </tbody>
        </table>

    </div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
    const tableBody = document.getElementById("expenseTable");
    const expenseTypeFilter = document.getElementById("expenseTypeFilter");

    const fromInput = document.getElementById("fromDateHidden");
    const toInput = document.getElementById("toDateHidden");

    let params = new URLSearchParams();

    /** ------------------------------------------------------------------
     * رسم الصفوف داخل الجدول — والشغل كله هنا
     * ------------------------------------------------------------------ */
    function renderRows(data) {

        // فضي الجدول
        tableBody.innerHTML = '';

        // لو مفيش بيانات
        if (!data || data.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="no-results">❌ لا توجد نتائج</td>
                </tr>`;
            return;
        }

        // في بيانات
      data.forEach((exp, index) => {

    const tr = document.createElement("tr");
    tr.classList.add("row-animate"); // عشان CSS يشتغل
    tr.style.animationDelay = `${index * 25}ms`;

    // تحويل التاريخ إلى Date object
    const dateObj = new Date(exp.created_at);

    // التاريخ: سنة - شهر - يوم (إنجليزي)
    const datePart = dateObj.toLocaleDateString("en-CA", {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
    }); 
    // en-CA يعطي شكل: 2025-12-05 مباشرة

    // الوقت: ساعة ودقيقة AM/PM (إنجليزي)
    const timePart = dateObj.toLocaleTimeString("en-US", {
        hour: "2-digit",
        minute: "2-digit",
        hour12: true,
    });

    // الشكل النهائي المعروض
    const createdAt = `${datePart}<br><small style="color:#666;">${timePart}</small>`;

    tr.innerHTML = `
        <td data-label="النوع">${exp.expense_type_name}</td>
        <td data-label="المبلغ">${exp.amount} ج.م</td>
        <td data-label="الملاحظة">${exp.note ?? '-'}</td>
        <td data-label="أضيف بواسطة">${exp.added_by}</td>
        <td data-label="التاريخ">${createdAt}</td>
    `;

    tableBody.appendChild(tr);
});
    }
    /** ------------------------------------------------------------------
     * جلب البيانات من السيرفر (Ajax)
     * ------------------------------------------------------------------ */
    function fetchExpenses() {

        params = new URLSearchParams();

        if (expenseTypeFilter.value)
            params.append("expense_type_id", expenseTypeFilter.value);

        if (fromInput.value)
            params.append("from", fromInput.value);

        if (toInput.value)
            params.append("to", toInput.value);

        // رسالة تحميل
        tableBody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center p-3">⏳ جاري التحميل...</td>
            </tr>
        `;

        fetch("{{ route('expense.ajaxSearch') }}?" + params.toString())
            .then(res => res.json())
            .then(data => {
                renderRows(data);
            })
            .catch(err => {
                console.error(err);
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center p-3">⚠️ حدث خطأ، حاول مرة أخرى</td>
                    </tr>`;
            });
    }


    /** ------------------------------------------------------------------
     * فلترة نوع المصروف
     * ------------------------------------------------------------------ */
    expenseTypeFilter.addEventListener("change", fetchExpenses);


    /** ------------------------------------------------------------------
     * Flatpickr للتاريخ (من — إلى)
     * ------------------------------------------------------------------ */
    flatpickr("#dateRange", {
        mode: "range",
        dateFormat: "Y-m-d",
        onChange: function (selectedDates) {

            // لو اختر تاريخين فقط
            if (selectedDates.length === 2) {

                const from = selectedDates[0].toISOString().slice(0, 10);
                const to   = selectedDates[1].toISOString().slice(0, 10);

                fromInput.value = from;
                toInput.value = to;

                fetchExpenses();
            }
        }
    });


    /** ------------------------------------------------------------------
     * تحميل البيانات أول مرة عند فتح الصفحة
     * ------------------------------------------------------------------ */
    window.addEventListener("DOMContentLoaded", fetchExpenses);
</script>

@endsection
