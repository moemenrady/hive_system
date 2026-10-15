<div id="addRecipeModal" class="modal">
    <div class="modal-content">
        <h3>🧪 إضافة ريسبي</h3>

        <form method="POST" action="{{ route('product-recipes.store') }}">
            @csrf

            <label>المنتج</label>
            <select name="product_id" required>
                @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                @endforeach
            </select>

            <label>الخامة</label>
            <select name="ingredient_id" required>
                @foreach($ingredients as $ingredient)
                    <option value="{{ $ingredient->id }}">{{ $ingredient->name }}</option>
                @endforeach
            </select>

            <label>الكمية المستخدمة</label>
            <input type="number" step="0.01" name="amount" required>

            <label>الوحدة</label>
            <select name="unit_id" required>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->symbol }}</option>
                @endforeach
            </select>

            <div style="margin-top:15px; display:flex; gap:10px;">
                <button class="btn-gold">حفظ</button>
                <button type="button" class="btn edit-btn" onclick="closeModal()">إلغاء</button>
            </div>
        </form>
    </div>
</div>
