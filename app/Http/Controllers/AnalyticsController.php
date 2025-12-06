<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\AnalyticsService;
class AnalyticsController extends Controller
{
  protected $analytics;

  public function __construct(AnalyticsService $analytics)
  {
    $this->analytics = $analytics;
  }
  /**
   * Helper: آمن لاستدعاء موديل لو موجود وإرجاعه كـ string classname أو null
   */
  protected function modelIfExists(string $shortName)
  {
    $fqcn = "App\\Models\\" . $shortName;
    return class_exists($fqcn) ? $fqcn : null;
  }

  /**
   * Helper: عد عناصر الموديل أو null لو الموديل مش موجود
   */
  protected function countModel(?string $modelClass, $where = null)
  {
    if (!$modelClass)
      return null;

    try {
      $query = $modelClass::query();
      if ($where && is_callable($where)) {
        $where($query);
      }
      return $query->count();
    } catch (\Throwable $e) {
      // لو فيه خطأ في الاستعلام نرجع null بدل ما يكسر الصفحة
      return null;
    }
  }

  /**
   * الصفحة العامة (all)
   */
  public function all()
  {
    $Booking = $this->modelIfExists('Booking');
    $Client = $this->modelIfExists('Client');
    $Product = $this->modelIfExists('Product');
    $Payment = $this->modelIfExists('Payment'); // أو Transaction, Receipt حسب مشروعك
    $Subscription = $this->modelIfExists('Subscription');

    // إجماليات بسيطة
    $totalBookings = $this->countModel($Booking);
    $totalClients = $this->countModel($Client);
    $totalProducts = $this->countModel($Product);

    // إجمالي إيرادات إن وجد موديل Payment وعمود amount
    $totalRevenue = null;
    if ($Payment) {
      try {
        // نحاول نجمع عمود amount لو موجود
        $totalRevenue = $Payment::query()->sum('amount');
      } catch (\Throwable $e) {
        $totalRevenue = null;
      }
    }

    // مؤشرات مشتقة (مثال ARPU)
    $arpu = null;
    if ($totalClients && $totalRevenue !== null && $totalClients > 0) {
      $arpu = round($totalRevenue / $totalClients, 2);
    }

    // بعض الـ KPIs الافتراضية
    $retention = null;
    $churn = null;

    return view('analytics.all', compact(
      'totalBookings',
      'totalClients',
      'totalProducts',
      'totalRevenue',
      'arpu',
      'retention',
      'churn'
    ));
  }

  /**
   * تحليل الحجوزات
   */
  public function bookings()
  {
    $Booking = $this->modelIfExists('Booking');
    $Hall = $this->modelIfExists('Hall');

    $totalBookings = $this->countModel($Booking);
    $cancelled = $Booking ? $Booking::query()->where('status', 'cancelled')->count() : null;

    // متوسط مدة لو عندك عمود duration_minutes
    $avgDuration = null;
    if ($Booking) {
      try {
        $avgDuration = $Booking::query()->avg('duration_minutes');
        $avgDuration = $avgDuration !== null ? round($avgDuration, 1) : null;
      } catch (\Throwable $e) {
        $avgDuration = null;
      }
    }

    $latestBookings = [];
    if ($Booking) {
      try {
        $latestBookings = $Booking::query()->latest('start_at')->take(10)->get();
      } catch (\Throwable $e) {
        $latestBookings = [];
      }
    }

    return view('analytics.bookings', compact('totalBookings', 'cancelled', 'avgDuration', 'latestBookings'));
  }

  /**
   * تحليل العملاء
   */
  public function clients()
  {
    $Client = $this->modelIfExists('Client');

    $newClients = null;
    $activeClients = null;
    $topClients = [];

    if ($Client) {
      try {
        $newClients = $Client::query()->whereDate('created_at', '>=', Carbon::now()->subDays(7))->count();
        // active: مثال على من سجل نشاط خلال 30 يوم (يعتمد موديلك)
        if (method_exists($Client::query()->getModel(), 'scopeActive')) {
          // لو عندك scopeActive
          $activeClients = $Client::query()->active()->count();
        } else {
          $activeClients = $Client::query()->count();
        }
        // أفضل عملاء حسب visits_count إذا موجود حقل
        $topClients = $Client::query()->orderByDesc('visits_count')->take(5)->get();
      } catch (\Throwable $e) {
        $newClients = $activeClients = null;
        $topClients = [];
      }
    }

    return view('analytics.clients', compact('newClients', 'activeClients', 'topClients'));
  }

  /**
   * تحليل القاعات
   */
  public function halls()
  {
    $Hall = $this->modelIfExists('Hall');
    $Booking = $this->modelIfExists('Booking');

    $usedHalls = $this->countModel($Hall);
    $topHallName = null;

    if ($Booking && $Hall) {
      try {
        // مثال: اكثر قاعة حجزاً
        $row = $Booking::query()
          ->selectRaw('hall_id, count(*) as cnt')
          ->groupBy('hall_id')
          ->orderByDesc('cnt')
          ->first();

        if ($row && $row->hall_id) {
          $h = $Hall::find($row->hall_id);
          $topHallName = $h ? $h->name : null;
        }
      } catch (\Throwable $e) {
        $topHallName = null;
      }
    }

    return view('analytics.halls', compact('usedHalls', 'topHallName'));
  }

  /**
   * تحليل التحصيل / الأموال
   */
  public function money(Request $request)
  {

    // ===== Default Date if not provided =====
    $from = $request->query('from');
    $to = $request->query('to');

    if (!$from || !$to) {
      // من يوم 30 قبل اليوم
      $from = now()->subDays(30)->toDateString();
      $to = now()->toDateString();

      // نعمل Redirect بالـ default values
      return redirect()->route('analytics.money', [
        'from' => $from,
        'to' => $to,
      ]);
    }
    // ================================
    // 1) فلترة Queries حسب التاريخ
    // ================================
    $invoiceQuery = Invoice::query();
    $this->analytics->applyDateFilter($invoiceQuery, $request);

    $invoiceItemQuery = InvoiceItem::query();
    $this->analytics->applyDateFilter($invoiceItemQuery, $request);

    $expenseQuery = Expense::query();
    $this->analytics->applyDateFilter($expenseQuery, $request);


    // ================================
    // 2) إجمالي الدخل
    // ================================
    $totalIncome = $invoiceQuery->sum('total');

    // ================================
    // 3) إجمالي المصاريف
    // ================================
    $productsMaterialExpenseId = ExpenseType::where('is_product_material', true)->value('id');
    $totalExpenses = (clone $expenseQuery)->where('expense_type_id', '!=', $productsMaterialExpenseId)->sum('amount');


    // ================================
    // 3) إجمالي تكلفة المنتجات
    // ================================

    $productInvoiceItems = (clone $invoiceItemQuery)
      ->where('item_type', 'product')
      ->selectRaw('SUM(cost * qty) as total_cost')
      ->value('total_cost');
    // ================================
    // 4) صافي الربح
    // ================================

    $netProfit = $totalIncome - ($totalExpenses+$productInvoiceItems);

    // ================================
    // 5) نسبة الربح
    // ================================
    $profitMargin = $totalIncome > 0 ? round(($netProfit / $totalIncome) * 100, 2) : 0;

    // ================================
    // 6) أعلى يوم جاب دخل (حسب الفلترة)
    // ================================
    $topIncomeDay = (clone $invoiceQuery)
      ->selectRaw('DATE(created_at) as day, SUM(total) as sum')
      ->groupBy('day')
      ->orderByDesc('sum')
      ->first();

    // ================================
    // 7) إجمالي لكل نوع خدمة (حسب الفلترة)
    // ================================
    $serviceTotals = (clone $invoiceItemQuery)
      ->selectRaw('item_type, SUM(total) as sum')
      ->groupBy('item_type')
      ->get();

    // لو فيه booking نجمع معاه deposit
    $bookingSum = $serviceTotals->where('item_type', 'booking')->sum('sum');
    $depositSum = $serviceTotals->where('item_type', 'deposit')->sum('sum');

    $totalBookingWithDeposit = $bookingSum + $depositSum;

    // ================================
    // 8) نحسب أعلى خدمة بعد دمج booking + deposit
    // ================================
    $topService = $serviceTotals->map(function ($item) use ($totalBookingWithDeposit) {
      if ($item->item_type === 'booking') {
        $item->sum = $totalBookingWithDeposit;
      }
      return $item;
    })->sortByDesc('sum')->first();

    // ================================
    // 9) مقارنة شهرية (بدون فلترة)
    // ================================
    // تترك كما هي — لأنها مقارنة تاريخية ثابتة
    $thisMonth = Invoice::whereMonth('created_at', now()->month)->sum('total');
    $lastMonth = Invoice::whereMonth('created_at', now()->subMonth()->month)->sum('total');

    $growthRate = $lastMonth > 0
      ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 2)
      : 0;

    // ================================
    // 10) Return View
    // ================================
    return view('analytics.money', [
      'totalIncome' => $totalIncome,
      'totalExpenses' => $totalExpenses,
      'netProfit' => $netProfit,
      'profitMargin' => $profitMargin,
      'thisMonth' => $thisMonth,
      'lastMonth' => $lastMonth,
      'growthRate' => $growthRate,
      'topIncomeDay' => $topIncomeDay,
      'topService' => $topService,
      'productInvoiceItems'=>$productInvoiceItems
    ]);
  }



  /**
   * تحليل الخطط (Plans)
   */
  public function plans()
  {
    $Subscription = $this->modelIfExists('Subscription');
    $Plan = $this->modelIfExists('Plan');

    $subscribers = $this->countModel($Subscription);
    $topPlan = null;

    if ($Subscription && $Plan) {
      try {
        $row = $Subscription::query()
          ->selectRaw('plan_id, count(*) as cnt')
          ->groupBy('plan_id')
          ->orderByDesc('cnt')
          ->first();
        if ($row && $row->plan_id) {
          $p = $Plan::find($row->plan_id);
          $topPlan = $p ? $p->name : null;
        }
      } catch (\Throwable $e) {
        $topPlan = null;
      }
    }

    return view('analytics.plans', compact('subscribers', 'topPlan'));
  }

  /**
   * تحليل المنتجات
   */
  public function products()
  {
    $Product = $this->modelIfExists('Product');

    $soldToday = null;
    $topProduct = null;
    $products = [];

    if ($Product) {
      try {
        // إذا عندك عمود sold_count أو sales relation عدله حسب مشروعك
        if (\Schema::hasColumn((new $Product)->getTable(), 'sold_count')) {
          $topProduct = $Product::query()->orderByDesc('sold_count')->first()->name ?? null;
          $soldToday = $Product::query()->whereDate('updated_at', Carbon::today())->sum('sold_count');
        } else {
          $products = $Product::query()->take(30)->get();
        }
      } catch (\Throwable $e) {
        $soldToday = $topProduct = null;
        $products = [];
      }
    }

    return view('analytics.products', compact('soldToday', 'topProduct', 'products'));
  }

  /**
   * تحليل الجلسات
   */
  public function sessions()
  {
    $Session = $this->modelIfExists('Session');

    $sessionsToday = null;
    $avgAttendance = null;

    if ($Session) {
      try {
        $sessionsToday = $Session::query()->whereDate('date', Carbon::today())->count();
        // افتراض وجود عمود attendance_count
        $avgAttendance = $Session::query()->avg('attendance_count');
      } catch (\Throwable $e) {
        $sessionsToday = $avgAttendance = null;
      }
    }

    return view('analytics.sessions', compact('sessionsToday', 'avgAttendance'));
  }

  /**
   * تحليل الاشتراكات
   */
  public function subscriptions()
  {
    $Subscription = $this->modelIfExists('Subscription');

    $newSubs = null;
    $expiring = null;

    if ($Subscription) {
      try {
        $newSubs = $Subscription::query()->where('created_at', '>=', Carbon::now()->subDays(7))->count();
        $expiring = $Subscription::query()->whereBetween('ends_at', [Carbon::now(), Carbon::now()->addDays(14)])->count();
      } catch (\Throwable $e) {
        $newSubs = $expiring = null;
      }
    }

    return view('analytics.subscriptions', compact('newSubs', 'expiring'));
  }

  /**
   * تحليل المستخدمين
   */
  public function users()
  {
    $User = $this->modelIfExists('User');

    $activeUsers = null;
    $newUsers = null;

    if ($User) {
      try {
        $activeUsers = $User::query()->where('last_active_at', '>=', Carbon::now()->subDays(30))->count();
        $newUsers = $User::query()->whereDate('created_at', '>=', Carbon::now()->subDays(7))->count();
      } catch (\Throwable $e) {
        $activeUsers = $newUsers = null;
      }
    }

    return view('analytics.users', compact('activeUsers', 'newUsers'));
  }

  /**
   * تحليل الزيارات
   */
  public function visits()
  {
    $Visit = $this->modelIfExists('Visit');

    $visitsToday = null;
    $avgVisit = null;
    $visits = [];

    if ($Visit) {
      try {
        $visitsToday = $Visit::query()->whereDate('created_at', Carbon::today())->count();
        $avgVisit = $Visit::query()->avg('duration_seconds');
        $visits = $Visit::query()->latest()->take(20)->get();
      } catch (\Throwable $e) {
        $visitsToday = $avgVisit = null;
        $visits = [];
      }
    }

    return view('analytics.visits', compact('visitsToday', 'avgVisit', 'visits'));
  }
  public function totalIncomeAndProfit(Request $request)
  {
    // ===== Default Date if not provided =====
    $from = $request->from;
    $to = $request->to;

    if (!$from || !$to) {
      $from = now()->subDays(30)->toDateString();
      $to = now()->toDateString();

      return redirect()->route('analytics.totalIncomeAndProfit', [
        'from' => $from,
        'to' => $to,
      ]);
    }

    // ================================ 1) فلترة حسب التاريخ
    $invoiceQuery = Invoice::query();
    $this->analytics->applyDateFilter($invoiceQuery, $request);

    $invoiceItemQuery = InvoiceItem::query();
    $this->analytics->applyDateFilter($invoiceItemQuery, $request);

    $expenseQuery = Expense::query();
    $this->analytics->applyDateFilter($expenseQuery, $request);

    // ================================ 2) الدخل بأنواعه
    $totalProductsIncome = (clone $invoiceItemQuery)->where('item_type', 'product')->sum('total');

    $totalSessionsIncome = (clone $invoiceItemQuery)->where('item_type', 'session')->sum('total');
    $totalBookingsIncome = (clone $invoiceItemQuery)->whereIn('item_type', ['booking', 'deposit'])->sum('total');
    $totalBookingHoursIncome = (clone $invoiceItemQuery)->where('item_type', 'booking')->sum('total');
    $totalBookingDepositIncome = (clone $invoiceItemQuery)->where('item_type', 'deposit')->sum('total');
    $totalSubscriptionsIncome = (clone $invoiceItemQuery)->where('item_type', 'subscription')->sum('total');

    $totalIncome = (clone $invoiceQuery)->sum('total');
    $totalIncomeWithoutProducts = $totalSessionsIncome + $totalBookingsIncome + $totalSubscriptionsIncome;

    $incomeDetails = [
      'جلسات فردية' => $totalSessionsIncome,
      'مبيعات منتجات' => $totalProductsIncome,
      'إجمالي الحجز (ساعات + مقدم)' => $totalBookingsIncome,
      'حجز ساعات' => $totalBookingHoursIncome,
      'مقدم حجز' => $totalBookingDepositIncome,
      'اشتراكات' => $totalSubscriptionsIncome,
    ];

    $productsMaterialExpenseId = ExpenseType::where('is_product_material', true)->value('id');

    // $productInvoiceItems = (clone $expenseQuery)->where('expense_type_id', $productsMaterialExpenseId)->sum('amount');
    $productInvoiceItems = (clone $invoiceItemQuery)
      ->where('item_type', 'product')
      ->selectRaw('SUM(cost * qty) as total_cost')
      ->value('total_cost');
    $expenseTypes = ExpenseType::get();
    $expenseList = [];
    foreach ($expenseTypes as $expenseType) {
      $expenseList[] = [
        'name' => $expenseType->name,
        'total' => (clone $expenseQuery)->where('expense_type_id', $expenseType->id)->sum('amount'),
      ];
    }

    $totalExpensesWithoutProducts = (clone $expenseQuery)->where('expense_type_id', '!=', $productsMaterialExpenseId)->sum('amount');
    $totalExpenses = $totalExpensesWithoutProducts + $productInvoiceItems;

    $netProfit = $totalIncome - $totalExpenses;
    $netProfitOfProducts = $totalProductsIncome - $productInvoiceItems;
    $netProfitWithoutProducts = $totalIncomeWithoutProducts - $totalExpensesWithoutProducts;

    // ================================ أعلى يوم دخل حسب الفلترة
    $topIncomeDay = (clone $invoiceQuery)
      ->selectRaw('DATE(created_at) as day, SUM(total) as sum')
      ->groupBy('day')
      ->orderByDesc('sum')
      ->first();

    // ================================ أعلى خدمة بعد دمج booking + deposit
    $serviceTotals = (clone $invoiceItemQuery)
      ->selectRaw('item_type, SUM(total) as sum')
      ->groupBy('item_type')
      ->get();

    $bookingSum = $serviceTotals->where('item_type', 'booking')->sum('sum');
    $depositSum = $serviceTotals->where('item_type', 'deposit')->sum('sum');
    $totalBookingWithDeposit = $bookingSum + $depositSum;

    $topService = $serviceTotals->map(function ($item) use ($totalBookingWithDeposit) {
      if ($item->item_type === 'booking') {
        $item->sum = $totalBookingWithDeposit;
      }
      return $item;
    })->sortByDesc('sum')->first();

    return view('analytics.income-profit-details', compact(
      'totalIncome',
      'totalExpensesWithoutProducts',
      'netProfitOfProducts',
      'netProfitWithoutProducts',
      'netProfit',
      'totalExpenses',
      'productInvoiceItems',
      'totalSessionsIncome',
      'totalProductsIncome',
      'totalBookingsIncome',
      'totalBookingHoursIncome',
      'totalBookingDepositIncome',
      'totalSubscriptionsIncome',
      'incomeDetails',
      'expenseList',
      'topIncomeDay',
      'topService'
    ));
  }

}