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
    box-shadow: 0 6px 18px rgba(0,0,0,0.3);
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
    box-shadow: 0 6px 18px rgba(0,0,0,0.3);
    border: none;
    transition: transform 0.28s ease, box-shadow 0.28s ease;
    margin: 0 40px;
    flex-shrink: 0;
}
#addButton:hover {
    transform: scale(1.05);
    background: #ffd97a;
    box-shadow: 0 12px 25px rgba(0,0,0,0.4);
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
    box-shadow: 0 0 8px rgba(255,184,77,0.3);
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
    #addButton {
        width: 80px;
        height: 80px;
        font-size: 36px;
        margin: 15px 0;
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

    {{-- الجدول --}}
    <table>
        <thead>
            <tr>
                <th>المعرف</th>
                <th>اسم المنتج</th>
                <th>السعر</th>
                <th>التكلفة</th>
                <th>العدد</th>
            </tr>
        </thead>
        <tbody id="productTable">
            @foreach ($products as $product)
                <tr data-href="{{ route('products.show', $product->id) }}" style="cursor:pointer;">
                    <td data-label="المعرف">{{ $product->id }}</td>
                    <td data-label="اسم المنتج">{{ $product->name }}</td>
                    <td data-label="السعر">{{ $product->price }}</td>
                    <td data-label="التكلفة">{{ $product->cost }}</td>
                    <td data-label="العدد">{{ $product->quantity }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // الانتقال لتفاصيل المنتج عند الضغط على الصف
    document.querySelectorAll('#productTable tr').forEach(row => {
        row.addEventListener('click', function () {
            const url = this.dataset.href;
            if (url) window.location.href = url;
        });
    });

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
                        tbody.innerHTML += `
                        <tr data-href="{{ route('products.show', ':id') }}".replace(':id', item.id)>
                            <td data-label="المعرف">${item.id}</td>
                            <td data-label="اسم المنتج">${item.name}</td>
                            <td data-label="السعر">${item.price}</td>
                            <td data-label="التكلفة">${item.cost}</td>
                            <td data-label="العدد">${item.quantity}</td>
                        </tr>`;
                    });
                } else {
                    tbody.innerHTML = `<tr><td colspan="5" class="text-center">❌ لا توجد نتائج</td></tr>`;
                }
            });
    });
});
</script>

{{-- المودالات --}}
@include('products.modals.choose-action')
@include('products.modals.add-product')
@include('products.modals.add-quantity')
@endsection
