@extends('layouts.app_page')

@section('content')
    <div class="drafts-wrapper">
        <div class="page-header">
            <h2 class="page-title">إدارة مصاريف الموظفين (Drafts)</h2>
            <div class="search-filter-bar">
                <input type="text" id="tableSearch" placeholder="بحث بالوصف أو اسم الموظف..." class="filter-input">
                <input type="date" id="dateFilter" class="filter-input">
                <label class="select-all-label">
                    <input type="checkbox" id="selectAll"> تحديد الكل
                </label>
            </div>
        </div>

        {{-- بار العمليات الجماعية العائم - تم إضافة زر التصدير --}}
        <div class="actions-bar" id="bulk-actions" style="display: none;">
            <span class="selected-count">تم تحديد <span id="count">0</span> مصروف</span>
            <div class="bulk-btns">
                <button id="export-selected-btn" class="btn-bulk-export">
                    <i class="fas fa-file-export"></i> تصدير المحدد
                </button>
                <button id="delete-selected-btn" class="btn-bulk-delete">
                    <i class="fas fa-trash"></i> حذف
                </button>
            </div>
        </div>

        <div class="timeline-container">
            @forelse ($drafts->groupBy(fn($date) => \Carbon\Carbon::parse($date->created_at)->format('Y-m-d')) as $day => $dayDrafts)
                <div class="day-group" data-date="{{ $day }}">
                    <div class="day-label">
                        <span class="date-text">
                            <i class="far fa-calendar-alt"></i>
                            {{ \Carbon\Carbon::parse($day)->translatedFormat('l, d F Y') }}
                        </span>
                        <span class="day-total">{{ $dayDrafts->count() }} عمليات</span>
                    </div>

                    @foreach ($dayDrafts as $draft)
                        <div class="expense-item-card"
                            data-searchable="{{ strtolower($draft->note . ' ' . ($draft->user->name ?? '')) }}">
                            <div class="item-main">
                                {{-- الجزء العلوي: التشيك بوكس والبيانات الأساسية --}}
                                <div class="item-top-row">
                                    <div class="item-checkbox">
                                        <input type="checkbox" class="draft-checkbox" value="{{ $draft->id }}">
                                    </div>
                                    <div class="item-info">
                                        <div class="info-header">
                                            <span class="employee-name">{{ $draft->user->name ?? 'غير معروف' }}</span>
                                            <span class="item-time"><i class="far fa-clock"></i>
                                                {{ \Carbon\Carbon::parse($draft->created_at)->format('g:i A') }}</span>
                                        </div>
                                        <p class="item-note">{{ $draft->note }}</p>
                                    </div>
                                    <div class="item-amount">
                                        <span class="amount-val">{{ number_format($draft->estimated_amount, 2) }}</span>
                                        <span class="currency">ج.م</span>
                                    </div>
                                </div>

                                {{-- الجزء السفلي: أزرار التحكم والتحويل --}}
                                <div class="item-actions-row">
                                    <form action="{{ route('expense-drafts.convert', $draft->id) }}" method="POST"
                                        class="inline-convert-form">
                                        @csrf
                                        <div class="input-group-custom">
                                            <select name="expense_type_id" class="mini-select" required>
                                                <option value="">نوع المصروف</option>
                                                @foreach ($types as $type)
                                                    <option value="{{ $type->id }}"
                                                        {{ $draft->expense_type_id == $type->id ? 'selected' : '' }}>
                                                        {{ $type->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input type="number" step="0.01" name="amount"
                                                value="{{ $draft->estimated_amount }}" class="mini-input" required>
                                            <button type="submit" class="btn-convert-submit">
                                                <i class="fas fa-check"></i> تحويل
                                            </button>
                                        </div>
                                    </form>

                                    <div class="secondary-actions">
                                        {{-- زر التصدير الفردي --}}
                                        <button class="btn-export-single" onclick="exportSingle('{{ $draft->id }}')"
                                            title="تصدير">
                                            <i class="fas fa-file-export"></i>
                                        </button>
                                        {{-- زر الحذف الفردي --}}
                                        <button class="btn-delete-single" onclick="deleteSingle('{{ $draft->id }}')"
                                            title="حذف">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @empty
                <div class="empty-state">
                    <i class="fas fa-file-invoice" style="font-size: 3rem; color: var(--text-p); opacity: 0.3;"></i>
                    <p>لا توجد مصاريف حالياً</p>
                </div>
            @endforelse
        </div>
    </div>

    <style>
        :root {
            --bg-dark: #0f111a;
            --card-bg: #1a1d29;
            --accent-gold: #ffb84d;
            --danger: #ff4d4d;
            --success: #2ecc71;
            --info: #3498db;
            --text-p: #a0a7b1;
            --border: rgba(255, 255, 255, 0.08);
        }

        .drafts-wrapper {
            padding: 15px;
            max-width: 1100px;
            margin: 0 auto;
        }

        .page-header {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 25px;
        }

        .page-title {
            color: var(--accent-gold);
            font-weight: bold;
            font-size: 1.5rem;
        }

        .search-filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .filter-input {
            background: var(--card-bg);
            border: 1px solid var(--border);
            color: white;
            padding: 10px;
            border-radius: 8px;
            flex: 1;
            min-width: 150px;
        }

        .select-all-label {
            color: #fff;
            cursor: pointer;
            background: var(--card-bg);
            padding: 8px 15px;
            border-radius: 8px;
            border: 1px solid var(--border);
        }

        /* Expense Card Styling */
        .expense-item-card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 12px;
            border: 1px solid var(--border);
            position: relative;
        }

        .item-top-row {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 15px;
        }

        .item-info {
            flex: 1;
        }

        .info-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .employee-name {
            font-weight: bold;
            color: #fff;
            font-size: 1rem;
        }

        .item-time {
            font-size: 0.75rem;
            color: var(--text-p);
        }

        .item-note {
            color: var(--text-p);
            font-size: 0.85rem;
            margin: 0;
        }

        .amount-val {
            color: var(--accent-gold);
            font-weight: bold;
            font-size: 1.2rem;
        }

        /* Actions Row - التحويل والتصدير */
        .item-actions-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            padding-top: 12px;
            border-top: 1px solid var(--border);
            flex-wrap: wrap;
        }

        .input-group-custom {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
            flex: 1;
        }

        .mini-select,
        .mini-input {
            background: #252936;
            border: 1px solid var(--border);
            color: white;
            padding: 8px;
            border-radius: 6px;
            font-size: 0.85rem;
        }

        .mini-select {
            flex: 1.5;
            min-width: 120px;
        }

        .mini-input {
            flex: 1;
            min-width: 80px;
        }

        .btn-convert-submit {
            background: var(--success);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .secondary-actions {
            display: flex;
            gap: 8px;
        }

        .btn-export-single,
        .btn-delete-single {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            transition: 0.3s;
        }

        .btn-export-single {
            background: rgba(52, 152, 219, 0.1);
            color: var(--info);
        }

        .btn-delete-single {
            background: rgba(255, 77, 77, 0.1);
            color: var(--danger);
        }

        .btn-export-single:hover {
            background: var(--info);
            color: white;
        }

        .btn-delete-single:hover {
            background: var(--danger);
            color: white;
        }

        /* Bulk Actions Bar */
        .actions-bar {
            position: fixed;
            bottom: 25px;
            left: 50%;
            transform: translateX(-50%);
            background: #fff;
            color: #000;
            padding: 12px 20px;
            border-radius: 12px;
            z-index: 2000;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 90%;
            max-width: 500px;
        }

        .bulk-btns {
            display: flex;
            gap: 10px;
        }

        .btn-bulk-export {
            background: var(--info);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-bulk-delete {
            background: var(--danger);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .item-actions-row {
                flex-direction: column;
                align-items: stretch;
            }

            .input-group-custom {
                flex-direction: column;
            }

            .secondary-actions {
                justify-content: flex-end;
                margin-top: 10px;
            }

            .page-header {
                text-align: center;
            }
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // 1. البحث الحي
            $("#tableSearch").on("keyup", function() {
                let value = $(this).val().toLowerCase();
                $(".expense-item-card").filter(function() {
                    $(this).toggle($(this).data('searchable').indexOf(value) > -1);
                });
                checkEmptyGroups();
            });

            // 2. فلتر التاريخ
            $("#dateFilter").on("change", function() {
                let selectedDate = $(this).val();
                if (!selectedDate) {
                    $(".day-group").show();
                } else {
                    $(".day-group").hide();
                    $(`.day-group[data-date="${selectedDate}"]`).show();
                }
            });

            // 3. تحديد الكل
            $('#selectAll').on('change', function() {
                let checked = $(this).is(':checked');
                $('.draft-checkbox:visible').prop('checked', checked).trigger('change');
            });

            // 4. إظهار بار العمليات الجماعية
            $(document).on('change', '.draft-checkbox', function() {
                let checkedCount = $('.draft-checkbox:checked').length;
                if (checkedCount > 0) {
                    $('#bulk-actions').fadeIn().css('display', 'flex');
                    $('#count').text(checkedCount);
                } else {
                    $('#bulk-actions').fadeOut();
                }
            });

            // 5. وظيفة التصدير الجماعي
            // 5. وظيفة التحويل الجماعي (تصدير المحدد)
            $('#export-selected-btn').on('click', function() {
                let checked = $('.draft-checkbox:checked');

                if (checked.length === 0) return;

                Swal.fire({
                    title: 'تحويل المحدد؟',
                    text: `سيتم تحويل ${checked.length} مصروف إلى مصروف رسمي`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#2ecc71',
                    confirmButtonText: 'نعم، تحويل',
                    cancelButtonText: 'إلغاء'
                }).then((result) => {

                    if (!result.isConfirmed) return;

                    checked.each(function() {

                        let card = $(this).closest('.expense-item-card');

                        let form = card.find('.inline-convert-form');

                        // إرسال الفورم مثل زر التحويل الأخضر
                        form.submit();
                    });

                });
            });

            // 6. وظيفة التصدير الفردي (استدعاء مباشر للفورم الموجود بجانب كل عنصر)
            window.exportSingle = function(id) {
                // نجد الفورم القريب من الزر ونقوم بعمل Submit له
                $(`.expense-item-card input[value="${id}"]`)
                    .closest('.expense-item-card')
                    .find('.inline-convert-form')
                    .submit();
            };
            // 7. الحذف الجماعي
            $('#delete-selected-btn').on('click', function() {
                let ids = getSelectedIds();
                confirmAndAction('حذف المصاريف المحددة؟', `سيتم حذف ${ids.length} عنصر`, '#ff4d4d', () =>
                    bulkDeleteAjax(ids));
            });

            window.deleteSingle = function(id) {
                confirmAndAction('حذف المصروف؟', "لا يمكن التراجع عن هذا الإجراء", '#ff4d4d', () =>
                    bulkDeleteAjax([id]));
            };

            function getSelectedIds() {
                return $('.draft-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();
            }

            function confirmAndAction(title, text, color, action) {
                Swal.fire({
                    title: title,
                    text: text,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: color,
                    confirmButtonText: 'نعم، استمر'
                }).then((result) => {
                    if (result.isConfirmed) action();
                });
            }

            function bulkDeleteAjax(ids) {
                $.post("{{ route('expense-drafts.bulk-delete') }}", {
                    _token: "{{ csrf_token() }}",
                    ids: ids
                }, function(res) {
                    Swal.fire('تم بنجاح', res.success, 'success').then(() => location.reload());
                });
            }

            function checkEmptyGroups() {
                $(".day-group").each(function() {
                    $(this).toggle($(this).find(".expense-item-card:visible").length > 0);
                });
            }
        });
    </script>
@endsection
