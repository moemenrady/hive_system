@extends('layouts.app')

@section('page_title', 'العملاء')

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
    background: #1c1c1f;
    color: #f5f5f5;
    transition: 0.2s;
}
.search-box input:focus {
    border-color: #ffb84d;
    box-shadow: 0 0 8px rgba(255,184,77,0.28);
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
    color: #ffd97a;
}
tbody tr {
    border-bottom: 1px solid rgba(255,255,255,0.05);
    text-align: center;
    transition: background 0.2s;
}
tbody tr:hover {
    background: rgba(255,184,77,0.08);
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
        padding: 10px;
        background: #1a1a1d;
        box-shadow: 0 6px 18px rgba(0,0,0,0.3);
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
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
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

    {{-- العداد --}}
    <div class="header-row" style="display: flex; gap: 20px;">
        <div class="stats-box">
            <p>عدد العملاء</p>
            <p>{{ $count_client }}</p>
        </div>
        <div class="stats-box">
            <p>العملاء النشطين</p>
            <p>{{ $active_clients_count }}</p>
        </div>
    </div>

    {{-- البحث --}}
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
    const showRouteTemplate = @json(route('clients.show', ['client' => ':id']));

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
                const url = showRouteTemplate.replace(':id', encodeURIComponent(item.id));
                window.location.href = url;
            });
            tbody.appendChild(tr);
        });
    }

    function fetchClients(query = '') {
        tbody.innerHTML = `<tr><td colspan="3" class="text-center p-3">⏳ جاري التحميل...</td></tr>`;
        const url = "{{ route('clients.search') }}" + '?query=' + encodeURIComponent(query);
        fetch(url)
            .then(res => {
                if (!res.ok) throw new Error('Network error');
                return res.json();
            })
            .then(data => {
                if (Array.isArray(data)) renderRows(data);
                else if (data.clients && Array.isArray(data.clients)) renderRows(data.clients);
                else renderRows([]);
            })
            .catch(() => {
                tbody.innerHTML = `<tr><td colspan="3" class="text-center p-3">⚠️ حدث خطأ، حاول مرة أخرى</td></tr>`;
            });
    }

    let debounceTimer = null;
    searchBox.addEventListener('keyup', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            fetchClients(this.value.trim());
        }, 250);
    });

    fetchClients();
});
</script>
@endsection
