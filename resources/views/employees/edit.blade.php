@extends('layouts.app_page')

@section('title', 'تعديل وإدارة: ' . $employee->name)

@section('content')
    <div class="container mx-auto p-2 md:p-4 max-w-5xl text-white" style="direction: rtl;">

        <!-- رسائل النجاح أو الأخطاء -->
        @if (session('success'))
            <div class="alert alert-success bg-success text-white border-0 mb-4 animate__animated animate__fadeIn">
                {{ session('success') }}
            </div>
        @endif

        <div class="row g-3 mb-4 animate__animated animate__fadeInDown">
            <!-- كارت تعديل بيانات الموظف الأساسية -->
            <div class="col-md-5">
                <div class="card p-4 h-100 border-0" style="background: linear-gradient(145deg, #1a1a1d, #25252a);">
                    <h5 class="text-warning mb-3"><i class="fas fa-user-edit me-2"></i> تعديل البيانات الأساسية</h5>
                    <form action="{{ route('employees.update', $employee->id) }}" method="POST" class="space-y-3">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="text-dim small mb-1 d-block">إسم الموظف</label>
                            <input type="text" name="name" value="{{ $employee->name }}"
                                class="form-control form-control-sm bg-dark border-secondary text-white">
                        </div>
                        <div>
                            <label class="text-dim small mb-1 d-block">رقم الهاتف</label>
                            <input type="text" name="phone" value="{{ $employee->phone }}"
                                class="form-control form-control-sm bg-dark border-secondary text-white">
                        </div>
                        <div>
                            <label class="text-dim small mb-1 d-block">الراتب الأساسي</label>
                            <input type="number" name="salary" value="{{ $employee->salary }}"
                                class="form-control form-control-sm bg-dark border-secondary text-white">
                        </div>
                        <button type="submit" class="btn btn-sm btn-warning w-100 mt-2 text-dark fw-bold">حفظ
                            التغييرات</button>
                    </form>
                </div>
            </div>

            <!-- كارت الملخص المالي للشهر الحالي -->
            <div class="col-md-7">
                <div class="card p-4 h-100 border-0" style="background: #25252a;">
                    <h5 class="text-accent mb-4"><i class="fas fa-chart-pie me-2"></i> موقف شهر
                        {{ now()->translatedFormat('F Y') }}</h5>
                    <div class="row text-center g-3 my-auto">
                        <div class="col-4">
                            <div class="text-dim small mb-1">الراتب الأساسي</div>
                            <div class="h5 text-white font-monospace">{{ number_format($employee->salary) }} ج.م</div>
                        </div>
                        <div class="col-4 border-start border-end border-secondary">
                            <div class="text-danger small mb-1">إجمالي الخصومات</div>
                            <div class="h5 text-danger font-monospace">-
                                {{ number_format($totalAdvances + $totalPurchases) }} ج.م</div>
                        </div>
                        <div class="col-4">
                            <div class="text-success small mb-1">الصافي المؤقت</div>
                            <div class="h5 text-success font-monospace">{{ number_format($netSalary) }} ج.م</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- <!-- كارت إضافة عملية جديدة (بديل مميز للمودال) -->
        <div class="card p-4 mb-4 border-0 animate__animated animate__fadeInUp" style="background: #1e1e22;">
            <h5 class="text-info mb-3"><i class="fas fa-plus-circle me-2"></i> إضافة عملية مالية جديدة للموظف</h5>
            <form action="{{ route('employees.transactions.store', $employee->id) }}" method="POST">
                @csrf
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="text-dim small mb-1">نوع العملية</label>
                        <select name="type" id="tx_type"
                            class="form-select form-select-sm bg-dark border-secondary text-white"
                            onchange="toggleProductField()">
                            <option value="advance">سلفة مالية</option>
                            <option value="purchase">مشتريات منتجات</option>
                        </select>
                    </div>
                    <div class="col-md-3" id="product_field" style="display: none;">
                        <label class="text-dim small mb-1">المنتج</label>
                        <select name="product_id" class="form-select form-select-sm bg-dark border-secondary text-white">
                            <option value="">اختر المنتج...</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->price }} ج.م)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="text-dim small mb-1">المبلغ / القيمة</label>
                        <input type="number" name="amount" step="0.01"
                            class="form-control form-control-sm bg-dark border-secondary text-white" placeholder="0.00"
                            required>
                    </div>
                    <div class="col-md-4">
                        <label class="text-dim small mb-1">ملاحظات / بيان</label>
                        <input type="text" name="notes"
                            class="form-control form-control-sm bg-dark border-secondary text-white"
                            placeholder="اكتب بياناً للعملية...">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-sm btn-info w-100 text-dark fw-bold">إضافة</button>
                    </div>
                </div>
            </form>
        </div> --}}

        <!-- إدارة عمليات الشهر الحالي -->
        <div class="title mt-5 mb-3 animate__animated animate__fadeInLeft" style="font-size: 1.2rem;">
            <i class="fas fa-calendar-check me-2 text-warning"></i> عمليات وحسومات شهر {{ now()->translatedFormat('F Y') }}
        </div>

        @php
            $groupedByDay = $currentTransactions->groupBy(fn($date) => $date->created_at->format('Y-m-d'));
        @endphp

        <div class="space-y-4 mb-5">
            @forelse($groupedByDay as $day => $txs)
                <div class="card p-0 overflow-hidden border-0 bg-transparent animate__animated animate__fadeInUp">
                    <div class="bg-dark p-2 px-3 border-bottom border-secondary d-flex justify-content-between align-items-center"
                        style="background-color: #1a1a1d !important;">
                        <span class="text-warning small"><i class="far fa-clock me-1"></i> {{ $day }}</span>
                        <span class="badge bg-secondary">{{ $txs->count() }} عمليات</span>
                    </div>

                    <!-- جدول ريسبونسف بالكامل يدعم التعديل والحذف المباشر -->
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0 small align-middle">
                            <thead>
                                <tr class="text-dim text-center" style="font-size: 0.8rem;">
                                    <th style="width: 5%">أيقونة</th>
                                    <th style="width: 15%">النوع</th>
                                    <th style="width: 20%">المبلغ الحالي (ج.م)</th>
                                    <th style="width: 45%">البيان / الملاحظات</th>
                                    <th style="width: 15%">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($txs as $tx)
                                    <tr>
                                        <form action="{{ route('employees.transactions.update', $tx->id) }}"
                                            method="POST">
                                            @csrf
                                            @method('PUT')
                                            <td class="text-center">
                                                @if ($tx->type == 'advance')
                                                    <i class="fas fa-hand-holding-usd text-warning fs-5"></i>
                                                @else
                                                    <i class="fas fa-shopping-cart text-info fs-5"></i>
                                                @endif
                                            </td>
                                            <td class="text-center font-bold">
                                                {{ $tx->type == 'advance' ? 'سلفة مالية' : 'مشتريات (' . ($tx->product->name ?? 'منتج') . ')' }}
                                            </td>
                                            <td>
                                                <input type="number" name="amount" step="0.01"
                                                    value="{{ $tx->amount }}"
                                                    class="form-control form-control-sm text-center bg-dark border-0 text-white font-monospace"
                                                    style="box-shadow: none;">
                                            </td>
                                            <td>
                                                <input type="text" name="notes" value="{{ $tx->notes }}"
                                                    class="form-control form-control-sm bg-dark border-0 text-white"
                                                    placeholder="-">
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2 justify-content-center">
                                                    <button type="submit" class="btn btn-sm btn-outline-success p-1 px-2"
                                                        title="حفظ التعديل">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                        </form>
                                        <!-- زر الحذف المباشر بفورم منفصل -->
                                        <form action="{{ route('employees.transactions.destroy', $tx->id) }}"
                                            method="POST"
                                            onsubmit="return confirm('هل أنت متأكد من حذف هذه العملية نهائياً؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger p-1 px-2"
                                                title="حذف">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                    </div>
                    </td>
                    </tr>
            @endforeach
            </tbody>
            </table>
        </div>
    </div>
@empty
    <div class="text-center p-5 card border-0 text-dim" style="background: #25252a;">لا توجد مسحوبات أو سلف لهذا الشهر حتى
        الآن</div>
    @endforelse
    </div>

    <!-- السجل التاريخي (الشهور السابقة) مع إمكانية تعديلها وحذفها أيضاً بنفس التنظيم التكراري -->
    <div class="title mt-5 mb-3" style="font-size: 1.2rem;">
        <i class="fas fa-history me-2 text-dim"></i> السجل التاريخي والشهور السابقة
    </div>

    <div class="accordion border-0" id="historyAccordion">
        @foreach ($history as $month => $txs)
            <div class="accordion-item bg-transparent border-0 mb-2">
                <h2 class="accordion-header">
                    <button
                        class="accordion-button collapsed card py-3 shadow-none text-white d-flex justify-content-between align-items-center"
                        type="button" data-bs-toggle="collapse" data-bs-target="#month-{{ $month }}"
                        style="background: #1a1a1d !important; border: 1px solid #2d2d32;">
                        <span>شهر {{ $month }}</span>
                        <span
                            class="badge bg-dark border border-secondary text-danger font-monospace p-2 ms-auto me-3">إجمالي
                            الخصم: {{ number_format($txs->sum('amount'), 2) }} ج.م</span>
                    </button>
                </h2>
                <div id="month-{{ $month }}" class="accordion-collapse collapse"
                    data-bs-parent="#historyAccordion">
                    <div class="accordion-body card mt-1 p-0 overflow-hidden border-0" style="background: #1e1e22;">
                        <div class="table-responsive">
                            <table class="table table-dark table-hover mb-0 small align-middle">
                                <thead class="text-dim" style="font-size: 0.75rem;">
                                    <tr class="text-center">
                                        <th style="width: 10%">التاريخ</th>
                                        <th style="width: 20%">النوع</th>
                                        <th style="width: 20%">المبلغ</th>
                                        <th style="width: 40%">الملاحظات</th>
                                        <th style="width: 10%">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($txs as $tx)
                                        <tr>
                                            <form action="{{ route('employees.transactions.update', $tx->id) }}"
                                                method="POST">
                                                @csrf
                                                @method('PUT')
                                                <td class="text-center text-dim">{{ $tx->created_at->format('d/m') }}</td>
                                                <td class="text-center fw-bold">
                                                    {{ $tx->type == 'advance' ? 'سلفة' : 'مشتريات' }}</td>
                                                <td>
                                                    <input type="number" name="amount" step="0.01"
                                                        value="{{ $tx->amount }}"
                                                        class="form-control form-control-sm text-center bg-dark border-0 text-white font-monospace">
                                                </td>
                                                <td>
                                                    <input type="text" name="notes" value="{{ $tx->notes }}"
                                                        class="form-control form-control-sm bg-dark border-0 text-white">
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2 justify-content-center">
                                                        <button type="submit"
                                                            class="btn btn-sm btn-outline-success p-1 px-2"
                                                            title="تحديث الفاتورة"><i class="fas fa-check"></i></button>
                                            </form>
                                            <form action="{{ route('employees.transactions.destroy', $tx->id) }}"
                                                method="POST"
                                                onsubmit="return confirm('هل تريد حذف هذه العملية التاريخية؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger p-1 px-2"><i
                                                        class="fas fa-trash-alt"></i></button>
                                            </form>
                        </div>
                        </td>
                        </tr>
        @endforeach
        </tbody>
        </table>
    </div>
    </div>
    </div>
    </div>
    @endforeach
    </div>
    </div>

    <script>
        // دالة لإظهار/إخفاء قائمة المنتجات ديناميكياً بناءً على اختيار نوع المعاملة
        function toggleProductField() {
            const type = document.getElementById('tx_type').value;
            const productField = document.getElementById('product_field');
            if (type === 'purchase') {
                productField.style.display = 'block';
            } else {
                productField.style.display = 'none';
            }
        }
    </script>
@endsection
