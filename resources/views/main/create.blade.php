@extends('layouts.app')

@section('page_title', 'الصفحة الرئيسية')

@section('content')
    <main class="container py-5">
        <div class="row g-4 justify-content-center">

            <div class="col-12 col-sm-6 col-lg-4">
                <form action="{{ route('session.index-manager') }}" method="GET">
                    <button type="submit" class="custom-card w-100">
                        <img src="https://img.icons8.com/ios/50/user-group-man-man.png" alt="icon">
                        <span>إدارة الجلسات</span>
                    </button>
                </form>
            </div>

            <div class="col-12 col-sm-6 col-lg-4">
                <form action="{{ route('sale_proccess.create') }}" method="GET">
                    <button type="submit" class="custom-card w-100">
                        <img src="https://img.icons8.com/ios/50/sell.png" alt="icon">
                        <span>بدء عملية بيع</span>
                    </button>
                </form>
            </div>

            @if (Auth::user()->role === 'admin')
                <div class="col-12 col-sm-6 col-lg-4">
                    <form action="{{ route('expenses.create') }}" method="GET">
                        <button type="submit" class="custom-card w-100">
                            <img src="https://img.icons8.com/ios/50/money-transfer.png" alt="icon">
                            <span>إضافة مصروفات</span>
                        </button>
                    </form>
                </div>
            @else
                <div class="col-12 col-sm-6 col-lg-4">
                    <form action="{{ route('expense-drafts.index') }}" method="GET">
                        <button type="submit" class="custom-card w-100">
                            <img src="https://img.icons8.com/ios/50/money-transfer.png" alt="icon">
                            <span>مصروفات</span>
                        </button>
                    </form>
                </div>
            @endif
            @if (Auth::user()->role === 'admin' || Auth::user()->role === 'supervisor')
                <div class="col-12 col-sm-6 col-lg-4">
                    <form action="{{ route('ingredients.index') }}" method="GET">
                        <button type="submit" class="custom-card w-100">
                            <img src="https://img.icons8.com/ios/50/calendar.png" alt="icon">
                            <span>الخامات</span>
                        </button>
                    </form>
                </div>
            @endif

            <div class="col-12 col-sm-6 col-lg-4">
                <form action="{{ route('invoice.index') }}" method="GET">
                    <button type="submit" class="custom-card w-100">
                        <img src="https://img.icons8.com/ios/50/bill.png" alt="فاتورة">
                        <span>الفواتير</span>
                    </button>
                </form>
            </div>
            <div class="col-12 col-sm-6 col-lg-4">
                <form action="{{ route('bookings.index-manager') }}" method="GET">
                    <button type="submit" class="custom-card w-100">
                        <img src="https://img.icons8.com/ios/50/calendar.png" alt="icon">
                        <span>إدارة الحجوزات</span>
                    </button>
                </form>
            </div>

            <div class="col-12 col-sm-6 col-lg-4">
                <form action="{{ route('subscriptions.index-manager') }}" method="GET">
                    <button type="submit" class="custom-card w-100">
                        <img src="https://img.icons8.com/ios/50/conference.png" alt="icon">
                        <span>المشتركين</span>
                    </button>
                </form>
            </div>

            @if (Auth::user()->role === 'admin')
                <div class="col-12 col-sm-6 col-lg-4">
                    <form action="{{ route('admin.calendar') }}" method="GET">
                        <button type="submit" class="custom-card w-100">
                            <img src="https://img.icons8.com/ios/50/handshake.png" alt="icon">
                            <span>حساب موظفين</span>
                        </button>
                    </form>
                </div>
            @else
                @php
                    $openShift = \App\Models\Shift::where('user_id', Auth::id())->whereNull('end_time')->first();
                @endphp
                <div class="col-12 col-sm-6 col-lg-4">
                    <form action="{{ route('shift.create') }}" method="GET">
                        <button type="submit" class="custom-card w-100">
                            <div class="card-content shift-stack">
                                <span class="shift-indicator-top {{ $openShift ? 'open' : 'closed' }}"></span>
                                <span>الشفت</span>
                            </div>
                        </button>
                    </form>
                </div>
            @endif

            <div class="col-12 col-sm-6 col-lg-4">
                <form action="{{ route('new-session.index') }}" method="GET">
                    <button type="submit" class="custom-card w-100 position-relative">
                        <img src="https://img.icons8.com/ios/50/barcode.png" alt="icon">
                        <span>جلسات لم تسجل</span>

                        {{-- ✅ عداد الجلسات --}}
                        @if ($newSessions && $newSessions->count() > 0)
                            <span class="badge-new-sessions">
                                {{ $newSessions->count() }}
                            </span>
                        @endif
                    </button>
                </form>
            </div>
            <div class="col-12 col-sm-6 col-lg-4">
                <form action="{{ route('employee_transactions.create') }}" method="GET">
                    <button type="submit" class="custom-card w-100">
                        <img src="https://img.icons8.com/ios/50/cash.png" alt="icon">
                        <span>إضافة مسحوبات موظفين</span>
                    </button>
                </form>
            </div>
            @if (Auth::user()->role === 'admin')
                <div class="col-12 col-sm-6 col-lg-4">
                    <form action="{{ route('employees.index') }}" method="GET">
                        <button type="submit" class="custom-card w-100">
                            <img src="https://img.icons8.com/ios/50/administrator-male.png" alt="icon">
                            <span>الموظفين</span>
                        </button>
                    </form>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <form action="{{ route('activities.index') }}" method="GET">
                        <button type="submit" class="custom-card w-100">
                            <img src="https://img.icons8.com/ios/50/administrator broadcasting.png" alt="icon">
                            <span>حركات المشرفين</span>
                        </button>
                    </form>
                </div>
            @endif


        </div>
    </main>


@endsection
