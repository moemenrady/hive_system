@extends('layouts.app_page')

@section('title', 'الشفتات المفتوحة')

@section('content')

    <div class="container">

        <h2 class="page-title">الشفتات المفتوحة الآن</h2>

        @if (session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="shift-grid">

            @forelse($shifts as $shift)
                <div class="shift-card">

                    <div class="shift-header">
                        <strong>{{ $shift->user->name }}</strong>
                        <span>#{{ $shift->id }}</span>
                    </div>

                    <div class="shift-body">

                        <p>بداية الشيفت</p>
                        <strong>{{ $shift->start_time->format('Y-m-d H:i') }}</strong>

                        <p>الإيرادات</p>
                        <strong>{{ number_format($shift->total_amount, 2) }} ج</strong>

                        <p>المصروفات</p>
                        <strong>{{ number_format($shift->total_expense, 2) }} ج</strong>

                        <p>الصافي</p>
                        <strong class="profit">{{ number_format($shift->net_profit, 2) }} ج</strong>

                    </div>

                    <div class="shift-actions">

                        <form method="POST" action="{{ route('shifts.close', $shift->id) }}">
                            @csrf
                            <button class="btn-close">إغلاق الشيفت</button>
                        </form>

                        <form method="POST" action="{{ route('shifts.delete', $shift->id) }}">
                            @csrf
                            @method('DELETE')
                            <button class="btn-delete">حذف</button>
                        </form>

                    </div>

                </div>

            @empty

                <p>لا يوجد شفتات مفتوحة</p>
            @endforelse

        </div>

    </div>
<style>
    .container{
max-width:1100px;
margin:auto;
padding:30px;
}

.page-title{
color:#ffb84d;
margin-bottom:20px;
}

.shift-grid{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
gap:20px;
}

.shift-card{
background:#1a1a1a;
border-radius:14px;
padding:20px;
border:1px solid rgba(255,184,77,0.2);
}

.shift-header{
display:flex;
justify-content:space-between;
margin-bottom:10px;
color:#ffb84d;
}

.shift-body p{
font-size:12px;
color:#aaa;
margin:5px 0;
}

.shift-body strong{
display:block;
margin-bottom:8px;
}

.profit{
color:#2ecc71;
}

.shift-actions{
display:flex;
gap:10px;
margin-top:15px;
}

.btn-close{
background:#ffb84d;
border:none;
padding:8px 14px;
border-radius:8px;
cursor:pointer;
}

.btn-delete{
background:#e74c3c;
color:#fff;
border:none;
padding:8px 14px;
border-radius:8px;
cursor:pointer;
}
</style>
@endsection
