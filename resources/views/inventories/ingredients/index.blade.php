@extends('layouts.app_page')

@section('title', 'إدارة الخامات والمخزون')

@section('style')
    <style>
        :root {
            --primary-gold: #e6c97a;
            --dark-gold: #b39a5a;
            --bg-deep: #0e0e12;
            --card-surface: #1c1c24;
            --card-hover: #252531;
            --text-bright: #ffffff;
            --text-muted: #a0a0ab;
            --danger: #ff5f5f;
            --success: #4ade80;
            --border-color: rgba(255, 255, 255, 0.08);
            --modal-bg: rgba(10, 10, 15, 0.95);
        }

        .subscription-container {
            padding: 25px;
            max-width: 1300px;
            margin: 0 auto;
            direction: rtl;
        }

        /* هيدر الصفحة والبحث */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 35px;
            padding: 25px;
            background: linear-gradient(145deg, #1c1c24, #14141b);
            border-radius: 24px;
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .search-wrapper {
            position: relative;
            flex: 1;
            min-width: 300px;
        }

        .search-input {
            width: 100%;
            padding: 12px 45px 12px 15px;
            background: rgba(0, 0, 0, 0.2) !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 15px;
            color: white !important;
            transition: 0.3s;
        }

        .search-input:focus {
            border-color: var(--primary-gold) !important;
            box-shadow: 0 0 15px rgba(230, 201, 122, 0.1);
        }

        .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        /* شبكة الكروت */
        .ingredients-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }

        .ingredient-card {
            background: var(--card-surface);
            border: 1px solid var(--border-color);
            border-radius: 22px;
            padding: 22px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: pointer;
        }

        .ingredient-card:hover {
            transform: translateY(-10px);
            border-color: var(--primary-gold);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
        }

        .ingredient-card h3 {
            color: var(--primary-gold);
            margin-bottom: 20px;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .stock-badge {
            background: rgba(230, 201, 122, 0.1);
            color: var(--primary-gold);
            padding: 8px 18px;
            border-radius: 14px;
            font-size: 1.2rem;
            font-weight: bold;
            border: 1px solid rgba(230, 201, 122, 0.2);
        }

        /* تحسين المودال */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(12px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 20px;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .modal-content {
            background: var(--card-surface);
            width: 100%;
            max-width: 550px;
            border-radius: 28px;
            padding: 35px;
            border: 1px solid rgba(230, 201, 122, 0.2);
            position: relative;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.6);
        }

        .modal-header h3 {
            color: var(--primary-gold);
            font-size: 1.6rem;
            margin: 0;
        }

        /* الحقول */
        .form-group label {
            color: var(--text-muted);
            margin-bottom: 10px;
            display: block;
            font-size: 0.95rem;
        }

        .custom-input {
            background: #0e0e12 !important;
            border: 1px solid #2d2d3a !important;
            color: #fff !important;
            height: 50px;
            border-radius: 12px;
            padding: 0 15px;
            font-size: 1rem;
        }

        /* الأزرار */
        .btn-gold {
            background: var(--primary-gold);
            color: #000;
            font-weight: 700;
            padding: 12px 25px;
            border-radius: 12px;
            border: none;
            transition: 0.3s;
        }

        .btn-gold:hover {
            background: #fff;
            transform: scale(1.02);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            padding: 10px 20px;
            border-radius: 12px;
        }

        /* كلاس لتمييز الكارت لما يكون المخزون قليل */
        .low-stock-warning {
            border: 2px solid var(--danger) !important;
            background: linear-gradient(145deg, #2a1c1c, #1c1c24) !important;
            animation: pulse-red 2s infinite;
        }

        @keyframes pulse-red {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 95, 95, 0.4);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(255, 95, 95, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(255, 95, 95, 0);
            }
        }
    </style>
@endsection

@section('content')
    <div class="subscription-container">

        <div class="page-header">
            <div style="display: flex; align-items: center; gap: 20px;">
                <h2 class="gold-burn">🧂 الخامات</h2>
                <div class="search-wrapper">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="ingredientSearch" class="search-input" placeholder="ابحث عن خامة محددة..."
                        onkeyup="filterIngredients()">
                </div>
            </div>

            <div class="action-buttons">
                <button class="btn-gold" onclick="openAddIngredient()">
                    ➕ خامة جديدة
                </button>

                <button class="btn-outline" onclick="openUnitsModal()">
                    ⚖️ الوحدات
                </button>

                <a href="{{ route('ingredients.history') }}" class="btn-outline history-btn">
                    📜 سجل حركة الخامات
                </a>
            </div>
        </div>

        <div class="ingredients-grid" id="ingredientsGrid">
            @foreach ($ingredients as $ingredient)
                <div class="ingredient-card {{ $ingredient->stock <= ($ingredient->alert_stock ?? 0) ? 'low-stock-warning' : '' }}"
                    data-name="{{ $ingredient->name }}"
                    onclick="openAddStockModal({{ $ingredient->id }}, '{{ $ingredient->name }}', {{ $ingredient->stock }}, '{{ $ingredient->unit->symbol }}')">
                    <h3>📦 {{ $ingredient->name }}</h3>

                    <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                        <div>
                            <div class="stock-badge">
                                {{ (float) $ingredient->stock }} <small
                                    style="font-size: 0.7rem">{{ $ingredient->unit->symbol }}</small>
                            </div>
                            <p style="color: var(--text-muted); font-size: 0.8rem; margin: 10px 0 0 0;">
                                🕒 {{ $ingredient->stocks->first()?->created_at?->diffForHumans() ?? 'لا يوجد سجل' }}
                            </p>
                        </div>

                        <div class="card-actions" onclick="event.stopPropagation()">
                            @if (Auth::user()->role === 'admin')
                                <button class="btn-outline"
                                    onclick="openEditIngredient(
        {{ $ingredient->id }}, 
        '{{ $ingredient->name }}', 
        {{ $ingredient->stock }}, 
        {{ $ingredient->unit_id }}, 
        {{ $ingredient->alert_stock ?? 0 }}
    )">
                                    تعديل
                                </button>
                            @endif
                            @if (Auth::user()->role === 'admin')
                                <button class="btn-outline"
                                    style="padding: 5px 12px; font-size: 0.85rem; border-color: var(--danger); color: var(--danger);"
                                    onclick="confirmDelete({{ $ingredient->id }}, '{{ $ingredient->name }}')">
                                    حذف
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @include('inventories.ingredients.modals.all_modals')

    <script>
        // 1. وظيفة البحث الذكي
        function filterIngredients() {
            let input = document.getElementById('ingredientSearch').value.toLowerCase();
            let cards = document.getElementsByClassName('ingredient-card');
            for (let card of cards) {
                let name = card.getAttribute('data-name').toLowerCase();
                card.style.display = name.includes(input) ? "" : "none";
            }
        }

        // 2. فتح مودال الوحدات (هذا هو الجزء الذي كان ينقصك)
        function openUnitsModal() {
            document.getElementById('unitsModal').style.display = 'flex';
        }

        // 3. فتح مودال إضافة خامة جديدة
        function openAddIngredient() {
            document.getElementById('addIngredientModal').style.display = 'flex';
        }

        // 4. فتح مودال توريد كمية (عند الضغط على الكرت)
        function openAddStockModal(id, name, currentStock, symbol) {
            document.getElementById('stock_ingredient_name').value = name;
            document.getElementById('stock_current_amount').value = currentStock + " " + symbol;
            document.getElementById('addStockForm').action = `/ingredient/${id}/add-stock`; // تأكد من صحة الراوت عندك
            document.getElementById('addStockModal').style.display = 'flex';
        }

        // 5. فتح مودال التعديل وتعبئة البيانات
        function openEditIngredient(id, name, stock, unitId, alertStock) {
            document.getElementById('edit_ingredient_id').value = id;
            document.getElementById('edit_ingredient_name').value = name;
            document.getElementById('edit_ingredient_stock').value = stock;
            document.getElementById('edit_ingredient_alert_stock').value = alertStock;

            // تحديث الأكشن للفورم
            document.getElementById('editForm').action = `/ingredient/${id}/update`;
            document.getElementById('editIngredientModal').style.display = 'flex';
        }

        // 6. وظيفة الإغلاق العامة
        function closeModal() {
            document.querySelectorAll('.modal').forEach(m => m.style.display = 'none');
        }

        // إغلاق عند الضغط خارج المودال
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) closeModal();
        }

        // 7. تأكيد الحذف
        function confirmDelete(id, name) {
            if (confirm(`⚠️ هل أنت متأكد من حذف خامة (${name})؟`)) {
                fetch(`{{ url('ingredient') }}/${id}/delete`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                }).then(response => {
                    if (response.ok) location.reload();
                });
            }
        }
        document.addEventListener("DOMContentLoaded", function() {
            // 1. الحصول على الـ ID من الرابط (URL Query Params)
            const urlParams = new URLSearchParams(window.location.search);
            const openId = urlParams.get('open_id');

            if (openId) {
                // 2. البحث عن كارت الخامة الذي يحمل هذا الـ ID
                // سنبحث عن الزرار أو الكرت الذي يحتوي على بيانات هذه الخامة
                const ingredientCard = document.querySelector(
                    `.ingredient-card[onclick*="openAddStockModal(${openId},"]`);

                if (ingredientCard) {
                    // 3. محاكاة ضغطة زر لفتح المودال
                    ingredientCard.click();

                    // اختياري: عمل سكرول للكرت عشان المستخدم يشوفه
                    ingredientCard.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });

                    // تنظيف الرابط (إزالة open_id من الـ URL بدون ريفريش)
                    window.history.replaceState({}, document.title, window.location.pathname);
                }
            }
        });
    </script>
@endsection
