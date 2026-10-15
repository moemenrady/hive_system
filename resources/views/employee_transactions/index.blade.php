@extends('layouts.app_page')

@section('title', 'سجل المسحوبات والسلفيات')

@section('style')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
    <style>
        .filter-card {
            background: #1e1e22;
            border: 1px solid var(--border-light);
            border-radius: 15px;
        }
        .flatpickr-input {
            background-color: #1a1a1d !important;
            color: white !important;
            border: 1px solid var(--border-light) !important;
        }
        .transaction-card {
            transition: all 0.3s ease;
            border-right: 4px solid transparent;
        }
        .transaction-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
        }
        .type-purchase { border-right-color: var(--accent) !important; }
        .type-advance { border-right-color: #ff4444 !important; }
    </style>
@endsection

@section('content')
<div class="container mx-auto p-2 md:p-4 max-w-6xl">
    <div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeInDown">
        <div class="title m-0">
            <i class="fas fa-file-invoice-dollar me-2 text-warning"></i> سجل العمليات المالية
        </div>
        <a href="{{ route('employee_transactions.create') }}" class="edit-btn text-decoration-none">
            <i class="fas fa-plus-circle me-1"></i> تسجيل عملية جديدة
        </a>
    </div>

    <div class="card filter-card p-4 mb-4 animate__animated animate__fadeIn">
        <form action="{{ route('employee_transactions.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="small text-dim mb-1">اسم الموظف</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       class="form-control input-box shadow-none" placeholder="ابحث عن اسم الموظف...">
            </div>

            <div class="col-md-2">
                <label class="small text-dim mb-1">نوع العملية</label>
                <select name="type" class="form-select input-box shadow-none">
                    <option value="">الكل</option>
                    <option value="purchase" {{ request('type') == 'purchase' ? 'selected' : '' }}>🛒 مسحوبات</option>
                    <option value="advance" {{ request('type') == 'advance' ? 'selected' : '' }}>💰 سلفيات</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="small text-dim mb-1">من تاريخ</label>
                <input type="text" name="date_from" id="date_from" value="{{ request('date_from') }}" 
                       class="form-control input-box shadow-none" placeholder="اختر تاريخ">
            </div>

            <div class="col-md-2">
                <label class="small text-dim mb-1">إلى تاريخ</label>
                <input type="text" name="date_to" id="date_to" value="{{ request('date_to') }}" 
                       class="form-control input-box shadow-none" placeholder="اختر تاريخ">
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn w-100" style="background: var(--accent); color: #000; font-weight: bold; border-radius: 10px; height: 45px;">
                    <i class="fas fa-filter me-1"></i> تصفية
                </button>
            </div>
        </form>
    </div>

    <div class="row g-3">
        @forelse($transactions as $trans)
        <div class="col-md-4 animate__animated animate__zoomIn">
            <div class="card p-4 h-100 transaction-card {{ $trans->type == 'purchase' ? 'type-purchase' : 'type-advance' }}">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="mb-0 text-white">{{ $trans->employee->name }}</h5>
                    <span class="small text-dim">{{ $trans->created_at->format('Y/m/d') }}</span>
                </div>

                <div class="mb-3">
                    @if($trans->type == 'purchase')
                        <span class="badge bg-soft-warning text-warning mb-2" style="background: rgba(255,184,77,0.1)">
                            <i class="fas fa-shopping-cart me-1"></i> مسحوبات منتجات
                        </span>
                        <div class="text-white-50 small">
                            {{ $trans->product->name ?? 'منتج غير معروف' }} 
                            <span class="text-accent mx-1">×</span> {{ $trans->quantity }}
                        </div>
                    @else
                        <span class="badge bg-soft-danger text-danger mb-2" style="background: rgba(255,68,68,0.1)">
                            <i class="fas fa-hand-holding-usd me-1"></i> سلفة مالية
                        </span>
                        <div class="text-white-50 small text-truncate" title="{{ $trans->note }}">
                            السبب: {{ $trans->note ?? 'لم يذكر' }}
                        </div>
                    @endif
                </div>

                <div class="mt-auto pt-3 border-top border-secondary d-flex justify-content-between align-items-center">
                    <div class="fw-bold fs-5 text-accent">
                        {{ number_format($trans->amount, 2) }} <small class="small">ج.م</small>
                    </div>
                    
                    <div class="dropdown">
                        <button class="btn btn-sm text-dim" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
                            <li><a class="dropdown-item text-info" href="#"><i class="fas fa-print me-2"></i>طباعة وصل</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('employee_transactions.destroy', $trans->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                    @csrf @method('DELETE')
                                    <button class="dropdown-item text-danger"><i class="fas fa-trash-alt me-2"></i>حذف السجل</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5 animate__animated animate__fadeIn">
            <i class="fas fa-search-minus fa-3x text-dim mb-3"></i>
            <h5 class="text-dim">لا توجد سجلات تطابق بحثك حالياً</h5>
        </div>
        @endforelse
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $transactions->appends(request()->input())->links() }}
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ar.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const config = {
            locale: "ar",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "F j, Y",
            disableMobile: "true"
        };
        flatpickr("#date_from", config);
        flatpickr("#date_to", config);
    });
</script>
@endsection