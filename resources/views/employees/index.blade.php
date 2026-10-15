@extends('layouts.app_page')

@section('title', 'إدارة الموظفين')

@section('content')
    <div class="container mx-auto p-2 md:p-4 max-w-6xl">
        <div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeInDown">
            <div class="title m-0">
                <i class="fas fa-users-cog me-2 text-warning"></i> قائمة الموظفين
            </div>
            <div class="d-flex gap-2">

                <a href="{{ route('employees.discountPercent') }}" class="btn text-dark fw-bold"
                    style="
                            background: linear-gradient(135deg, #ffcc00, #ff9900);
                            border-radius: 12px;
                            padding: 10px 18px;
                            box-shadow: 0 4px 15px rgba(255, 170, 0, 0.35);
                            transition: 0.3s;
                    "
                    onmouseover="this.style.transform='translateY(-2px) scale(1.03)'"
                    onmouseout="this.style.transform='translateY(0) scale(1)'">
                    <i class="fas fa-percent me-1"></i>
                    نسبة الخصم على المشاريب
                </a>

                <a href="{{ route('employees.create') }}" class="edit-btn text-decoration-none">
                    <i class="fas fa-plus-circle me-1"></i> إضافة موظف جديد
                </a>

            </div>
        </div>

        <div class="card p-3 mb-4 animate__animated animate__fadeIn">
            <form action="{{ route('employees.index') }}" method="GET" class="row g-2">
                <div class="col-md-10">
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="form-control input-box shadow-none" placeholder="ابحث باسم الموظف أو رقم الهاتف...">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn w-100 h-100"
                        style="background: var(--accent); color: #000; font-weight: bold; border-radius: 10px;">
                        <i class="fas fa-search"></i> بحث
                    </button>
                </div>
            </form>
        </div>

        <div class="row g-3">
            @foreach ($employees as $emp)
                <div class="col-md-4 animate__animated animate__zoomIn">
                    <div class="card p-4 h-100 relative overflow-hidden" style="border-right: 4px solid var(--accent)">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="mb-1 text-white">{{ $emp->name }}</h5>
                                <p class="small text-dim mb-0"><i class="fas fa-phone-alt me-1"></i>
                                    {{ $emp->phone ?? 'بدون رقم' }}</p>
                            </div>
                            <span class="badge bg-dark text-warning border border-warning">{{ number_format($emp->salary) }}
                                ج.م</span>
                        </div>

                        <div class="mt-3 pt-3 border-top border-secondary d-flex justify-content-between">
                            <a href="{{ route('employees.show', $emp->id) }}" class="btn btn-sm"
                                style="color: var(--accent-light)">
                                <i class="fas fa-eye me-1"></i> التفاصيل والحساب
                            </a>
                            <div class="dropdown">
                                <button class="btn btn-sm text-dim" data-bs-toggle="dropdown"><i
                                        class="fas fa-ellipsis-v"></i></button>
                                <ul class="dropdown-menu dropdown-menu-dark">
                                    <li><a class="dropdown-item" href="{{ route('employees.edit', $emp->id) }}">تعديل
                                            البيانات</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $employees->links() }}
        </div>
    </div>
@endsection
