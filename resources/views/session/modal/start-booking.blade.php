@php
    $halls = $halls ?? \App\Models\Hall::all();
@endphp

<!-- مودال بدء جلسة خاصة -->
<div class="modal fade animate__animated animate__zoomIn" id="startBookingModal" tabindex="-1" aria-labelledby="startBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-lg border-0" style="background: #1a1a1f; color: #f5f5f5;">
            <div class="modal-header border-bottom border-secondary d-flex align-items-center" style="background: #24242c;">
                <button type="button" class="btn-close btn-close-white me-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                <h5 class="modal-title mx-auto text-warning font-weight-bold" id="startBookingModalLabel">⚡ بدء جلسة خاصة (حجز فوري)</h5>
            </div>

            <div class="modal-body px-4 pb-3 pt-3">
                <form id="startBookingForm" action="{{ route('bookings.start-now') }}" method="POST">
                    @csrf
                    <input type="hidden" id="modal_client_id" name="client_id" value="">

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-light small mb-1">اسم العميل <span class="text-danger">*</span></label>
                            <input type="text" id="modal_name" name="client_name" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="اسم العميل" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-light small mb-1">رقم الهاتف <span class="text-danger">*</span></label>
                            <input type="tel" id="modal_phone" name="client_phone" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="01xxxxxxxxx" maxlength="11" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-light small mb-1">اختار القاعة <span class="text-danger">*</span></label>
                        <select id="hallSelect" name="hall_id" class="form-select form-select-sm bg-dark text-white border-secondary" required>
                            <option value="">-- اختر القاعة --</option>
                            @foreach($halls as $hall)
                                <option value="{{ $hall->id }}">{{ $hall->name }} (السعة: {{ $hall->min_capacity }} - {{ $hall->max_capacity }} فرد)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-light small mb-1">عدد الأشخاص <span class="text-danger">*</span></label>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-warning" id="modalDecreasePersons">➖</button>
                            <input type="number" id="modal_persons" name="attendees" class="form-control form-control-sm bg-dark text-white border-secondary text-center" value="1" min="1" style="max-width: 90px;" required>
                            <button type="button" class="btn btn-sm btn-outline-warning" id="modalIncreasePersons">➕</button>
                            <span id="personsDisplayInModal" class="d-none">1</span>
                        </div>
                    </div>

                    <div id="estimateBanner" class="p-3 rounded-3 mb-2" style="display:none; background: #262633; border: 1px solid rgba(255,215,0,0.2);">
                        <div id="estimateMessage" style="font-weight:600; color: #ffc107; margin-bottom:4px;">جارِ الحساب...</div>
                        <div id="estimateAmount" style="font-size:18px; font-weight:700; color:#4ade80;"></div>
                        <div id="estimatePerHour" style="font-size:12px; color:#aaa;"></div>
                    </div>

                    <div id="ongoingWarning" class="alert alert-danger py-2 small" style="display:none; margin-top:8px;">
                        <strong>⚠️ لا يمكن البدء:</strong> <span id="ongoingText"></span>
                    </div>

                    <input type="hidden" name="duration_minutes" value="60">

                    <div class="d-flex gap-2 mt-4">
                        <button type="button" class="btn btn-secondary w-50" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" id="startNowBtn" class="btn btn-warning font-weight-bold w-50" disabled>🚀 بدء الآن</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
