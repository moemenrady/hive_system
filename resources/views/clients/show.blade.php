@extends('layouts.app_page')

@section('title', "تفاصيل العميل — {$client->name}")

@section('content')
<div class="client-container">

    <div class="card">
        <div class="card-header">
            <h2>👥 بيانات العميل</h2>
            <span class="badge">#{{ $client->id }}</span>
        </div>

        <div class="header-left">
            <a href="{{ route('clients.edit', $client->id) }}" class="btn edit-btn" title="تعديل بيانات العميل">
                <span class="edit-ico" aria-hidden="true">✏️</span>
                <span class="edit-txt">تعديل</span>
            </a>
        </div>

        <div class="section client-main">
            <div class="box client-info">
                <div class="row">
                    <div class="col">
                        <label class="lbl">اسم العميل</label>
                        <p class="value">{{ $client->name }}</p>
                    </div>
                    <div class="col">
                        <label class="lbl">رقم الهاتف</label>
                        <p class="value">{{ $client->phone }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="section statuses">
            <h3>📌 حالة الحساب</h3>
            <div class="box flex-grid">
                {{-- SUBSCRIPTION --}}
                <div class="status-card">
                    <label class="d-check">
                        <input type="checkbox" {{ $subscription ? 'checked' : '' }} disabled>
                        <span>مشترك</span>
                    </label>

                    @if ($subscription)
                        <p class="small">الحالة: <strong>{{ $subscription->is_active ? 'فعال' : 'منتهي' }}</strong></p>
                        <div class="actions">
                            <a href="{{ route('subscriptions.show', $subscription->id) }}" class="btn small">🔍 تفاصيل الاشتراك</a>
                        </div>
                    @else
                        <p class="small muted">غير مشترك</p>
                    @endif
                </div>

                {{-- BOOKINGS --}}
                <div class="status-card">
                    <label class="d-check">
                        <input type="checkbox" {{ $bookings->count() ? 'checked' : '' }} disabled>
                        <span>حاجز</span>
                    </label>

                    @if ($bookings->count())
                        <p class="small">حجوزات حالية: <strong>{{ $bookings->count() }}</strong></p>
                        <div class="actions">
                            <a href="{{ route('client.bookings', $client->id) }}" class="btn small">🔍 تفاصيل الحجوزات</a>
                        </div>
                    @else
                        <p class="small muted">غير حاجز</p>
                    @endif
                </div>

                {{-- SESSIONS --}}
                <div class="status-card">
                    <label class="d-check">
                        <input type="checkbox" {{ $activeSession ? 'checked' : '' }} disabled>
                        <span>يوجد جلسة</span>
                    </label>

                    @if ($activeSession)
                        <p class="small">تبدأ: {{ \Carbon\Carbon::parse($activeSession->start_time)->format('Y-m-d H:i') }}</p>
                        <div class="actions">
                            <a href="{{ route('session.show', $activeSession->id) }}" class="btn small">🔍 فتح الجلسة</a>
                        </div>
                    @else
                        <p class="small muted">لا يوجد جلسة نشطة</p>
                    @endif
                </div>

                {{-- FINANCIALS --}}
                <div class="status-card">
                    <label class="d-check">
                        <input type="checkbox" {{ $invoicesTotal > 0 ? 'checked' : '' }} disabled>
                        <span>التعاملات المالية</span>
                    </label>

                    <p class="small">المجموع: <strong>{{ number_format($invoicesTotal, 2) }} جنيه</strong></p>
                    <div class="actions">
                        <a href="{{ route('client.invoices', $client->id) }}" class="btn small">🔍 تفاصيل الفواتير</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick view -->
        <div class="section">
            <h3>🗂️ نظرة سريعة</h3>
            <div class="box">
                <div class="quick-grid">
                    <div>
                        <h4>أحدث ٣ حجوزات</h4>
                        @if ($bookings->count())
                            <ul class="mini-list">
                                @foreach ($bookings->take(3) as $b)
                                    <li>{{ $b->title }} — {{ $b->status }} — {{ \Carbon\Carbon::parse($b->start_at)->format('Y-m-d H:i') }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="muted">لا توجد حجوزات حالية</p>
                        @endif
                    </div>

                    <div>
                        <h4>الجلسات الأخيرة</h4>
                        @if ($recentSessions->count())
                            <ul class="mini-list">
                                @foreach ($recentSessions->take(3) as $s)
                                    <li>بدء: {{ \Carbon\Carbon::parse($s->start_time)->format('Y-m-d H:i') }} — حالة: {{ $s->status }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="muted">لا توجد جلسات</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('style')
<style>
    body { background: #121212; font-family: "Tahoma", sans-serif; color:#fff; }
    .client-container { max-width: 960px; margin: 40px auto; padding: 20px; }

    .card { background: #1e1e1e; border-radius: 20px; box-shadow: 0 5px 20px rgba(0,0,0,0.5); padding: 26px; animation: fadeInUp .6s ease; color:#fff; }
    .card-header { display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #333; margin-bottom:16px; padding-bottom:10px; }
    .card-header h2 { font-size:24px; color:#ffb84d; margin:0; }
    .badge { background:#ffb84d; color:#000; padding:6px 12px; border-radius:30px; font-weight:bold; box-shadow:0 3px 6px rgba(0,0,0,0.2); }

    .section h3 { color:#ffb84d; font-size:20px; margin-bottom:10px; }
    .box { background:#2a2a2a; padding:14px 18px; border-radius:12px; box-shadow: inset 0 2px 6px rgba(0,0,0,0.3); margin-bottom:18px; font-size:15px; line-height:1.6; }

    .flex-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:14px; }
    .status-card { background:#1e1e1e; padding:12px; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,0.5); display:flex; flex-direction:column; gap:8px; min-height:110px; }
    .d-check { display:flex; align-items:center; gap:8px; font-weight:600; }
    .d-check input { transform:scale(1.1); margin-right:6px; }

    .actions { margin-top:6px; }
    .btn.small { display:inline-block; background:#ffb84d; color:#000; padding:8px 12px; border-radius:10px; text-decoration:none; font-weight:700; box-shadow:0 4px 10px rgba(0,0,0,0.3); transition: transform .18s; }
    .btn.small:hover { transform:translateY(-3px); background:#fdc36c; }

    .muted { color:#888; }
    .value { font-size:16px; font-weight:700; margin-top:6px; color:#fff; }

    .row { display:flex; gap:20px; }
    .col { flex:1; }

    .quick-grid { display:flex; gap:18px; }
    .mini-list { list-style:none; padding-left:0; margin:0; font-size:14px; color:#fff; }
    .mini-list li { margin-bottom:6px; }

    @keyframes fadeInUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }

    @media (max-width:800px) {
        .flex-grid { grid-template-columns:1fr; }
        .quick-grid { flex-direction:column; }
    }
</style>
@endsection
