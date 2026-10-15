<div class="modal fade animate__animated animate__fadeInDown" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 shadow" style="background: var(--card-surface); color: var(--text-bright);">
            <div class="modal-header bg-warning text-dark rounded-top-4">
                <h5 class="modal-title">➕ إضافة منتج جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('products.store') }}" method="POST" id="productForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="text-gold">معرف المنتج (ID)</label>
                            <input type="number" name="product_id" class="form-control" placeholder="123456" required>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="text-gold">اسم المنتج</label>
                            <input type="text" name="name" class="form-control" placeholder="قهوة عربي" required>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="text-gold">السعر البيع</label>
                            <input type="number" step="0.01" name="price" class="form-control" required>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="text-gold">التكلفة</label>
                            <input type="number" step="0.01" name="cost" class="form-control" required>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="text-gold">الحد الأدنى للتنبيه</label>
                            <input type="number" name="min_quantity" class="form-control" value="5" required>
                        </div>

                        <div class="col-12 mb-4">
                            <div class="d-flex align-items-center justify-content-between p-3 rounded-3"
                                style="background: rgba(230, 201, 122, 0.1); border: 1px solid var(--primary-gold);">
                                <div>
                                    <h6 class="mb-0 text-gold">تصنيع داخلي؟</h6>
                                    <small class="text-muted">هل يتكون هذا المنتج من خامات أخرى؟</small>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input custom-switch" type="checkbox" name="is_produced"
                                        id="isProducedToggle" value="1">
                                </div>
                            </div>
                        </div>

                        <div id="quantitySection" class="col-md-12 form-group mb-3">
                            <label class="text-gold">الكمية الحالية في المخزن</label>
                            <input type="number" name="quantity" class="form-control" placeholder="0">
                        </div>
                        <div class="col-md-12 form-group mb-3">
                            <label class="text-gold">تصنيف المنتج (Category)</label>
                            <select name="category_id" class="form-control" required>
                                <option value="" selected disabled>اختر الصنف الخاص بهذا المنتج...</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="recipeSection" class="col-md-12" style="display: none;">
                            <label class="text-gold mb-2 d-block">مكونات المنتج (الريسبي):</label>
                            <div id="ingredientsList">
                                <div class="row g-2 mb-2 ingredient-row">
                                    <div class="col-md-5">
                                        <select name="ingredients[]" class="form-control">
                                            <option value="">اختر الخامة...</option>
                                            @foreach ($ingredients as $ing)
                                                <option value="{{ $ing->id }}">{{ $ing->name }}
                                                    ({{ $ing->unit->symbol }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <input type="number" step="0.001" name="amounts[]" class="form-control"
                                            placeholder="الكمية المستخدمة">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-outline-danger w-100"
                                            onclick="removeRow(this)">🗑️</button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-warning mt-2"
                                onclick="addIngredientRow()">+ إضافة خامة أخرى</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn-icon btn-edit"
                        style="background: var(--primary-gold); color:#000; width: 100%;">✅ حفظ المنتج النهائي</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* مودال داكن رمادي */
    #addProductModal .modal-content {
        background: #2e2e33 !important;
        /* لون رمادي داكن */
        color: #f5f5f5;
        /* النصوص واضحة */
    }

    /* هيدر المودال */
    #addProductModal .modal-header {
        background: #6c5ce7;
        /* لون غامق جميل للعنوان */
        color: #fff;
        /* نص العنوان باين */
        border-bottom: 1px solid #555;
    }

    /* Inputs والفورم كنترول */
    #addProductModal .form-control {
        background: #3b3b44 !important;
        /* خلفية الرمادي الغامق */
        border: 1px solid #555 !important;
        /* حدود واضحة */
        color: #f5f5f5 !important;
        /* النص باين */
    }

    /* Placeholder */
    #addProductModal .form-control::placeholder {
        color: #ccc !important;
        /* placeholder باين */
        opacity: 1;
        /* اجعلها واضحة بالكامل */
    }

    /* Labels */
    #addProductModal label {
        color: #ffd700;
        /* أصفر ذهبي باين */
        font-weight: 600;
    }

    /* زر الإغلاق */
    #addProductModal .btn-close {
        filter: brightness(0) invert(1);
        /* أبيض واضح */
    }

    /* Switch */
    #addProductModal .custom-switch {
        cursor: pointer;
    }

    /* الأزرار داخل المودال */
    #addProductModal .btn,
    #addProductModal .btn-outline-warning,
    #addProductModal .btn-outline-danger {
        font-weight: bold;
    }

    /* المربع الداخلي لتصنيع داخلي */
    #addProductModal .d-flex.align-items-center {
        background: #3a3a44;
        /* رمادي غامق */
        border: 1px solid #6c5ce7;
        /* حدود واضحة */
        color: #fff;
    }

    /* Hover على select و input */
    #addProductModal select.form-control:hover,
    #addProductModal input.form-control:hover {
        border-color: #ffd700;
        box-shadow: 0 0 8px rgba(255, 215, 0, 0.3);
    }

    /* Footer */
    #addProductModal .modal-footer {
        border-top: 1px solid #555;
    }

    /* زر الحفظ */
    #addProductModal .btn-icon.btn-edit {
        background: #ffd700 !important;
        color: #1a1a1a !important;
    }
</style>


<script>
    const toggle = document.getElementById('isProducedToggle');
    const qSection = document.getElementById('quantitySection');
    const rSection = document.getElementById('recipeSection');

    toggle.addEventListener('change', function() {
        if (this.checked) {
            qSection.style.display = 'none';
            rSection.style.display = 'block';
            document.getElementsByName('quantity')[0].value =
                0; // المنتجات المصنعة تبدأ بـ 0 وتعتمد على الإنتاج
        } else {
            qSection.style.display = 'block';
            rSection.style.display = 'none';
        }
    });

    function addIngredientRow() {
        const row = document.querySelector('.ingredient-row').cloneNode(true);
        row.querySelector('select').value = "";
        row.querySelector('input').value = "";
        document.getElementById('ingredientsList').appendChild(row);
    }

    function removeRow(btn) {
        const rows = document.querySelectorAll('.ingredient-row');
        if (rows.length > 1) btn.closest('.ingredient-row').remove();
    }
</script>
