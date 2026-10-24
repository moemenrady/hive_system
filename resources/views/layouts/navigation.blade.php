        <button class="drawer-toggle" onclick="toggleDrawer()">☰</button>

        <nav class="navbar">
            <!-- زرار الـ Drawer -->


            <a href="{{ route('main.create') }}" class={{ Request::is('/') || Request::is('main') ? 'active' : '' }}>
                الرئيسية
            </a>
            @if (Auth::user()->role === 'admin')
                <a href="{{ route('expenses.index') }}" class={{ Request::is('expenses*') ? 'active' : '' }}>
                    المصروفات
                </a>
                <a href="{{ route('admin.refund-requests.index') }}" class={{ Request::is('admin/refund-requests*') ? 'active' : '' }}>
                    المرتجعات
                </a>
            @endif

            <a href="{{ route('subscriptions.index') }}" class={{ Request::is('subscriptions') ? 'active' : '' }}>
                الاشتراكات
            </a>
            <a href="{{ route('clients.index') }}" class={{ Request::is('clients') ? 'active' : '' }}>
                العملاء
            </a>

            @if (Auth::user()->role === 'supervisor')
                <a href="{{ route('managment-system-edit.create') }}"
                    class={{ Request::is('managment') ? 'active' : '' }}>
                    لوحة تحكم
                </a>
            @endif
            @if (Auth::user()->role === 'admin' || Auth::user()->role === 'supervisor')
                <a href="{{ route('products.index') }}" class={{ Request::is('products') ? 'active' : '' }}>
                    المخزن
                </a>
            @endif
            @if (Auth::user()->role === 'admin')
                <a href="{{ route('analytics.comprehensive') }}" class={{ Request::is('analytics/comprehensive*') ? 'active' : '' }}>
                    التحليلات الشاملة
                </a>
                <a href="{{ route('managment.create') }}" class={{ Request::is('managment') ? 'active' : '' }}>
                    الأدارة
                </a>
            @endif




        </nav>
