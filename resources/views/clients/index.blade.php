@extends('layouts.app')

@section('page_title', 'العملاء')

<style>
  /* body */
body {
    font-family: "Tahoma", sans-serif;
    background: #1c1c1f; /* خلفية داكنة */
    margin: 0;
    padding: 0;
    color: #f5f5f5;
}

/* container */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 30px 20px;
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
    box-shadow: 0 6px 18px rgba(0,0,0,0.3);
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

/* العداد */
.stats-box {
    background: #1a1a1d;
    padding: 20px;
    border-radius: 15px;
    text-align: center;
    width: 220px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.3);
    font-size: 15px;
    margin: 10px;
    flex-shrink: 0;
}

.stats-box p:first-child {
    margin: 0;
    font-weight: bold;
    color: #ffd97a;
    font-size: 16px;
}

.stats-box p:last-child {
    margin: 10px 0 0;
    font-size: 22px;
    color: #f5f5f5;
}

/* header row */
.header-row {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 40px;
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

/* badges */
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
    .header-row {
        flex-direction: column;
    }

    .search-box input {
        width: 100%;
    }

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

/* مودال العملاء */
.modal-content {
    background: #1c1c1f;
    color: #f5f5f5;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.3);
}

.modal-header {
    background: #333;
    color: #ffd97a;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.modal-body input, .modal-body select, .modal-body textarea {
    background: #1c1c1f;
    color: #f5f5f5;
    border: 1px solid #555;
}

.modal-body input:focus, .modal-body select:focus, .modal-body textarea:focus {
    border-color: #ffb84d;
    box-shadow: 0 0 8px rgba(255,184,77,0.28);
}

.modal-footer {
    border-top: 1px solid rgba(255,255,255,0.1);
}

</style>

@section('content')
    <div class="container">

        {{-- الإشعارات --}}
        @if (session('success'))
            <div style="background: #d4edda; padding: 12px; margin-bottom: 20px; border-radius: 8px; color:#155724;">
                {{ session('success') }}
            </div>
        @endif
        {{-- العداد --}}
        <div class="header-row" style="display: flex; gap: 20px;">
            <div class="stats-box">
                <p>عدد العملاء</p>
                <p>{{ $count_client }}</p> <!-- مش محتاج id -->
            </div>
            <div class="stats-box">
                <p>العملاء النشطين</p>
                <p>{{ $active_clients_count }}</p> <!-- مش محتاج id -->
            </div>

        </div>

        <div class="search-box">
            <input type="text" id="searchBox" placeholder="🔍 بحث عن عميل (اسم أو هاتف أو ID)">
        </div>

        {{-- الجدول --}}
        <table>
            <thead>
                <tr>
                    <th>المعرف</th>
                    <th>اسم العميل</th>
                    <th>رقم الهاتف</th>
                </tr>
            </thead>
            <tbody id="clientTable">
                <tr>
                    <td colspan="3" class="text-center p-3">⏳ جاري التحميل...</td>
                </tr>
            </tbody>

        </table>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchBox = document.getElementById('searchBox');
            const tbody = document.getElementById('clientTable');

            // استخدم قالب الراوت لصفحة show (نفس أسلوب صفحة الاشتراكات)
            const showRouteTemplate = @json(route('clients.show', ['client' => ':id']));

            // تنسيق صفوف النتائج وإضافة حدث النقر
            function renderRows(data) {
                tbody.innerHTML = '';

                if (!data || data.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="3" class="text-center p-3">❌ لا توجد نتائج</td></tr>`;
                    return;
                }

                data.forEach(item => {
                    const tr = document.createElement('tr');
                    tr.style.cursor = 'pointer';
                    tr.dataset.id = item.id;

                    tr.innerHTML = `
                <td data-label="المعرف">${item.id}</td>
                <td data-label="اسم العميل">${item.name}</td>
                <td data-label="رقم الهاتف">${item.phone ?? '-'}</td>
            `;

                    tr.addEventListener('click', () => {
                        // استبدال :id بالـ id الحقيقي بطريقة آمنة
                        const url = showRouteTemplate.replace(':id', encodeURIComponent(item.id));
                        window.location.href = url;
                    });

                    tbody.appendChild(tr);
                });
            }

            // جلب البيانات من السيرفر
            function fetchClients(query = '') {
                tbody.innerHTML = `<tr><td colspan="3" class="text-center p-3">⏳ جاري التحميل...</td></tr>`;

                const url = "{{ route('clients.search') }}" + '?query=' + encodeURIComponent(query);

                fetch(url)
                    .then(res => {
                        if (!res.ok) throw new Error('Network response was not ok');
                        return res.json();
                    })
                    .then(data => {
                        if (Array.isArray(data)) {
                            renderRows(data);
                        } else if (data.clients && Array.isArray(data.clients)) {
                            renderRows(data.clients);
                            // ملاحظة: لا نقوم بتحديث العداد هنا - الأعداد ثابتة من الـ Blade
                        } else {
                            renderRows([]);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        tbody.innerHTML =
                            `<tr><td colspan="3" class="text-center p-3">⚠️ حدث خطأ، حاول مرة أخرى</td></tr>`;
                    });
            }

            // debounce بسيط
            let debounceTimer = null;
            searchBox.addEventListener('keyup', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    fetchClients(this.value.trim());
                }, 250);
            });

            // تحميل أولي
            fetchClients();
        });
    </script>

@endsection
