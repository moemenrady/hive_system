@extends('layouts.app_page')
@section('title', 'إدارة الفواتير')

@section('content')
    @if (session('success'))
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                showSnackbar("{{ session('success') }}", "success");
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                showSnackbar("{{ session('error') }}", "error");
            });
        </script>
    @endif

    <div class="page-container">

        {{-- صفحة عنوان --}}
    @section('page_title')
        <h1 class="title">إدارة الفواتير</h1>
    @endsection

    {{-- صندوق البحث --}}
    <div class="search-box" style="margin-bottom:15px;">
        <input type="text" id="invoiceSearch" placeholder="ابحث بالعميل أو رقم الفاتورة" style="width:100%; padding:8px;"
            value="{{ request('search') ?? '' }}">
    </div>

    {{-- فلتر النوع --}}
    <div class="filters" style="margin-bottom:15px; display:flex; flex-wrap:wrap; gap:12px;">
        @php
            $types = ['product', 'subscription', 'booking', 'session', 'deposit', 'mixed'];
        @endphp
        @foreach ($types as $type)
            <label style="display:flex; align-items:center; gap:4px;">
                <input type="checkbox" class="filter-type" value="{{ $type }}">
                <span>{{ $type }}</span>
            </label>
        @endforeach
    </div>

    {{-- فلتر التاريخ --}}
    <div class="date-filters" style="margin-bottom:20px; display:flex; gap:8px; flex-wrap:wrap;">
        <input type="date" id="fromDate" placeholder="من">
        <input type="date" id="toDate" placeholder="إلى">
    </div>

    {{-- قائمة الفواتير --}}
    <div class="invoice-list" id="invoiceList">
        <p class="text-center p-3">⏳ جاري التحميل...</p>
    </div>

</div>

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

        function safeText(s) {
            return String(s ?? '').replace(/[&<>"]/g, function(c) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;'
                } [c];
            });
        }

        function renderInvoiceCard(inv) {
            const client = safeText(inv.client_name ?? 'عميل غير معروف');
            const number = safeText(inv.invoice_number);
            const type = safeText(inv.type);
            const total = inv.total ?? 0;
            const date = inv.updated_at ? new Date(inv.updated_at).toLocaleDateString() : '-';
            const url = showRoute.replace(':id', inv.id);

            return `
        <div class="session-card" role="button" onclick="window.location.href='${url}'">
            <div class="info" style="text-align:right;">
                <h3>#${number}</h3>
                <p>العميل: ${client}</p>
                <p>النوع: ${type}</p>
                <p>التاريخ: ${date}</p>
            </div>
            <div class="persons">
    <div class="total-amount">الإجمالي: ${total} ج</div>
</div>

        </div>
        `;
        }

        function showLoading() {
            invoiceList.innerHTML = `<p class="text-center p-3">⏳ جاري التحميل...</p>`;
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
                if (!res.ok) throw new Error('Network response was not ok');
                const data = await res.json();
                const items = Array.isArray(data) ? data : (data.data ?? data.items ?? data);

                if (!items || items.length === 0) {
                    showNoResults();
                    return;
                }

                invoiceList.innerHTML = '';
                items.forEach(i => {
                    invoiceList.innerHTML += renderInvoiceCard(i);
                });
            } catch (err) {
                console.error(err);
                invoiceList.innerHTML = `<p class="no-results">حدث خطأ أثناء جلب الفواتير</p>`;
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
  /* 🎨 ثيم Hive الداكن - صفحة إدارة الفواتير */
:root {
    --gold: #ffb84d;
    --dark-bg: #0f0f0f;
    --card-bg: #1a1a1a;
    --mid-bg: #161616;
    --text-light: #f5f5f5;
    --text-dim: #cfcfcf;
    --success-green: #37d67a;
}

body {
    font-family: "Cairo", sans-serif;
    background: var(--dark-bg);
    color: var(--text-light);
    margin: 0;
}

.page-container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 20px;
}

/* ====== العنوان الرئيسي ====== */
.title {
    color: var(--gold);
    font-size: 28px;
    font-weight: 700;
    text-align: center;
    margin-bottom: 25px;
    letter-spacing: 1px;
}

/* ====== صناديق البحث والفلاتر ====== */
.search-box input,
.date-filters input {
    width: 100%;
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid rgba(255, 184, 77, 0.25);
    background: var(--mid-bg);
    color: var(--text-light);
    outline: none;
    transition: 0.25s;
}

.search-box input:focus,
.date-filters input:focus {
    border-color: var(--gold);
    box-shadow: 0 0 8px rgba(255, 184, 77, 0.3);
}

.filters label {
    cursor: pointer;
    font-size: 14px;
    color: var(--text-dim);
    background: var(--mid-bg);
    padding: 6px 10px;
    border-radius: 8px;
    border: 1px solid rgba(255, 184, 77, 0.1);
    transition: 0.25s;
}

.filters label:hover {
    background: rgba(255, 184, 77, 0.1);
    color: var(--gold);
}

/* ====== كروت الفواتير ====== */
.session-card {
    background: var(--card-bg);
    border: 1px solid rgba(255, 184, 77, 0.08);
    border-radius: 18px;
    padding: 16px 20px;
    margin-bottom: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: 0.3s ease-in-out;
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.25);
}

.session-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 14px rgba(255, 184, 77, 0.12);
    border-color: rgba(255, 184, 77, 0.25);
}

.session-card .info h3 {
    font-size: 18px;
    color: var(--gold);
    margin-bottom: 6px;
    font-weight: 600;
}

.session-card .info p {
    margin: 2px 0;
    color: var(--text-dim);
    font-size: 13px;
}

.session-card .persons {
    text-align: center;
}

/* 💰 الإجمالي */
.session-card .total-amount {
    font-size: 17px;
    font-weight: bold;
    color: var(--success-green);
    background: rgba(55, 214, 122, 0.08);
    padding: 8px 14px;
    border-radius: 10px;
    border: 1px solid rgba(55, 214, 122, 0.25);
    display: inline-block;
}

/* 🕐 نصوص حالة التحميل */
.no-results,
.text-center {
    color: var(--text-dim);
    font-size: 14px;
    text-align: center;
    margin-top: 15px;
}

/* ====== هوفرات رايقة ====== */
input[type="checkbox"] {
    accent-color: var(--gold);
}

/* ====== الأنيميشن ====== */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ====== للموبايل ====== */
@media (max-width: 600px) {
    .session-card {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
}


</style>
@endsection
