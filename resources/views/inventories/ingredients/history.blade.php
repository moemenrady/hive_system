@extends('layouts.app_page')

@section('title', 'سجل حركات الخامات المخزنية')

@section('style')
<style>
    :root {
        --bg-dark: #0e0e11;
        --card-bg: #16161a;
        --card-hover: #1f1f25;
        --text-main: #f1f1f1;
        --text-dim: #9ca3af;
        --accent: #ffb84d;
        --accent-dark: #d49a3d;
        --success: #10b981;
        --danger: #ef4444;
        --info: #0dcaf0;
        --border-color: rgba(255, 255, 255, 0.08);
    }

    /* أنيميشنز الدخول */
    @keyframes slideInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .animate-up {
        animation: slideInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        opacity: 0;
    }
    .delay-1 { animation-delay: 0.1s; }
    .delay-2 { animation-delay: 0.2s; }
    .delay-3 { animation-delay: 0.3s; }

    /* الإحصائيات العلوية */
    .stat-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.25rem;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 4px; height: 100%;
    }
    .stat-card.in::before { background: var(--success); }
    .stat-card.out::before { background: var(--danger); }
    .stat-card.alert::before { background: var(--accent); }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        background: var(--card-hover);
    }

    /* شريط البحث والفلاتر الذكية */
    .smart-filter-bar {
        background: linear-gradient(145deg, #1a1a1d, #25252a);
        border-radius: 16px;
        padding: 1rem;
        border: 1px solid var(--border-color);
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        align-items: center;
        justify-content: space-between;
    }

    .input-dark {
        background: #111114;
        border: 1px solid var(--border-color);
        color: var(--text-main);
        border-radius: 10px;
        padding: 10px 15px;
        width: 100%;
        transition: 0.3s;
    }
    .input-dark:focus {
        border-color: var(--accent);
        outline: none;
        box-shadow: 0 0 0 3px rgba(255, 184, 77, 0.1);
    }

    /* أزرار التحليل */
    .btn-glow {
        background: rgba(255, 184, 77, 0.1);
        color: var(--accent);
        border: 1px solid var(--accent);
        border-radius: 10px;
        padding: 8px 16px;
        font-weight: bold;
        transition: 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-glow:hover {
        background: var(--accent);
        color: #000;
        box-shadow: 0 0 15px rgba(255, 184, 77, 0.4);
        transform: scale(1.02);
    }
    .btn-glow.danger {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
        border-color: var(--danger);
    }
    .btn-glow.danger:hover {
        background: var(--danger);
        color: #fff;
        box-shadow: 0 0 15px rgba(239, 68, 68, 0.4);
    }

    /* كروت الحركات */
    .tx-card {
        background: var(--card-bg);
        border-radius: 14px;
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
        border: 1px solid var(--border-color);
        transition: all 0.2s ease;
    }
    .tx-card:hover {
        background: var(--card-hover);
        border-color: rgba(255, 255, 255, 0.2);
    }

    .tx-icon {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .tx-in .tx-icon { background: rgba(16, 185, 129, 0.1); color: var(--success); }
    .tx-out .tx-icon { background: rgba(239, 68, 68, 0.1); color: var(--danger); }

    .tx-details { flex: 1; padding: 0 15px; }
    .tx-amount { text-align: left; }

    .badge-custom {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: bold;
        letter-spacing: 0.5px;
    }
    .badge-unit { background: #2a2a35; color: var(--text-dim); }

    /* Responsive */
    @media (max-width: 768px) {
        .tx-card { flex-direction: column; align-items: flex-start; gap: 10px; }
        .tx-amount { 
            width: 100%; 
            text-align: right; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border-top: 1px dashed var(--border-color); 
            padding-top: 10px; 
            margin-top: 5px; 
        }
        .tx-details { padding: 0 10px; }
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4" style="max-width: 1200px;">

    <div class="row g-3 mb-4 animate-up">
        <div class="col-6 col-md-3">
            <div class="stat-card in h-100">
                <div class="text-dim small mb-2"><i class="fas fa-boxes me-1"></i> إجمالي الوارد (الشهر)</div>
                <div class="h4 text-success mb-0 fw-bold">+ {{ $stats['total_in'] ?? 0 }} <span class="fs-6 text-dim">حركة</span></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card out h-100">
                <div class="text-dim small mb-2"><i class="fas fa-fire me-1"></i> إجمالي المنصرف (الشهر)</div>
                <div class="h4 text-danger mb-0 fw-bold">- {{ $stats['total_out'] ?? 0 }} <span class="fs-6 text-dim">حركة</span></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card alert h-100">
                <div class="text-dim small mb-2"><i class="fas fa-exclamation-triangle text-warning me-1"></i> خامات تحت الحد الأدنى</div>
                <div class="h4 text-warning mb-0 fw-bold">{{ $stats['low_stock_count'] ?? 0 }} <span class="fs-6 text-dim">خامة</span></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card h-100" style="border-inline-start: 4px solid var(--info);">
                <div class="text-dim small mb-2"><i class="fas fa-chart-pie text-info me-1"></i> المنتجات المرتبطة بالخامات</div>
                <div class="h4 text-info mb-0 fw-bold">{{ $stats['total_recipes'] ?? 0 }} <span class="fs-6 text-dim">وصفة</span></div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-4 animate-up delay-1">
        <button class="btn-glow" onclick="showTopIngredient()">
            <i class="fas fa-crown"></i> الخامة الأكثر استهلاكاً
        </button>
        <button class="btn-glow danger" onclick="showCostlyProduct()">
            <i class="fas fa-file-invoice-dollar"></i> المنتج الأعلى استهلاكاً للخامات
        </button>
    </div>

    <div class="smart-filter-bar mb-4 animate-up delay-2">
        <div class="flex-grow-1" style="min-width: 250px;">
            <div class="position-relative">
                <i class="fas fa-search position-absolute text-dim" style="top: 50%; right: 15px; transform: translateY(-50%);"></i>
                <input type="text" id="smartSearch" class="input-dark" style="padding-right: 40px;" placeholder="ابحث باسم الخامة... أو باسم المنتج (مثال: لاتيه)...">
            </div>
        </div>
        <div style="min-width: 150px;">
            <select id="typeFilter" class="input-dark">
                <option value="all">كل الحركات</option>
                <option value="in">الوارد (إضافة رصيد)</option>
                <option value="out">المنصرف (استهلاك/مبيعات)</option>
            </select>
        </div>
    </div>

    <div class="history-container animate-up delay-3" id="historyList">
        @forelse($groupedStocks as $date => $stocks)
            <div class="mb-4 date-group">
                <div class="d-flex align-items-center gap-3 mb-3 px-2">
                    <div class="badge bg-dark border border-secondary text-dim px-3 py-2 rounded-pill">
                        <i class="far fa-calendar-alt text-warning me-1"></i>
                        {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}
                    </div>
                    <hr class="flex-grow-1 border-secondary opacity-25">
                </div>

                <div class="space-y-2">
                    @foreach ($stocks as $stock)
                        @php
                            // استخراج المتغيرات الأساسية
                            $isIn = $stock->type === 'in';
                            $rowClass = $isIn ? 'tx-in' : 'tx-out';
                            $icon = $isIn ? 'fa-box-open' : 'fa-coffee';
                            $sign = $isIn ? '+' : '-';
                            $amountColor = $isIn ? 'text-success' : 'text-danger';

                            // استخراج آمن للبيانات لتجنب خطأ Attempt to read property on null
                            $ingredientName = $stock->ingredient?->name ?? 'خامة غير مسجلة/محذوفة';
                            $unitSymbol = $stock->unit?->symbol ?? '';
                            
                            // استخراج آمن للمنتجات المرتبطة (لتشغيل البحث الذكي)
                            $relatedProducts = '';
                            if ($stock->ingredient && $stock->ingredient->recipes) {
                                $relatedProducts = $stock->ingredient->recipes
                                    ->map(fn($r) => $r->product?->name)
                                    ->filter()
                                    ->implode(' ');
                            }

                            // تجميع بيانات البحث
                            $searchData = $ingredientName . ' ' . ($stock->note ?? '') . ' ' . $relatedProducts;
                        @endphp
                        
                        <div class="tx-card {{ $rowClass }} stock-row" data-search="{{ strtolower($searchData) }}" data-type="{{ $stock->type }}">
                            <div class="d-flex align-items-center w-100">
                                <div class="tx-icon">
                                    <i class="fas {{ $icon }}"></i>
                                </div>

                                <div class="tx-details">
                                    <h5 class="mb-1 text-white fw-bold fs-6">
                                        {{ $ingredientName }}
                                        @if($unitSymbol)
                                            <span class="badge-custom badge-unit ms-2">{{ $unitSymbol }}</span>
                                        @endif
                                    </h5>
                                    <div class="text-dim small d-flex gap-3 flex-wrap">
                                        <span><i class="far fa-clock me-1"></i> {{ $stock->created_at->format('h:i A') }}</span>
                                        @if ($stock->note)
                                            <span class="text-info opacity-75"><i class="fas fa-info-circle me-1"></i> {{ $stock->note }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="tx-amount">
                                    <div class="h5 mb-0 fw-bold font-monospace {{ $amountColor }}">
                                        {{ $sign }} {{ floatval($stock->amount) }}
                                    </div>
                                    <div class="small text-dim mt-1" style="font-size: 11px;">
                                        رصيد: <span class="text-white text-decoration-line-through opacity-50">{{ floatval($stock->before_amount) }}</span>
                                        <i class="fas fa-arrow-left fa-sm mx-1 text-warning"></i>
                                        <span class="text-white fw-bold">{{ floatval($stock->after_amount) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center p-5 card border-0 text-dim bg-dark rounded-4" style="border: 1px dashed rgba(255,255,255,0.1) !important;">
                <i class="fas fa-box-open fs-1 mb-3 text-secondary"></i>
                <h5>لا توجد حركات مخزنية مسجلة</h5>
            </div>
        @endforelse
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('smartSearch');
        const typeFilter = document.getElementById('typeFilter');
        const stockRows = document.querySelectorAll('.stock-row');
        const dateGroups = document.querySelectorAll('.date-group');

        function filterHistory() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            const filterType = typeFilter.value;

            dateGroups.forEach(group => {
                let hasVisibleRows = false;
                const rows = group.querySelectorAll('.stock-row');

                rows.forEach(row => {
                    const searchData = row.getAttribute('data-search');
                    const rowType = row.getAttribute('data-type');

                    const matchesSearch = searchData.includes(searchTerm);
                    const matchesType = (filterType === 'all') || (rowType === filterType);

                    if (matchesSearch && matchesType) {
                        row.style.display = 'flex';
                        hasVisibleRows = true;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // إخفاء يوم بالكامل إذا لم توجد حركات مطابقة داخله
                group.style.display = hasVisibleRows ? 'block' : 'none';
            });
        }

        searchInput.addEventListener('input', filterHistory);
        typeFilter.addEventListener('change', filterHistory);
    });

    // نموذج لأزرار التحليل (تستطيع لاحقاً ربطها بداتا حقيقية عبر AJAX)
    function showTopIngredient() {
        Swal.fire({
            title: '👑 الخامة الأكثر استهلاكاً',
            html: `
            <div class="text-start mt-3">
                <h3 class="text-warning text-center fw-bold mb-3">حبوب البن (Espresso Beans)</h3>
                <ul class="list-group list-group-flush bg-transparent">
                    <li class="list-group-item bg-transparent text-white border-secondary">إجمالي المستهلك: <strong class="text-danger">45.5 KG</strong></li>
                    <li class="list-group-item bg-transparent text-white border-secondary">أكثر يوم استهلاكاً: <strong>الخميس</strong></li>
                </ul>
            </div>
            `,
            background: '#16161a',
            color: '#fff',
            confirmButtonColor: '#ffb84d',
            confirmButtonText: 'حسناً'
        });
    }

    function showCostlyProduct() {
        Swal.fire({
            title: '💸 المنتج الأعلى تكلفة خامات',
            html: `
            <div class="text-start mt-3">
                <h3 class="text-danger text-center fw-bold mb-3">سبانيش لاتيه كبير</h3>
                <p class="text-center text-dim">يستهلك 5 خامات مختلفة بكميات كبيرة</p>
                <ul class="list-group list-group-flush bg-transparent">
                    <li class="list-group-item bg-transparent text-white border-secondary d-flex justify-content-between"><span>بن اسبريسو</span> <span>18 g</span></li>
                    <li class="list-group-item bg-transparent text-white border-secondary d-flex justify-content-between"><span>حليب مكثف</span> <span>30 ml</span></li>
                    <li class="list-group-item bg-transparent text-white border-secondary d-flex justify-content-between"><span>حليب طازج</span> <span>200 ml</span></li>
                </ul>
            </div>
            `,
            background: '#16161a',
            color: '#fff',
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'إغلاق'
        });
    }
</script>
@endsection

