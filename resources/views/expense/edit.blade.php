@extends('layouts.app_page')


@section('content')
<div class="subscription-container">

    @if (session('success'))
        <script>
            document.addEventListener("DOMContentLoaded", () =>
                showSnackbar("{{ session('success') }}", "success")
            );
        </script>
    @endif

    @if (session('error'))
        <script>
            document.addEventListener("DOMContentLoaded", () =>
                showSnackbar("{{ session('error') }}", "error")
            );
        </script>
    @endif

    <div class="card">
        <div class="card-header">
            <h2>✏️ تعديل مصروف</h2>
            <span class="badge">#{{ $expense->id }}</span>
        </div>

        <form action="{{ route('expenses.update', $expense->id) }}" method="POST" autocomplete="off">
            @csrf
            @method('PUT')

            {{-- بيانات المصروف --}}
            <div class="section">
                <h3>💰 بيانات المصروف</h3>

                <div class="box">

                    {{-- نوع المصروف --}}
                    <div class="mb-3">
                        <label class="form-label">نوع المصروف</label>
                        <select name="expense_type_id" class="form-control" required>
                            @foreach ($types as $type)
                                <option value="{{ $type->id }}"
                                    {{ $expense->expense_type_id == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- المبلغ --}}
                    <div class="mb-3">
                        <label class="form-label">المبلغ</label>
                        <input type="number" step="0.01"
                               name="amount"
                               class="form-control"
                               value="{{ old('amount', $expense->amount) }}"
                               required>
                    </div>

                    {{-- الملاحظة --}}
                    <div class="mb-3">
                        <label class="form-label">ملاحظة</label>
                        <textarea name="note"
                                  class="form-control"
                                  rows="3"
                                  placeholder="ملاحظة اختيارية">{{ old('note', $expense->note) }}</textarea>
                    </div>

                    {{-- تاريخ ووقت المصروف --}}
                    <div class="mb-3">
                        <label class="form-label">📅 تاريخ ووقت المصروف</label>
                        <input type="datetime-local"
                               name="expense_time"
                               class="form-control"
                               value="{{ old('expense_time', $expense->created_at->format('Y-m-d\TH:i')) }}">
                    </div>

                </div>
            </div>

            {{-- أزرار التحكم --}}
            <div class="actions">
                <button type="submit" class="btn yellow">
                    💾 حفظ التعديلات
                </button>

                <a href="{{ route('expenses.index') }}" class="btn">
                    إلغاء
                </a>
            </div>

        </form>
    </div>
</div>
@endsection

@section('style')
<style>
/* ===== System Theme ===== */
body {
    background: #0e0e11;
    color: #ffffff;
    font-family: "Cairo", sans-serif;
    direction: rtl;
}

/* ===== Container ===== */
.subscription-container {
    max-width: 720px;
    margin: 40px auto;
    padding: 20px;
}

/* ===== Card ===== */
.card {
    background: #1b1b21;
    border-radius: 20px;
    padding: 30px;
    border: 1px solid rgba(255,255,255,0.08);
    box-shadow: 0 0 25px rgba(0,0,0,0.45);
    animation: fadeInUp .6s ease;
}

/* ===== Header ===== */
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #2e2e36;
    margin-bottom: 22px;
    padding-bottom: 12px;
}

.card-header h2 {
    font-size: 26px;
    margin: 0;
    color: #ffe483;
}

.badge {
    background: linear-gradient(135deg, #b8860b, #e6c200);
    color: #1b1b21;
    padding: 6px 16px;
    border-radius: 30px;
    font-weight: bold;
    box-shadow: 0 0 10px rgba(255,215,0,.35);
}

/* ===== Sections ===== */
.section h3 {
    color: #ffe483;
    font-size: 20px;
    margin-bottom: 12px;
}

.box {
    background: #22222a;
    padding: 20px 22px;
    border-radius: 15px;
    margin-bottom: 25px;
    box-shadow: inset 0 2px 6px rgba(0,0,0,.45);
}

/* ===== Form ===== */
.form-label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #ffe483;
}

.form-control {
    width: 100%;
    padding: 12px;
    border-radius: 10px;
    border: 1px solid #444;
    background: #1a1a1d;
    color: #fff;
    font-size: 15px;
    transition: .25s;
}

.form-control:focus {
    outline: none;
    border-color: #ffe483;
    box-shadow: 0 0 8px rgba(255,228,131,.35);
    background: #1a1a1d;
}

/* textarea */
textarea.form-control {
    resize: vertical;
}

/* ===== Actions ===== */
.actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 10px;
}

/* ===== Buttons ===== */
.btn {
    border: none;
    padding: 12px 22px;
    border-radius: 12px;
    font-weight: bold;
    cursor: pointer;
    font-size: 15px;
    transition: all .3s ease;
    background: #2a2a32;
    color: #fff;
}

.btn:hover {
    background: #3a3a45;
    transform: translateY(-2px);
}

.btn.yellow {
    background: linear-gradient(135deg, #b8860b, #e6c200);
    color: #1b1b21;
}

.btn.yellow:hover {
    background: linear-gradient(135deg, #ffd700, #ffde5a);
    transform: translateY(-2px) scale(1.03);
}

/* ===== Animation ===== */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(18px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ===== Responsive ===== */
@media (max-width: 600px) {
    .card {
        padding: 22px;
    }

    .card-header h2 {
        font-size: 22px;
    }

    .actions {
        flex-direction: column;
    }

    .btn {
        width: 100%;
        text-align: center;
    }
}
.form-control,
.form-select,
input,
select,
textarea {
    background-color: #1a1a1d !important;
    color: #ffffff !important;
    border: 1px solid rgba(255, 255, 255, 0.15) !important;
}

/* focus */
.form-control:focus,
.form-select:focus,
input:focus,
select:focus,
textarea:focus {
    background-color: #1a1a1d !important;
    color: #ffffff !important;
    border-color: #ffb84d !important;
    box-shadow: 0 0 0 2px rgba(255, 184, 77, 0.25) !important;
}

/* placeholder */
.form-control::placeholder,
input::placeholder,
textarea::placeholder {
    color: rgba(255, 255, 255, 0.6) !important;
}

/* select options */
select option {
    background: #1a1a1d !important;
    color: #ffffff !important;
}

/* labels & titles */
label,
.form-label,
.section h3 {
    color: #ffffff !important;
}

/* disabled fields */
.form-control:disabled,
.form-select:disabled {
    background-color: #141416 !important;
    color: rgba(255,255,255,0.5) !important;
}

</style>
@endsection
