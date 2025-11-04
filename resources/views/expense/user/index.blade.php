@extends('layouts.app_page')

@section('content')
    <div class="drafts-wrapper">

        <!-- فورم إضافة Draft -->
        <div class="draft-form">
            <h2>✏️ إضافة ملاحظة مصروف (Draft)</h2>
            <form action="{{ route('expense-drafts.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>الوصف</label>
                    <input type="text" name="note" placeholder="مثال: شراء ورق للطابعة...">
                </div>
                <div class="form-group">
                    <label>نوع المصروف</label>
                    <select name="expense_type_id" class="styled-select">
                        <option value="">اختر النوع...</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>المبلغ (تقديري)</label>
                    <input type="number" step="0.01" name="estimated_amount" placeholder="مثال: 200">
                </div>

                <button type="submit" class="btn-submit">💾 حفظ Draft</button>
            </form>
        </div>

        <!-- ليستة الملاحظات -->
        <div class="draft-list">
            <h3>📋 قائمة الملاحظات</h3>
            <div class="cards">
                @forelse($drafts as $draft)
                    <div class="card-content">
                        <h4>{{ $draft->note }}</h4>
                        <p class="amount">💰 {{ $draft->estimated_amount ?? 'غير محدد' }}</p>
                        <p class="type">🏷️ {{ $draft->expenseType->name ?? 'غير محدد' }}</p>
                        <span class="date">🕒 {{ $draft->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="empty">🚀 لا توجد ملاحظات حتى الآن</p>
                @endforelse
            </div>
        </div>

    </div>
@endsection

@section('style')
    <style>
        :root {
            --bg-color: #0e0e11;
            --card-bg: #16161a;
            --accent: #ffb84d;
            --accent-light: #ffe6a3;
            --text-main: #f5f5f5;
            --text-dim: #bfbfbf;
            --border-light: rgba(255, 255, 255, 0.08);
            --radius: 14px;
        }

        html,
        body {
            box-sizing: border-box;
        }

        *,
        *::before,
        *::after {
            box-sizing: inherit;
        }

        body {
            font-family: "Cairo", sans-serif;
            margin: 0;
            padding: 40px;
            background: var(--bg-color);
            color: var(--text-main);
            -webkit-font-smoothing: antialiased;
        }

        /* ===== Container ===== */
        .drafts-wrapper {
            max-width: 980px;
            margin: 18px auto;
            display: grid;
            gap: 28px;
            grid-template-columns: 1fr 420px;
        }

        @media (max-width: 1024px) {
            .drafts-wrapper {
                grid-template-columns: 1fr;
                padding: 0 12px;
                gap: 20px;
            }
        }

        /* ===== Cards & Form ===== */
        .draft-form,
        .draft-list {
            background: var(--card-bg);
            padding: 22px;
            border-radius: var(--radius);
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.3);
            transition: transform .28s, box-shadow .28s;
            border: 1px solid var(--border-light);
        }

        @media (max-width: 420px) {

            .draft-form,
            .draft-list {
                padding: 14px;
                border-radius: 12px;
            }
        }

        .draft-form:hover,
        .draft-list:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.4);
        }

        h2,
        h3 {
            margin: 0 0 14px 0;
            color: var(--accent-light);
            font-size: 18px;
        }

        /* ===== Inputs ===== */
        .form-group {
            margin-bottom: 14px;
        }

        input[type="text"],
        input[type="number"],
        select.styled-select {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid var(--border-light);
            border-radius: 10px;
            outline: none;
            font-size: 15px;
            transition: box-shadow .2s, transform .15s;
            background: #1c1c1f;
            color: var(--text-main);
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        select.styled-select:focus {
            box-shadow: 0 0 8px rgba(255, 184, 77, 0.3);
            transform: translateY(-1px);
            border-color: var(--accent);
        }

        /* ===== Button ===== */
        .btn-submit {
            background: linear-gradient(135deg, var(--accent), #e0a44a);
            color: #111;
            border: none;
            padding: 12px 16px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            box-shadow: 0 6px 18px rgba(255, 184, 77, 0.25);
            transition: transform .18s, box-shadow .18s;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(255, 184, 77, 0.35);
        }

        /* ===== Cards ===== */
        .cards {
            display: grid;
            gap: 12px;
            align-content: start;
        }

        .card-content {
            background: var(--theme-primary, #1a1a1d);
            padding: 14px 16px;
            border-radius: 12px;
            border-left: 6px solid var(--accent);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.35);
            transition: transform .2s, box-shadow .2s;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .card-content:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.4);
        }

        .card-content h4 {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
            color: var(--text-main);
        }

        .card-content .amount {
            margin: 0;
            font-size: 14px;
            font-weight: 700;
            color: #6fff92;
        }

        .card-content .type {
            margin: 0;
            font-size: 13px;
            color: var(--accent);
            font-weight: 700;
        }

        .card-content .date {
            margin-top: 6px;
            font-size: 13px;
            color: var(--text-dim);
        }

        .empty {
            color: var(--text-dim);
            font-style: italic;
            text-align: center;
            padding: 18px;
        }

        @media (max-width: 420px) {
            .drafts-wrapper {
                padding: 0 10px;
            }

            .card-content {
                padding: 12px;
                border-radius: 10px;
            }

            .cards {
                gap: 10px;
            }

            .btn-submit {
                padding: 10px;
            }

            h2,
            h3 {
                font-size: 16px;
            }
        }

        .btn-submit,
        input[type="text"],
        input[type="number"],
        .styled-select {
            min-height: 44px;
        }

        @media (min-width: 1100px) {
            .draft-list {
                position: sticky;
                top: 28px;
                height: calc(100vh - 56px);
                overflow: auto;
                padding-bottom: 32px;
            }
        }
    </style>
@endsection
