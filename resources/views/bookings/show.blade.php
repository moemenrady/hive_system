@extends('layouts.app_page')

@section('title', 'تفاصيل الحجز')

@section('content')
    <div class="subscription-container">
        @if (session('success'))
            <script>
                document.addEventListener("DOMContentLoaded", () => {
                    showSnackbar("{{ session('success') }}", "success");
                });
            </script>
        @endif

        @if (session('error'))
            <script>
                document.addEventListener("DOMContentLoaded", () => {
                    showSnackbar("{{ session('error') }}", "error");
                });
            </script>
        @endif
        <div class="card">
            <!-- الهيدر -->
            <div class="card-header">
                <h2>📋 تفاصيل الحجز</h2>
                <span class="badge">#{{ $booking->id }}</span>
            </div>

            <!-- بيانات العميل -->
            <div class="section">
                <h3>👤 بيانات العميل</h3>
                <div class="box">
                    <p><strong> المعرف: </strong>{{ $booking->client->id }}</p>

                    <p><strong>الاسم:</strong> {{ $booking->client->name }}</p>
                    <p><strong>الموبايل:</strong> {{ $booking->client->phone }}</p>
                    <a href="{{ route('clients.edit', $booking->client->id) }}" class="btn edit-btn"
                        title="تعديل بيانات العميل">
                        <span class="edit-ico" aria-hidden="true">✏️</span>
                        <span class="edit-txt">تعديل</span>
                    </a>
                </div>
            </div>

            <!-- بيانات القاعة -->
            <div class="section">
                <h3>🏛️ القاعة</h3>
                <div class="box">
                    <p><strong>اسم القاعة:</strong> {{ $booking->hall->name }}</p>
                    <p><strong>الحد الأدنى:</strong> {{ $booking->min_capacity_snapshot }} فرد</p>
                </div>
            </div>

            <!-- بيانات الحجز -->
            <div class="section">
                <h3>📅 تفاصيل الحجز</h3>
                <div class="box">
                    <p><strong>العنوان:</strong> {{ $booking->title }}</p>
                    <p><strong>الحضور:</strong> {{ $booking->attendees }} فرد</p>
                    <p><strong>من:</strong> {{ \Carbon\Carbon::parse($booking->start_at)->format('Y-m-d h:i A') }}</p>
                    <p><strong>الى:</strong> {{ \Carbon\Carbon::parse($booking->end_at)->format('Y-m-d h:i A') }}</p>

                    <p><strong>الحالة:</strong>
                        @php
                            $statuses = [
                                'scheduled' => '⏳ لم يبدأ بعد',
                                'due' => '📌 موعده الآن',
                                'in_progress' => '▶️ جاري',
                                'finished' => '✅ منتهي',
                                'cancelled' => '❌ ملغي',
                            ];
                        @endphp
                        <span class="badge">{{ $statuses[$booking->status] ?? $booking->status }}</span>
                    </p>

                    @if (in_array($booking->status, ['in_progress', 'finished']) && $booking->real_start_at)
                        <p><strong>بداية فعلية:</strong>
                            {{ \Carbon\Carbon::parse($booking->real_start_at)->format('Y-m-d h:i A') }}
                        </p>
                    @endif

                    @if ($booking->real_end_at)
                        <p><strong>نهاية فعلية:</strong>
                            {{ \Carbon\Carbon::parse($booking->real_end_at)->format('Y-m-d h:i A') }}</p>
                    @endif

                    @if (!empty($actual_duration))
                        @php
                            $totalMin = intval($actual_duration);
                            $hours = intdiv($totalMin, 60);
                            $mins = $totalMin % 60;

                            $parts = [];

                            // دالة مساعدة صغنونة للثواني (دقيقة/دقايق)
                            $minutesLabel = function ($n) {
                                if ($n == 1) {
                                    return 'دقيقة';
                                }
                                if ($n == 2) {
                                    return 'دقيقتان';
                                }
                                return 'دقائق';
                            };

                            // دالة للساعة (واحد/اتنين/ساعات)
                            $hoursLabel = function ($n) {
                                if ($n == 1) {
                                    return 'ساعة';
                                }
                                if ($n == 2) {
                                    return 'ساعتان';
                                }
                                return 'ساعات';
                            };

                            if ($hours > 0) {
                                // عند وجود ساعات
                                $hText = $hours == 1 ? 'ساعة' : ($hours == 2 ? 'ساعتان' : $hours . ' ' . 'ساعات');

                                if ($mins === 0) {
                                    $display = $hText;
                                } else {
                                    // تعامل خاص للـ 15 و 30 (ربع / نص)
                                    if ($mins === 15) {
                                        // مثال: "ساعة وربع" أو "2 ساعات وربع"
                                        $display = $hText . ' وربع';
                                    } elseif ($mins === 30) {
                                        $display = $hText . ' ونصف';
                                    } else {
                                        // دقائق عادية
                                        $mLabel = $minutesLabel($mins);
                                        $display = $hText . ' و' . $mins . ' ' . $mLabel;
                                    }
                                }
                            } else {
                                // أقل من ساعة: دقائق فقط
                                if ($mins === 15) {
                                    $display = 'ربع ساعة';
                                } elseif ($mins === 30) {
                                    $display = 'نصف ساعة';
                                } else {
                                    $mLabel = $minutesLabel($mins);
                                    $display = $mins . ' ' . $mLabel;
                                }
                            }
                        @endphp

                        <p><strong>المدة الفعلية:</strong> {{ $display }}</p>
                    @endif

                </div>
            </div>

            <!-- الدفع -->
            <div class="section">
                <h3>💰 الحساب</h3>
                <div class="box">
                    <p><strong>إجمالي متوقع:</strong>
                        <span class="price" style="color:green;">{{ number_format($booking->estimated_total, 2) }}
                            جنيه</span>
                    </p>

                    <p><strong>سعر الساعة:</strong> {{ number_format($bookingHourPrice, 2) }} جنيه</p>

                    <p><strong>سعر الساعات:</strong>
                        <span class="price-hours">{{ number_format($hours_total ?? 0, 2) }} جنيه</span>
                    </p>

                    <p><strong>سعر المشتريات:</strong>
                        <span class="price-purchases">{{ number_format($purchases_total ?? 0, 2) }} جنيه</span>
                    </p>

                    <p><strong>الإجمالي الفعلي حتى الآن:</strong>
                        <span class="price" style="color:red; font-weight:800;">
                            {{ number_format($combined_actual ?? 0, 2) }} جنيه
                        </span>
                    </p>

                    <p><strong>الدفعة المقدمة:</strong>
                        @if (($deposit_paid ?? 0) > 0)
                            ✅ {{ number_format($deposit_paid, 2) }} جنيه
                        @else
                            ❌ 0.00 جنيه
                        @endif
                    </p>

                    <p><strong>المتبقي للدفع:</strong>
                        {{ number_format($remaining ?? 0, 2) }} جنيه
                        @if (!empty($remaining_label ?? null))
                            <span style="color:#777; font-style:italic; margin-left:8px;">{{ $remaining_label }}</span>
                        @endif
                    </p>
                </div>
            </div>

            <!-- المشتريات -->
            <div class="section">
                <h3>🛒 المشتريات</h3>
                <div class="box selected-products">
                    @forelse ($purchases as $purchase)
                        <p>{{ $purchase->product->name }} × {{ $purchase->quantity }}</p>
                    @empty
                        <p>لا يوجد مشتريات</p>
                    @endforelse
                </div>

                @if ($booking->status === 'in_progress')
                    <div class="products-list">

                        @foreach ($importantProducts as $importantProduct)
                            <form class="invoiceForm" action="{{ route('booking.purchase.store', $booking->id) }}"
                                method="POST">
                                @csrf
                                <input type="hidden" name="items" class="itemsInput">
                                <button type="submit" class="product-item" data-id="{{ $importantProduct->product_id }}">
                                    {{ $importantProduct->name }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- الأزرار حسب الحالة -->
            <div class="actions">
                @if (in_array($booking->status, ['scheduled', 'due']))
                    <a href="{{ route('bookings.edit', $booking) }}" class="btn yellow">✏️ تعديل الميعاد</a>

                    <form action="{{ route('bookings.start', $booking) }}" method="POST" style="display:inline">
                        @csrf
                        <button class="btn green" type="submit">✅ بدء الحجز</button>
                    </form>
                @elseif($booking->status === 'in_progress')
                    <a href="{{ route('booking.purchases.create', $booking->id) }}" class="btn">➕ إضافة مشتريات</a>

                    <form id="checkoutForm" action="{{ route('booking.checkout', $booking->id) }}" method="POST"
                        style="display:inline;">
                        @csrf
                        <input type="hidden" name="booking" value="{{ $booking->id }}">
                        <input type="hidden" name="hours_total" id="hours_total"
                            value="{{ number_format($hours_total ?? 0, 2, '.', '') }}">
                        <input type="hidden" name="purchases_total" id="purchases_total"
                            value="{{ number_format($purchases_total ?? 0, 2, '.', '') }}">
                        <input type="hidden" name="deposit_paid" id="deposit_paid"
                            value="{{ number_format($deposit_paid ?? 0, 2, '.', '') }}">
                        <input type="hidden" name="hourly_rate" id="hourly_rate"
                            value="{{ number_format($bookingHourPrice ?? 0, 2, '.', '') }}">
                        <button type="submit" class="btn btn-danger">إنهاء الحساب</button>
                    </form>
                @elseif($booking->status === 'finished')
                    {{-- <a href="{{ route('invoices.show', $booking->id) }}" class="btn">🧾 عرض الفاتورة</a> --}}
                @elseif($booking->status === 'cancelled')
                    <span class="badge">هذا الحجز ملغي</span>
                @endif
            </div>


        </div>
    </div>

    <script>
        document.querySelectorAll(".invoiceForm").forEach(form => {
            form.addEventListener("submit", function(e) {
                e.preventDefault();
                let button = form.querySelector(".product-item");
                if (!button) return;
                let id = button.getAttribute("data-id");
                let item = [{
                    id: parseInt(id),
                    qty: 1
                }];
                form.querySelector(".itemsInput").value = JSON.stringify(item);
                form.submit();
            });
        });

        // تحديث الحقول المخفية قبل إرسال checkoutForm
        document.getElementById('checkoutForm')?.addEventListener('submit', function() {
            const hoursSpan = document.querySelector('.price-hours');
            const purchasesSpan = document.querySelector('.price-purchases');

            if (hoursSpan) {
                const hours = hoursSpan.textContent.replace(/[^\d.-]/g, '').trim();
                document.getElementById('hours_total').value = hours || 0;
            }
            if (purchasesSpan) {
                const purchases = purchasesSpan.textContent.replace(/[^\d.-]/g, '').trim();
                document.getElementById('purchases_total').value = purchases || 0;
            }
            // deposit_paid يأتي من السيرفر كقيمة افتراضية، لا حاجة لتغييره هنا
        });
    </script>
@endsection

@section('style')
    <style>
        body {
            background: #121212;
            /* دارك */
            color: #fff;
            font-family: "Tahoma", sans-serif;
        }

        /* ===== Snackbar ===== */
        .snackbar {
            background: #333;
            color: #fff;
        }

        .snackbar.success {
            background: #28a745;
        }

        .snackbar.error {
            background: #dc3545;
        }

        .subscription-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }

        .card {
            background: #1e1e1e;
            /* كارد غامق */
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.5);
            padding: 30px;
            animation: fadeInUp 0.6s ease;
            color: #fff;
            /* كل الكلام جوه أبيض */
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }

        .card-header h2 {
            font-size: 26px;
            margin: 0;
            color: #ffd700;
            /* الذهب للنص */
        }

        .badge {
            background: #ffd700;
            /* الذهب */
            color: #000;
            padding: 6px 15px;
            border-radius: 30px;
            font-weight: bold;
        }

        .section h3 {
            color: #ffd700;
            /* عناوين الأقسام بالذهبي */
            font-size: 20px;
            margin-bottom: 10px;
        }

        .box {
            background: #2a2a2a;
            /* صندوق الكارد غامق شويه */
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            color: #fff;
            /* نصوص بيضاء */
        }

        .price {
            font-weight: bold;
            font-size: 18px;
            color: #ffd700;
            /* أسعار بالذهبي */
        }

        /* الأزرار */
        .btn {
            border: none;
            padding: 12px 18px;
            border-radius: 12px;
            font-weight: bold;
            cursor: pointer;
            transition: .3s;
            font-size: 15px;
            color: #fff;
        }

        .btn.yellow {
            background: #ffd700;
            color: #000;
            border: 1px solid #e6c200;
        }

        .btn.green {
            background: #4caf50;
        }

        .btn.red,
        .btn.btn-danger {
            background: #f44336;
        }

        /* المنتجات المختارة */
        .selected-products span {
            background: #ffd700;
            color: #000;
        }

        /* الكروت */
        .product-item {
            background: #2a2a2a;
            border: 1px solid #444;
            border-radius: 12px;
            padding: 12px 14px;
            min-width: 120px;
            min-height: 70px;
            font-size: 14px;
            font-weight: 600;
            color: #fff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
            transition: all 0.25s ease;
            cursor: pointer;

            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .product-item:hover {
            transform: translateY(-4px) scale(1.03);
            border-color: #ffd700;
            box-shadow: 0 6px 16px rgba(255, 215, 0, 0.4);
            color: #ffd700;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endsection
