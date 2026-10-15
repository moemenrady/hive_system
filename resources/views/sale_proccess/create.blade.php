@extends('layouts.app_page')

@section('title', 'بدء عملية بيع')

@section('style')
    <style>
        :root {
            --bg-color: #1e1e1e;
            --card-bg: #2b2b2b;
            --accent: #d4af37;
            /* ذهبى محروق */
            --accent-hover: #f1c232;
            --text-main: #ffffff;
            --border-light: rgba(255, 255, 255, 0.08);
        }

        body {
            margin: 0;
            font-family: "Cairo", sans-serif;
            background: var(--bg-color);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-main);
        }

        .sale-container {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 20px;
            max-width: 700px;
            width: 100%;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.8s ease;
            border: 1px solid var(--border-light);
        }

        /* حقل البحث */
        .search-box {
            position: relative;
            margin-bottom: 25px;
        }

        #product-search {
            width: 100%;
            padding: 12px 14px;
            border-radius: 12px;
            border: 1px solid var(--border-light);
            background: #1e1e1e;
            color: var(--text-main);
            transition: 0.3s;
            font-size: 16px;
        }

        #product-search:focus {
            border-color: var(--accent);
            box-shadow: 0 0 6px rgba(212, 175, 55, 0.4);
            outline: none;
        }

        #search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1000;
            background: #2b2b2b;
            border-radius: 0 0 12px 12px;
            border: 1px solid var(--border-light);
            max-height: 250px;
            overflow-y: auto;
            display: none;
        }

        .result-item {
            padding: 12px;
            border-bottom: 1px solid var(--border-light);
            cursor: pointer;
            transition: 0.2s;
        }

        .result-item:hover {
            background: rgba(212, 175, 55, 0.15);
            color: var(--accent);
        }

        /* قائمة المنتجات (أزرار سريعة) */
        .products-list {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 20px;
        }

        .product-item {
            background: #1e1e1e;
            border: 1px solid var(--border-light);
            border-radius: 12px;
            padding: 10px;
            min-width: 110px;
            color: var(--text-main);
            cursor: pointer;
            transition: all 0.25s ease;
            text-align: center;
            font-weight: 600;
        }

        .product-item:hover {
            transform: translateY(-3px);
            border-color: var(--accent);
            color: var(--accent);
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.2);
        }

        /* الـ Snackbar المعدل */
        #selectedProductsSnackbar {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #333;
            color: #fff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            z-index: 10000;
            min-width: 300px;
            border: 1px solid var(--accent);
            display: none;
        }

        .snackbar-header {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .btn-qty {
            background: var(--accent);
            border: none;
            border-radius: 4px;
            width: 24px;
            height: 24px;
            cursor: pointer;
            font-weight: bold;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .category-item {
            background: #3a3a3a !important;
            border-color: var(--accent) !important;
        }

        #back-to-categories:hover {
            text-decoration: underline;
        }

        /* تحسين شكل المنتجات داخل الصنف */
        .product-item {
            transition: transform 0.2s;
        }
    </style>
@endsection

@section('content')
    <div class="sale-container">
        <h3 class="text-center mb-4" style="color: var(--accent)">🛒 نقطة البيع السريعة</h3>

        <div id="back-to-categories" style="display: none; cursor: pointer; margin-bottom: 15px; color: var(--accent);">
            <i class="fas fa-arrow-right"></i> ⬅️ العودة للأصناف
        </div>

        <div class="search-box">
            <input type="text" id="product-search" placeholder="ابحث باسم المنتج أو امسح الباركود..." autocomplete="off">
            <div id="search-results"></div>
        </div>

        <hr style="border-color: var(--border-light)">

        <div id="pos-display" class="products-list">
            @foreach ($categories as $category)
                <div class="category-item product-item" data-category-id="{{ $category->id }}"
                    data-products='@json($category->products)'>
                    📁 {{ $category->name }}
                </div>
            @endforeach
        </div>

    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {



            let selectedProducts = [];
            const categoriesData = @json($categories); // البيانات القادمة من السيرفر

            const posDisplay = document.querySelector("#pos-display");
            const backBtn = document.querySelector("#back-to-categories");
            const searchInput = document.querySelector("#product-search");
            const resultsDiv = document.querySelector("#search-results");

            // --- 1. إنشاء الـ Snackbar ---
            const snackbar = document.createElement("div");
            snackbar.id = "selectedProductsSnackbar";
            document.body.appendChild(snackbar);

            // --- 2. دالة إضافة المنتج (Global) ---
            window.addProduct = function(id, name) {
                if (!id || !name) return; // حماية من القيم الفارغة

                const existing = selectedProducts.find(p => p.product_id === id);
                if (existing) {
                    existing.qty += 1;
                } else {
                    selectedProducts.push({
                        product_id: id,
                        name: name,
                        qty: 1
                    });
                }
                updateSnackbarUI();

                // تنظيف البحث لو كان مفتوحاً
                searchInput.value = "";
                resultsDiv.style.display = "none";
            };

            // --- 3. عرض الأصناف ---
            function renderCategories() {
                backBtn.style.display = "none";
                posDisplay.innerHTML = "";
                categoriesData.forEach(cat => {
                    const div = document.createElement("div");
                    div.className = "product-item category-item";
                    div.innerHTML = `📁 ${cat.name}`;
                    div.onclick = () => renderProducts(cat.id);
                    posDisplay.appendChild(div);
                });
            }

            // --- 4. عرض المنتجات داخل الصنف ---
            function renderProducts(categoryId) {
                const category = categoriesData.find(c => c.id == categoryId);
                if (!category) return;

                backBtn.style.display = "block";
                posDisplay.innerHTML = "";

                if (category.products.length === 0) {
                    posDisplay.innerHTML = "<p>لا توجد منتجات في هذا الصنف</p>";
                    return;
                }

                category.products.forEach(prod => {
                    const div = document.createElement("div");
                    div.className = "product-item";
                    div.innerHTML = prod.name;
                    // هنا نمرر ID المنتج واسمه بشكل صحيح
                    div.onclick = () => window.addProduct(prod.id, prod.name);
                    posDisplay.appendChild(div);
                });
            }

            // زر الرجوع
            backBtn.onclick = renderCategories;

            // --- 5. تحديث واجهة السلة (Snackbar) ---
            function updateSnackbarUI() {
                if (selectedProducts.length === 0) {
                    snackbar.style.display = "none";
                    return;
                }

                let html = `
                <div class="snackbar-header">
                    <span style="color: var(--accent); font-weight:bold;">المنتجات المختارة (${selectedProducts.length})</span>
                    <span id="clear-all" style="cursor:pointer;">❌</span>
                </div>
                <div id="snack-list" style="max-height: 200px; overflow-y: auto;">
            `;

                selectedProducts.forEach(p => {
                    html += `
                    <div class="item-row">
                        <span>${p.name}</span>
                        <div>
                            <button class="btn-qty" onclick="changeQty(${p.product_id}, -1)">-</button>
                            <span style="margin: 0 10px;">${p.qty}</span>
                            <button class="btn-qty" onclick="changeQty(${p.product_id}, 1)">+</button>
                        </div>
                    </div>
                `;
                });

                html +=
                    `</div><button id="confirm-sale" class="btn btn-success w-100 mt-3">✅ تأكيد المشتريات</button>`;

                snackbar.innerHTML = html;
                snackbar.style.display = "block";

                document.getElementById("clear-all").onclick = () => {
                    selectedProducts = [];
                    updateSnackbarUI();
                };
                document.getElementById("confirm-sale").onclick = submitSale;
            }

            window.changeQty = function(id, delta) {
                const item = selectedProducts.find(p => p.product_id === id);
                if (item) {
                    item.qty += delta;
                    if (item.qty <= 0) selectedProducts = selectedProducts.filter(p => p.product_id !== id);
                    updateSnackbarUI();
                }
            };

            function submitSale() {
                if (selectedProducts.length === 0) return;
                const itemsStr = JSON.stringify(selectedProducts.map(p => ({
                    id: p.product_id,
                    qty: p.qty
                })));
                window.location.href = "{{ route('invoice.create') }}?items=" + encodeURIComponent(itemsStr);
            }

            // تشغيل العرض الأول للأصناف
            renderCategories();

            // --- البحث المباشر ---
            searchInput.addEventListener("keyup", function() {

                const query = this.value.trim();

                if (query.length < 1) {
                    resultsDiv.style.display = "none";
                    resultsDiv.innerHTML = "";
                    return;
                }

                fetch("{{ route('products.search') }}?query=" + encodeURIComponent(query))
                    .then(res => res.json())
                    .then(data => {

                        resultsDiv.innerHTML = "";

                        if (data.length === 0) {
                            resultsDiv.innerHTML = `<div class="result-item">لا توجد نتائج</div>`;
                            resultsDiv.style.display = "block";
                            return;
                        }

                        data.forEach(product => {

                            const div = document.createElement("div");
                            div.className = "result-item";

                            div.innerHTML = `
                    <div style="display:flex; justify-content:space-between;">
                        <span>${product.name}</span>
                        <small>${product.price} ج</small>
                    </div>
                `;

                            // 👇 هنا السحر
                            div.onclick = () => {
                                window.addProduct(product.id, product.name);
                            };

                            resultsDiv.appendChild(div);
                        });

                        resultsDiv.style.display = "block";
                    });
            });

        });
    </script>
@endsection
