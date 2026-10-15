<div id="addIngredientModal" class="modal">
    <div class="modal-content">
        <h3>➕ إضافة خامة</h3>

        <form method="POST" action="{{ route('ingredients.store') }}">
            @csrf

            <label>اسم الخامه</label>
            <input type="text" name="name" required>

            <label>الكمية</label>
            <input type="number" step="0.01" name="stock" required>

            <label>الوحدة</label>
            <select name="unit_id" required>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}">
                        {{ $unit->name }} ({{ $unit->symbol }})
                    </option>
                @endforeach
            </select>

            <div style="margin-top:15px; display:flex; gap:10px;">
                <button class="btn-gold">حفظ</button>
                <button type="button" class="btn edit-btn" onclick="closeModal()">إلغاء</button>
            </div>
        </form>
    </div>
</div>
