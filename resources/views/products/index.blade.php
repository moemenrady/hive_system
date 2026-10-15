@extends('layouts.app')
@section('page_title', 'المخزن')

<style>
    /* 🌑 نفس تقسيمة الفاتح لكن داكن */
    body {
        font-family: "Tahoma", sans-serif;
        background: #1c1c1f;
        margin: 0;
        padding: 0;
        color: #f5f5f5;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 30px 20px;
    }

    /* العداد */
    .stats-box {
        background: #1a1a1d;
        padding: 20px;
        border-radius: 15px;
        text-align: center;
        width: 220px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.3);
        font-size: 15px;
        margin: 10px;
        flex-shrink: 0;
    }

    .stats-box p:first-child {
        margin: 0;
        font-weight: bold;
        color: #ffb84d;
        font-size: 16px;
    }

    .stats-box p:last-child {
        margin: 10px 0 0;
        font-size: 22px;
        color: #f5f5f5;
    }

    /* زر الإضافة */
    #addButton {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #ffb84d;
        font-size: 48px;
        font-weight: 700;
        color: #1a1a1a;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.3);
        border: none;
        transition: transform 0.28s ease, box-shadow 0.28s ease;
        margin: 0 40px;
        flex-shrink: 0;
    }

    #addButton:hover {
        transform: scale(1.05);
        background: #ffd97a;
        box-shadow: 0 12px 25px rgba(0, 0, 0, 0.4);
    }

    /* الصف الأول */
    .header-row {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 40px;
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
        background: #1a1a1d;
        color: #f5f5f5;
        transition: 0.2s;
    }

    .search-box input:focus {
        border-color: #ffb84d;
        box-shadow: 0 0 8px rgba(255, 184, 77, 0.3);
    }

    /* الجدول */
    table {
        width: 100%;
        border-collapse: collapse;
        background: transparent;
        border-radius: 12px;
        overflow: hidden;
        margin-top: 20px;
    }

    thead {
        background: #333;
    }

    thead th {
        padding: 16px 20px;
        text-align: center;
        font-size: 15px;
        font-weight: bold;
        color: #ffb84d;
    }

    tbody tr {
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        text-align: center;
        transition: background 0.2s;
    }

    tbody tr:hover {
        background: rgba(255, 184, 77, 0.08);
    }

    tbody td {
        padding: 14px 18px;
        font-size: 15px;
        color: #f5f5f5;
    }

    /* الموبايل */
    @media (max-width: 768px) {
        .header-row {
            flex-direction: column;
        }

        #addButton {
            width: 80px;
            height: 80px;
            font-size: 36px;
            margin: 15px 0;
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
            background: #1a1a1d;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.3);
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

    .recipe-btn {
        background: #ffb84d;
        color: #1a1a1a;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 13px;
        text-decoration: none;
        font-weight: bold;
        transition: 0.2s;
        display: inline-block;
    }

    .recipe-btn:hover {
        background: #ffd97a;
        transform: scale(1.05);
    }
        /* وضع التحديد */
.selection-active .select-col {
    display: table-cell !important;
}

tr.selected {
    background: rgba(255, 184, 77, 0.3) !important;
    border-left: 5px solid #ffb84d;
    transition: all 0.2s ease;
}

tr:hover {
    background: rgba(255, 184, 77, 0.08);
}
</style>

@section('content')
    <div class="container">

        {{-- الإشعارات --}}
        @if (session('success'))
            <div style="background: #0f5132; padding: 12px; margin-bottom: 20px; border-radius: 8px; color:#d1e7dd;">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div style="background: #5f0a0a; padding: 12px; margin-bottom: 20px; border-radius: 8px; color:#f8d7da;">
                {{ session('error') }}
            </div>
        @endif

        {{-- العدادين وزرار الإضافة --}}
        <div class="header-row">
            <div class="stats-box">
                <p>عدد المنتجات</p>
                <p>{{ $countItems }}</p>
            </div>

            <button id="addButton" data-bs-toggle="modal" data-bs-target="#chooseActionModal">+</button>

            <div class="stats-box">
                <p>عدد الأصناف</p>
                <p>{{ $countProducts }}</p>
            </div>
        </div>

        {{-- البحث --}}
        <div class="search-box">
            <input type="text" id="searchBox" placeholder="🔍 بحث عن منتج">
        </div>
        <div style="text-align:left; margin-bottom:10px;">
            <button id="toggleSelection" class="btn btn-outline-light rounded-pill px-4 fw-bold mb-3">
                🗂️ وضع التحديد
            </button>

            <div id="selectionActions" class="d-none align-items-center gap-3 bg-dark px-4 py-2 rounded-pill mb-3">
                <span id="selectedCount" class="fw-bold text-warning">0 محدد</span>
                <button id="deleteSelected" class="btn btn-danger btn-sm rounded-pill px-3">🗑️ حذف المحدد</button>
                <button id="cancelSelection" class="btn btn-secondary btn-sm rounded-pill px-3">إلغاء</button>
            </div>
        </div>



        {{-- الجدول --}}
        <table>
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" id="selectAll">
                    </th>
                    <th>المعرف</th>
                    <th>اسم المنتج</th>
                    <th>الصنف</th>
                    <th>السعر</th>
                    @if(Auth::user()->role === 'admin')
                    <th>التكلفة</th>
                    @endif
                    <th>العدد</th>
                </tr>
            </thead>

            <tbody id="productTable">
                @foreach ($products as $product)
                    <tr data-id="{{ $product->id }}">
                        <td>
                            <input type="checkbox" class="rowCheckbox" value="{{ $product->id }}">
                        </td>
                        <td>{{ $product->id }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category?->name ?? '---' }}</td>
                        <td>{{ $product->price }}</td>
                        @if(Auth::user()->role === 'admin')
                            <td>{{ $product->cost }}</td>
                        @endif
                        <td>
                            @if ($product->is_produced)
                                <a href="{{ route('products.show', $product->id) }}" class="recipe-btn">عرض الريسبي</a>
                            @else
                                {{ $product->quantity }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>


        </table>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // الانتقال لتفاصيل المنتج عند الضغط على الصف
            document.querySelectorAll('#productTable tr').forEach(row => {
                row.addEventListener('click', function() {
                    const url = this.dataset.href;
                    if (url) window.location.href = url;
                });
            });

            // قالب الرابط من لارافيل (مرة واحدة فقط)
            const showRouteTemplate = "{{ route('products.show', ':id') }}";

            // البحث AJAX
            document.getElementById('searchBox').addEventListener('keyup', function() {

                let query = this.value;

                fetch("{{ route('products.search') }}?query=" + query)
                    .then(response => response.json())
                    .then(data => {

                        let tbody = document.getElementById('productTable');
                        tbody.innerHTML = "";

                        if (data.length > 0) {

                            data.forEach(item => {

                                const tr = document.createElement('tr');

                                // عمل الرابط داخل data-href
                                const url = showRouteTemplate.replace(':id', encodeURIComponent(
                                    item.id));
                                tr.dataset.href = url;
                                tr.style.cursor = "pointer";

                                tr.innerHTML = `
    <td data-label="المعرف">${item.id}</td>
    <td data-label="اسم المنتج">${item.name}</td>
    <td data-label="الصنف">
        ${item.category ? item.category.name : '---'}
    </td>
    <td data-label="السعر">${item.price}</td>
    <td data-label="التكلفة">${item.cost}</td>
    <td data-label="العدد">
        ${
            item.is_produced == 1
            ? `<a href="${showRouteTemplate.replace(':id', item.id)}" class="recipe-btn">عرض الريسبي</a>`
            : item.quantity
        }
    </td>
`;


                                // نفس الفكرة زي الكود الأول
                                tr.addEventListener('click', () => {
                                    window.location.href = url;
                                });

                                tbody.appendChild(tr);
                            });

                        } else {

                            tbody.innerHTML = `
                        <tr><td colspan="5" class="text-center">❌ لا توجد نتائج</td></tr>
                    `;
                        }
                    });
            });
        });
        document.addEventListener('DOMContentLoaded', function() {

            const tableBody = document.getElementById('productTable');
            const toggleBtn = document.getElementById('toggleSelection');
            const selectionBar = document.getElementById('selectionActions');
            const selectedCount = document.getElementById('selectedCount');
            const deleteBtn = document.getElementById('deleteSelected');
            const cancelBtn = document.getElementById('cancelSelection');
            const showRouteTemplate = "{{ route('products.show', ':id') }}";

            let selectionMode = false;
            let selectedIds = new Set();

            function updateSelectedUI() {
                selectedCount.textContent = selectedIds.size + " محدد";
            }

            function enableSelectionMode() {
                selectionMode = true;
                selectionBar.classList.remove('d-none');

                tableBody.querySelectorAll('tr').forEach(row => {
                    // إضافة checkbox لكل صف
                    if (!row.querySelector('.select-col')) {
                        const td = document.createElement('td');
                        td.className = 'select-col';
                        const checkbox = document.createElement('input');
                        checkbox.type = 'checkbox';
                        checkbox.className = 'rowCheckbox';
                        td.appendChild(checkbox);
                        row.insertBefore(td, row.firstChild);
                    }
                });
            }

            function disableSelectionMode() {
                selectionMode = false;
                selectedIds.clear();
                selectionBar.classList.add('d-none');

                tableBody.querySelectorAll('tr').forEach(row => {
                    row.classList.remove('selected');
                    const checkbox = row.querySelector('.rowCheckbox');
                    if (checkbox) checkbox.parentNode.removeChild(checkbox);
                    const selectTd = row.querySelector('.select-col');
                    if (selectTd) selectTd.remove();
                });
                updateSelectedUI();
            }

            toggleBtn.addEventListener('click', enableSelectionMode);
            cancelBtn.addEventListener('click', disableSelectionMode);

            deleteBtn.addEventListener('click', async function() {
                if (selectedIds.size === 0) return alert("اختر منتجات أولاً");
                if (!confirm("هل أنت متأكد من حذف المنتجات المحددة؟")) return;

                const ids = Array.from(selectedIds);
                try {
                    const res = await fetch("{{ route('products.bulkDelete') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            ids
                        })
                    });
                    if (res.ok) {
                        ids.forEach(id => document.querySelector(`tr[data-id='${id}']`)?.remove());
                        disableSelectionMode();
                        alert("✅ تم الحذف بنجاح");
                    }
                } catch (e) {
                    alert("❌ حدث خطأ أثناء الحذف");
                }
            });

            // التعامل مع الضغط على الصف
            tableBody.querySelectorAll('tr').forEach(row => {
                row.addEventListener('click', function(e) {
                    const id = row.dataset.id;

                    if (selectionMode) {
                        e.stopPropagation();
                        const checkbox = row.querySelector('.rowCheckbox');
                        const isSelected = row.classList.toggle('selected');

                        if (isSelected) selectedIds.add(id);
                        else selectedIds.delete(id);
                        if (checkbox) checkbox.checked = isSelected;
                        updateSelectedUI();
                        return;
                    }

                    // الوضع العادي: فتح صفحة المنتج
                    window.location.href = showRouteTemplate.replace(':id', id);
                });
            });

        });
    </script>





    {{-- المودالات --}}
    @include('products.modals.choose-action')
    @include('products.modals.add-product')
    @include('products.modals.add-quantity')
    @include('products.modals.add-new-category')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addQtyModal = document.getElementById('addQuantityModal');
            if (!addQtyModal) return;

            // لما المودال يفتح
            addQtyModal.addEventListener('shown.bs.modal', function() {
                const searchInput = document.getElementById('searchProduct');
                const resultsList = document.getElementById('searchResults');
                const form = document.getElementById('addQuantityForm');
                const productIdInput = document.getElementById('product_id');

                if (!searchInput || !resultsList || !form || !productIdInput) return;

                // reset كل مرة يفتح فيها المودال
                searchInput.value = '';
                resultsList.innerHTML = '';
                form.style.display = 'none';
                productIdInput.value = '';
                searchInput.focus();

                // علشان ما نربطش نفس الحدث أكتر من مرة
                if (searchInput.dataset.bound === '1') return;
                searchInput.dataset.bound = '1';

                // السيرش
                searchInput.addEventListener('keyup', function() {
                    const q = this.value.trim();
                    if (q.length < 1) {
                        resultsList.innerHTML = '';
                        form.style.display = 'none';
                        return;
                    }

                    fetch("{{ route('products.search') }}?query=" + encodeURIComponent(q))
                        .then(res => res.json())
                        .then(items => {
                            resultsList.innerHTML = '';

                            if (!items.length) {
                                resultsList.innerHTML =
                                    '<li class="list-group-item text-center text-muted">لا توجد نتائج</li>';
                                form.style.display = 'none';
                                return;
                            }

                            items.forEach(item => {
                                const li = document.createElement('li');
                                li.className = 'list-group-item list-group-item-action';
                                li.style.cursor = 'pointer';
                                li.textContent = `${item.name} (المعرف: ${item.id})`;

                                li.addEventListener('click', function() {
                                    productIdInput.value = item.id;
                                    form.action =
                                        "{{ route('products.addQuantity', ':id') }}"
                                        .replace(':id', item.id);

                                    form.style.display = 'block';
                                    resultsList.innerHTML = '';
                                    searchInput.value = item.name;
                                });

                                resultsList.appendChild(li);
                            });
                        })
                        .catch(err => {
                            console.error('Search error:', err);
                        });
                });
            });
            // التحديد المتعدد وحذف المحدد
            const selectAll = document.getElementById('selectAll');
            const deleteBtn = document.getElementById('deleteSelected');

            function toggleDeleteBtn() {
                deleteBtn.style.display = document.querySelectorAll('.rowCheckbox:checked').length ?
                    'inline-block' : 'none';
            }

            selectAll.addEventListener('change', function() {
                document.querySelectorAll('.rowCheckbox').forEach(cb => cb.checked = this.checked);
                toggleDeleteBtn();
            });

            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('rowCheckbox')) toggleDeleteBtn();
            });

            deleteBtn.addEventListener('click', function() {
                let ids = Array.from(document.querySelectorAll('.rowCheckbox:checked')).map(cb => cb.value);
                if (!ids.length || !confirm("هل أنت متأكد من حذف المنتجات المحددة؟")) return;

                fetch("{{ route('products.bulkDelete') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            ids
                        })
                    })
                    .then(res => res.json())
                    .then(() => location.reload());
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const table = document.querySelector('table');
            const tableBody = document.getElementById('productTable');
            const showRouteTemplate = "{{ route('products.show', ':id') }}";

            // ====== نظام التحديد لحذف المنتجات ======
            let selectionMode = false;
            let selectedIds = new Set();

            // إنشاء عناصر الـ selection UI مثل صفحة الاشتراكات
            const toggleBtn = document.createElement('button');
            toggleBtn.id = "toggleSelection";
            toggleBtn.className = "btn btn-outline-light rounded-pill px-3 fw-bold mb-3";
            toggleBtn.textContent = "🗂️ وضع التحديد";
            table.parentNode.insertBefore(toggleBtn, table);

            const selectionBar = document.createElement('div');
            selectionBar.id = "selectionActions";
            selectionBar.className = "d-none align-items-center gap-3 bg-dark px-4 py-2 rounded-pill mb-3";
            selectionBar.style.color = "#fff";
            selectionBar.innerHTML = `
        <span id="selectedCount" class="fw-bold text-warning">0 محدد</span>
        <button id="deleteSelected" class="btn btn-danger btn-sm rounded-pill px-3">🗑️ حذف المحدد</button>
        <button id="cancelSelection" class="btn btn-secondary btn-sm rounded-pill px-3">إلغاء</button>
    `;
            table.parentNode.insertBefore(selectionBar, table);

            const selectedCount = document.getElementById('selectedCount');
            const deleteBtn = document.getElementById('deleteSelected');
            const cancelBtn = document.getElementById('cancelSelection');

            function updateSelectedUI() {
                selectedCount.textContent = selectedIds.size + " محدد";
            }

            function enableSelectionMode() {
                selectionMode = true;
                table.classList.add("selection-active");
                selectionBar.classList.remove("d-none");
                tableBody.querySelectorAll('tr').forEach(row => {
                    const td = document.createElement('td');
                    td.className = "select-col";
                    const checkbox = document.createElement('input');
                    checkbox.type = "checkbox";
                    checkbox.className = "rowCheckbox";
                    td.appendChild(checkbox);
                    row.insertBefore(td, row.firstChild);
                });
            }

            function disableSelectionMode() {
                selectionMode = false;
                selectedIds.clear();
                table.classList.remove("selection-active");
                selectionBar.classList.add("d-none");
                tableBody.querySelectorAll('tr').forEach(row => {
                    row.classList.remove("selected");
                    const checkbox = row.querySelector(".rowCheckbox");
                    if (checkbox) checkbox.parentNode.removeChild(checkbox);
                    const selectTd = row.querySelector('.select-col');
                    if (selectTd) selectTd.remove();
                });
                updateSelectedUI();
            }

            toggleBtn.addEventListener('click', enableSelectionMode);
            cancelBtn.addEventListener('click', disableSelectionMode);

            deleteBtn.addEventListener('click', async function() {
                if (selectedIds.size === 0) return alert("اختر منتجات أولاً");
                if (!confirm("هل أنت متأكد من حذف المنتجات المحددة؟")) return;

                const ids = Array.from(selectedIds);
                try {
                    const res = await fetch("{{ route('products.bulkDelete') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            ids
                        })
                    });
                    const data = await res.json();
                    if (res.ok) {
                        ids.forEach(id => document.querySelector(`tr[data-id='${id}']`)?.remove());
                        disableSelectionMode();
                        alert("✅ تم الحذف بنجاح");
                    }
                } catch (e) {
                    alert("❌ حدث خطأ أثناء الحذف");
                }
            });

            // ====== فتح صفحة المنتج عند الضغط على الصف ======
            function attachRowClick() {
                tableBody.querySelectorAll('tr').forEach(row => {
                    row.style.cursor = "pointer";
                    row.addEventListener('click', function(e) {
                        const id = row.dataset.id;

                        if (selectionMode) {
                            e.stopPropagation();
                            const checkbox = row.querySelector(".rowCheckbox");
                            const isSelected = row.classList.toggle('selected');
                            if (isSelected) selectedIds.add(id);
                            else selectedIds.delete(id);
                            if (checkbox) checkbox.checked = isSelected;
                            updateSelectedUI();
                            return;
                        }

                        // الوضع العادي: فتح صفحة المنتج
                        window.location.href = showRouteTemplate.replace(':id', id);
                    });
                });
            }

            attachRowClick();

            // ====== البحث ======
            const searchBox = document.getElementById('searchBox');
            searchBox.addEventListener('keyup', function() {
                const query = this.value;

                fetch("{{ route('products.search') }}?query=" + encodeURIComponent(query))
                    .then(res => res.json())
                    .then(data => {
                        tableBody.innerHTML = '';
                        if (!data.length) {
                            tableBody.innerHTML =
                                `<tr><td colspan="7" class="text-center">❌ لا توجد نتائج</td></tr>`;
                            return;
                        }

                        data.forEach(item => {
                            const tr = document.createElement('tr');
                            tr.dataset.id = item.id;
                            tr.innerHTML = `
                        <td>${item.id}</td>
                        <td>${item.name}</td>
                        <td>${item.category ? item.category.name : '---'}</td>
                        <td>${item.price}</td>
                        <td>${item.cost}</td>
                        <td>${item.is_produced ? '<a href="${showRouteTemplate.replace(':id', item.id)}" class="recipe-btn">عرض الريسبي</a>' : item.quantity}</td>
                    `;
                            tableBody.appendChild(tr);
                        });

                        attachRowClick();
                    });
            });

        });
    </script>

@endsection
