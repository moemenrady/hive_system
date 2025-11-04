{{-- resources/views/client/bookings.blade.php --}}
@extends('layouts.app_page')

@section('title', "حجوزات — {$client->name}")

@section('content')
    <div class="client-container">
        <div class="card">
            <div class="card-header">
                <div>
                    <h2>📋 حجوزات {{ $client->name }}</h2>
                    <div class="muted small">#{{ $client->id }} — {{ $client->phone }}</div>
                </div>

                <div class="header-actions">
                    <button id="exportCsv" class="btn small">⬇︎ تصدير CSV</button>
                    <button id="printBtn" class="btn small">🖨 طباعة</button>
                </div>
            </div>

            <!-- Summary -->
            <div class="section">
                <div class="box stats-grid">
                    <div class="stat">
                        <div class="stat-label">إجمالي الحجوزات</div>
                        <div class="stat-value">{{ $totalBookings }}</div>
                    </div>
                    <div class="stat">
                        <div class="stat-label">مقدمات</div>
                        <div class="stat-value">{{ number_format($depositsTotal, 2) }} ج.م</div>
                    </div>
                    <div class="stat">
                        <div class="stat-label">المستلم فعليًا</div>
                        <div class="stat-value">{{ number_format($receivedTotal, 2) }} ج.م</div>
                    </div>
                    <div class="stat">
                        <div class="stat-label">تقديرات</div>
                        <div class="stat-value">{{ number_format($estimatedTotal, 2) }} ج.م</div>
                    </div>
                    <div class="stat">
                        <div class="stat-label">مشتريات الحجوزات</div>
                        <div class="stat-value">{{ number_format($purchasesTotal, 2) }} ج.م</div>
                    </div>
                    <div class="stat status-breakdown">
                        <div class="stat-label">تفصيل الحالات</div>
                        <div class="stat-value statuses-inline">
                            <span class="pill">مجدولة: {{ $countsByStatus['scheduled'] ?? 0 }}</span>
                            <span class="pill">مستحقة: {{ $countsByStatus['due'] ?? 0 }}</span>
                            <span class="pill">جارية: {{ $countsByStatus['in_progress'] ?? 0 }}</span>
                            <span class="pill">منتهية: {{ $countsByStatus['finished'] ?? 0 }}</span>
                            <span class="pill">ملغاة: {{ $countsByStatus['cancelled'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="section">
                <div class="box">
                    <div class="filters-row">
                        <div class="filter-left">
                            <input id="searchInput" placeholder="ابحث بالعنوان أو القاعة..." />
                            <div class="status-filters">
                                @foreach (['scheduled' => 'مجدولة', 'due' => 'مستحقة', 'in_progress' => 'جارية', 'finished' => 'منتهية', 'cancelled' => 'ملغاة'] as $v => $t)
                                    <label><input type="checkbox" class="status-filter" value="{{ $v }}"
                                            checked> {{ $t }}</label>
                                @endforeach
                            </div>
                        </div>
                        <div class="filter-right">
                            <label class="small muted">من:</label><input type="date" id="fromDate">
                            <label class="small muted">إلى:</label><input type="date" id="toDate">
                            <button id="clearFilters" class="btn small ghost">مسح</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bookings grid -->
            <div class="section">
                <div class="bookings-grid" id="bookingsGrid">
                    @foreach ($bookings as $b)
                        @php
                            $deposit = $depositsPerBooking[$b->id] ?? 0;
                            $purchases = $purchasesPerBooking[$b->id] ?? 0;
                        @endphp

                        <div class="booking-card" data-status="{{ $b->status }}"
                            data-start="{{ \Carbon\Carbon::parse($b->start_at)->format('Y-m-d') }}"
                            data-title="{{ Str::lower($b->title) }}" data-hall="{{ Str::lower($b->hall->name ?? '') }}"
                            onclick="window.location='{{ route('bookings.show', $b->id) }}'">
                            <div class="booking-header">
                                <h4>📅 {{ $b->title }}</h4>
                                <span class="status-badge status-{{ $b->status }}">{{ $b->status }}</span>
                            </div>

                            <p>🏛 <strong>القاعة:</strong> {{ $b->hall->name ?? '—' }}</p>
                            <p>🕒 <strong>الموعد:</strong> {{ \Carbon\Carbon::parse($b->start_at)->format('Y-m-d H:i') }}
                            </p>
                            <p>⏱ <strong>المدة:</strong> {{ intdiv($b->duration_minutes, 60) }}س
                                {{ $b->duration_minutes % 60 }}د</p>

                            <div class="booking-finance">
                                <p>💰 <strong>تقديري:</strong> {{ number_format($b->estimated_total, 2) }}</p>
                                <p>💵 <strong>فعلي:</strong> {{ number_format($b->real_total ?? 0, 2) }}</p>
                                <p>💸 <strong>مقدم:</strong> {{ number_format($deposit, 2) }}</p>
                                <p>🛒 <strong>مشتريات:</strong> {{ number_format($purchases, 2) }}</p>
                            </div>

                            <div class="booking-actions">
                                <a class="btn small" href="{{ route('bookings.show', $b->id) }}">🔍 عرض</a>
                                <a class="btn small ghost" href="{{ route('bookings.edit', $b->id) }}">✏️ تعديل</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const cards = Array.from(document.querySelectorAll('.booking-card'));
            const searchInput = document.getElementById('searchInput');
            const statusCheckboxes = Array.from(document.querySelectorAll('.status-filter'));
            const fromDate = document.getElementById('fromDate');
            const toDate = document.getElementById('toDate');
            const clearBtn = document.getElementById('clearFilters');
            const exportBtn = document.getElementById('exportCsv');
            const printBtn = document.getElementById('printBtn');

            function getActiveStatuses() {
                return statusCheckboxes.filter(c => c.checked).map(c => c.value);
            }

            function matchesFilters(card) {
                const active = getActiveStatuses();
                const status = card.dataset.status;
                if (!active.includes(status)) return false;

                const q = searchInput.value.trim().toLowerCase();
                if (q) {
                    const txt = card.dataset.title + ' ' + card.dataset.hall;
                    if (!txt.includes(q)) return false;
                }

                const date = card.dataset.start;
                if (fromDate.value && date < fromDate.value) return false;
                if (toDate.value && date > toDate.value) return false;
                return true;
            }

            function applyFilters() {
                let visible = 0;
                cards.forEach(card => {
                    if (matchesFilters(card)) {
                        card.style.display = '';
                        visible++;
                    } else {
                        card.style.display = 'none';
                    }
                });
            }

            statusCheckboxes.forEach(c => c.addEventListener('change', applyFilters));
            searchInput.addEventListener('input', debounce(applyFilters, 250));
            fromDate.addEventListener('change', applyFilters);
            toDate.addEventListener('change', applyFilters);
            clearBtn.addEventListener('click', () => {
                searchInput.value = '';
                fromDate.value = '';
                toDate.value = '';
                statusCheckboxes.forEach(c => c.checked = true);
                applyFilters();
            });

            exportBtn.addEventListener('click', () => {
                const visibleCards = cards.filter(c => c.style.display !== 'none');
                const rows = visibleCards.map(c => [
                    c.querySelector('h4').innerText,
                    c.querySelector('.status-badge').innerText,
                    c.querySelectorAll('p')[0]?.innerText,
                    c.querySelectorAll('p')[1]?.innerText,
                    c.querySelectorAll('p')[2]?.innerText,
                ].map(v => `"${v}"`).join(','));

                const csv = ['العنوان,الحالة,القاعة,الموعد,المدة', ...rows].join('\n');
                const blob = new Blob([csv], {
                    type: 'text/csv;charset=utf-8;'
                });
                const a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = 'bookings_{{ $client->id }}_{{ now()->format('Ymd') }}.csv';
                a.click();
                URL.revokeObjectURL(a.href);
            });

            printBtn.addEventListener('click', () => {
                const printContents = document.querySelector('.client-container').innerHTML;
                const w = window.open('', '', 'height=800,width=1200');
                w.document.write('<html><head><title>طباعة — حجوزات {{ $client->name }}</title>');
                w.document.write(
                    '<style>body{font-family:Cairo,sans-serif;direction:rtl;background:#fff;color:#000;} .booking-card{border:1px solid #ccc;margin:8px;padding:10px;border-radius:10px;} h4{margin:0 0 5px;}</style>'
                    );
                w.document.write('</head><body>');
                w.document.write(printContents);
                w.document.write('</body></html>');
                w.document.close();
                w.print();
            });

            function debounce(fn, delay) {
                let t;
                return function() {
                    clearTimeout(t);
                    t = setTimeout(() => fn.apply(this, arguments), delay);
                };
            }

            applyFilters();
        })();
    </script>
@endsection

@section('style')
    <style>
        :root {
            --bg-dark: #0e0e11;
            --card-bg: #16161a;
            --border: rgba(255, 255, 255, 0.08);
            --text-main: #f5f5f5;
            --text-dim: #b5b5b5;
            --accent: #e5b35c;
            --accent-dark: #c29545;
            --danger: #ff6b6b;
            --success: #7df59a;
            --info: #74c0fc;
        }

        body {
            background: var(--bg-dark);
            color: var(--text-main);
            font-family: "Cairo", sans-serif;
        }

        .client-container {
            max-width: 1100px;
            margin: 36px auto;
            padding: 20px;
        }

        .card {
            background: var(--card-bg);
            border-radius: 20px;
            border: 1px solid var(--border);
            padding: 22px;
            box-shadow: 0 0 18px rgba(0, 0, 0, 0.4);
            animation: fadeInUp .4s ease;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 12px;
            margin-bottom: 14px;
        }

        .card-header h2 {
            font-size: 22px;
            color: var(--accent);
            margin: 0;
        }

        .muted {
            color: var(--text-dim);
        }

        .small {
            font-size: 13px;
        }

        .header-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .btn {
            border: none;
            cursor: pointer;
            font-weight: 700;
            border-radius: 10px;
            padding: 8px 14px;
            background: var(--accent);
            color: #000;
            transition: background .2s ease, transform .15s ease;
        }

        .btn:hover {
            background: var(--accent-dark);
            transform: translateY(-2px);
        }

        .btn.small {
            padding: 6px 10px;
            font-size: 13px;
            border-radius: 8px;
        }

        .btn.ghost {
            background: transparent;
            border: 1px solid var(--accent);
            color: var(--accent);
        }

        .btn.ghost:hover {
            background: var(--accent-dark);
            color: #000;
        }

        .section {
            margin-top: 14px;
        }

        .box {
            background: #1c1c20;
            border-radius: 12px;
            border: 1px solid var(--border);
            padding: 14px;
        }

        /* stats grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
        }

        .stat {
            background: #1b1b1f;
            border-radius: 10px;
            border: 1px solid var(--border);
            padding: 12px;
        }

        .stat-label {
            color: var(--text-dim);
            font-size: 13px;
        }

        .stat-value {
            font-size: 18px;
            font-weight: 800;
            margin-top: 6px;
            color: var(--accent);
        }

        /* filters */
        .filters-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        #searchInput,
        input[type="date"] {
            background: #1b1b1f;
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text-main);
            padding: 8px 10px;
        }

        #searchInput::placeholder {
            color: var(--text-dim);
        }

        .status-filters label {
            margin-right: 12px;
            color: var(--text-dim);
            font-weight: 600;
        }

        .status-filters input {
            accent-color: var(--accent);
            transform: scale(1.1);
            margin: 0 6px;
        }

        /* bookings grid */
        .bookings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
            gap: 16px;
            margin-top: 12px;
        }

        .booking-card {
            background: #16161a;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 18px;
            color: #fff;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.4);
            transition: transform 0.25s ease, border-color 0.25s ease;
        }

        .booking-card:hover {
            transform: translateY(-6px);
            border-color: #ffb84d;
        }

        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .booking-card h4 {
            font-size: 18px;
            color: #ffb84d;
            margin: 0;
        }

        .booking-card p {
            margin: 4px 0;
            color: #e0e0e0;
            font-size: 14px;
        }

        .booking-card strong {
            color: #fff;
        }

        .booking-finance {
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            margin-top: 8px;
            padding-top: 8px;
        }

        .booking-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 10px;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 13px;
            text-transform: capitalize;
        }

        .status-scheduled {
            background: rgba(255, 200, 100, 0.1);
            color: var(--accent);
        }

        .status-due {
            background: rgba(255, 200, 100, 0.1);
            color: var(--accent-dark);
        }

        .status-in_progress {
            background: rgba(116, 192, 252, 0.15);
            color: var(--info);
        }

        .status-finished {
            background: rgba(125, 245, 154, 0.15);
            color: var(--success);
        }

        .status-cancelled {
            background: rgba(255, 107, 107, 0.1);
            color: var(--danger);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        @media (max-width:600px) {
            .booking-card {
                padding: 14px;
            }

            .booking-card h4 {
                font-size: 16px;
            }
        }
    </style>
@endsection
