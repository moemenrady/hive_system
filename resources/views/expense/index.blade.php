@extends('layouts.app')

@section('page_title', 'قائمة الحجوزات')

@section('content')
    <style>
        <style>

        /* 🌑 الأساس */
        body {
            font-family: "Tahoma", sans-serif;
            background: #1c1c1f;
            color: #f5f5f5;
        }

        /* container */
        .container {
            max-width: 1200px;
            margin: auto;
            padding: 30px 20px;
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
            font-weight: 700;
            color: #1a1a1a;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .35);
            transition: transform .28s ease, box-shadow .28s ease, background .28s;
            margin: 20px auto;
            position: relative;
            overflow: hidden;
        }

        #addButton:hover {
            transform: scale(1.06);
            background: #ffd97a;
            box-shadow: 0 14px 30px rgba(0, 0, 0, .45);
        }

        /* filters */
        .filters-box {
            display: flex;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
            align-items: center;
        }

        .filters-box label {
            color: #ffb84d;
            font-weight: bold;
        }

        /* inputs */
        .form-control,
        .form-select {
            background: #1a1a1d;
            color: #f5f5f5;
            border: 1px solid #555;
            border-radius: 10px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #ffb84d;
            box-shadow: 0 0 8px rgba(255, 184, 77, .3);
            background: #1a1a1d;
            color: #fff;
        }

        /* الجدول */
        table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 14px;
            overflow: hidden;
            margin-top: 16px;
            background: transparent;
        }

        thead {
            background: #2a2a2e;
        }

        thead th {
            padding: 16px;
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            color: #ffb84d;
        }

        tbody tr {
            border-bottom: 1px solid rgba(255, 255, 255, .05);
            text-align: center;
            transition: background .2s, transform .2s;
        }

        tbody tr:hover {
            background: rgba(255, 184, 77, .08);
            transform: translateY(-3px);
        }

        tbody td {
            padding: 14px;
            font-size: 15px;
            color: #f5f5f5;
        }

        /* no results */
        .no-results {
            text-align: center;
            color: #bbb;
            padding: 20px;
        }

        /* animation */
        .row-animate {
            animation: fadeUp .32s ease both;
        }

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

        /* mobile cards */
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
                border: 1px solid #333;
                border-radius: 12px;
                padding: 12px;
                background: #1a1a1d;
                box-shadow: 0 6px 18px rgba(0, 0, 0, .4);
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
                left: 10px;
                font-weight: bold;
                color: #bbb;
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

        /* accessibility */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation: none !important;
                transition: none !important;
            }
        }
    </style>

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
                    <th>إجراء</th>

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
        <td>
          <a href="/expenses/${exp.id}/edit" class="btn btn-sm btn-outline-primary">
              ✏️ تعديل
          </a>
        </td>
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
            onChange: function(selectedDates) {

                // لو اختر تاريخين فقط
                if (selectedDates.length === 2) {

                    const from = selectedDates[0].toISOString().slice(0, 10);
                    const to = selectedDates[1].toISOString().slice(0, 10);

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
