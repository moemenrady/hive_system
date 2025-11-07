@extends('layouts.app_page')
@section('title', '📜 إدارة الفواتير')

@section('content')
    <div class="page-container fade-in">

        {{-- ✅ العنوان --}}
    @section('page_title')
        <h1 class="title text-gold">📜 إدارة الفواتير</h1>
    @endsection

    {{-- 🔍 صندوق البحث --}}
    <div class="search-box mb-3">
        <input type="text" id="invoiceSearch" class="search-input" placeholder="ابحث بالعميل أو رقم الفاتورة..."
            value="{{ request('search') ?? '' }}">
    </div>

    {{-- 🗂️ فلاتر الأنواع --}}
    <div class="filters mb-3">
        @php $types = ['product', 'subscription', 'booking', 'session', 'deposit', 'mixed']; @endphp
        @foreach ($types as $type)
            <label class="filter-item">
                <input type="checkbox" class="filter-type" value="{{ $type }}">
                <span>{{ ucfirst($type) }}</span>
            </label>
        @endforeach
    </div>

    {{-- 📅 فلاتر التاريخ --}}
    <div class="date-filters mb-4">
        <input type="date" id="fromDate" class="date-input">
        <input type="date" id="toDate" class="date-input">
    </div>

    {{-- 🧾 قائمة الفواتير --}}
    <div id="invoiceList">
        <p class="loading">⏳ جاري التحميل...</p>
    </div>
</div>

{{-- 📜 سكريبت --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById('invoiceSearch');
        const invoiceList = document.getElementById('invoiceList');
        const typeCheckboxes = document.querySelectorAll('.filter-type');
        const fromDate = document.getElementById('fromDate');
        const toDate = document.getElementById('toDate');

        const searchRoute = @json(route('invoices.ajaxSearch'));
        const showRoute = @json(route('invoices.client.show', ':id'));

        function debounce(fn, delay = 300) {
            let t;
            return function(...args) {
                clearTimeout(t);
                t = setTimeout(() => fn.apply(this, args), delay);
            };
        }

        function safe(s) {
            return String(s ?? '').replace(/[&<>"]/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;'
            } [c]));
        }

        function renderInvoiceCard(inv) {
            const client = safe(inv.client_name ?? 'عميل غير معروف');
            const number = safe(inv.invoice_number);
            const type = safe(inv.type);
            const total = inv.total ?? 0;
            const paid = inv.paid ?? 0;
            const remaining = total - paid;
            const date = inv.updated_at ? new Date(inv.updated_at).toLocaleDateString() : '-';
            const url = showRoute.replace(':id', inv.id);

            return `
        <div class="invoice-card" onclick="window.location.href='${url}'">
            <div class="invoice-top">
                <div>
                    <h3 class="invoice-number">#${number}</h3>
                    <p class="client-name">${client}</p>
                </div>
                <span class="invoice-type">${type}</span>
            </div>

            <div class="invoice-body">
                <div class="row">
                    <span>📅 ${date}</span>
                </div>
                <div class="amounts">
                    <span class="total text-green">الإجمالي: ${total} ج</span>
                    <span class="paid">المدفوع: ${paid} ج</span>
                    <span class="remain">المتبقي: ${remaining} ج</span>
                </div>
            </div>
        </div>`;
        }

        function showLoading() {
            invoiceList.innerHTML = `<p class="loading">⏳ جاري التحميل...</p>`;
        }

        function showNoResults() {
            invoiceList.innerHTML = `<p class="no-results">❌ لا توجد فواتير</p>`;
        }

        async function fetchInvoices() {
            const q = searchInput.value.trim();
            const types = Array.from(typeCheckboxes).filter(c => c.checked).map(c => c.value);
            const from = fromDate.value;
            const to = toDate.value;

            showLoading();
            try {
                const url = new URL(searchRoute, location.origin);
                if (q) url.searchParams.append('q', q);
                if (types.length > 0) url.searchParams.append('types', types.join(','));
                if (from) url.searchParams.append('from', from);
                if (to) url.searchParams.append('to', to);

                const res = await fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!res.ok) throw new Error('Network error');
                const data = await res.json();
                const items = Array.isArray(data) ? data : (data.data ?? data.items ?? []);

                if (!items || items.length === 0) return showNoResults();

                invoiceList.innerHTML = items.map(renderInvoiceCard).join('');
            } catch (err) {
                invoiceList.innerHTML = `<p class="no-results">⚠️ حدث خطأ أثناء جلب البيانات</p>`;
            }
        }

        const debouncedFetch = debounce(fetchInvoices, 250);
        searchInput.addEventListener('input', debouncedFetch);
        typeCheckboxes.forEach(cb => cb.addEventListener('change', fetchInvoices));
        fromDate.addEventListener('change', fetchInvoices);
        toDate.addEventListener('change', fetchInvoices);

        fetchInvoices();
    });
</script>
@endsection

@section('style')
<style>
    /* 🎨 ثيم Hive */
    :root {
        --dark-bg: #16161a;
        --card-bg: #1e1e23;
        --text-light: #f2f2f2;
        --gold: #e5b35c;
        --muted: #aaaaaa;
        --green: #5cd98b;
        --red: #ff6b6b;
    }

    body {
        background: var(--dark-bg);
        color: var(--text-light);
        font-family: "Cairo", sans-serif;
    }

    .page-container {
        max-width: 1100px;
        margin: 0 auto;
        padding: 22px;
    }

    /* العنوان */
    .title {
        font-size: 26px;
        color: var(--gold);
        margin-bottom: 18px;
    }

    /* البحث */
    .search-input {
        width: 100%;
        padding: 10px 14px;
        border-radius: 10px;
        background: #222;
        border: 1px solid #333;
        color: #fff;
    }

    /* الفلاتر */
    .filters {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .filter-item {
        background: #2a2a30;
        color: var(--text-light);
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 14px;
    }

    .filter-item input {
        accent-color: var(--gold);
    }

    /* التاريخ */
    .date-input {
        padding: 8px 12px;
        background: #222;
        border: 1px solid #333;
        border-radius: 8px;
        color: #fff;
    }

    /* 🧾 كارت الفاتورة */
    .invoice-card {
        background: var(--card-bg);
        border-radius: 16px;
        padding: 18px;
        margin-bottom: 14px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        transition: all 0.25s ease;
        cursor: pointer;
    }

    .invoice-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 18px rgba(255, 204, 102, 0.2);
    }

    .invoice-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .invoice-number {
        font-size: 18px;
        color: var(--gold);
        margin: 0;
    }

    .client-name {
        color: var(--text-light);
        font-size: 15px;
    }

    .invoice-type {
        background: var(--gold);
        color: #111;
        padding: 6px 10px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
    }

    /* المبالغ */
    .amounts {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 10px;
    }

    .amounts span {
        background: #2b2b30;
        padding: 6px 10px;
        border-radius: 8px;
        font-size: 14px;
    }

    .text-green {
        color: var(--green);
    }

    /* حالات */
    .loading,
    .no-results {
        text-align: center;
        color: var(--muted);
        margin-top: 30px;
    }

    .fade-in {
        animation: fadeInUp 0.6s ease;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endsection
