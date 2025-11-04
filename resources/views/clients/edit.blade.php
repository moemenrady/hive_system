@extends('layouts.app_page')

@section('title', "تعديل بيانات العميل — {$client->name}")

@section('content')
<div class="client-container">

    <div class="card">
        <div class="card-header">
            <h2>✏️ تعديل بيانات العميل</h2>
            <span class="badge">#{{ $client->id }}</span>
        </div>

        <form action="{{ route('clients.update', $client->id) }}" method="POST" class="section client-main" novalidate>
            @csrf
            @method('PUT')

            <div class="box client-info">
                <div class="row">
                    <div class="col">
                        <label class="lbl">اسم العميل</label>
                        <input type="text" name="name" value="{{ old('name', $client->name) }}" class="input" required>
                        @error('name') <p class="error">{{ $message }}</p> @enderror
                    </div>

                    <div class="col">
                        <label class="lbl">رقم الهاتف</label>
                        <input type="text" name="phone" value="{{ old('phone', $client->phone) }}" class="input" required>
                        @error('phone') <p class="error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="row" style="margin-top:12px;">
                    <div class="col">
                        <label class="lbl">العمر (اختياري)</label>
                        <input type="number" name="age" value="{{ old('age', $client->age) }}" min="1" class="input">
                        @error('age') <p class="error">{{ $message }}</p> @enderror
                    </div>

                    <div class="col">
                        <label class="lbl">التخصص (اختياري)</label>
                        <select name="specialization_id" class="input">
                            <option value="">— لا يوجد —</option>
                            @foreach($specializations as $spec)
                                <option value="{{ $spec->id }}"
                                    {{ (string) old('specialization_id', $client->specialization_id) === (string) $spec->id ? 'selected' : '' }}>
                                    {{ $spec->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('specialization_id') <p class="error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="row" style="margin-top:12px;">
                    <div class="col">
                        <label class="lbl">المرحلة الدراسية (اختياري)</label>
                        <select name="education_stage_id" class="input">
                            <option value="">— لا يوجد —</option>
                            @foreach($educationStages as $stage)
                                <option value="{{ $stage->id }}"
                                    {{ (string) old('education_stage_id', $client->education_stage_id) === (string) $stage->id ? 'selected' : '' }}>
                                    {{ $stage->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('education_stage_id') <p class="error">{{ $message }}</p> @enderror
                    </div>

                    <div class="col" style="display:flex; align-items:flex-end; justify-content:flex-end;">
                        <div>
                            <button type="submit" class="btn save-btn">حفظ التعديلات</button>
                            <a href="{{ route('clients.show', $client->id) }}" class="btn small cancel-btn">إلغاء</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="section statuses" style="margin-top:18px;">
            <h3>📌 حالة الحساب</h3>
            <div class="box flex-grid">
                <p class="muted">عرض سريع لحالة العميل — التفاصيل في صفحة العرض.</p>
            </div>
        </div>

    </div>
</div>
@endsection

@section('style')
<style>
    body { background: #121212; font-family: "Tahoma", sans-serif; color:#fff; }
    .client-container { max-width: 960px; margin: 40px auto; padding: 20px; position: relative; }

    .card { background: #1e1e1e; border-radius: 20px; box-shadow: 0 5px 20px rgba(0,0,0,0.5); padding: 26px; animation: fadeInUp .6s ease; color:#fff; }
    .card-header { display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #333; margin-bottom: 16px; padding-bottom: 10px; }
    .card-header h2 { font-size:24px; color:#ffb84d; margin:0; }
    .badge { background:#ffb84d; color:#000; padding:6px 12px; border-radius:30px; font-weight:bold; box-shadow: 0 3px 6px rgba(0,0,0,0.2); }

    .lbl { display:block; font-weight:700; margin-bottom:6px; color:#ffb84d; }
    .input { width:100%; padding:10px 12px; border-radius:10px; border:1px solid #444; background:#2a2a2a; box-shadow: inset 0 1px 3px rgba(0,0,0,0.3); font-size:15px; color:#fff; }
    .input:focus { outline:none; box-shadow: 0 6px 20px rgba(255,215,0,0.2); transform: translateY(-1px); transition: all .12s ease; }

    .box { background:#2a2a2a; padding:14px 18px; border-radius:12px; box-shadow: inset 0 2px 6px rgba(0,0,0,0.2); margin-bottom:18px; font-size:15px; line-height:1.6; }

    .row { display:flex; gap:20px; }
    .col { flex:1; }

    .btn.save-btn { background: linear-gradient(90deg,#ffb84d,#ffb84d); color:#000; padding:10px 18px; border-radius:12px; border:none; cursor:pointer; font-weight:700; box-shadow: 0 6px 18px rgba(255,215,0,0.3); transition: transform .16s; }
    .btn.save-btn:hover { transform: translateY(-3px); }
    .btn.small.cancel-btn { display:inline-block; background:#444; color:#ffb84d; padding:8px 12px; border-radius:10px; margin-left:8px; text-decoration:none; }

    .error { color:#f44336; margin-top:6px; font-size:13px; }

    @media (max-width:800px) { .row { flex-direction:column; } }

    @keyframes fadeInUp { from { opacity:0; transform: translateY(14px); } to { opacity:1; transform: translateY(0); } }
</style>
@endsection
