<div id="unitsModal" class="modal">
    <div class="modal-content">
        <h3>⚖️ الوحدات</h3>

        <form method="POST" action="{{ route('units.store') }}" style="margin-bottom:15px;">
            @csrf
            <input type="text" name="name" placeholder="اسم الوحدة" required>
            <input type="text" name="symbol" placeholder="الرمز" required>
            <button class="btn" style="margin-top:8px;">➕ إضافة</button>
        </form>

        <hr style="border-color:#333; margin:15px 0;">

        @foreach($units as $unit)
            <div class="recipe-row">
                <span>{{ $unit->name }}</span>
                <span>{{ $unit->symbol }}</span>
            </div>
        @endforeach

        <button class="btn-gold" onclick="closeModal()" style="margin-top:15px;">إغلاق</button>
    </div>
</div>
