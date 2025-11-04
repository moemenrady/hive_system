@extends('layouts.app')
@section('page_title', 'المخزن')

<style>
    /* 🎨 نفس ستايل صفحة إدارة الاشتراكات */
    body {
        font-family: "Tahoma", sans-serif;
        background: #1c1c1f;
        margin: 0;
        padding: 0;
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
        background: linear-gradient(120deg, transparent 35%, rgba(255, 255, 255, .35) 50%, transparent 65%);
        transform: translateX(-100%);
        transition: transform .6s ease;
        pointer-events: none;
    }

    #addButton:hover {
        transform: translateY(-4px) scale(1.03);
        background: #ffd97a;
        box-shadow: 0 14px 30px rgba(0, 0, 0, .45);
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
        box-shadow: 0 0 8px rgba(255, 184, 77, 0.28);
    }

    /* صندوق العداد */
    .stats-box {
        background: #1a1a1d;
        padding: 20px;
        border-radius: 15px;
        text-align: center;
        width: 220px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.3);
        font-size: 15px;
        margin: 10px;
    }

    .stats-box p:first-child {
        font-weight: bold;
        color: #ffb84d;
        font-size: 16px;
        margin: 0;
    }

    .stats-box p:last-child {
        margin-top: 8px;
        font-size: 22px;
        color: #f5f5f5;
    }

    /* الصف */
    .header-row {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 30px;
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
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
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
        background: rgba(255, 184, 77, 0.08);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
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
            border-radius: 10px;
            padding: 12px;
            background: #1c1c1f;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.3);
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
        from {
            opacity: 0;
            transform: translateY(8px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
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

        @if (session('error'))
            <div style="background: #f8d7da; padding: 12px; margin-bottom: 20px; border-radius: 8px; color:#721c24;">
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
                    <tr>
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
                        <tr>
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
    </script>

    @include('products.modals.choose-action')
    @include('products.modals.add-product')
    @include('products.modals.add-quantity')
@endsection
