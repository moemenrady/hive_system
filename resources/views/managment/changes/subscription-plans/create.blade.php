@extends('layouts.app_page_admin')

@section('content')
    <div class="expenses-wrapper">

        <!-- فورم إضافة خطة اشتراك -->
        <div class="expense-form">
            <h2>✚ إضافة خطة اشتراك جديدة</h2>
            <form action="{{ route('subscription-plan.store') }}" method="POST" novalidate>
                @csrf

                <div class="form-group">
                    <label>اسم الخطة <small class="muted">(مثال: أسبوعي)</small></label>
                    <input type="text" name="name" placeholder="ادخل اسم الخطة" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="small text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>اسم المُضيف / المُسجل <small class="muted">(اختياري)</small></label>
                    <input type="text" name="setter_name" placeholder="اسم المُضيف / المُسجل"
                        value="{{ old('setter_name') }}">
                    @error('setter_name')
                        <div class="small text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>عدد الزيارات المسموح بها</label>
                    <input type="number" name="visits_count" placeholder="عدد الزيارات" value="{{ old('visits_count') }}"
                        min="1" required>
                    @error('visits_count')
                        <div class="small text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>مدة الأيام المسموح بها</label>
                    <input type="number" name="duration_days" placeholder="عدد الأيام" value="{{ old('duration_days') }}"
                        min="1" required>
                    @error('duration_days')
                        <div class="small text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>السعر</label>
                    <input type="number" step="0.01" name="price" placeholder="السعر" value="{{ old('price') }}"
                        min="0" required>
                    @error('price')
                        <div class="small text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn-submit">💾 حفظ الخطة</button>
            </form>
        </div>

        <!-- ليستة خطط الاشتراك -->
        <div class="expense-list">
            <div class="list-header">
                <h3>📋 قائمة خطط الاشتراك</h3>
            </div>

            <div class="cards">
                @forelse($records as $plan)
                    <div class="card-content">
                        <div class="content-left">
                            <h4>✴ {{ $plan->name }}</h4>
                            <p class="meta small">بواسطة: {{ $plan->setter_name ?? '---' }}</p>
                            <p class="meta small">عدد الزيارات: {{ $plan->visits_count }}</p>
                            <p class="meta small">مدة الأيام: {{ $plan->duration_days }}</p>
                            <p class="meta small">السعر: {{ number_format($plan->price, 2) }} جنيه</p>
                        </div>

                        <div class="content-right">
                            <span class="date small">🕒 {{ $plan->created_at->diffForHumans() }}</span>
                            <div class="actions-group mt-3">
                                <button type="button" class="btn-action btn-edit"
                                    onclick="openEditPlanModal({{ json_encode($plan) }})">
                                    ✏️ تعديل
                                </button>
                                <form action="{{ route('subscription-plan.destroy', $plan->id) }}" method="POST"
                                    onsubmit="return confirm('⚠️ هل أنت متأكد من حذف خطة ({{ $plan->name }})؟\nفي حال وجود اشتراكات نشطة سابقة يفضل تعديل السعر فقط.');"
                                    style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-delete">
                                        🗑️ حذف
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="empty">🚀 لا توجد خطط حتى الآن</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- مودال تعديل خطة الاشتراك -->
    <div class="modal fade" id="editPlanModal" tabindex="-1" aria-labelledby="editPlanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 18px; border: 1px solid var(--accent); background: #ffffff;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="editPlanModalLabel" style="color: #333;">✏️ تعديل خطة الاشتراك وتحديث السعر</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <form id="editPlanForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body pt-3">
                        <div class="form-group mb-3">
                            <label class="fw-bold">اسم الخطة <small class="text-muted">(مثال: أسبوعي - شهري)</small></label>
                            <input type="text" name="name" id="edit_name" class="form-control styled-input" required>
                        </div>

                        <div class="form-group mb-3">
                            <label class="fw-bold">السعر الجديد (جنيه) <small class="text-success fw-bold">★ السعر المحدث</small></label>
                            <input type="number" step="0.01" name="price" id="edit_price" class="form-control styled-input" min="0" required style="border-color: #28a745; font-size: 1.15rem; font-weight: bold;">
                        </div>

                        <div class="form-group mb-3">
                            <label class="fw-bold">عدد الزيارات المسموح بها</label>
                            <input type="number" name="visits_count" id="edit_visits_count" class="form-control styled-input" min="1" required>
                        </div>

                        <div class="form-group mb-3">
                            <label class="fw-bold">مدة الأيام المسموح بها</label>
                            <input type="number" name="duration_days" id="edit_duration_days" class="form-control styled-input" min="1" required>
                        </div>

                        <div class="form-group mb-2">
                            <label class="fw-bold">اسم المُضيف / المُسجل <small class="text-muted">(اختياري)</small></label>
                            <input type="text" name="setter_name" id="edit_setter_name" class="form-control styled-input">
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 10px; font-weight: 600;">إلغاء</button>
                        <button type="submit" class="btn btn-primary" style="background: var(--accent); color: #333; border: none; border-radius: 10px; font-weight: 700; padding: 8px 24px;">💾 حفظ التعديلات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openEditPlanModal(plan) {
            const form = document.getElementById('editPlanForm');
            form.action = "{{ url('/managment-subscription-plans') }}/" + plan.id;
            document.getElementById('edit_name').value = plan.name;
            document.getElementById('edit_price').value = plan.price;
            document.getElementById('edit_visits_count').value = plan.visits_count;
            document.getElementById('edit_duration_days').value = plan.duration_days;
            document.getElementById('edit_setter_name').value = plan.setter_name || '';

            const modal = new bootstrap.Modal(document.getElementById('editPlanModal'));
            modal.show();
        }
    </script>
@endsection

@section('style')
    <style>
        :root {
            --accent: #E6C7FF;
            --bg: #F6F7FB;
            --card: #FFFFFF;
            --muted: #777;
            --max-width: 1100px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: "Cairo", sans-serif;
            margin: 0;
            color: #333;
            background: var(--bg);
        }

        .container,
        .expenses-wrapper {
            max-width: var(--max-width);
            margin: 0 auto;
            padding: 28px;
        }

        .expenses-wrapper {
            display: flex;
            flex-direction: column;
            gap: 22px;
            padding-bottom: 40px;
        }

        .expense-form,
        .expense-list {
            background: var(--card);
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 10px 26px rgba(0, 0, 0, 0.06);
            border: 1px solid #f1ecef;
            transition: transform .28s, box-shadow .28s;
        }

        .expense-form:hover,
        .expense-list:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.08);
        }

        .expense-form h2,
        .expense-list h3 {
            margin: 0 0 14px;
            font-size: 20px;
        }

        .muted {
            color: var(--muted);
            font-weight: 500;
            font-size: 13px;
        }

        .form-group {
            margin-bottom: 14px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
        }

        input[type="text"],
        input[type="number"],
        select.styled-select {
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid #e9e6e6;
            font-size: 15px;
            background: #fff;
            transition: box-shadow .16s, transform .12s, border-color .12s;
            appearance: none;
        }

        input:focus,
        select.styled-select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 10px 30px rgba(230, 199, 255, 0.12);
            transform: translateY(-1px);
        }

        .btn-submit {
            display: block;
            width: 100%;
            background: var(--accent);
            color: #fff;
            border: 0;
            padding: 12px 14px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 8px 22px rgba(217, 177, 171, 0.12);
            transition: transform .18s ease, box-shadow .18s ease;
            min-height: 44px;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
        }

        .list-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
        }

        .cards {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 6px;
        }

        .card-content {
            background: #FBFBFF;
            padding: 14px;
            border-radius: 12px;
            border-left: 6px solid var(--accent);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
            transition: transform .22s, box-shadow .22s;
        }

        .card-content:hover {
            transform: translateY(-6px);
            box-shadow: 0 14px 34px rgba(0, 0, 0, 0.08);
        }

        .card-content h4 {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
        }

        .card-content .meta {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 13px;
        }

        .content-left {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .content-right {
            text-align: right;
            font-size: 13px;
            color: var(--muted);
            min-width: 86px;
        }

        .empty {
            color: #999;
            text-align: center;
            padding: 18px;
            font-style: italic;
        }

        .small {
            font-size: 13px;
            color: var(--muted);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(10px)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        .expense-form,
        .expense-list,
        .card-content {
            animation: slideUp .6s both;
        }

        @media(min-width:900px) {
            .expenses-wrapper {
                flex-direction: row;
                gap: 28px;
                align-items: flex-start;
            }

            .expense-form {
                flex: 0 0 380px;
                padding: 22px;
            }

            .expense-list {
                flex: 1;
                padding: 22px;
            }
        }

        @media(min-width:700px) and (max-width:899px) {
            .expenses-wrapper {
                padding: 28px;
            }

            .expense-form {
                flex: 0 0 360px;
            }
        }

        @media(max-width:420px) {

            .container,
            .expenses-wrapper {
                padding: 16px;
            }

            .expense-form,
            .expense-list {
                padding: 14px;
                border-radius: 12px;
            }

            .expense-form h2,
            .expense-list h3 {
                font-size: 18px;
            }

            input[type="text"],
            input[type="number"],
            select.styled-select {
                padding: 10px 12px;
                font-size: 14px;
            }

            .btn-submit {
                padding: 10px;
                font-size: 15px;
                min-height: 40px;
            }

            .card-content {
                padding: 12px;
            }

            .content-right {
                min-width: 70px;
                font-size: 12px;
            }
        }

        .actions-group {
            display: flex;
            gap: 8px;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-edit {
            background: #e6c7ff;
            color: #2c2c2c;
            border: 1px solid #d4a5fa;
        }

        .btn-edit:hover {
            background: #d8aaff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(216, 170, 255, 0.4);
        }

        .btn-delete {
            background: #ffe5e5;
            color: #d32f2f;
            border: 1px solid #ffcccc;
        }

        .btn-delete:hover {
            background: #ffcdd2;
            color: #b71c1c;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 100, 100, 0.25);
        }

        .styled-input {
            width: 100%;
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid #ddd;
            font-family: inherit;
            transition: all 0.2s;
        }

        .styled-input:focus {
            border-color: var(--accent);
            outline: none;
            box-shadow: 0 0 8px rgba(230, 199, 255, 0.5);
        }
    </style>
@endsection
