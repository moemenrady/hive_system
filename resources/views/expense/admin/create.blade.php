@extends('layouts.app_page')

@section('content')
    <div class="add-expense-wrapper">

        {{-- Alerts --}}
        @if (session('success'))
            <script>
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: "{{ session('success') }}",
                    showConfirmButton: false,
                    timer: 3500,
                    timerProgressBar: true,
                    background: '#fff',
                    color: '#333',
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp'
                    }
                });
            </script>
        @endif

        @if (session('error'))
            <script>
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: "{{ session('error') }}",
                    showConfirmButton: false,
                    timer: 3500,
                    timerProgressBar: true,
                    background: '#fff',
                    color: '#333',
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp'
                    }
                });
            </script>
        @endif

        <div class="page-actions">
            <a href="{{ route('admin_draft.create') }}" class="start-session-btn" aria-label="بدء جلسه">مصروف الموظفين</a>
        </div>
        <h2 class="page-title">إضافة مصروف جديد</h2>

        <div class="form-container animate__animated animate__fadeInUp">
            <form action="{{ route('expense.store') }}" method="POST" class="expense-form">
                @csrf

                <div class="form-group">
                    <label for="expense_type_id">نوع المصروف</label>
                    <select name="expense_type_id" id="expense_type_id" class="styled-select" required>
                        <option value="">اختر النوع</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="amount">المبلغ</label>
                    <input type="number" step="0.01" name="amount" id="amount" placeholder="أدخل قيمة المصروف"
                        required>
                </div>
                <div class="form-group">
                    <label for="expense_time">وقت المصروف (اختياري)</label>
                    <input type="datetime-local" name="expense_time" id="expense_time" class="styled-select">
                </div>


                <div class="form-group">
                    <label for="note">الوصف (اختياري)</label>
                    <textarea name="note" id="note" rows="3" placeholder="اكتب ملاحظة..."></textarea>
                </div>

                <button type="submit" class="btn-submit">💰 إضافة المصروف</button>
            </form>
        </div>
    </div>
    <style>
        /* 🌙 Hive Dark Theme Variables */
        :root {
            --bg-color: #0e0e11;
            --card-bg: #16161a;
            --accent: #ffb84d;
            --accent-light: #ffe6a3;
            --text-main: #f5f5f5;
            --text-dim: #bfbfbf;
            --border-light: rgba(255, 255, 255, 0.08);
            --theme-primary: #1a1a1d;
            --btn-bg: #ffb84d;
            --btn-border: #f2d35e;
            --btn-text: #111;
            --btn-bg-hover: #ffd97a;
        }

        /* body */
        body {
            font-family: "Cairo", sans-serif;
            background: var(--bg-color);
            margin: 0;
            padding: 90px;
            color: var(--text-main);
        }

        .page-title {
            font-size: 28px;
            margin-bottom: 25px;
            color: var(--accent-light);
            text-align: center;
        }

        /* form container */
        .form-container {
            background: var(--card-bg);
            padding: 35px;
            border-radius: 18px;
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.4);
            max-width: 550px;
            margin: auto;
            border: 1px solid var(--border-light);
        }

        .form-group {
            margin-bottom: 18px;
            text-align: right;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--accent-light);
        }

        .styled-select,
        input[type="number"],
        textarea {
            width: 100%;
            padding: 12px 15px;
            border-radius: 12px;
            border: 1px solid var(--border-light);
            font-size: 15px;
            background: var(--theme-primary);
            color: var(--text-main);
            transition: all 0.3s ease;
        }

        .styled-select:focus,
        input[type="number"]:focus,
        textarea:focus {
            border-color: var(--accent);
            box-shadow: 0 0 10px rgba(255, 184, 77, 0.3);
            outline: none;
        }

        /* submit button */
        .btn-submit {
            background: linear-gradient(135deg, var(--accent), #d4a043);
            border: none;
            padding: 14px 28px;
            border-radius: 12px;
            color: var(--btn-text);
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: transform 0.25s, box-shadow 0.25s;
            box-shadow: 0 6px 14px rgba(255, 184, 77, 0.25);
        }

        .btn-submit:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(255, 184, 77, 0.4);
        }

        /* start-session button */
        .start-session-btn {
            position: relative;
            display: inline-block;
            padding: 12px 18px;
            background: var(--btn-bg);
            color: var(--btn-text);
            font-weight: 800;
            font-size: 15px;
            border: 1px solid var(--btn-border);
            border-radius: 14px;
            text-decoration: none;
            letter-spacing: .2px;
            box-shadow: 0 6px 14px rgba(0, 0, 0, .12), inset 0 -2px 0 rgba(0, 0, 0, .05);
            transition: transform .25s ease, box-shadow .25s ease, background-color .25s ease, border-color .25s ease;
            overflow: hidden;
            -webkit-tap-highlight-color: transparent;
        }

        .start-session-btn::before {
            content: "";
            position: absolute;
            inset: -120% -30%;
            background: linear-gradient(120deg, transparent 35%, rgba(255, 255, 255, .45) 50%, transparent 65%);
            transform: translateX(-100%);
            transition: transform .6s ease;
            pointer-events: none;
        }

        .start-session-btn:hover {
            background-color: var(--btn-bg-hover);
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 10px 22px rgba(255, 184, 77, 0.3);
            border-color: #e9c94e;
        }

        .start-session-btn:hover::before {
            transform: translateX(100%);
        }

        .start-session-btn:active {
            transform: translateY(0) scale(0.99);
            box-shadow: 0 6px 14px rgba(0, 0, 0, .12), inset 0 -2px 0 rgba(0, 0, 0, .08);
        }

        .start-session-btn:focus {
            outline: none;
            box-shadow:
                0 0 0 3px rgba(255, 228, 131, .6),
                0 10px 22px rgba(0, 0, 0, .16),
                inset 0 -2px 0 rgba(0, 0, 0, .05);
        }

        @media (prefers-reduced-motion: reduce) {

            .start-session-btn,
            .start-session-btn::before {
                transition: none;
            }

            .start-session-btn:hover {
                transform: none;
            }
        }

        @media (max-width: 768px) {
            .add-expense-wrapper {
                padding-top: 90px !important;
            }
        }
    </style>
@endsection
