@extends('layouts.app_page')

@section('title', isset($employee) ? 'تعديل موظف' : 'إضافة موظف')

@section('style')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
    .ts-control { background-color: #1a1a1d !important; color: #fff !important; border-radius: 10px !important; padding: 12px !important; border: 1px solid var(--border-light) !important; }
</style>
@endsection

@section('content')
<div class="container mx-auto p-2 md:p-4 max-w-2xl">
    <div class="title animate__animated animate__fadeInDown">
        <i class="fas fa-user-plus me-2 text-warning"></i> {{ isset($employee) ? 'تعديل بيانات الموظف' : 'تسجيل موظف جديد' }}
    </div>

    <form action="{{ isset($employee) ? route('employees.update', $employee->id) : route('employees.store') }}" method="POST">
        @csrf
        @if(isset($employee)) @method('PUT') @endif

        <div class="card p-4 space-y-4 animate__animated animate__fadeInUp">
            <div class="mb-3">
                <label class="small text-dim mb-2">اسم الموظف بالكامل *</label>
                <input type="text" name="name" value="{{ $employee->name ?? old('name') }}" required
                       class="form-control input-box shadow-none" placeholder="محمد علي...">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="small text-dim mb-2">رقم الهاتف</label>
                    <input type="text" name="phone" value="{{ $employee->phone ?? old('phone') }}"
                           class="form-control input-box shadow-none" placeholder="010...">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="small text-dim mb-2">الراتب الأساسي للشهر</label>
                    <div class="input-group">
                        <input type="number" name="salary" value="{{ $employee->salary ?? old('salary') }}" required
                               class="form-control input-box shadow-none" placeholder="5000">
                        <span class="input-group-text bg-dark border-secondary text-dim">ج.م</span>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="small text-dim mb-2">ربط بحساب مستخدم (اختياري)</label>
                <select id="user_select" name="user_id">
                    <option value="">لا يوجد حساب مرتبط</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ (isset($employee) && $employee->user_id == $user->id) ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="pt-4">
                <button type="submit" class="edit-btn w-100 py-3">
                    <i class="fas fa-save me-1"></i> حفظ البيانات
                </button>
            </div>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    new TomSelect('#user_select', { create: false, placeholder: 'ابحث عن ايميل او اسم المستخدم...' });
</script>
@endsection