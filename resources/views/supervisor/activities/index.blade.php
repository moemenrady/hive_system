@extends('layouts.app_page')

@section('content')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root {
        --card-bg: rgba(30, 41, 59, 0.7);
        --primary-glow: #3b82f6;
        --danger-glow: #ef4444;
        --text-main: #f8fafc;
        --text-muted: #94a3b8;
    }

    .activities-container {
        direction: rtl;
        font-family: 'Tajawal', sans-serif; /* يفضل استخدام خط عربي مثل تجوال */
        background-color: var(--dark-bg);
        min-height: 100vh;
        padding: 2rem;
        color: var(--text-main);
    }

    /* أنيميشن الدخول */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* عنوان المشرف المائل لليمين */
    .supervisor-section {
        margin-bottom: 3rem;
        animation: fadeInUp 0.6s ease-out forwards;
    }

    .right-title {
        font-size: 1.5rem;
        font-weight: bold;
        color: var(--text-main);
        text-align: right;
        padding-right: 15px;
        border-right: 4px solid var(--primary-glow);
        margin-bottom: 1.5rem;
        position: relative;
        text-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
    }

    /* شبكة الكروت (الأنشطة) */
    .activities-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    /* تصميم الكارت - Glassmorphism */
    .activity-card {
        background: var(--card-bg);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 15px;
        padding: 1.5rem;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        position: relative;
        overflow: hidden;
        opacity: 0;
        animation: fadeInUp 0.5s ease-out forwards;
    }

    /* تأخير ظهور الكروت لعمل تأثير متتالي */
    .activity-card:nth-child(1) { animation-delay: 0.1s; }
    .activity-card:nth-child(2) { animation-delay: 0.2s; }
    .activity-card:nth-child(3) { animation-delay: 0.3s; }
    .activity-card:nth-child(4) { animation-delay: 0.4s; }

    /* تأثيرات الـ Hover */
    .activity-card:hover {
        transform: translateY(-5px) scale(1.02);
        box-shadow: 0 10px 25px rgba(59, 130, 246, 0.2);
        border-color: rgba(59, 130, 246, 0.5);
    }

    /* شريط جانبي مضيء للكارت */
    .activity-card::before {
        content: '';
        position: absolute;
        top: 0; right: 0;
        width: 4px; height: 100%;
        background: var(--primary-glow);
        box-shadow: 0 0 15px var(--primary-glow);
        transform: scaleY(0);
        transition: transform 0.3s ease;
        transform-origin: top;
    }

    .activity-card:hover::before {
        transform: scaleY(1);
    }

    /* نصوص الكارت */
    .action-badge {
        display: inline-block;
        background: rgba(59, 130, 246, 0.2);
        color: #60a5fa;
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: bold;
        margin-bottom: 1rem;
    }

    .action-desc {
        font-size: 1rem;
        color: var(--text-muted);
        margin-bottom: 1.5rem;
        line-height: 1.6;
    }

    .action-date {
        font-size: 0.8rem;
        color: rgba(148, 163, 184, 0.6);
        display: flex;
        align-items: center;
        gap: 5px;
    }

    /* الأزرار */
    .card-actions {
        display: flex;
        gap: 10px;
        margin-top: 15px;
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
        background: rgba(59, 130, 246, 0.1);
        color: #60a5fa;
    }
    .btn-edit:hover {
        background: #3b82f6;
        color: white;
        box-shadow: 0 0 15px rgba(59, 130, 246, 0.5);
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

    /* Modal Styling */
    .dark-modal {
        background: var(--dark-bg) !important;
        color: var(--text-main) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
    }
</style>

<div class="activities-container">
    
    @if(session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'نجاح!',
                text: '{{ session("success") }}',
                background: '#1e293b',
                color: '#fff',
                showConfirmButton: false,
                timer: 2000
            });
        </script>
    @endif

    @forelse($groupedActivities as $supervisorId => $activities)
        <div class="supervisor-section">
            <h2 class="right-title">
                <i class="fa-solid fa-user-shield ml-2"></i> 
                حركات المشرف: <span style="color: #60a5fa;">{{ $activities->first()->supervisor->name ?? 'مستخدم محذوف' }}</span>
            </h2>

            <div class="activities-grid">
                @foreach($activities as $activity)
                    <div class="activity-card">
                        <div class="action-badge">
                            <i class="fa-solid fa-bolt ml-1"></i> {{ $activity->action }}
                        </div>
                        <p class="action-desc">
                            {{ $activity->description ?? 'لا يوجد تفاصيل إضافية لهذا الإجراء.' }}
                        </p>
                        <div class="action-date">
                            <i class="fa-regular fa-clock"></i> 
                            {{ $activity->created_at->diffForHumans() }} 
                            <span style="font-size: 10px; opacity: 0.5">({{ $activity->created_at->format('Y-m-d H:i') }})</span>
                        </div>

                        <div class="card-actions">
                            <button class="btn-action btn-edit" onclick="editActivity({{ $activity->id }}, '{{ addslashes($activity->action) }}', '{{ addslashes($activity->description) }}')">
                                <i class="fa-solid fa-pen-to-square"></i> تعديل
                            </button>

                            <form action="{{ route('activities.destroy', $activity->id) }}" method="POST" id="delete-form-{{ $activity->id }}" style="flex: 1;">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn-action btn-delete w-100" onclick="confirmDelete({{ $activity->id }})">
                                    <i class="fa-solid fa-trash-can"></i> حذف
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div style="text-align: center; padding: 50px; color: var(--text-muted);">
            <i class="fa-solid fa-folder-open" style="font-size: 4rem; margin-bottom: 20px; opacity: 0.5;"></i>
            <h3>لا توجد حركات مسجلة حتى الآن.</h3>
        </div>
    @endforelse

</div>

<form id="edit-form" method="POST" style="display: none;">
    @csrf
    @method('PUT')
    <input type="hidden" name="action" id="edit-action-input">
    <input type="hidden" name="description" id="edit-desc-input">
</form>

<script>
    // أنيميشن وتأكيد الحذف باستخدام SweetAlert2
    function confirmDelete(id) {
        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: "لن تتمكن من التراجع عن هذا الإجراء!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#3b82f6',
            confirmButtonText: 'نعم، احذف الحركة!',
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

    // واجهة تعديل سريعة باستخدام SweetAlert2
    async function editActivity(id, currentAction, currentDesc) {
        const { value: formValues } = await Swal.fire({
            title: 'تعديل الحركة',
            html:
                `<input id="swal-input1" class="swal2-input" value="${currentAction}" placeholder="نوع الحركة" style="direction: rtl;">` +
                `<textarea id="swal-input2" class="swal2-textarea" placeholder="الوصف والتفاصيل" style="direction: rtl;">${currentDesc}</textarea>`,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'حفظ التعديلات',
            cancelButtonText: 'إلغاء',
            background: '#1e293b',
            color: '#f8fafc',
            confirmButtonColor: '#3b82f6',
            preConfirm: () => {
                return [
                    document.getElementById('swal-input1').value,
                    document.getElementById('swal-input2').value
                ]
            }
        });

        if (formValues) {
            let form = document.getElementById('edit-form');
            form.action = `/supervisor-activities/${id}`;
            document.getElementById('edit-action-input').value = formValues[0];
            document.getElementById('edit-desc-input').value = formValues[1];
            form.submit();
        }
    }
</script>

@endsection