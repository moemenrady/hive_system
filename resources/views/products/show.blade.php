@extends('layouts.app_page')

@section('title', "تفاصيل المنتج — {$product->name}")

@section('content')
    <div class="product-container fade-in">
        <div class="card">
            <div class="card-header">
                <div class="header-left">
                    <span class="badge">#{{ $product->id }}</span>
                    <h2>🛒 تفاصيل المنتج</h2>
                </div>
                <a href="#" id="openEditModal" class="btn edit-btn" data-bs-toggle="modal"
                    data-bs-target="#editProductModal">
                    ✏️ تعديل
                </a>
            </div>

            {{-- بيانات أساسية --}}
            <div class="section product-main">
                <div class="box">
                    <div class="row">
                        <div class="col">
                            <label class="lbl">📦 الاسم</label>
                            <p class="value">{{ $product->name }}</p>
                        </div>
                        <div class="col">
                            <label class="lbl">💰 سعر البيع</label>
                            <p class="value gold">{{ number_format($product->price, 2) }} ج.م</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label class="lbl">🧾 التكلفة</label>
                            <p class="value">{{ number_format($product->cost, 2) }} ج.م</p>
                        </div>
                        <div class="col">
                            <label class="lbl">📦 الكمية بالمخزون</label>
                            <p class="value">{{ $product->quantity }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- حالة المنتج المهم --}}
            <div class="section statuses">
                <h3>⭐ حالة المنتج</h3>
                <div class="box flex-grid">
                    <div class="status-card">
                        <label class="d-check">
                            <input type="checkbox" {{ $importantProduct ? 'checked' : '' }} disabled>
                            <span>منتج مهم</span>
                        </label>

                        @if ($importantProduct)
                            <button class="btn small edit-important-btn" data-id="{{ $importantProduct->id }}"
                                data-name="{{ $importantProduct->name }}"
                                data-product="{{ $importantProduct->product->name ?? $product->name }}"
                                data-product-id="{{ $importantProduct->product_id ?? $product->id }}">
                                ✏️ تعديل المنتج المهم
                            </button>
                            <p class="small">مسجل كمنتج مهم باسم: <strong>{{ $importantProduct->name }}</strong></p>
                        @else
                            <p class="small muted">غير مسجل كمنتج مهم</p>
                            <div class="actions">
                                <form id="addImportantForm" method="POST"
                                    action="{{ route('important_products.store') }}">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="name" value="{{ $product->name }}">
                                    <button type="submit" class="btn small success">➕ إضافة للمنتجات المهمة</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- نظرة عامة --}}
            <div class="section">
                <h3>📊 نظرة عامة</h3>
                <div class="box">
                    <ul class="mini-list">
                        <li><strong>تاريخ الإضافة:</strong> {{ $product->created_at->format('Y-m-d H:i') }}</li>
                        <li><strong>آخر تعديل:</strong> {{ $product->updated_at->format('Y-m-d H:i') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- 🟢 مودال تعديل المنتج --}}
    <div class="modal fade" id="editProductModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-dark">
                <div class="modal-header">
                    <h5 class="modal-title text-gold">✏️ تعديل المنتج</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="updateProductForm" action="{{ route('products.update', $product->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label text-gold">📦 الاسم</label>
                            <input type="text" name="name" class="form-control dark-input"
                                value="{{ $product->name }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-gold">💰 السعر</label>
                            <input type="number" step="0.01" name="price" class="form-control dark-input"
                                value="{{ $product->price }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-gold">🧾 التكلفة</label>
                            <input type="number" step="0.01" name="cost" class="form-control dark-input"
                                value="{{ $product->cost }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-gold">📦 الكمية</label>
                            <input type="number" name="quantity" class="form-control dark-input"
                                value="{{ $product->quantity }}" required>
                        </div>

                        <button type="submit" class="btn-submit w-100 mt-3">💾 حفظ التعديلات</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- 🟣 مودال تعديل المنتج المهم --}}
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-dark">
                <div class="modal-header">
                    <h5 class="modal-title text-gold">✏️ تعديل المنتج المهم</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm" method="POST" action="">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label text-gold">اسم التعريف</label>
                            <input type="text" name="name" id="editName" class="form-control dark-input"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-gold">المنتج المرتبط</label>
                            <input type="hidden" name="product_id" id="editProductId">
                            <p class="small text-muted">المنتج الحالي: <span id="editSelectedProductText"></span></p>
                        </div>

                        <button type="submit" class="btn-submit w-100 mt-2">💾 حفظ التعديلات</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function() {

            // 🔹 فتح مودال تعديل المنتج
            $('#openEditModal').on('click', function(e) {
                e.preventDefault();
                $('#editProductModal').modal('show');
            });

            // 🔹 حفظ تعديل المنتج (AJAX)
            $('#updateProductForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'تم التحديث!',
                            timer: 1200,
                            showConfirmButton: false
                        });
                        $('#editProductModal').modal('hide');
                        setTimeout(() => location.reload(), 1300);
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ!',
                            text: xhr.responseJSON?.message || 'حدث خطأ أثناء الحفظ'
                        });
                    }
                });
            });

            // 🔹 إضافة منتج مهم
            $('#addImportantForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'تمت الإضافة!',
                            timer: 1200,
                            showConfirmButton: false
                        });
                        setTimeout(() => location.reload(), 1300);
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ!',
                            text: xhr.responseJSON?.message || 'تعذر الإضافة'
                        });
                    }
                });
            });

            // 🔹 فتح مودال تعديل المنتج المهم
            $(document).on('click', '.edit-important-btn', function() {
                $('#editName').val($(this).data('name'));
                $('#editProductId').val($(this).data('product-id'));
                $('#editSelectedProductText').text($(this).data('product'));
                $('#editForm').attr('action', `/important-products/${$(this).data('id')}`);
                $('#editModal').modal('show');
            });

            // 🔹 حفظ تعديل المنتج المهم
            $('#editForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'تم التعديل!',
                            timer: 1200,
                            showConfirmButton: false
                        });
                        $('#editModal').modal('hide');
                        setTimeout(() => location.reload(), 1300);
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'فشل التعديل',
                            text: 'حاول مرة أخرى'
                        });
                    }
                });
            });
        });
    </script>
@endsection

@section('style')
    <style>
        body {
            background: #0f0f11;
            font-family: "Cairo", sans-serif;
            color: #f5f5f5;
        }

        .product-container {
            max-width: 900px;
            margin: 60px auto;
            padding: 20px;
        }

        .card {
            background: linear-gradient(145deg, #1b1b1f, #1a1a1c);
            border-radius: 22px;
            padding: 28px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 215, 0, 0.05);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding-bottom: 14px;
        }

        .badge {
            background: linear-gradient(135deg, #b78d2a, #d4af37);
            color: #fff;
            padding: 6px 14px;
            border-radius: 50px;
            font-weight: bold;
        }

        .edit-btn {
            background: linear-gradient(135deg, #d4af37, #b78d2a);
            color: #111;
            padding: 8px 16px;
            border-radius: 12px;
            font-weight: 700;
            text-decoration: none;
        }

        .edit-btn:hover {
            background: linear-gradient(135deg, #ffd75f, #d4af37);
        }

        .section h3 {
            color: #d4af37;
            font-size: 19px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .box {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 14px;
            padding: 18px;
            margin-bottom: 20px;
        }

        .lbl {
            font-weight: 600;
            color: #bbb;
            font-size: 14px;
        }

        .value {
            font-size: 18px;
            font-weight: 700;
            color: #f5f5f5;
        }

        .value.gold {
            color: #d4af37;
        }

        .btn.small {
            background: linear-gradient(135deg, #d4af37, #b78d2a);
            color: #111;
            padding: 7px 14px;
            border-radius: 10px;
            font-weight: 700;
        }

        .btn.small.success {
            background: linear-gradient(135deg, #2e7d32, #4caf50);
            color: #fff;
        }

        .modal-dark {
            background: #1b1b1f;
            border-radius: 20px;
            color: #fff;
            border: 1px solid rgba(255, 215, 0, 0.1);
        }

        .dark-input {
            background: #141416;
            border: 1px solid #333;
            color: #fff;
            border-radius: 10px;
        }

        .dark-input:focus {
            border-color: #d4af37;
            box-shadow: 0 0 8px rgba(212, 175, 55, 0.3);
        }

        .btn-submit {
            background: linear-gradient(135deg, #d4af37, #b78d2a);
            border: none;
            padding: 12px;
            border-radius: 12px;
            color: #111;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            transition: .25s;
        }

        .btn-submit:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.3);
        }

        .fade-in {
            animation: fadeInUp .6s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endsection
