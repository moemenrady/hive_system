@extends('layouts.app_page')

@section('content')
<div class="shift-hive-container">
    <div class="card-header">
        <h2>🧾 تفاصيل الشيفت <span class="badge">#{{ $shift->id }}</span></h2>
        <div class="meta">
            الموظف: <strong>{{ $shift->user?->name ?? '—' }}</strong> |
            البداية: <strong>{{ $shift->start_time ? \Carbon\Carbon::parse($shift->start_time)->format('Y-m-d H:i') : \Carbon\Carbon::parse($shift->created_at)->format('Y-m-d H:i') }}</strong> |
            النهاية: <strong>{{ $shift->end_time ? \Carbon\Carbon::parse($shift->end_time)->format('Y-m-d H:i') : '—' }}</strong>
        </div>
    </div>

    <div class="summary-row">
        <div class="summary-box income">الإيراد <span>{{ number_format($totalIncome, 2) }} ج.م</span></div>
        <div class="summary-box expense">المصروف <span>{{ number_format($totalExpense, 2) }} ج.م</span></div>
        <div class="summary-box net">الصافي <span>{{ number_format($totalNet, 2) }} ج.م</span></div>
        <button id="exportCsv" class="btn export-btn">📤 تصدير CSV</button>
    </div>

    <div class="filter-bar">
        <input id="searchInput" type="text" placeholder="ابحث..." class="input-dark">
        <select id="filterType" class="input-dark">
            <option value="">كل الأنواع</option>
            <option value="new_subscription">اشتراك جديد</option>
            <option value="renew_subscription">تجديد</option>
            <option value="end_session">إنهاء جلسة</option>
            <option value="expense_note">مذكرة مصروف</option>
            <option value="separate_sale">بيع منفصل</option>
            <option value="add_booking">إضافة حجز</option>
            <option value="end_booking">إنهاء حجز</option>
        </select>
        <button id="toggleCollapse" class="btn btn-outline">طي / توسيع</button>
    </div>

    @if ($actions->isEmpty())
        <div class="no-actions">لا توجد حركات لهذا الشيفت</div>
    @else
        <div id="actionsList" class="actions-list">
            @foreach ($actions as $action)
                <div class="action-card" 
                     data-id="{{ $action->id }}" 
                     data-action-type="{{ $action->action_type }}" 
                     data-search="{{ implode(' ', [$action->action_type, $action->invoice?->id, $action->expenseDraft?->id, $action->notes]) }}">
                    <div class="action-header">
                        <div class="left">
                            <div class="icon">{!! actionIcon($action->action_type) !!}</div>
                            <div>
                                <strong>{{ humanizeAction($action->action_type) }}</strong><br>
                                <small>{{ \Carbon\Carbon::parse($action->created_at)->format('Y-m-d H:i') }}</small>
                            </div>
                        </div>
                        <div class="right">
                            @if($action->amount)
                                <span class="income">+{{ number_format($action->amount, 2) }} ج.م</span>
                            @endif
                            @if($action->expense_amount)
                                <span class="expense">-{{ number_format($action->expense_amount, 2) }} ج.م</span>
                            @endif
                            <button class="btn btn-sm toggle-details">تفاصيل ▾</button>
                        </div>
                    </div>

                    <div class="action-body">
                        <p><strong>النوع:</strong> {{ $action->action_type }}</p>
                        <p><strong>الفاتورة:</strong>
                            @if($action->invoice)
                                <a href="{{ route('invoices.show', $action->invoice->id) }}">#{{ $action->invoice->id }}</a>
                            @else
                                —
                            @endif
                        </p>
                        <p><strong>المبلغ:</strong> {{ number_format($action->amount ?? 0, 2) }} ج.م</p>
                        <p><strong>ملاحظات:</strong> {!! $action->notes ? nl2br(e($action->notes)) : '—' !!}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<style>
:root {
    --bg-dark: #0e0e11;
    --card-bg: #16161a;
    --text-main: #f1f1f1;
    --text-dim: #c5c5c5;
    --accent: #ffb84d;
    --accent-dark: #d49a3d;
    --success: #8ef58a;
    --danger: #ff7676;
    --border: rgba(255,255,255,0.1);
}

body {
    background: var(--bg-dark);
    color: var(--text-main);
    font-family: "Cairo", sans-serif;
}

.shift-hive-container {
    max-width: 980px;
    margin: 40px auto;
    padding: 20px;
}

.card-header h2 {
    color: var(--accent);
    font-size: 22px;
}

.badge {
    background: var(--accent-dark);
    color: #000;
    padding: 4px 10px;
    border-radius: 20px;
    font-weight: bold;
}

.summary-row {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.summary-box {
    flex: 1;
    background: var(--card-bg);
    padding: 12px;
    border-radius: 12px;
    border: 1px solid var(--border);
    text-align: center;
    font-weight: bold;
    box-shadow: 0 4px 14px rgba(0,0,0,0.4);
}
.summary-box.income span { color: var(--success); }
.summary-box.expense span { color: var(--danger); }
.summary-box.net span { color: var(--accent); }

.filter-bar {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}
.input-dark {
    background: #1c1c20;
    border: 1px solid var(--border);
    color: var(--text-main);
    border-radius: 10px;
    padding: 10px;
    flex: 1;
}
.btn {
    background: var(--accent);
    color: #000;
    border: none;
    border-radius: 10px;
    padding: 10px 14px;
    cursor: pointer;
    font-weight: bold;
    transition: 0.2s;
}
.btn:hover { background: var(--accent-dark); transform: translateY(-2px); }

.btn-outline {
    background: transparent;
    color: var(--accent);
    border: 1px solid var(--accent);
}

.actions-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.action-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 14px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.4);
}
.action-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.icon svg {
    stroke: var(--accent);
}
.action-body {
    display: none;
    border-top: 1px dashed var(--border);
    margin-top: 10px;
    padding-top: 10px;
    color: var(--text-dim);
}
.action-card.show .action-body {
    display: block;
    animation: fadeIn .25s ease;
}
.income { color: var(--success); }
.expense { color: var(--danger); }
.no-actions {
    text-align: center;
    padding: 30px;
    color: var(--text-dim);
}
@keyframes fadeIn {
    from {opacity: 0; transform: translateY(5px);}
    to {opacity: 1; transform: none;}
}
</style>

@php
function humanizeAction($type) {
    return match ($type) {
        'new_subscription' => 'اشتراك جديد',
        'renew_subscription' => 'تجديد اشتراك',
        'end_session' => 'إنهاء جلسة',
        'expense_note' => 'مذكرة مصروف',
        'add_booking' => 'إضافة حجز',
        'end_booking' => 'إنهاء حجز',
        'separate_sale' => 'بيع منفصل',
        default => $type,
    };
}
function actionIcon($type) {
    return match ($type) {
        'new_subscription' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 2v6"></path><path d="M20 12H4"></path><path d="M12 22v-6"></path></svg>',
        'renew_subscription' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M21 12a9 9 0 1 0-3 6.7"></path><path d="M21 12v6h-6"></path></svg>',
        'end_session' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M16 17l5-5-5-5"></path></svg>',
        'expense_note' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M21 10v6a2 2 0 0 1-2 2H5"></path></svg>',
        'add_booking' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="4" width="18" height="18" rx="2"></rect></svg>',
        default => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="10"/></svg>',
    };
}
@endphp

<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggleCollapse = document.getElementById('toggleCollapse');
    const searchInput = document.getElementById('searchInput');
    const filterType = document.getElementById('filterType');
    const exportCsv = document.getElementById('exportCsv');

    document.querySelectorAll('.toggle-details').forEach(btn => {
        btn.addEventListener('click', () => {
            const card = btn.closest('.action-card');
            card.classList.toggle('show');
            btn.textContent = card.classList.contains('show') ? 'تفاصيل ▴' : 'تفاصيل ▾';
        });
    });

    let expanded = false;
    toggleCollapse.addEventListener('click', () => {
        expanded = !expanded;
        document.querySelectorAll('.action-card').forEach(card => {
            card.classList.toggle('show', expanded);
        });
        document.querySelectorAll('.toggle-details').forEach(btn => {
            btn.textContent = expanded ? 'تفاصيل ▴' : 'تفاصيل ▾';
        });
    });

    const applyFilter = () => {
        const q = searchInput.value.trim().toLowerCase();
        const t = filterType.value.trim();
        document.querySelectorAll('.action-card').forEach(card => {
            const text = (card.dataset.search || '').toLowerCase();
            const type = card.dataset.actionType;
            const visible = (!q || text.includes(q)) && (!t || type === t);
            card.style.display = visible ? '' : 'none';
        });
    };
    searchInput.addEventListener('input', applyFilter);
    filterType.addEventListener('change', applyFilter);

    exportCsv.addEventListener('click', () => {
        const rows = [['id','type','time','amount','expense_amount','notes']];
        document.querySelectorAll('.action-card').forEach(card => {
            if (card.style.display === 'none') return;
            const id = card.dataset.id;
            const type = card.dataset.actionType;
            const time = card.querySelector('small')?.textContent.trim();
            const amount = card.querySelector('.income')?.textContent.replace(/[^\d.]/g,'') || '';
            const expense = card.querySelector('.expense')?.textContent.replace(/[^\d.]/g,'') || '';
            const notes = card.querySelector('.action-body p:last-child')?.textContent.trim();
            rows.push([id,type,time,amount,expense,notes]);
        });
        const csv = rows.map(r => r.map(v => `"${v}"`).join(',')).join('\n');
        const blob = new Blob([csv], {type:'text/csv'});
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url; a.download = 'shift_{{ $shift->id }}.csv';
        a.click(); a.remove();
    });
});
</script>
@endsection
