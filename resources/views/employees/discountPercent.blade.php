@extends('layouts.app_page')

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --dark-bg: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --primary-glow: #3b82f6;
            --danger-glow: #ef4444;
            --warning-glow: #f59e0b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        .activities-container {
            direction: rtl;
            font-family: 'Tajawal', sans-serif;
            background-color: var(--dark-bg);
            min-height: 100vh;
            padding: 2rem;
            color: var(--text-main);
        }

        /* تقسيم الشاشة (ريسبونسف) */
        .page-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        @media (min-width: 992px) {
            .page-layout {
                grid-template-columns: 350px 1fr;
                /* النموذج يمين، والكروت يسار */
                align-items: start;
            }
        }

        /* أنيميشن الدخول */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* تصميم النموذج الجانبي (Glassmorphism) */
        .form-panel {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            position: sticky;
            top: 2rem;
            animation: fadeInUp 0.5s ease-out forwards;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }

        .form-panel.edit-mode {
            border-color: var(--warning-glow);
            box-shadow: 0 0 20px rgba(245, 158, 11, 0.2);
        }

        .form-title {
            font-size: 1.4rem;
            font-weight: bold;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #60a5fa;
        }

        .custom-input-group {
            margin-bottom: 1.5rem;
        }

        .custom-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .custom-input {
            width: 100%;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-main);
            padding: 12px 15px;
            border-radius: 10px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            outline: none;
        }

        .custom-input:focus {
            border-color: var(--primary-glow);
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.3);
        }

        .input-icon-wrapper {
            position: relative;
        }

        .input-icon-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        /* الأزرار */
        .btn-main {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            font-size: 1rem;
        }

        .btn-submit {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
        }

        .btn-submit:hover {
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.5);
            transform: translateY(-2px);
        }

        .btn-cancel {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-main);
            margin-top: 10px;
            display: none;
            /* مخفي افتراضيا */
        }

        .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* شبكة الكروت */
        .activities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        /* تصميم الكارت */
        .activity-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 1.5rem;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.5s ease-out forwards;
        }

        .activity-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.15);
            border-color: rgba(59, 130, 246, 0.4);
        }

        .percent-badge {
            font-size: 2.5rem;
            font-weight: 900;
            color: var(--text-main);
            text-shadow: 0 0 15px rgba(59, 130, 246, 0.6);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .setter-info {
            font-size: 0.95rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .action-date {
            font-size: 0.8rem;
            color: rgba(148, 163, 184, 0.6);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* أزرار الكارت */
        .card-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding-top: 15px;
        }

        .btn-action {
            flex: 1;
            padding: 8px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-edit {
            background: rgba(245, 158, 11, 0.1);
            color: #fbbf24;
        }

        .btn-edit:hover {
            background: #f59e0b;
            color: white;
            box-shadow: 0 0 15px rgba(245, 158, 11, 0.5);
        }

        .btn-delete {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
        }

        .btn-delete:hover {
            background: #ef4444;
            color: white;
            box-shadow: 0 0 15px rgba(239, 68, 68, 0.5);
        }

        .dark-modal {
            background: var(--dark-bg) !important;
            color: var(--text-main) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
        }
    </style>

    <div class="activities-container">

        @if (session('success'))
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'تم بنجاح!',
                    text: '{{ session('success') }}',
                    background: '#1e293b',
                    color: '#fff',
                    showConfirmButton: false,
                    timer: 2000
                });
            </script>
        @endif

        @if ($errors->any())
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ!',
                    text: '{{ $errors->first() }}',
                    background: '#1e293b',
                    color: '#fff',
                });
            </script>
        @endif

        <div class="page-layout">
            <!-- قسم النموذج (ثابت ومتجاوب) -->
            <div class="form-panel" id="actionPanel">
                <h3 class="form-title" id="formTitle">
                    <i class="fa-solid fa-plus-circle"></i>
                    <span>إضافة نسبة خصم جديدة</span>
                </h3>

                <form id="discountForm" action="{{ route('employees.storeDiscountPercent') }}" method="POST">
                    @csrf
                    <div id="methodContainer"></div> <!-- لحقن @method('PUT') عبر الجافاسكريبت -->

                    <div class="custom-input-group">
                        <label class="custom-label">نسبة الخصم المتفق عليها (%)</label>
                        <div class="input-icon-wrapper">
                            <input type="number" step="0.01" min="0" max="100" name="base_hour_price"
                                id="discountInput" class="custom-input" placeholder="مثال: 25" required>
                            <i class="fa-solid fa-percent"></i>
                        </div>
                        <small style="color: var(--text-muted); font-size: 0.75rem; margin-top: 5px; display: block;">سيتم
                            تسجيل اسمك <b>({{ Auth::user()->name }})</b> كمسؤول عن هذا الإجراء تلقائياً.</small>
                    </div>

                    <button type="submit" class="btn-main btn-submit" id="submitBtn">
                        <i class="fa-solid fa-check"></i> حفظ النسبة
                    </button>

                    <button type="button" class="btn-main btn-cancel" id="cancelBtn" onclick="resetForm()">
                        <i class="fa-solid fa-times"></i> إلغاء التعديل
                    </button>
                </form>
            </div>

            <!-- قسم عرض الكروت (السجلات) -->
            <div class="cards-section">
                <div class="activities-grid">
                    @forelse($discounts as $discount)
                        <div class="activity-card" style="animation-delay: {{ $loop->index * 0.1 }}s;">

                            <div class="percent-badge">
                                {{ floatval($discount->base_hour_price) }}%
                            </div>

                            <div class="setter-info">
                                <i class="fa-solid fa-user-pen"></i>
                                بواسطة: {{ $discount->setter_name }}
                            </div>

                            <div class="action-date">
                                <i class="fa-regular fa-clock"></i>
                                {{ $discount->created_at->diffForHumans() }}
                                <span
                                    style="font-size: 10px; opacity: 0.5">({{ $discount->created_at->format('Y-m-d H:i') }})</span>
                            </div>

                            <div class="card-actions">
                                <button class="btn-action btn-edit"
                                    onclick="triggerEdit({{ $discount->id }}, '{{ floatval($discount->base_hour_price) }}')">
                                    <i class="fa-solid fa-pen-to-square"></i> تعديل سريع
                                </button>

                                <form action="{{ route('employees.destroyDiscountPercent', $discount->id) }}"
                                    method="POST" id="delete-form-{{ $discount->id }}" style="flex: 1;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn-action btn-delete w-100"
                                        onclick="confirmDelete({{ $discount->id }})">
                                        <i class="fa-solid fa-trash-can"></i> حذف
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div style="grid-column: 1 / -1; text-align: center; padding: 50px; color: var(--text-muted);">
                            <i class="fa-solid fa-mug-hot" style="font-size: 4rem; margin-bottom: 20px; opacity: 0.3;"></i>
                            <h3>لا توجد نسب خصم مسجلة للموظفين حتى الآن.</h3>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script>
        // تأكيد الحذف
        function confirmDelete(id) {
            Swal.fire({
                title: 'هل أنت متأكد؟',
                text: "لن تتمكن من التراجع عن حذف هذه النسبة!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#3b82f6',
                confirmButtonText: 'نعم، احذف!',
                cancelButtonText: 'إلغاء',
                background: '#1e293b',
                color: '#f8fafc',
                customClass: {
                    popup: 'dark-modal'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            })
        }

        // تفعيل وضع التعديل (بدون Modals)
        function triggerEdit(id, currentPercent) {
            const form = document.getElementById('discountForm');
            const input = document.getElementById('discountInput');
            const methodContainer = document.getElementById('methodContainer');
            const titleSpan = document.querySelector('#formTitle span');
            const titleIcon = document.querySelector('#formTitle i');
            const submitBtn = document.getElementById('submitBtn');
            const cancelBtn = document.getElementById('cancelBtn');
            const panel = document.getElementById('actionPanel');

            // تغيير مسار الفورم ليصبح للتعديل
            form.action = `/employees/discount-percent/${id}`;

            // حقن طريقة PUT
            methodContainer.innerHTML = '<input type="hidden" name="_method" value="PUT">';

            // وضع القيمة الحالية
            input.value = currentPercent;

            // تغيير شكليات الفورم ليوحي بالتعديل
            titleSpan.innerText = 'تعديل نسبة الخصم';
            titleIcon.className = 'fa-solid fa-pen-to-square';
            titleIcon.style.color = '#fbbf24';

            submitBtn.innerHTML = '<i class="fa-solid fa-save"></i> حفظ التعديلات';
            submitBtn.style.background = 'linear-gradient(135deg, #f59e0b, #d97706)';

            cancelBtn.style.display = 'flex';
            panel.classList.add('edit-mode');

            // عمل تمرير (Scroll) ناعم للموبايل ليرى المستخدم الفورم
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });

            // التركيز على حقل الإدخال
            setTimeout(() => input.focus(), 300);
        }

        // العودة لوضع الإضافة
        function resetForm() {
            const form = document.getElementById('discountForm');
            const input = document.getElementById('discountInput');
            const methodContainer = document.getElementById('methodContainer');
            const titleSpan = document.querySelector('#formTitle span');
            const titleIcon = document.querySelector('#formTitle i');
            const submitBtn = document.getElementById('submitBtn');
            const cancelBtn = document.getElementById('cancelBtn');
            const panel = document.getElementById('actionPanel');

            // إعادة المسار للإضافة
            form.action = "{{ route('employees.storeDiscountPercent') }}";
            methodContainer.innerHTML = ''; // إزالة PUT
            input.value = '';

            // استعادة الشكليات
            titleSpan.innerText = 'إضافة نسبة خصم جديدة';
            titleIcon.className = 'fa-solid fa-plus-circle';
            titleIcon.style.color = '#60a5fa';

            submitBtn.innerHTML = '<i class="fa-solid fa-check"></i> حفظ النسبة';
            submitBtn.style.background = 'linear-gradient(135deg, #3b82f6, #2563eb)';

            cancelBtn.style.display = 'none';
            panel.classList.remove('edit-mode');
        }
    </script>
@endsection
