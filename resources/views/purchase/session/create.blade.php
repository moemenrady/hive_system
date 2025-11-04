@extends('layouts.app_page')

@section('title', 'اضافة مشتريات')

@section('content')
    @if (session('success'))
        <div class="alert alert-success" id="snackbar">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger" id="snackbar">
            {{ session('error') }}
        </div>
    @endif


    <div class="sale-container">

        <div class="mb-3 position-relative">
            <div class="d-flex align-items-center gap-2">

                <!-- البحث -->
                <input type="text" id="product" name="product" placeholder="🔍 ابحث عن منتج" class="form-control"
                    value="{{ old('product') }}" autocomplete="off" required>


            </div>

            <!-- نتائج البحث -->
            <div id="product-results" class="list-group position-absolute d-none"
                style="top:100%; left:0; right:0; max-height:200px; overflow-y:auto; z-index:10;">
            </div>
        </div>

        <!-- المنتجات المختارة -->
        <div class="card mt-3">
            <div class="card-body p-2">
                <ul class="list-group" id="itemsListContainer">
                    <li class="list-group-item text-center text-muted">لا توجد منتجات</li>
                </ul>
            </div>
        </div>

        <!-- زرار عرض الفاتورة -->
        <form id="invoiceForm" action="{{ route('session.purchase.store', $session->id ?? 0) }}" method="POST"
            class="mt-3">
            @csrf
            <input type="hidden" name="items" id="itemsInput">
            <button type="submit" class="invoice-btn">أضافة للجسة</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        (function($) {
            const sel = {
                input: '#product',
                results: '#product-results',
                itemsContainer: '#itemsListContainer',
                addBtn: '#addItemButton',
                qtyInc: '#increaseQtyInput',
                qtyDec: '#decreaseQtyInput',
                qtyDisplay: '#qtyInput',
                invoiceForm: '#invoiceForm',
                itemsInputHidden: '#itemsInput'
            };

            let selectedProduct = null;
            let itemsList = [];
            let qtyInput = 1;
            let currentResults = [];
            let highlightedIndex = -1;
            let itemsFocusedIndex = -1;
            let searchDebounceTimer = null;

            function escapeHtml(text) {
                if (!text && text !== 0) return '';
                return String(text)
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
            }

            function formatPrice(p) {
                return Number(p || 0).toFixed(2);
            }

            function renderResults(data) {
                highlightedIndex = -1;
                if (!data || data.length === 0) {
                    $(sel.results).html(
                            '<div class="list-group-item text-muted" style="color:#fff;">لا توجد نتائج</div>')
                        .removeClass('d-none');

                    currentResults = [];
                    return;
                }
                let html = '';
                data.forEach((item, idx) => {
                    html += `
        <a href="#" class="list-group-item list-group-item-action result-item"
           data-index="${idx}" data-id="${item.id}" data-name="${escapeHtml(item.name)}" data-price="${item.price}">
           #${item.id} - ${escapeHtml(item.name)} (${formatPrice(item.price)} جنيه)
        </a>`;
                });
                $(sel.results).html(html).removeClass('d-none');
                currentResults = data;
            }

            function clearResults() {
                currentResults = [];
                highlightedIndex = -1;
                $(sel.results).addClass('d-none').empty();
            }

            function highlightResult(idx) {
                $(sel.results + ' .result-item').removeClass('active');
                if (idx == null || idx < 0 || idx >= currentResults.length) {
                    highlightedIndex = -1;
                    return;
                }
                highlightedIndex = idx;
                const $el = $(sel.results + ' .result-item').eq(idx);
                $el.addClass('active');
                const container = $(sel.results)[0];
                if (container && $el.length) {
                    const itemEl = $el[0];
                    const containerTop = container.scrollTop;
                    const containerBottom = containerTop + container.clientHeight;
                    const itemTop = itemEl.offsetTop;
                    const itemBottom = itemTop + itemEl.offsetHeight;
                    if (itemTop < containerTop) container.scrollTop = itemTop;
                    if (itemBottom > containerBottom) container.scrollTop = itemBottom - container.clientHeight;
                }
            }

            function renderItems() {
                const $container = $(sel.itemsContainer);
                if (!$container.length) return;
                $container.empty();
                if (itemsList.length === 0) {
                    $container.html(`<li class="list-group-item text-center text-muted">لا توجد منتجات</li>`);
                    itemsFocusedIndex = -1;
                    return;
                }
                itemsList.forEach((item, index) => {
                    const activeClass = (index === itemsFocusedIndex) ? 'selected-item' : '';
                    const $li = $(`

        <li tabindex="0" class="list-group-item d-flex justify-content-between align-items-center ${activeClass}" data-item-index="${index}">
          <span><strong>${escapeHtml(item.product)}</strong> - ID: ${item.id}</span>
          <span>
            <button type="button" class="counter-btn btn-decr" data-index="${index}">➖</button>
            <span class="mx-2 qty-span">${item.qty}</span>
            <button type="button" class="counter-btn btn-incr" data-index="${index}">➕</button>
            <button type="button" class="counter-btn remove-btn" data-index="${index}">🗑️</button>
          </span>
        </li>
      `);
                    $container.append($li);
                });

                if (itemsFocusedIndex >= 0) {
                    const $selItem = $container.find(`li[data-item-index="${itemsFocusedIndex}"]`);
                    if ($selItem.length) {
                        try {
                            $selItem.get(0).focus({
                                preventScroll: false
                            });
                        } catch (e) {
                            $selItem.get(0).focus();
                        }
                        const parent = $container[0];
                        const el = $selItem[0];
                        const parentTop = parent.scrollTop;
                        const parentBottom = parentTop + parent.clientHeight;
                        const elTop = el.offsetTop;
                        const elBottom = elTop + el.offsetHeight;
                        if (elTop < parentTop) parent.scrollTop = elTop;
                        if (elBottom > parentBottom) parent.scrollTop = elBottom - parent.clientHeight;
                    }
                }
            }

            function increaseQtyAt(idx) {
                if (itemsList[idx]) {
                    itemsList[idx].qty++;
                    renderItems();
                }
            }
            // keep decrease min = 1 (don't auto-remove) — Backspace will remove regardless
            function decreaseQtyAt(idx) {
                if (!itemsList[idx]) return;
                if (itemsList[idx].qty > 1) {
                    itemsList[idx].qty--;
                    renderItems();
                } else {
                    // flash min reached
                    const $container = $(sel.itemsContainer);
                    const $el = $container.find(`li[data-item-index="${idx}"]`);
                    $el.addClass('min-reached');
                    setTimeout(() => $el.removeClass('min-reached'), 300);
                    renderItems();
                }
            }

            // Remove item (used by backspace and trash button) — keeps focus sane
            function removeItemAt(idx) {
                if (!itemsList[idx]) return;
                itemsList.splice(idx, 1);
                // adjust focus: try keep same index (which is next item), otherwise move to previous
                if (itemsList.length === 0) {
                    itemsFocusedIndex = -1;
                } else if (idx <= itemsList.length - 1) {
                    itemsFocusedIndex = idx;
                } else {
                    itemsFocusedIndex = itemsList.length - 1;
                }
                renderItems();
            }

            // delegated handlers
            $(document).on('click', sel.itemsContainer + ' .btn-incr', function() {
                const idx = parseInt($(this).data('index'));
                increaseQtyAt(idx);
            });
            $(document).on('click', sel.itemsContainer + ' .btn-decr', function() {
                const idx = parseInt($(this).data('index'));
                if (isNaN(idx) || !itemsList[idx]) return;
                if (itemsList[idx].qty > 1) itemsList[idx].qty--;
                else {
                    const $container = $(sel.itemsContainer);
                    const $el = $container.find(`li[data-item-index="${idx}"]`);
                    $el.addClass('min-reached');
                    setTimeout(() => $el.removeClass('min-reached'), 300);
                }
                renderItems();
            });
            $(document).on('click', sel.itemsContainer + ' .remove-btn', function() {
                const idx = parseInt($(this).data('index'));
                if (!isNaN(idx)) {
                    removeItemAt(idx);
                }
            });

            function selectResultByIndex(idx) {
                const item = currentResults[idx];
                if (!item) return;
                // أضف مباشرة بدل مجرد تعبئة الحقل
                const existing = itemsList.find(it => it.id === item.id);
                if (existing) existing.qty += 1;
                else itemsList.push({
                    id: item.id,
                    product: item.name,
                    price: item.price,
                    qty: 1
                });
                $(sel.input).val('');
                clearResults();
                renderItems();
            }


            function addSelectedProduct() {
                if (!selectedProduct) {
                    alert("⚠️ اختر منتج من البحث الأول");
                    return false;
                }
                if (!qtyInput || qtyInput <= 0) {
                    alert("⚠️ اختر عدد صحيح");
                    return false;
                }
                const existing = itemsList.find(it => it.id === selectedProduct.id);
                if (existing) existing.qty += qtyInput;
                else itemsList.push({
                    id: selectedProduct.id,
                    product: selectedProduct.name,
                    price: selectedProduct.price,
                    qty: qtyInput
                });
                $(sel.input).val('');
                qtyInput = 1;
                if ($(sel.qtyDisplay).length) $(sel.qtyDisplay).text(qtyInput);
                selectedProduct = null;
                itemsFocusedIndex = -1;
                renderItems();
                return true;
            }

            function doSearch(query) {
                if (!query || query.trim().length === 0) {
                    clearResults();
                    return;
                }
                if (searchDebounceTimer) clearTimeout(searchDebounceTimer);
                searchDebounceTimer = setTimeout(() => {
                    $.ajax({
                        url: "{{ route('products.search') }}",
                        type: "GET",
                        data: {
                            query
                        },
                        success: function(data) {
                            renderResults(data || []);
                        },
                        error: function() {
                            $(sel.results).html(
                                    '<div class="list-group-item text-muted">خطأ في البحث</div>')
                                .removeClass('d-none');
                            currentResults = [];
                            highlightedIndex = -1;
                        }
                    });
                }, 180);
            }

            function trySubmitInvoiceForm() {
                const $form = $(sel.invoiceForm);
                const $hidden = $(sel.itemsInputHidden);
                if ($form.length && $hidden.length) {
                    $hidden.val(JSON.stringify(itemsList));
                    $form.trigger('submit');
                    return true;
                }
                return false;
            }

            function resetItemsFocus() {
                itemsFocusedIndex = -1;
                renderItems();
            }

            function focusItemIndex(idx) {
                if (itemsList.length === 0) return;
                if (idx < 0) idx = 0;
                if (idx > itemsList.length - 1) idx = itemsList.length - 1;
                itemsFocusedIndex = idx;
                highlightedIndex = -1;
                $(sel.results + ' .result-item').removeClass('active');
                renderItems();
            }

            $(function() {
                $(document).on('keyup', sel.input, function() {
                    const query = $(this).val() || '';
                    if (query.length >= 1) {
                        doSearch(query);
                    } else {
                        clearResults();
                    }
                });

                $(document).on('click', '.result-item', function(e) {
                    e.preventDefault();
                    const idx = parseInt($(this).data('index'));
                    selectResultByIndex(idx);
                });

                if ($(sel.addBtn).length) {
                    $(document).on('click', sel.addBtn, function() {
                        addSelectedProduct();
                    });
                }

                if ($(sel.qtyInc).length) {
                    $(document).on('click', sel.qtyInc, function() {
                        qtyInput++;
                        if ($(sel.qtyDisplay).length) $(sel.qtyDisplay).text(qtyInput);
                    });
                }
                if ($(sel.qtyDec).length) {
                    $(document).on('click', sel.qtyDec, function() {
                        if (qtyInput > 1) qtyInput--;
                        if ($(sel.qtyDisplay).length) $(sel.qtyDisplay).text(qtyInput);
                    });
                }

                $(document).on('input', sel.input, function() {
                    selectedProduct = null;
                    const q = $(this).val() || '';
                    if (q.trim().length >= 1) {
                        doSearch(q.trim());
                        resetItemsFocus();
                    } else {
                        clearResults();
                    }
                });

                // KEYDOWN when focus is in search input
                $(document).on('keydown', sel.input, function(e) {
                    // Backspace behavior when query empty -> delete focused item
                    if (e.key === 'Backspace') {
                        const q = $(sel.input).val() || '';
                        if (q.trim() === '' && itemsFocusedIndex >= 0) {
                            e.preventDefault();
                            removeItemAt(itemsFocusedIndex);
                            return;
                        }
                    }

                    if (e.key === 'Enter') {
                        const q = $(sel.input).val() || '';
                        if (q.trim() === '' && itemsList.length > 0) {
                            e.preventDefault();
                            if (!trySubmitInvoiceForm()) {
                                if ($(sel.addBtn).length) $(sel.addBtn).trigger('click');
                            }
                            return;
                        }
                    }

                    if (!$(sel.results).hasClass('d-none') && currentResults.length > 0) {
                        if (e.key === 'ArrowDown') {
                            e.preventDefault();
                            if (highlightedIndex < currentResults.length - 1) highlightedIndex++;
                            else highlightedIndex = currentResults.length - 1;
                            highlightResult(highlightedIndex);
                            return;
                        }
                        if (e.key === 'ArrowUp') {
                            e.preventDefault();
                            if (highlightedIndex > 0) highlightedIndex--;
                            else highlightedIndex = 0;
                            highlightResult(highlightedIndex);
                            return;
                        }
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            const pickIndex = (highlightedIndex >= 0) ? highlightedIndex : 0;
                            const item = currentResults[pickIndex];
                            if (item) {
                                const id = item.id,
                                    name = item.name,
                                    price = parseFloat(item.price) || 0;
                                const existing = itemsList.find(it => it.id === id);
                                if (existing) existing.qty += 1;
                                else itemsList.push({
                                    id,
                                    product: name,
                                    price,
                                    qty: 1
                                });
                                $(sel.input).val('');
                                clearResults();
                                renderItems();
                                $(sel.input).focus();
                            }
                            return;
                        }
                        if (e.key === 'Shift') {
                            if (currentResults.length > 0) {
                                e.preventDefault();
                                const item = currentResults[0];
                                const id = item.id,
                                    name = item.name,
                                    price = parseFloat(item.price) || 0;
                                const existing = itemsList.find(it => it.id === id);
                                if (existing) existing.qty += 1;
                                else itemsList.push({
                                    id,
                                    product: name,
                                    price,
                                    qty: 1
                                });
                                $(sel.input).val('');
                                clearResults();
                                renderItems();
                                $(sel.input).focus();
                            }
                            return;
                        }
                    } else {
                        // results hidden OR empty search -> navigate itemsList with arrows
                        if (itemsList.length > 0) {
                            if (e.key === 'ArrowDown') {
                                e.preventDefault();
                                if (itemsFocusedIndex < itemsList.length - 1) itemsFocusedIndex++;
                                else itemsFocusedIndex = itemsList.length - 1;
                                focusItemIndex(itemsFocusedIndex);
                                return;
                            }
                            if (e.key === 'ArrowUp') {
                                e.preventDefault();
                                if (itemsFocusedIndex > 0) itemsFocusedIndex--;
                                else itemsFocusedIndex = 0;
                                focusItemIndex(itemsFocusedIndex);
                                return;
                            }
                            // <-- now LEFT increases, RIGHT decreases (inverted per request)
                            if (e.key === 'ArrowLeft') {
                                e.preventDefault();
                                if (itemsFocusedIndex === -1) {
                                    itemsFocusedIndex = 0;
                                }
                                increaseQtyAt(itemsFocusedIndex);
                                return;
                            }
                            if (e.key === 'ArrowRight') {
                                e.preventDefault();
                                if (itemsFocusedIndex === -1) {
                                    itemsFocusedIndex = 0;
                                }
                                decreaseQtyAt(itemsFocusedIndex);
                                return;
                            }
                        }
                    }
                });

                // global handler
                $(document).on('keydown.globalTypeToSearch', function(e) {
                    if ($(e.target).is('input, textarea, [contenteditable="true"]')) return;

                    // global Backspace -> if search empty delete focused item
                    if (e.key === 'Backspace') {
                        const qVal = $(sel.input).val() || '';
                        if (qVal.trim() === '' && itemsFocusedIndex >= 0) {
                            e.preventDefault();
                            removeItemAt(itemsFocusedIndex);
                            return;
                        }
                    }

                    if (e.key === 'Enter') {
                        const qVal = $(sel.input).val() || '';
                        if (qVal.trim() === '' && itemsList.length > 0) {
                            e.preventDefault();
                            if (!trySubmitInvoiceForm()) {
                                if ($(sel.addBtn).length) $(sel.addBtn).trigger('click');
                            }
                            return;
                        }
                    }

                    if (e.ctrlKey || e.metaKey || e.altKey) {
                        if ((e.ctrlKey || e.metaKey) && e.key && e.key.toLowerCase() === 'k') {
                            e.preventDefault();
                            $(sel.input).focus().select();
                        }
                        return;
                    }

                    const key = e.key;

                    if (key === 'Escape') {
                        const $p = $(sel.input);
                        $p.val('');
                        clearResults();
                        $p.blur();
                        return;
                    }

                    if (key === '/' && !e.ctrlKey && !e.metaKey && !e.altKey) {
                        e.preventDefault();
                        const $p = $(sel.input);
                        $p.focus().select();
                        return;
                    }

                    if (key && key.length === 1) {
                        const code = key.charCodeAt(0);
                        if (code >= 32) {
                            e.preventDefault();
                            const $p = $(sel.input);
                            const inputEl = $p.get(0);
                            if (!inputEl) return;
                            try {
                                const start = (typeof inputEl.selectionStart === 'number') ? inputEl
                                    .selectionStart : $p.val().length;
                                const end = (typeof inputEl.selectionEnd === 'number') ? inputEl
                                    .selectionEnd : start;
                                const val = $p.val() || '';
                                const newVal = val.slice(0, start) + key + val.slice(end);
                                $p.val(newVal);
                                const caret = start + 1;
                                inputEl.setSelectionRange(caret, caret);
                            } catch (err) {
                                $p.val(($p.val() || '') + key);
                            }
                            $p.trigger('input');
                            $p.focus();
                            resetItemsFocus();
                            return;
                        }
                    }

                    // navigation over items when not in inputs
                    if (itemsList.length > 0) {
                        if (key === 'ArrowDown') {
                            e.preventDefault();
                            if (itemsFocusedIndex < itemsList.length - 1) itemsFocusedIndex++;
                            focusItemIndex(itemsFocusedIndex);
                            return;
                        }
                        if (key === 'ArrowUp') {
                            e.preventDefault();
                            if (itemsFocusedIndex > 0) itemsFocusedIndex--;
                            focusItemIndex(itemsFocusedIndex);
                            return;
                        }
                        if (key === 'ArrowLeft') {
                            e.preventDefault();
                            if (itemsFocusedIndex === -1) itemsFocusedIndex = 0;
                            increaseQtyAt(itemsFocusedIndex);
                            return;
                        }
                        if (key === 'ArrowRight') {
                            e.preventDefault();
                            if (itemsFocusedIndex === -1) itemsFocusedIndex = 0;
                            decreaseQtyAt(itemsFocusedIndex);
                            return;
                        }
                    }
                });

                $(document).on('click', function(e) {
                    if (!$(e.target).closest(sel.results + ', ' + sel.input).length) {
                        clearResults();
                    }
                });

                if ($(sel.invoiceForm).length && $(sel.itemsInputHidden).length) {
                    $(sel.invoiceForm).on('submit', function(e) {
                        if (itemsList.length === 0) {
                            e.preventDefault();
                            alert("⚠️ لم يتم إضافة منتجات");
                            return;
                        }
                        $(sel.itemsInputHidden).val(JSON.stringify(itemsList));
                    });
                }

                renderItems();
            });

            window._productSearchHelper = {
                getItems: () => itemsList,
                clearItems: () => {
                    itemsList = [];
                    itemsFocusedIndex = -1;
                    renderItems();
                },
                getSelected: () => selectedProduct
            };

        })(jQuery);
    </script>

@endsection

@section('style')
    <style>
        /* 🌙 Dark Theme Consistent with Layout */
        :root {
            --bg-color: #0e0e11;
            --card-bg: #16161a;
            --accent: #ffb84d;
            --accent-light: #ffe6a3;
            --text-main: #f5f5f5;
            --text-dim: #bfbfbf;
            --border-light: rgba(255, 255, 255, 0.08);
            --theme-primary: #1a1a1d;
            --btn-bg: #ffb84d;
            --btn-border: #f2d35e;
            --btn-text: #111;
        }

        /* الصفحة */
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

        /* الكونتينر */
        .sale-container {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 20px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
            animation: fadeIn 0.8s ease;
            border: 1px solid var(--border-light);
        }

        #product-results .list-group-item.text-muted {
            color: #fff !important;
        }

        #itemsListContainer .list-group-item.text-muted {
            color: #fff !important;
        }

        /* البحث */
        #product {
            width: 100%;
            padding: 12px 14px;
            border-radius: 12px;
            border: 1px solid var(--border-light);
            background: #1c1c21;
            color: var(--text-main);
            transition: 0.3s;
        }

        #product:focus {
            border-color: var(--accent);
            box-shadow: 0 0 8px var(--accent);
            outline: none;
        }

        /* نتائج البحث */
        #product-results {
            background: var(--theme-primary);
            border-radius: 10px;
            border: 1px solid var(--border-light);
        }

        #product-results .list-group-item {
            background: transparent;
            color: var(--text-dim);
            border: none;
            transition: 0.25s;
        }

        #product-results .list-group-item:hover,
        #product-results .list-group-item.active {
            background: rgba(255, 184, 77, 0.1);
            color: var(--accent-light);
        }

        /* المنتجات */
        .card {
            background: var(--theme-primary) !important;
            color: var(--text-main);
            border: 1px solid var(--border-light);
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .list-group-item {
            background: transparent;
            color: var(--text-dim);
            border: 1px solid var(--border-light);
        }

        #itemsListContainer .selected-item {
            background: rgba(255, 184, 77, 0.1);
            border-color: var(--accent);
        }

        #itemsListContainer li:focus {
            box-shadow: 0 0 0 3px rgba(255, 184, 77, 0.25);
            outline: none;
        }

        #itemsListContainer .min-reached {
            animation: flashMin 0.28s ease;
        }

        @keyframes flashMin {
            0% {
                background-color: rgba(244, 67, 54, 0.08);
            }

            50% {
                background-color: rgba(244, 67, 54, 0.15);
            }

            100% {
                background-color: transparent;
            }
        }

        /* الأزرار */
        .counter-btn {
            background: var(--accent);
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            font-size: 16px;
            color: var(--btn-text);
            cursor: pointer;
            transition: 0.3s;
        }

        .counter-btn:hover {
            background: var(--accent-light);
            transform: scale(1.1);
        }

        .remove-btn {
            background: #e74c3c;
            color: #fff;
        }

        .remove-btn:hover {
            background: #ff5f50;
        }

        /* زرار إضافة الفاتورة */
        .invoice-btn {
            background: linear-gradient(135deg, var(--accent), #d4a043);
            border: none;
            border-radius: 30px;
            padding: 12px 22px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            color: var(--btn-text);
            display: flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 184, 77, 0.3);
            margin: auto;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeSlideIn 0.6s ease forwards;
        }

        .invoice-btn:hover {
            transform: scale(1.08);
            box-shadow: 0 6px 20px rgba(255, 184, 77, 0.4);
        }

        /* الرسائل */
        .alert {
            border-radius: 10px;
            padding: 12px;
            font-weight: 500;
            text-align: center;
        }

        .alert-success {
            background: rgba(76, 175, 80, 0.15);
            color: #9cff9c;
        }

        .alert-danger {
            background: rgba(244, 67, 54, 0.15);
            color: #ff9c9c;
        }

        /* انيمشن */
        @keyframes fadeSlideIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
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

        /* ✅ 1. خلي placeholder أبيض */
        #product::placeholder {
            color: rgba(255, 255, 255, 0.7);
            opacity: 1;
            /* للتوافق مع كل المتصفحات */
        }

        /* ✅ 2. نتائج البحث بلون أبيض بدل الرمادي */
        #product-results .list-group-item {
            background: var(--theme-primary);
            color: var(--text-main);
            /* أبيض */
            border: none;
            transition: 0.25s;
        }

        #product-results .list-group-item:hover,
        #product-results .list-group-item.active {
            background: rgba(255, 184, 77, 0.2);
            color: var(--accent-light);
        }

        /* ✅ 3. الكارت اللي فيه لستة المشتريات يكون دارك بنفس الثيم */
        .card {
            background: #1a1a1d !important;
            /* أغمق شوية من الخلفية */
            border: 1px solid var(--border-light);
            border-radius: 16px;
            color: var(--text-main);
        }

        /* ✅ 4. عناصر المشتريات نفسها بنفس شكل الثيم */
        #itemsListContainer .list-group-item {
            background: #1e1e22;
            color: var(--text-main);
            border: 1px solid var(--border-light);
            border-radius: 10px;
            margin-bottom: 8px;
            transition: background 0.3s, transform 0.2s;
        }

        #itemsListContainer .list-group-item:hover {
            background: rgba(255, 184, 77, 0.1);
            transform: scale(1.02);
        }

        /* ✅ 5. العنصر المختار يظهر مميز */
        #itemsListContainer .selected-item {
            background: rgba(255, 184, 77, 0.15);
            border-color: var(--accent);
        }
    </style>
@endsection
