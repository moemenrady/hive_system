{{-- resources/views/admin/day_shifts.blade.php --}}
@extends('layouts.app_page')

@section('content')

    <div class="page-wrapper">
        <h2 class="page-title">{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}</h2>

        {{-- ✅ إشعارات النجاح --}}
        @if (session('success'))
            <script>
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: "{{ session('success') }}",
                    showConfirmButton: false,
                    timer: 3500,
                    background: '#2E2B29',
                    color: '#fff'
                });
            </script>
        @endif

        {{-- الملخص الكلي --}}
        <div class="form-container animate__animated animate__fadeInUp" style="max-width:1100px; margin: 0 auto 20px;">
            <div style="display:flex; gap:18px; flex-wrap:wrap; justify-content:space-between; align-items:center;">
                <div style="flex:1; min-width:200px;">
                    <h4 style="margin:0 0 8px;">💰 الإيراد الكلي</h4>

                    <div style="font-size:20px; font-weight:700;">
                        {{ number_format($total_income, 2) }} ج.م
                        <small style="font-size:14px; color:#555; font-weight:400;">
                            (كاش: {{ number_format($totalCash, 2) }} - ديجيتال: {{ number_format($totalDigital, 2) }})
                        </small>
                    </div>
                </div>

                <div style="flex:1; min-width:200px;">
                    <h4 style="margin:0 0 8px;">🧾 المصروف الكلي</h4>
                    <div style="font-size:20px; font-weight:700;">{{ number_format($total_expense, 2) }} ج.م</div>
                </div>
                <div style="flex:1; min-width:200px; text-align:right;">
                    <h4 style="margin:0 0 8px;">🏁 الصافي الكلي</h4>
                    <div style="font-size:20px; font-weight:700;">{{ number_format($total_net, 2) }} ج.م</div>
                </div>
            </div>
        </div>

        {{-- ✅ جدول الشيفتات --}}
        <div class="table-card animate__animated animate__fadeInUp">
            @if ($shifts->isEmpty())
                <div class="no-shifts">❌ لا توجد شيفتات في هذا اليوم</div>
            @else
                <table class="hive-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الموظف</th>
                            <th>بداية</th>
                            <th>نهاية</th>
                            <th>المدة</th>
                            <th>الإيراد</th>
                            <th>المصروف</th>
                            <th>الصافي</th>
                            <th>تفاصيل</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($shifts as $shift)
                            @php
                                $net = $shift->total_amount - $shift->total_expense;
                                $durationText = '—';
                                if (!empty($shift->duration)) {
                                    $totalMinutes = (int) floor($shift->duration);
                                    if ($totalMinutes <= 0 && $shift->duration > 0) {
                                        $totalMinutes = 1;
                                    }
                                    if ($totalMinutes < 60) {
                                        $durationText = $totalMinutes . ' دقيقة';
                                    } else {
                                        $hours = intdiv($totalMinutes, 60);
                                        $minutes = $totalMinutes % 60;
                                        $durationText =
                                            $hours . ' ساعة' . ($minutes > 0 ? ' ' . $minutes . ' دقيقة' : '');
                                    }
                                }
                                $isAdmin = $shift->user && ($shift->user->role ?? '') === 'admin';
                                $badgeEmoji = $isAdmin ? '👑' : '🧳';
                            @endphp
                            <tr>
                                <td>{{ $shift->id }}</td>
                                <td>
                                    @if ($shift->user)
                                        <span>{{ $badgeEmoji }}</span> {{ $shift->user->name }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($shift->created_at)->format('H:i') }}</td>
                                <td>{{ $shift->updated_at ? \Carbon\Carbon::parse($shift->updated_at)->format('H:i') : '—' }}
                                </td>
                                <td>{{ $durationText }}</td>
                                <td>{{ number_format($shift->total_amount, 2) }}</td>
                                <td>{{ number_format($shift->total_expense, 2) }}</td>
                                <td>{{ number_format($net, 2) }}</td>
                                <td>
                                    <a href="{{ route('shift.show', $shift->id) }}" class="hive-btn">عرض</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

@endsection

@section('style')
    <style>
        /* 🌑 الخلفية العامة */
        body {
            background-color: #1B1A19;
            font-family: "Cairo", sans-serif;
            color: #fff;
            padding: 40px;
        }

        /* العنوان */
        .page-title {
            text-align: center;
            font-size: 26px;
            color: #F8F5F2;
            margin-bottom: 20px;
            font-weight: 700;
        }

        /* كروت الملخص */
        .summary-card {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
            background: #2E2B29;
            border-radius: 18px;
            padding: 25px;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.25);
            margin-bottom: 25px;
        }

        .summary-item {
            flex: 1;
            min-width: 180px;
            text-align: center;
        }

        .summary-item h4 {
            margin-bottom: 8px;
            color: #b9962d;
        }

        .summary-item div {
            font-size: 20px;
            font-weight: 700;
        }

        /* الجدول */
        .table-card {
            background: #2E2B29;
            border-radius: 18px;
            padding: 20px;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.25);
        }

        .hive-table {
            width: 100%;
            border-collapse: collapse;
            color: #fff;
        }

        .hive-table th {
            background: #b9962d;
            color: #1B1A19;
            padding: 12px;
            font-weight: 700;
        }

        .hive-table td {
            padding: 12px;
            border-bottom: 1px solid #3E3C3A;
        }

        .hive-table tr:hover td {
            background: #3E3C3A;
        }

        /* زر العرض */
        .hive-btn {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 10px;
            background: #b9962d;
            color: #1B1A19;
            font-weight: 700;
            transition: all 0.25s ease;
        }

        .hive-btn:hover {
            background: #b9962d;
            transform: translateY(-2px);
        }

        /* لا توجد بيانات */
        .no-shifts {
            text-align: center;
            color: #aaa;
            padding: 20px;
            font-weight: 600;
        }

        /* موبايل */
        @media (max-width: 768px) {
            body {
                padding: 20px;
            }

            .summary-card {
                flex-direction: column;
                text-align: center;
            }

            .hive-table,
            thead,
            tbody,
            tr,
            td,
            th {
                display: block;
            }

            thead {
                display: none;
            }

            tbody tr {
                margin-bottom: 10px;
                border: 1px solid #3E3C3A;
                border-radius: 10px;
                padding: 12px;
                background: #262422;
            }

            tbody td {
                padding: 8px 10px;
                position: relative;
            }

            tbody td::before {
                content: attr(data-label);
                font-weight: 600;
                color: #b9962d;
                position: absolute;
                right: 10px;
            }
        }
    </style>
@endsection
