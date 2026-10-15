@extends('layouts.app_page')

@section('title', 'تسجيل مسحوبات وسلفيات الموظفين')

@section('style')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

    <style>
        /* === تحسينات محرك البحث TomSelect المتوافق مع الثيم المظلم === */
        .ts-wrapper .ts-control {
            background-color: #1a1a1d !important;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 12px !important;
            padding: 12px 16px !important;
            font-size: 0.95rem;
            transition: all 0.25s ease;
        }

        .ts-wrapper.focus .ts-control {
            border-color: var(--accent) !important;
            box-shadow: 0 0 0 3px rgba(255, 184, 77, 0.15) !important;
        }

        /* جعل حقل الإدخال الداخلي يأخذ لون أبيض */
        .ts-wrapper .ts-control input {
            color: #ffffff !important;
        }

        /* قائمة نتائج البحث المنبثقة - تم ضبط الـ z-index لمنع الاختفاء */
        .ts-dropdown {
            background-color: #16161a !important;
            border: 1px solid rgba(255, 184, 77, 0.3) !important;
            border-radius: 12px !important;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.6) !important;
            z-index: 99999 !important;
            /* ضمان ظهورها فوق كل شيء */
        }

        .ts-dropdown .option {
            padding: 10px 14px !important;
            color: #e0e0e0 !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
            cursor: pointer;
        }

        .ts-dropdown .active {
            background-color: rgba(255, 184, 77, 0.15) !important;
            color: var(--accent) !important;
        }

        /* === كروت العمليات الديناميكية === */
        .item-entry {
            animation: slideIn 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            background: #16161a;
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            transition: all 0.3s ease;
            position: relative;
        }

        /* تلوين حافة الكارت ديناميكياً حسب النوع المختارات عبر Alpine */
        .entry-purchase {
            border-inline-start: 5px solid #10b981 !important;
        }

        .entry-advance {
            border-inline-start: 5px solid #ffb84d !important;
        }

        .entry-deduction {
            border-inline-start: 5px solid #ef4444 !important;
        }

        /* لون بنفسجي جذاب ومميز للمكافآت */
        .entry-bonus {
            border-inline-start: 5px solid #a855f7 !important;
        }

        .item-entry:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* أسماء الحقول الفاتحة والمميزة */
        .form-label-custom {
            color: #ffffff !important;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 6px;
            display: block;
        }

        .form-control-custom {
            background-color: #1a1a1d !important;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 12px !important;
            padding: 11px 14px !important;
        }

        .form-control-custom:focus {
            background-color: #1e1e22 !important;
            color: #ffffff !important;
            border-color: var(--accent) !important;
            box-shadow: 0 0 0 3px rgba(255, 184, 77, 0.15) !important;
        }

        /* شارات التنبيه للمشروبات */
        .badge-status {
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-free {
            background: rgba(16, 185, 129, 0.12);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .badge-charged {
            background: rgba(239, 68, 68, 0.12);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
    </style>
@endsection

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <div class="container mx-auto p-2 md:p-4 max-w-4xl" x-data="transactionForm()">

        <div
            class="mb-4 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 bg-dark p-3 rounded-4 border border-secondary border-opacity-10">
            <span class="fs-5 fw-bold text-white mb-0">
                <i class="fas fa-exchange-alt me-2" style="color: var(--accent)"></i> لوحة تسجيل عمليات الموظفين
            </span>

            <template x-if="employeeStatus">
                <div :class="employeeStatus.has_free_today ? 'badge-status badge-free' : 'badge-status badge-charged'">
                    <i class="fas"
                        :class="employeeStatus.has_free_today ? 'fa-star-of-life me-1' : 'fa-exclamation-circle me-1'"></i>
                    <span
                        x-text="employeeStatus.has_free_today ? 'متاح مشروب مجاني اليوم ✨' : 'تم استهلاك المشروب المجاني'"></span>
                </div>
            </template>
        </div>

        <form @submit.prevent="submitForm">

            <div class="card p-3 p-md-4 mb-4 shadow-sm border-0 bg-dark">
                <label class="form-label-custom fs-6" style="color: var(--accent-light) !important;">
                    <i class="fas fa-user-check me-2 text-warning"></i> ابحث واختر الموظف المسؤول <span
                        class="text-danger">*</span>
                </label>
                <div class="mt-2 position-relative">
                    <select id="employee_select" name="employee_id" required x-init="initEmployeeSelect($el)">
                        <option value="">اكتب اسم الموظف للبحث السريع...</option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="space-y-4">
                <h5 class="text-white border-bottom border-secondary border-opacity-20 pb-2 mb-3 d-flex align-items-center">
                    <i class="fas fa-list-ol me-2 text-muted fs-6"></i> بنود العمليات الحالية
                </h5>

                <template x-for="(item, index) in items" :key="item.id">
                    <div class="card p-3 p-md-4 mb-3 item-entry border-0"
                        :class="{
                            'entry-purchase': item.type === 'purchase',
                            'entry-advance': item.type === 'advance',
                            'entry-deduction': item.type === 'deduction',
                            'entry-bonus': item.type === 'bonus'
                        }">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-secondary text-dark fw-bold px-2 py-1"
                                    x-text="'بند #' + (index + 1)"></span>

                                <template
                                    x-if="item.type === 'purchase' && employeeStatus && employeeStatus.has_free_today && isFirstPurchase(index)">
                                    <span
                                        class="text-success small fw-bold bg-success bg-opacity-10 px-2 py-1 rounded border border-success border-opacity-20">
                                        <i class="fas fa-gift me-1"></i> أول مشروب (سيحتسب مجاني)
                                    </span>
                                </template>
                            </div>

                            <button type="button" @click="removeItem(index)"
                                class="btn btn-sm btn-outline-danger border-0 p-1" x-show="items.length > 1">
                                <i class="fas fa-trash-alt fs-5"></i>
                            </button>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-3">
                                <label class="form-label-custom">نوع العملية</label>
                                <select x-model="item.type" class="form-select form-control-custom shadow-none">
                                    <option value="purchase">🛒 شراء منتج</option>
                                    <template x-if="canUserMakePaidPurchase()">
                                        <option value="advance">💰 سلفة مالية</option>
                                    </template> <template x-if="isAdmin">
                                        <option value="bonus">🎁 مكافأة مالية</option>
                                    </template> <template x-if="isAdmin">
                                        <option value="deduction">🛑 خصم مالي (أدمن)</option>
                                    </template>
                                </select>
                            </div>

                            <div class="col-12 col-md-6" x-show="item.type === 'purchase'">
                                <label class="form-label-custom">المنتج المراد شراءه</label>
                                <template x-if="employee_id && !canUserMakePaidPurchase()">
                                    <div class="alert alert-warning py-2 small mt-2 border-0">
                                        <i class="fas fa-lock me-1"></i>
                                        مسموح لك فقط بإضافة المشروب المجاني لهذا الموظف
                                    </div>
                                </template>
                                <div class="position-relative">
                                    <select class="product-select" x-init="initProductSelect($el, index)"></select>
                                </div>
                            </div>

                            <div class="col-12 col-md-3" x-show="item.type === 'purchase'">
                                <label class="form-label-custom">الكمية المطلوبة</label>
                                <input type="number" min="1" x-model="item.quantity"
                                    class="form-control form-control-custom shadow-none">
                            </div>

                            {{-- حقول السلف والمكافآت مدمجة بشكل مرن لمنع تكرار الكود مع تغير العناوين تلقائياً --}}
                            <template x-if="item.type === 'advance' || item.type === 'bonus'">
                                <div class="col-12 col-md-9 row g-3 m-0 p-0">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label-custom"
                                            x-text="item.type === 'advance' ? 'مبلغ السلفة' : 'مبلغ المكافأة'"></label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" min="0" x-model="item.amount"
                                                class="form-control form-control-custom shadow-none" placeholder="0.00">
                                            <span class="input-group-text bg-dark border-secondary border-opacity-20 small"
                                                :class="item.type === 'bonus' ? 'text-purple text-opacity-75' :
                                                    'text-white-50'">ج.م</span>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-8">
                                        <label class="form-label-custom"
                                            x-text="item.type === 'advance' ? 'ملاحظات / سبب السلفة' : 'ملاحظات / سبب المكافأة'"></label>
                                        <input type="text" x-model="item.note"
                                            class="form-control form-control-custom shadow-none"
                                            :placeholder="item.type === 'advance' ? 'اكتب التفاصيل هنا...' :
                                                'اكتب سبب صرف المكافأة للموظف...'">
                                    </div>
                                </div>
                            </template>

                            <template x-if="item.type === 'deduction'">
                                <div class="col-12 col-md-9 row g-3 m-0 p-0">
                                    <div class="col-12 col-md-3">
                                        <label class="form-label-custom text-danger">مبلغ الخصم</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" min="0" x-model="item.amount"
                                                class="form-control form-control-custom border-danger border-opacity-50 shadow-none"
                                                placeholder="0.00">
                                            <span
                                                class="input-group-text bg-dark text-danger border-danger border-opacity-20 small">ج.م</span>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label-custom">تصنيف الخصم</label>
                                        <select x-model="item.deduction_type"
                                            class="form-select form-control-custom shadow-none" required>
                                            <option value="">اختر نوع الخصم...</option>
                                            <option value="تأخير">⏰ تأخير عن المواعيد</option>
                                            <option value="غياب">🚪 غياب بدون إذن</option>
                                            <option value="إتلاف">🔨 إتلاف أدوات/معدات</option>
                                            <option value="جزاء_إداري">📋 جزاء إداري عام</option>
                                            <option value="أخرى">📝 أسباب أخرى</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-5">
                                        <label class="form-label-custom">سبب الخصم والشرح</label>
                                        <input type="text" x-model="item.note"
                                            class="form-control form-control-custom shadow-none"
                                            placeholder="اكتب سبب تطبيق الجزاء المالي...">
                                    </div>
                                </div>
                            </template>

                        </div>
                    </div>
                </template>
            </div>

            <div class="mt-4 row g-3">
                <div class="col-12 col-md-6">
                    <button type="button" @click="addItem()"
                        class="btn btn-outline-warning w-100 py-3 rounded-3 d-flex align-items-center justify-content-center gap-2"
                        style="background: none !important; color: var(--accent) !important; border-color: var(--accent) !important;">
                        <i class="fas fa-plus-circle"></i> إضافة عملية/بند آخر في القائمة
                    </button>
                </div>

                <div class="col-12 col-md-6">
                    <button type="submit"
                        class="btn btn-warning w-100 py-3 rounded-3 fw-bold d-flex align-items-center justify-content-center gap-2"
                        :disabled="loading">
                        <template x-if="!loading">
                            <span><i class="fas fa-cloud-upload-alt"></i> اعتماد وحفظ العمليات بالكامل</span>
                        </template>
                        <template x-if="loading">
                            <span><i class="fas fa-circle-notch fa-spin"></i> جاري إرسال البيانات ومعالجتها...</span>
                        </template>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function transactionForm() {
            return {
                items: [{
                    id: Date.now(),
                    type: 'purchase',
                    product_id: null,
                    quantity: 1,
                    amount: 0,
                    note: '',
                    deduction_type: ''
                }],
                employee_id: null,
                employeeStatus: null,
                loading: false,
                isAdmin: {{ auth()->user() &&
                (auth()->user()->is_admin || in_array(auth()->user()->role, ['admin', 'super_admin', 'manager']))
                    ? 'true'
                    : 'false' }},

                myEmployeeId: {{ optional(auth()->user()->employee)->id ?? 'null' }},
                isFirstPurchase(index) {
                    const firstPurchaseIndex = this.items.findIndex(i => i.type === 'purchase');
                    return index === firstPurchaseIndex;
                },
                canUserMakePaidPurchase() {

                    // الأدمن يقدر يعمل أي حاجة
                    if (this.isAdmin) return true;

                    // اليوزر العادي:
                    // مسموح فقط لو الموظف المختار هو نفسه
                    return Number(this.employee_id) === Number(this.myEmployeeId);
                },
                initEmployeeSelect(el) {
                    new TomSelect(el, {
                        dropdownParent: 'body',
                        onChange: (value) => {
                            this.employee_id = value;
                            this.checkEmployeeStatus(value);
                        }
                    });
                },

                async checkEmployeeStatus(id) {
                    if (!id) {
                        this.employeeStatus = null;
                        return;
                    }
                    try {
                        const response = await fetch(`/employees/${id}/free-drink-status`);
                        this.employeeStatus = await response.json();
                    } catch (e) {
                        console.error("Error fetching employee status");
                    }
                },

                initProductSelect(el, index) {
                    if (el.tomselect) return;
                    new TomSelect(el, {
                        dropdownParent: 'body',
                        valueField: 'id',
                        labelField: 'name',
                        searchField: ['name', 'id'],
                        placeholder: 'اكتب اسم المنتج أو الكود...',
                        load: (query, callback) => {
                            if (!query.length) return callback();
                            fetch(`/products/search?query=${encodeURIComponent(query)}`)
                                .then(r => r.json())
                                .then(json => callback(json))
                                .catch(() => callback());
                        },
                        onChange: (val) => {
                            this.items[index].product_id = val;
                        },
                        render: {
                            option: (item, escape) => `
                                <div class="p-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-white">#${escape(item.id)} - ${escape(item.name)}</span>
                                    </div>
                                    <span class="badge bg-warning text-dark">${escape(item.price)} ج.م</span>
                                </div>`,
                            item: (item, escape) => `<div>#${escape(item.id)} - ${escape(item.name)}</div>`
                        }
                    });
                },

                addItem() {
                    if (!this.canUserMakePaidPurchase()) {

                        const purchases = this.items.filter(i => i.type === 'purchase');

                        if (purchases.length >= 1) {

                            return Swal.fire({
                                icon: 'warning',
                                title: 'غير مسموح',
                                text: 'يمكنك إضافة مشروب مجاني واحد فقط لهذا الموظف'
                            });
                        }
                    }
                    this.items.push({
                        id: Date.now() + Math.random(),
                        type: 'purchase',
                        product_id: null,
                        quantity: 1,
                        amount: 0,
                        note: '',
                        deduction_type: ''
                    });
                },

                removeItem(index) {
                    this.items.splice(index, 1);
                },

                async submitForm() {
                    if (!this.employee_id) {
                        return Swal.fire({
                            icon: 'error',
                            title: 'تنبيه ناقص',
                            text: 'من فضلك اختر الموظف أولاً.'
                        });
                    }

                    for (let item of this.items) {
                        if (item.type === 'deduction' && !item.deduction_type) {
                            return Swal.fire({
                                icon: 'warning',
                                title: 'حقل مطلوب',
                                text: 'يرجى تحديد "تصنيف الخصم" لجميع بنود الخصومات الإدارية.'
                            });
                        }
                    }

                    this.loading = true;
                    try {
                        const response = await fetch("{{ route('employee_transactions.store') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                employee_id: this.employee_id,
                                items: this.items
                            })
                        });

                        const result = await response.json();
                        if (result.success) {
                            Swal.fire({
                                    icon: 'success',
                                    title: 'تم الحفظ بنجاح',
                                    text: result.message,
                                    confirmButtonText: 'ممتاز'
                                })
                                .then(() => {
                                    window.location.reload();
                                });
                        } else {
                            throw new Error(result.message);
                        }
                    } catch (e) {
                        Swal.fire({
                            icon: 'error',
                            title: 'فشل عمل الحفظ',
                            text: e.message
                        });
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
@endsection
