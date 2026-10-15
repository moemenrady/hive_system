<div id="editIngredientModal" class="modal">
    <div class="modal-content">
        <h3>✏️ تعديل خامة</h3>

        <form id="editIngredientForm">
            @csrf
            @method('PUT')

            <input type="hidden" id="ingredient_id">

            <label>الاسم</label>
            <input type="text" id="ingredient_name">

            <label>الكمية</label>
            <input type="number" step="0.01" id="ingredient_stock">

            <button class="btn-gold">حفظ</button>
            <button type="button" class="btn edit-btn" onclick="closeModal()">إلغاء</button>
        </form>
    </div>
</div>
