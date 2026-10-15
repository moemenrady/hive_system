@extends('layouts.app_page')
@section('title', 'الجلسات غير المسجلة')

@section('content')
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

    <div class="page-container">
    @section('page_title')
        <h1 class="title">الجلسات غير المسجلة</h1>
    @endsection

    {{-- صندوق البحث --}}
    <div class="search-box" style="margin-bottom:20px;">
        <input type="text" id="sessionSearch" placeholder="ابحث بالاسم أو رقم الهاتف أو ID"
            style="width:100%; padding:8px;">
    </div>
    <div class="d-flex gap-2 mb-3">
        <button id="selectModeBtn" class="btn btn-outline-primary btn-sm">
            Selection Mode
        </button>

        <button id="clearSelectionBtn" class="btn btn-outline-secondary btn-sm d-none">
            Clear All
        </button>

        <form id="bulkDeleteForm" method="POST" action="{{ route('session-not-added-bulk-delete') }}">
            @csrf
            @method('DELETE')
            <input type="hidden" name="ids" id="selectedIds">
            <button type="submit" class="btn btn-danger btn-sm d-none"
                onclick="return confirm('هل متأكد من حذف الجلسات المحددة؟')">
                Delete Selected
            </button>
        </form>
    </div>
    <form method="POST" action="{{ route('session-not-added-clear-all') }}"
        onsubmit="return confirm('⚠ هل أنت متأكد من حذف كل الجلسات غير المسجلة؟');">
        @csrf
        @method('DELETE')

        <button class="btn btn-danger btn-sm">
            🗑 Clear All Sessions
        </button>
    </form>

    {{-- قائمة الجلسات --}}
    <div class="subscription-list">
        @forelse ($sessions as $sess)
            <div class="session-card" data-id="{{ $sess->id }}">
                <input type="checkbox" class="session-checkbox d-none">

                <div class="info">
                    <h3>{{ $sess->client->name ?? '-' }}</h3>
                    <p>📞 {{ $sess->client->phone ?? '-' }}</p>
                    <p>عدد الأشخاص: {{ $sess->persons }}</p>
                    <p>{{ $sess->created_at->format('Y-m-d H:i') }}</p>
                </div>

                <div class="session-actions">
                    {{-- حذف فردي --}}
                    <form method="POST" action="{{ route('session-not-added-delete', $sess->id) }}">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">حذف</button>
                    </form>

                    {{-- بدء الجلسة --}}
                    <button type="button" class="btn btn-sm btn-success start-session-btn"
                        data-id="{{ $sess->id }}" data-name="{{ $sess->client->name ?? '' }}"
                        data-phone="{{ $sess->client->phone ?? '' }}" data-persons="{{ $sess->persons }}"
                        {{-- إضافة الوقت هنا --}} data-start-time="{{ $sess->created_at->format('Y-m-d H:i:s') }}">
                        بدء الجلسة
                    </button>
                </div>
            </div>

        @empty
            <p class="text-center p-3 text-muted">❌ لا توجد جلسات</p>
        @endforelse
    </div>
</div>

{{-- مودال البدء --}}
<div class="modal fade" id="startSessionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-lg p-3">
            <div class="modal-header">
                <h5 class="modal-title">بدء الجلسة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="startSessionForm" method="POST" action="{{ route('session.start-for-late') }}">
                @csrf
                <input type="hidden" name="session_id" id="modal_session_id">
                <input type="hidden" name="session_time" id="modal_session_time">
                <div class="modal-body">
                    <div class="mb-2">
                        <label>اسم العميل</label>
                        <input type="text" name="name" id="modal_name" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>رقم الهاتف</label>
                        <input type="text" name="phone" id="modal_phone" class="form-control" required>
                    </div>
                    <div class="mb-2 d-flex align-items-center">
                        <label style="margin-right:8px;">عدد الأشخاص:</label>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="minusPerson">-</button>
                        <input type="text" name="persons" id="modal_persons" value="1"
                            class="form-control text-center mx-1" style="width:60px;" readonly>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="plusPerson">+</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">بدء الجلسة</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const showModal = new bootstrap.Modal(document.getElementById('startSessionModal'));
        const modalName = document.getElementById('modal_name');
        const modalPhone = document.getElementById('modal_phone');
        const modalPersons = document.getElementById('modal_persons');
        const modalSessionId = document.getElementById('modal_session_id');
        const minusBtn = document.getElementById('minusPerson');
        const plusBtn = document.getElementById('plusPerson');

        document.querySelectorAll('.start-session-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                modalSessionId.value = this.dataset.id;
                modalName.value = this.dataset.name;
                modalPhone.value = this.dataset.phone;
                modalPersons.value = this.dataset.persons ?? 1;

                // تحديث حقل وقت الجلسة بالقيمة القادمة من الزر
                const sessionTimeInput = document.getElementById('modal_session_time');
                if (this.dataset.startTime) {
                    sessionTimeInput.value = this.dataset.startTime;
                } else {
                    // كخطة احتياطية لو التاريخ غير موجود نستخدم الوقت الحالي
                    sessionTimeInput.value = new Date().toISOString().slice(0, 19).replace('T',
                        ' ');
                }

                showModal.show();
            });
        });

        minusBtn.addEventListener('click', () => {
            let val = parseInt(modalPersons.value) || 1;
            if (val > 1) modalPersons.value = val - 1;
        });
        plusBtn.addEventListener('click', () => {
            let val = parseInt(modalPersons.value) || 1;
            modalPersons.value = val + 1;
        });
    });
</script>

{{-- Selection  --}}
<script>
    document.addEventListener("DOMContentLoaded", () => {

        let selectionMode = false;

        const selectBtn = document.getElementById('selectModeBtn');
        const clearBtn = document.getElementById('clearSelectionBtn');
        const deleteBtn = document.querySelector('#bulkDeleteForm button');
        const checkboxes = document.querySelectorAll('.session-checkbox');
        const selectedIdsInput = document.getElementById('selectedIds');

        selectBtn.onclick = () => {
            selectionMode = !selectionMode;

            checkboxes.forEach(cb => cb.classList.toggle('d-none', !selectionMode));
            clearBtn.classList.toggle('d-none', !selectionMode);
            deleteBtn.classList.toggle('d-none', !selectionMode);

            selectBtn.textContent = selectionMode ? 'Exit Selection' : 'Selection Mode';
        };

        clearBtn.onclick = () => {
            checkboxes.forEach(cb => cb.checked = false);
            selectedIdsInput.value = '';
        };

        checkboxes.forEach(cb => {
            cb.addEventListener('change', () => {
                const ids = [...checkboxes]
                    .filter(c => c.checked)
                    .map(c => c.closest('.session-card').dataset.id);

                selectedIdsInput.value = ids.join(',');
            });
        });

    });
</script>

@endsection

@section('style')
<style>
    /* ===============================
   Page container (Dark friendly)
================================ */
    .page-container {
        padding: 20px;
        color: #eee;
    }

    /* ===============================
   Search box
================================ */
    .search-box input {
        background: #1b1b1b;
        border: 1px solid rgba(255, 255, 255, .08);
        color: #fff;
        border-radius: 10px;
    }

    .search-box input::placeholder {
        color: #aaa;
    }

    /* ===============================
   Session Card (Dark Hive Style)
================================ */
    .session-card {
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 16px;
        border-radius: 14px;
        background: #151515;
        border: 1px solid rgba(255, 255, 255, 0.06);
        box-shadow: 0 10px 26px rgba(0, 0, 0, .45);
        cursor: default;
        margin-bottom: 14px;
        transition: all .25s ease;
    }

    .session-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 16px 36px rgba(0, 0, 0, .6);
        border-color: rgba(217, 177, 171, .35);
    }

    /* ===============================
   Info text
================================ */
    .session-card .info h3 {
        margin: 0;
        font-size: 15px;
        font-weight: 700;
        color: #fff;
    }

    .session-card .info p {
        margin: 4px 0 0;
        font-size: 13px;
        color: #cfcfcf;
    }

    /* ===============================
   Checkbox
================================ */
    .session-checkbox {
        margin-left: 10px;
        transform: scale(1.2);
        accent-color: var(--accent);
    }

    /* ===============================
   Actions
================================ */
    .session-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* ===============================
   Buttons tuning
================================ */
    .btn-sm {
        font-size: 13px;
        padding: 6px 10px;
        border-radius: 10px;
    }

    .btn-danger {
        background: linear-gradient(135deg, #eb5757, #c0392b);
        border: none;
    }

    .btn-success {
        background: linear-gradient(135deg, #27ae60, #1e8f4d);
        border: none;
    }

    .btn-outline-primary {
        color: #fff;
        border-color: rgba(255, 255, 255, .25);
    }

    .btn-outline-primary:hover {
        background: rgba(217, 177, 171, .15);
        border-color: var(--accent);
    }

    .btn-outline-secondary {
        color: #ccc;
        border-color: rgba(255, 255, 255, .15);
    }

    /* ===============================
   Empty state
================================ */
    .subscription-list p.text-muted {
        color: #aaa !important;
    }

    /* ===============================
   Modal Dark Adjust
================================ */
    .modal-content {
        background: #161616;
        color: #eee;
        border: 1px solid rgba(255, 255, 255, .08);
    }

    .modal-content label {
        color: #ddd;
    }

    .modal-content .form-control {
        background: #1f1f1f;
        border: 1px solid rgba(255, 255, 255, .1);
        color: #fff;
    }

    .modal-content .form-control:focus {
        background: #1f1f1f;
        border-color: var(--accent);
        box-shadow: none;
    }
</style>
@endsection
