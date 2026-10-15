<div class="modal fade animate__animated animate__fadeInDown" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow" style="background: #2e2e33; color: #f5f5f5;">
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title">📂 إدارة الأصناف</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="p-3 rounded-3 mb-4"
                    style="background: rgba(255, 255, 255, 0.05); border: 1px dashed #6c5ce7;">
                    <h6 class="text-gold mb-3"><i class="fas fa-plus-circle"></i> إضافة صنف جديد</h6>
                    <form action="{{ route('categories.store') }}" method="POST">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="name" class="form-control shadow-none"
                                placeholder="اسم الصنف الجديد..." required
                                style="background: #3b3b44; border: 1px solid #555; color: #fff;">
                            <button type="submit" class="btn btn-primary fw-bold">
                                ✅ حفظ
                            </button>
                        </div>
                    </form>
                </div>

                <h6 class="text-gold mb-2 mt-4"><i class="fas fa-list"></i> الأصناف الحالية:</h6>
                <div class="categories-list scrollbar-custom" style="max-height: 250px; overflow-y: auto;">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0" style="background: transparent;">
                            <thead class="text-muted" style="font-size: 0.8rem;">
                                <tr>
                                    <th>#</th>
                                    <th>اسم الصنف</th>
                                    <th class="text-center">الإجراء</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                    <tr style="border-color: #444;">
                                        <td class="text-muted">{{ $loop->iteration }}</td>
                                        <td class="fw-bold">{{ $category->name }}</td>
                                        <td class="text-center">
                                            <form action="{{ route('categories.destroy', $category->id) }}"
                                                method="POST"
                                                onsubmit="return confirm('هل أنت متأكد من حذف هذا الصنف؟')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="btn btn-sm btn-outline-danger rounded-circle"
                                                    style="width:32px; height:32px;">
                                                    <i class="fas fa-trash"></i>
                                                </button>

                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">لا توجد أصناف مضافة
                                            حالياً</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* تنسيق السكرول بار ليناسب الشكل الداكن */
    .scrollbar-custom::-webkit-scrollbar {
        width: 6px;
    }

    .scrollbar-custom::-webkit-scrollbar-track {
        background: #2e2e33;
    }

    .scrollbar-custom::-webkit-scrollbar-thumb {
        background: #6c5ce7;
        border-radius: 10px;
    }

    .text-gold {
        color: #ffd700 !important;
    }
</style>
