@extends('layouts.app_page')

@section('title', 'زيارات الاشتراك #' . $subscription->id)

@section('content')
    <div class="subscription-container simple-padding">
        <div class="card compact-card">

            {{-- عنوان بسيط --}}
            <div class="card-header simple-header">
                <h2>📑 قائمة زيارات الاشتراك العميل <strong>{{ $subscription->client->name }}</strong></h2>
            </div>

            {{-- القائمة فقط --}}
            <div class="section">
                @if ($visits->count() === 0)
                    <div class="box empty-box">
                        لا توجد زيارات لهذا الاشتراك.
                    </div>
                @else
                    <div class="visits-grid">
                        @foreach ($visits as $v)
                            <div class="visit-row" aria-label="Visit {{ $v->id }}">
                                <div class="visit-left">
                                    <div class="visit-title">
                                        <strong>زيارة رقم : {{ $v->visit_number ?? $v->id }}</strong>
                                    </div>
                                    @php
                                        $duration = $v->duration_minutes;
                                        $durationText = '—';

                                        if ($duration) {
                                            $hours = intdiv($duration, 60);
                                            $minutes = $duration % 60;

                                            if ($hours > 0 && $minutes > 0) {
                                                if ($minutes == 30) {
                                                    $durationText = "{$hours} ساعة ونصف";
                                                } elseif ($minutes < 30) {
                                                    $durationText = "{$hours} ساعة وربع";
                                                } else {
                                                    $durationText = "{$hours} ساعة و {$minutes} دقيقة";
                                                }
                                            } elseif ($hours > 0) {
                                                $durationText = "{$hours} " . ($hours == 1 ? 'ساعة' : 'ساعات');
                                            } else {
                                                $durationText = "{$minutes} دقيقة";
                                            }
                                        }
                                        $daysArabic = [
                                            'Saturday' => 'السبت',
                                            'Sunday' => 'الأحد',
                                            'Monday' => 'الاثنين',
                                            'Tuesday' => 'الثلاثاء',
                                            'Wednesday' => 'الأربعاء',
                                            'Thursday' => 'الخميس',
                                            'Friday' => 'الجمعة',
                                        ];
                                    @endphp
                                    {{-- التفاصيل (كل حقول الجدول) --}}
                                    <div class="visit-details" style="margin-top:8px; font-size:13px; color:#444;">
                                        <div style="display:flex; gap:12px; flex-wrap:wrap;">
                                            <div>
                                                <strong>تاريخ الدخول:</strong>
                                                @if ($v->checked_in_at)
                                                    @php
                                                        $dayName = $daysArabic[$v->checked_in_at->format('l')] ?? '';
                                                        $formattedDate = $v->checked_in_at->format('Y-m-d g:i a');
                                                    @endphp
                                                    {{ $dayName }} — {{ $formattedDate }}
                                                @else
                                                    -
                                                @endif
                                            </div>

                                            {{-- <div>
                                                <strong>تاريخ الخروج:</strong>
                                                @if ($v->checked_out_at)
                                                    @php
                                                        $dayName = $daysArabic[$v->checked_out_at->format('l')] ?? '';
                                                        $formattedDate = $v->checked_out_at->format('Y-m-d g:i a');
                                                    @endphp
                                                    {{ $dayName }} — {{ $formattedDate }}
                                                @else
                                                    -
                                                @endif
                                            </div> --}}
                                        </div>

                                        <div style="margin-top:8px; display:flex; gap:12px; flex-wrap:wrap;">


                                            {{-- <div><strong>المدة :</strong> {{ $durationText }}</div> --}}
                                            <div><strong>سجل بواسطة:</strong>
                                                {{ $v->creator?->name ?? ($v->created_by ?? '—') }}</div>
                                        </div>

                                        <div style="margin-top:8px;">
                                            <strong>الملاحظات:</strong>
                                            <div class="notes"
                                                style="margin-top:6px; color:#333; background:#fafafa; padding:8px; border-radius:8px; border:1px solid #f0f0f0;">
                                                {!! nl2br(e($v->notes ?: 'لا توجد')) !!}
                                            </div>
                                        </div>


                                    </div>
                                </div>

                                {{-- <div class="visit-actions" style="min-width:160px; justify-content:flex-end;">
                                    @if ($v->checked_out_at)
                                        <span class="chip green">ختم:
                                            {{ $v->checked_out_at->format('Y-m-d g:i a') }}</span>
                                    @else
                                        <button class="btn checkout-btn" data-id="{{ $v->id }}">⏱️ ختم خروج</button>
                                    @endif
                                </div> --}}
                            </div>
                        @endforeach
                    </div>

                    <div class="pagination-wrap">
                        {{ $visits->links() }}
                    </div>
                @endif
            </div>

            <div id="visit-toast" class="snackbar" aria-hidden="true"><i>ℹ️</i><span id="visit-toast-text"></span></div>
        </div>
    </div>
    @parent
    <script>
        (function() {
            const checkoutBase = "{{ url('subscription-visits') }}";
            const toast = document.getElementById('visit-toast');
            const toastText = document.getElementById('visit-toast-text');

            function showTempToast(msg, type = 'info') {
                toastText.innerText = msg;
                toast.classList.add('show');
                if (type === 'error') toast.classList.add('error');
                else toast.classList.remove('error');
                setTimeout(() => toast.classList.remove('show'), 1600);
            }

            document.querySelectorAll('.checkout-btn').forEach(btn => {
                btn.addEventListener('click', async function() {
                    const id = this.dataset.id;
                    this.disabled = true;
                    const orig = this.innerHTML;
                    this.innerHTML = 'جاري الختم...';

                    try {
                        const resp = await fetch(`${checkoutBase}/${id}/checkout`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        });
                        const data = await resp.json();
                        if (resp.ok && data.success) {
                            showTempToast(data.message || 'تم الختم');
                            setTimeout(() => location.reload(), 400);
                        } else {
                            showTempToast(data.message || 'خطأ', 'error');
                            this.disabled = false;
                            this.innerHTML = orig;
                        }
                    } catch (err) {
                        console.error(err);
                        showTempToast('خطأ في الاتصال', 'error');
                        this.disabled = false;
                        this.innerHTML = orig;
                    }
                });
            });

            // expose for copy button
            window.showTempToast = showTempToast;
        })();
    </script>
@endsection
@section('style')
@parent
<style>
    /* ===== الحاوية العامة ===== */
    .subscription-container.simple-padding {
        max-width: 1100px;
        margin: 28px auto;
        padding: 0 40px;
        color: #fff;
    }

    @media (max-width:900px) {
        .subscription-container.simple-padding {
            padding: 0 20px;
            margin: 18px auto;
        }
    }

    /* ===== الكارت الرئيسي ===== */
    .compact-card {
        padding: 20px;
        border-radius: 18px;
        background: #1b1b21;
        box-shadow: 0 0 25px rgba(0, 0, 0, 0.6);
        border: 1px solid rgba(255, 215, 0, 0.08);
    }

    /* ===== العنوان ===== */
    .simple-header {
        display: flex;
        align-items: center;
        justify-content: center;
        border-bottom: none;
        margin-bottom: 16px;
    }

    .simple-header h2 {
        font-size: 20px;
        font-weight: 700;
        color: #FFD700;
        text-shadow: 0 0 8px rgba(255, 215, 0, 0.4);
    }

    /* ===== الصندوق الفارغ ===== */
    .empty-box {
        text-align: center;
        color: #ccc;
        padding: 26px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.06);
        border: 1px dashed rgba(255, 215, 0, 0.2);
    }

    /* ===== شبكة الزيارات ===== */
    .visits-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
    }

    .visit-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 16px;
        border-radius: 14px;
        background: linear-gradient(145deg, #1e1e25, #2a2a32);
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.35);
        border: 1px solid rgba(255, 215, 0, 0.08);
        align-items: flex-start;
        transition: all 0.25s ease;
    }

    .visit-row:hover {
        transform: translateY(-4px);
        box-shadow: 0 0 25px rgba(255, 215, 0, 0.15);
    }

    .visit-left {
        flex: 1;
    }

    .visit-title {
        font-size: 15px;
        font-weight: 700;
        color: #FFD700;
        margin-bottom: 8px;
    }

    .visit-details strong {
        color: #FFD700;
    }

/* ===== إبراز ميعاد الزيارة ===== */
.visit-details div:first-child {
    color: #ffffff;
    font-size: 15px;
    font-weight: 700;
    padding: 10px 14px;
    border-radius: 10px;
    display: inline-block;
    margin-bottom: 10px;
}


    /* ===== الأزرار ===== */
    .btn {
        background: linear-gradient(135deg, #e6b800, #b8860b);
        color: #1b1b21;
        padding: 10px 16px;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        font-weight: 700;
        font-size: 13px;
        transition: all 0.3s ease;
    }

    .btn:hover {
        background: linear-gradient(135deg, #ffd700, #ffae00);
        transform: scale(1.05);
        box-shadow: 0 0 15px rgba(255, 215, 0, 0.4);
    }

    /* ===== الشيب ===== */
    .chip {
        padding: 6px 10px;
        border-radius: 999px;
        font-weight: 700;
        font-size: 13px;
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
    }

    .chip.green {
        background: rgba(40, 167, 69, 0.2);
        color: #28a745;
    }

    /* ===== الصفحات ===== */
    .pagination-wrap {
        margin-top: 20px;
        display: flex;
        justify-content: center;
    }

    /* ===== سناكبار ===== */
    .snackbar {
        position: fixed;
        top: 18px;
        right: 18px;
        background: #222;
        color: #fff;
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 14px;
        z-index: 9999;
        opacity: 0;
        transform: translateX(120%);
        transition: opacity 0.3s ease, transform 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        border: 1px solid rgba(255, 215, 0, 0.25);
    }

    .snackbar.show {
        opacity: 1;
        transform: translateX(0);
        box-shadow: 0 0 18px rgba(255, 215, 0, 0.25);
    }

    .snackbar.error {
        background: #a30000;
        border-color: #ff4c4c;
    }

    /* ===== موبايل ===== */
    @media (max-width:900px) {
        .visits-grid {
            grid-template-columns: 1fr;
        }

        .compact-card {
            padding: 16px;
        }

        .subscription-container.simple-padding {
            padding: 0 16px;
        }
    }
    /* ===== ملاحظات دارك ===== */
.notes-container {
    background: #2b2b2b !important; /* نفس خلفية الكروت */
    border-radius: 12px;
    padding: 15px 20px;
    box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.3);
    color: #ffffff; /* النص أبيض */
}

.notes-container textarea,
.notes-container input,
.notes-container .note {
    background: transparent !important;
    border: 1px solid #444;
    color: #ffffff !important;
}

.notes-container label {
    color: #ffd700; /* العنوان ذهبي */
}

.notes-container .note {
    font-size: 16px;
    line-height: 1.6;
}

</style>
@endsection
