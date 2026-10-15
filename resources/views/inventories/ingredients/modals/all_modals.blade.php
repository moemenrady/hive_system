<style>
    /* أنيميشن الظهور */
    @keyframes modalSlideUp {
        from {
            transform: translateY(30px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .modal-content {
        animation: modalSlideUp 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        border: 1px solid var(--border-color);
        background: var(--card-surface);
        color: var(--text-bright);
    }

    .modal-header {
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 15px;
        margin-bottom: 20px;
    }

    .modal-header h3 {
        margin: 0;
        font-size: 1.4rem;
    }

    /* تحسين شكل المدخلات داخل المودال */
    .modal .form-group input,
    .modal .form-group select {
        background: #14141b !important;
        border: 1px solid #2d2d3a !important;
        color: #fff !important;
        margin-top: 5px;
    }

    .modal .form-group input:focus {
        border-color: var(--primary-gold) !important;
        box-shadow: 0 0 8px rgba(230, 201, 122, 0.2);
    }

    .modal .form-group label {
        color: var(--primary-gold);
        font-size: 0.9rem;
        font-weight: 500;
    }

    /* شكل عناصر القائمة (الوحدات) */
    .unit-list-item {
        background: rgba(255, 255, 255, 0.03);
        padding: 12px;
        border-radius: 10px;
        margin-bottom: 8px;
        display: flex;
        justify-content: space-between;
        border: 1px solid transparent;
        transition: 0.3s;
    }

    .unit-list-item:hover {
        border-color: var(--primary-gold);
        background: rgba(230, 201, 122, 0.05);
    }
</style>

<div id="addIngredientModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="gold-burn">➕ إضافة خامة جديدة</h3>
        </div>
        <form method="POST" action="{{ route('ingredients.store') }}">
            @csrf
            <div class="form-group">
                <label>اسم الخامة</label>
                <input type="text" name="name" class="custom-input" placeholder="مثلاً: سكر، بن برازيلي..."
                    required>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>الكمية الافتتاحية</label>
                    <input type="number" step="0.01" name="stock" class="custom-input" placeholder="0.00"
                        required>
                </div>
                <div class="form-group">
                    <label>كمية الإنذار (Alert)</label>
                    <input type="number" step="0.01" name="alert_stock" class="custom-input" value="0"
                        required>
                </div>
            </div>
            <div class="form-group">
                <label>الوحدة الأساسية</label>
                <select name="unit_id" class="custom-input" required>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->symbol }})</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex; gap:10px; margin-top:25px;">
                <button type="submit" class="btn-gold" style="flex: 2;">حفظ الخامة</button>
                <button type="button" class="btn-outline" style="flex: 1;" onclick="closeModal()">إلغاء</button>
            </div>
        </form>
    </div>
</div>

<div id="editIngredientModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="gold-burn">✏️ تعديل بيانات الخامة</h3>
        </div>
        <form id="editForm" method="POST">
            @csrf
            <input type="hidden" name="id" id="edit_ingredient_id">
            <div class="form-group">
                <label>الاسم</label>
                <input type="text" name="name" id="edit_ingredient_name" class="custom-input" required>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>الكمية الحالية</label>
                    <input type="number" step="0.01" name="stock" id="edit_ingredient_stock" class="custom-input"
                        required>
                </div>
                <div class="form-group">
                    <label>حد الإنذار</label>
                    <input type="number" step="0.01" name="alert_stock" id="edit_ingredient_alert_stock"
                        class="custom-input" required>
                </div>
            </div>
            <div style="display:flex; gap:10px; margin-top:25px;">
                <button type="submit" class="btn-gold" style="flex: 1;">تحديث البيانات</button>
                <button type="button" class="btn-outline" onclick="closeModal()">إغلاق</button>
            </div>
        </form>
    </div>
</div>

<div id="unitsModal" class="modal">
    <div class="modal-content" style="max-width: 450px; width: 90%;">
        <div class="modal-header">
            <h3 class="gold-burn">⚖️ إدارة الوحدات</h3>
        </div>

        <form method="POST" action="{{ route('units.store') }}">
            @csrf
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <div style="display: flex; gap: 10px;">
                    <div class="form-group" style="flex: 2; margin:0;">
                        <label>اسم الوحدة</label>
                        <input type="text" name="name" class="custom-input" style="width: 100%;"
                            placeholder="كيلو" required>
                    </div>
                    <div class="form-group" style="flex: 1; margin:0;">
                        <label>الرمز</label>
                        <input type="text" name="symbol" class="custom-input" style="width: 100%;" placeholder="كغ"
                            required>
                    </div>
                </div>

                <button type="submit" class="btn-gold" style="width:100%; height: 50px;">
                    ➕ إضافة وحدة جديدة
                </button>
            </div>
        </form>

        <div style="margin-top: 25px; max-height: 200px; overflow-y: auto; padding-left: 5px; scrollbar-width: thin;">
            <p
                style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 12px; border-bottom: 1px solid var(--border-color); padding-bottom: 5px;">
                الوحدات المسجلة حالياً:
            </p>
            @foreach ($units as $unit)
                <div class="unit-list-item"
                    style="display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.02); margin-bottom: 5px; padding: 10px 15px; border-radius: 12px;">
                    <span style="font-weight: 500; color: var(--text-bright);">{{ $unit->name }}</span>
                    <span
                        style="background: var(--primary-gold); color: #000; padding: 2px 10px; border-radius: 8px; font-size: 0.8rem; font-weight: bold;">
                        {{ $unit->symbol }}
                    </span>
                </div>
            @endforeach
        </div>

        <button class="btn-outline" onclick="closeModal()"
            style="width:100%; margin-top:20px; border-color: rgba(255,255,255,0.1);">
            إغلاق النافذة
        </button>
    </div>
</div>

<div id="addStockModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="gold-burn">📥 توريد / إضافة كمية</h3>
        </div>
        <form id="addStockForm" method="POST">
            @csrf
            <div class="form-group">
                <label>الخامة المختارة</label>
                <input type="text" id="stock_ingredient_name" class="custom-input" style="opacity: 0.7;"
                    disabled>
            </div>
            <div class="form-group">
                <label>الرصيد الحالي بالمخزن</label>
                <input type="text" id="stock_current_amount" class="custom-input"
                    style="border-style: dashed !important; opacity: 0.7;" disabled>
            </div>
            <div class="form-group">
                <label>الكمية الجديدة المراد إضافتها</label>
                <input type="number" step="0.01" name="amount" class="custom-input" placeholder="0.00"
                    autofocus required>
            </div>
            <div class="form-group">
                <label>ملاحظات التوريد</label>
                <input type="text" name="note" class="custom-input" placeholder="مثلاً: فاتورة رقم #123">
            </div>
            <div style="display:flex; gap:10px; margin-top:25px;">
                <button type="submit" class="btn-gold" style="flex: 1;">تأكيد الإضافة</button>
                <button type="button" class="btn-outline" onclick="closeModal()">إلغاء</button>
            </div>
        </form>
    </div>
</div>
