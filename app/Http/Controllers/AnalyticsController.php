<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Sation;
use App\Models\SessionPurchase;
use App\Models\Ingredient;
use App\Models\IngredientStock;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Booking;
use App\Models\Hall;
use App\Models\Category;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\AnalyticsService;
use Illuminate\Support\Facades\DB;

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
  public function clients(Request $request)

  {
    $from = $request->query('from');
    $to   = $request->query('to');

    if (!$from || !$to) {
      $from = now()->subDays(30)->toDateString();
      $to   = now()->toDateString();
    }
    // أوقات
    $now = Carbon::now();

    // ===== العملاء =====
    $totalClients = Client::count();

    $totalVisits = Invoice::where('total', '>', 0)->count();


    $avgVisitsPerClient = $totalClients > 0
      ? round($totalVisits / $totalClients, 1)
      : 0;

    $topClients = Client::withCount([
      'invoices as invoices_count' => function ($q) {
        $q->where('total', '>', 0);
      }
    ])
      ->orderByDesc('invoices_count')
      ->take(10)
      ->get();


    $clientsInRange = Client::whereBetween('created_at', [
      Carbon::parse($from)->startOfDay(),
      Carbon::parse($to)->endOfDay()
    ])->count();

    $clientsLastDay   = Client::whereDate('created_at', $now->toDateString())->count();
    $clientsLastWeek  = Client::where('created_at', '>=', $now->subDays(7))->count();
    $clientsLastMonth = Client::where('created_at', '>=', $now->subMonth())->count();
    $clientsLastYear  = Client::where('created_at', '>=', $now->subYear())->count();

    $visitsLastWeek = Invoice::where('total', '>', 0)
      ->where('created_at', '>=', now()->subDays(7))
      ->count();

    $visitsLastMonth = Invoice::where('total', '>', 0)
      ->where('created_at', '>=', now()->subMonth())
      ->count();




    // ===== الأعمار =====
    $ageStats = [
      'under18' => Client::where('age', '<', 18)->count(),
      '18_25'   => Client::whereBetween('age', [18, 25])->count(),
      '25_35'   => Client::whereBetween('age', [26, 35])->count(),
      '35plus'  => Client::where('age', '>', 35)->count(),
    ];

    // ===== المراحل التعليمية =====
    $educationStats = Client::select('education_stage_id', DB::raw('count(*) as total'))
      ->whereNotNull('education_stage_id')
      ->groupBy('education_stage_id')
      ->with('educationStage')
      ->get();

    // ===== التخصصات =====
    $specializationStats = Client::select('specialization_id', DB::raw('count(*) as total'))
      ->whereNotNull('specialization_id')
      ->groupBy('specialization_id')
      ->with('specialization')
      ->get();

    return view('analytics.clients', compact(
      'totalClients',
      'clientsInRange',
      'from',
      'to',
      'totalVisits',
      'avgVisitsPerClient',
      'topClients',
      'clientsLastDay',
      'clientsLastWeek',
      'clientsLastMonth',
      'clientsLastYear',
      'visitsLastWeek',
      'visitsLastMonth',
      'ageStats',
      'educationStats',
      'specializationStats'
    ));
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
    $totalIncome = $invoiceItemQuery->sum('total');

    // ================================
    // 3) إجمالي المصاريف
    // ================================
    $productsMaterialExpenseId = ExpenseType::where('is_product_material', true)->value('id');

    $totalExpenses = (clone $expenseQuery)->where('expense_type_id', '!=', $productsMaterialExpenseId)->sum('amount');

    // ================================
    // 4) إجمالي شراء المنتجات
    // ================================

    // ================================
    // 5) إجمالي تكلفة المنتجات
    // ================================

    $productInvoiceItems = (clone $expenseQuery)->where('expense_type_id', $productsMaterialExpenseId)->sum('amount');
    // ================================
    // 4) صافي الربح
    // ================================

    $netProfit = $totalIncome - ($totalExpenses + $productInvoiceItems);

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
      'productInvoiceItems' => $productInvoiceItems,
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
  public function products(Request $request)
  {

    $from = $request->query('from');
    $to   = $request->query('to');

    if (!$from || !$to) {
      $from = now()->subDays(30)->toDateString();
      $to   = now()->toDateString();

      return redirect()->route('analytics.products', [
        'from' => $from,
        'to' => $to
      ]);
    }

    // ====================================
    // Base Query
    // ====================================

    $items = InvoiceItem::where('item_type', 'product')
      ->whereBetween('invoice_items.created_at', [
        Carbon::parse($from)->startOfDay(),
        Carbon::parse($to)->endOfDay()
      ]);

    // ====================================
    // 1️⃣ إجمالي المنتجات المباعة
    // ====================================

    $totalProductsSold = (clone $items)->sum('qty');

    // ====================================
    // 2️⃣ إجمالي الإيراد من المنتجات
    // ====================================

    $productsRevenue = (clone $items)->sum('total');

    // ====================================
    // 3️⃣ إجمالي تكلفة المنتجات
    // ====================================

    $productsCost = (clone $items)->sum(DB::raw('cost * qty'));

    // ====================================
    // 4️⃣ إجمالي الربح
    // ====================================

    $productsProfit = $productsRevenue - $productsCost;

    // ====================================
    // 5️⃣ نسبة الربح
    // ====================================

    $productsProfitMargin = $productsRevenue > 0
      ? round(($productsProfit / $productsRevenue) * 100, 2)
      : 0;

    // ====================================
    // 6️⃣ ترتيب المنتجات الأكثر مبيعًا
    // ====================================

    $topProducts = (clone $items)
      ->selectRaw('product_id,name,SUM(qty) as total_qty,SUM(total) as revenue')
      ->groupBy('product_id', 'name')
      ->orderByDesc('total_qty')
      ->take(10)
      ->get();

    // ====================================
    // 7️⃣ أكثر المنتجات ربحًا
    // ====================================

    $mostProfitableProducts = (clone $items)
      ->selectRaw('product_id,name,
            SUM(qty) as qty,
            SUM(total) as revenue,
            SUM(cost*qty) as cost,
            SUM(total - cost*qty) as profit
        ')
      ->groupBy('product_id', 'name')
      ->orderByDesc('profit')
      ->take(10)
      ->get();

    // ====================================
    // 8️⃣ المنتجات الخاسرة
    // ====================================

    $losingProducts = (clone $items)
      ->selectRaw('product_id,name,
            SUM(qty) as qty,
            SUM(total) as revenue,
            SUM(cost*qty) as cost,
            SUM(total - cost*qty) as profit
        ')
      ->groupBy('product_id', 'name')
      ->having('profit', '<', 0)
      ->orderBy('profit')
      ->get();

    // ====================================
    // 9️⃣ تحليلات الكاتيجوري
    // ====================================

    $categoryStats = (clone $items)
      ->join('products', 'invoice_items.product_id', '=', 'products.id')
      ->join('categories', 'products.category_id', '=', 'categories.id')
      ->selectRaw('
            categories.name as category,
            SUM(invoice_items.qty) as qty,
            SUM(invoice_items.total) as revenue,
            SUM(invoice_items.cost * invoice_items.qty) as cost,
            SUM(invoice_items.total - invoice_items.cost * invoice_items.qty) as profit
        ')
      ->groupBy('categories.name')
      ->orderByDesc('revenue')
      ->get();

    $topCategory = $categoryStats->first();

    // ====================================
    // 🔟 استهلاك الخامات
    // ====================================

    $ingredientUsage = DB::table('invoice_items')
      ->join('product_recipes', 'invoice_items.product_id', '=', 'product_recipes.product_id')
      ->join('ingredients', 'product_recipes.ingredient_id', '=', 'ingredients.id')
      ->where('invoice_items.item_type', 'product')
      ->whereBetween('invoice_items.created_at', [
        Carbon::parse($from)->startOfDay(),
        Carbon::parse($to)->endOfDay()
      ])
      ->selectRaw('
            ingredients.name,
            SUM(invoice_items.qty * product_recipes.amount) as used_amount
        ')
      ->groupBy('ingredients.name')
      ->orderByDesc('used_amount')
      ->get();

    // ====================================
    // أكثر منتج يستهلك خامات
    // ====================================

    $productIngredientUsage = DB::table('invoice_items')
      ->join('product_recipes', 'invoice_items.product_id', '=', 'product_recipes.product_id')
      ->where('invoice_items.item_type', 'product')
      ->whereBetween('invoice_items.created_at', [
        Carbon::parse($from)->startOfDay(),
        Carbon::parse($to)->endOfDay()
      ])
      ->selectRaw('
            invoice_items.product_id,
            SUM(invoice_items.qty * product_recipes.amount) as used_material
        ')
      ->groupBy('invoice_items.product_id')
      ->orderByDesc('used_material')
      ->take(10)
      ->get();

    // ====================================
    // Return View
    // ====================================

    return view('analytics.products', [

      'totalProductsSold' => $totalProductsSold,
      'productsRevenue' => $productsRevenue,
      'productsCost' => $productsCost,
      'productsProfit' => $productsProfit,
      'productsProfitMargin' => $productsProfitMargin,

      'topProducts' => $topProducts,
      'mostProfitableProducts' => $mostProfitableProducts,
      'losingProducts' => $losingProducts,

      'categoryStats' => $categoryStats,
      'topCategory' => $topCategory,

      'ingredientUsage' => $ingredientUsage,
      'productIngredientUsage' => $productIngredientUsage,
    ]);
  }


public function productDetails(Request $request, $productId)
{

    $from = $request->query('from');
    $to   = $request->query('to');

    if (!$from || !$to) {
        $from = now()->subDays(30)->toDateString();
        $to   = now()->toDateString();

        return redirect()->route('analytics.product.details', [
            'productId' => $productId,
            'from' => $from,
            'to' => $to
        ]);
    }

    // =================================
    // 1️⃣ بيانات المنتج الأساسية
    // =================================

    $product = Product::with('category','ingredients.unit')
        ->findOrFail($productId);

    // =================================
    // 2️⃣ Query المبيعات
    // =================================

    $items = InvoiceItem::where('item_type','product')
        ->where('product_id',$productId)
        ->whereBetween('created_at',[
            Carbon::parse($from)->startOfDay(),
            Carbon::parse($to)->endOfDay()
        ]);

    // =================================
    // 3️⃣ إجمالي المبيعات
    // =================================

    $totalQty = (clone $items)->sum('qty');

    $totalRevenue = (clone $items)->sum('total');

    $totalCost = (clone $items)->sum(DB::raw('cost * qty'));

    $totalProfit = $totalRevenue - $totalCost;

    $profitMargin = $totalRevenue > 0
        ? round(($totalProfit / $totalRevenue) * 100 ,2)
        : 0;

    // =================================
    // 4️⃣ عدد الفواتير
    // =================================

    $invoiceCount = (clone $items)
        ->distinct('invoice_id')
        ->count('invoice_id');

    // =================================
    // 5️⃣ متوسط سعر البيع
    // =================================

    $avgPrice = (clone $items)->avg('price');

    // =================================
    // 6️⃣ آخر عملية بيع
    // =================================

    $lastSale = (clone $items)
        ->latest('created_at')
        ->first();

    // =================================
    // 7️⃣ أعلى يوم بيع
    // =================================

    $topDay = (clone $items)
        ->selectRaw('DATE(created_at) as day, SUM(qty) as qty')
        ->groupBy('day')
        ->orderByDesc('qty')
        ->first();

    // =================================
    // 8️⃣ أكثر عميل اشترى المنتج
    // =================================

    $topClient = DB::table('invoice_items')
        ->join('invoices','invoice_items.invoice_id','=','invoices.id')
        ->join('clients','invoices.client_id','=','clients.id')
        ->where('invoice_items.product_id',$productId)
        ->selectRaw('clients.name, SUM(invoice_items.qty) as qty')
        ->groupBy('clients.name')
        ->orderByDesc('qty')
        ->first();

    // =================================
    // 9️⃣ استهلاك الخامات
    // =================================

    $ingredientUsage = collect();

    if($product->is_produced){

        $ingredientUsage = DB::table('invoice_items')
            ->join('product_recipes','invoice_items.product_id','=','product_recipes.product_id')
            ->join('ingredients','product_recipes.ingredient_id','=','ingredients.id')
            ->join('units','product_recipes.unit_id','=','units.id')
            ->where('invoice_items.product_id',$productId)
            ->whereBetween('invoice_items.created_at',[
                Carbon::parse($from)->startOfDay(),
                Carbon::parse($to)->endOfDay()
            ])
            ->selectRaw('
                ingredients.name,
                units.name as unit,
                SUM(invoice_items.qty * product_recipes.amount) as used_amount
            ')
            ->groupBy('ingredients.name','units.name')
            ->get();
    }

    // =================================
    // 🔟 تحليل المخزون
    // =================================

    $dailyAvg = $totalQty > 0
        ? $totalQty / max(Carbon::parse($from)->diffInDays($to),1)
        : 0;

    $stockDaysLeft = $dailyAvg > 0
        ? floor($product->quantity / $dailyAvg)
        : null;

    $isLowStock = $product->quantity <= $product->min_quantity;

    // =================================
    // Return
    // =================================

    return view('analytics.product-details', [

        'product' => $product,

        'totalQty' => $totalQty,
        'totalRevenue' => $totalRevenue,
        'totalCost' => $totalCost,
        'totalProfit' => $totalProfit,
        'profitMargin' => $profitMargin,

        'invoiceCount' => $invoiceCount,
        'avgPrice' => $avgPrice,
        'lastSale' => $lastSale,
        'topDay' => $topDay,
        'topClient' => $topClient,

        'ingredientUsage' => $ingredientUsage,

        'stockDaysLeft' => $stockDaysLeft,
        'isLowStock' => $isLowStock,

    ]);
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

    $totalIncome = (clone $invoiceItemQuery)->sum('total');
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

  /**
   * الصفحة الشاملة للتحليلات الذكية للنظام بالكامل
   */
  public function comprehensive(Request $request)
  {
    $period = $request->query('period', 'this_month');
    $now = Carbon::now();

    switch ($period) {
      case 'today':
        $from = $now->toDateString();
        $to = $now->toDateString();
        break;
      case 'yesterday':
        $from = $now->copy()->subDay()->toDateString();
        $to = $now->copy()->subDay()->toDateString();
        break;
      case 'this_week':
        $from = $now->copy()->startOfWeek()->toDateString();
        $to = $now->toDateString();
        break;
      case 'last_30_days':
        $from = $now->copy()->subDays(30)->toDateString();
        $to = $now->toDateString();
        break;
      case 'this_year':
        $from = $now->copy()->startOfYear()->toDateString();
        $to = $now->toDateString();
        break;
      case 'all':
        $from = null;
        $to = null;
        break;
      case 'custom':
      default:
        if ($request->filled('from') && $request->filled('to')) {
          $from = $request->query('from');
          $to = $request->query('to');
          $period = 'custom';
        } else {
          $period = 'this_month';
          $from = $now->copy()->startOfMonth()->toDateString();
          $to = $now->toDateString();
        }
        break;
    }

    $fromDateTime = $from ? Carbon::parse($from)->startOfDay() : null;
    $toDateTime = $to ? Carbon::parse($to)->endOfDay() : null;

    // ==========================================
    // 1️⃣ التحليلات المالية العامة (Financial Overview)
    // ==========================================
    $invoiceItemsQuery = InvoiceItem::query();
    if ($fromDateTime && $toDateTime) {
      $invoiceItemsQuery->whereBetween('invoice_items.created_at', [$fromDateTime, $toDateTime]);
    }

    $totalRevenue = (float) (clone $invoiceItemsQuery)->sum('total');
    $sessionHoursRevenue = (float) (clone $invoiceItemsQuery)->where('item_type', 'session')->sum('total');
    $productRevenue = (float) (clone $invoiceItemsQuery)->where('item_type', 'product')->sum('total');
    $subscriptionRevenue = (float) (clone $invoiceItemsQuery)->where('item_type', 'subscription')->sum('total');
    $bookingRevenue = (float) (clone $invoiceItemsQuery)->whereIn('item_type', ['booking', 'deposit'])->sum('total');

    $invoicesQuery = Invoice::query();
    if ($fromDateTime && $toDateTime) {
      $invoicesQuery->whereBetween('created_at', [$fromDateTime, $toDateTime]);
    }
    $invoicesCount = (clone $invoicesQuery)->count();
    $totalDiscounts = (float) (clone $invoicesQuery)->sum('discount_amount');
    $avgInvoiceValue = $invoicesCount > 0 ? round($totalRevenue / $invoicesCount, 2) : 0;

    // تكلفة البضاعة المباعة والمصروفات
    $cogs = (float) (clone $invoiceItemsQuery)->where('item_type', 'product')->sum(DB::raw('cost * qty'));

    $expensesQuery = Expense::query();
    if ($fromDateTime && $toDateTime) {
      $expensesQuery->whereBetween('created_at', [$fromDateTime, $toDateTime]);
    }
    $totalExpenses = (float) (clone $expensesQuery)->sum('amount');

    $grossProfit = $totalRevenue - $cogs;
    $netProfit = $totalRevenue - ($cogs + $totalExpenses);
    $grossProfitMargin = $totalRevenue > 0 ? round(($grossProfit / $totalRevenue) * 100, 1) : 0;
    $netProfitMargin = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0;

    // ==========================================
    // 2️⃣ تحليلات الجلسات ومساحة العمل (Sessions Intelligence)
    // ==========================================
    $sationQuery = Sation::query();
    if ($fromDateTime && $toDateTime) {
      $sationQuery->whereBetween('start_time', [$fromDateTime, $toDateTime]);
    }

    $totalSessions = (clone $sationQuery)->count();
    $activeSessionsNow = Sation::where('status', 'active')->count();
    $closedSessions = (clone $sationQuery)->where('status', 'closed')->count();
    $totalPersons = (int) (clone $sationQuery)->sum('persons');
    $avgPersonsPerSession = $totalSessions > 0 ? round($totalPersons / $totalSessions, 1) : 0;

    $isSqlite = DB::connection()->getDriverName() === 'sqlite';

    // متوسط مدة الجلسة (بالدقائق)
    if ($isSqlite) {
      $avgDurationMinutes = (clone $sationQuery)
        ->where('status', 'closed')
        ->whereNotNull('end_time')
        ->selectRaw('AVG((strftime(\'%s\', end_time) - strftime(\'%s\', start_time)) / 60) as dur')
        ->value('dur') ?? 0;
    } else {
      $avgDurationMinutes = (clone $sationQuery)
        ->where('status', 'closed')
        ->whereNotNull('end_time')
        ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, start_time, end_time)) as dur')
        ->value('dur') ?? 0;
    }
    $avgDurationMinutes = round((float) $avgDurationMinutes);
    $durHours = intdiv($avgDurationMinutes, 60);
    $durMins = $avgDurationMinutes % 60;
    $avgDurationFormatted = ($durHours > 0 ? "{$durHours} س و " : "") . "{$durMins} د";

    // إيرادات المشروبات داخل الجلسات مقابل البيع المنفصل
    $sessionBeverageRevenue = (float) (clone $invoiceItemsQuery)
      ->where('item_type', 'product')
      ->whereNotNull('session_id')
      ->sum('total');
    $standaloneSalesRevenue = max(0, $productRevenue - $sessionBeverageRevenue);

    // معدل طلب المشروبات للجلسات (Attachment Rate)
    $sessionsWithPurchasesCount = (clone $sationQuery)->whereHas('purchases')->count();
    $beverageAttachmentRate = $totalSessions > 0 ? round(($sessionsWithPurchasesCount / $totalSessions) * 100, 1) : 0;

    // متوسط إنفاق الجلسة والزائر
    $totalSessionCombinedRevenue = $sessionHoursRevenue + $sessionBeverageRevenue;
    $avgSpendPerSession = $totalSessions > 0 ? round($totalSessionCombinedRevenue / $totalSessions, 2) : 0;
    $avgSpendPerPerson = $totalPersons > 0 ? round($totalSessionCombinedRevenue / $totalPersons, 2) : 0;

    // توزيع ساعات الذروة (24 ساعة)
    if ($isSqlite) {
      $peakHoursRaw = (clone $sationQuery)
        ->selectRaw('CAST(strftime(\'%H\', start_time) AS INTEGER) as hr, COUNT(*) as cnt')
        ->groupBy('hr')
        ->pluck('cnt', 'hr')
        ->toArray();
    } else {
      $peakHoursRaw = (clone $sationQuery)
        ->selectRaw('HOUR(start_time) as hr, COUNT(*) as cnt')
        ->groupBy('hr')
        ->pluck('cnt', 'hr')
        ->toArray();
    }
    $peakHoursData = [];
    for ($h = 0; $h < 24; $h++) {
      $peakHoursData[$h] = (int) ($peakHoursRaw[$h] ?? 0);
    }

    // ==========================================
    // 3️⃣ تحليلات المنتجات والمخزون والمبيعات (Products & Inventory)
    // ==========================================
    $totalProductCatalog = Product::count();
    $producedProductsCount = Product::where('is_produced', true)->count();
    $packagedProductsCount = Product::where('is_produced', false)->count();

    // تقييم المخزون الحالي
    $inventoryTotalCost = (float) Product::sum(DB::raw('cost * quantity'));
    $inventoryTotalRetail = (float) Product::sum(DB::raw('price * quantity'));
    $inventoryPotentialProfit = $inventoryTotalRetail - $inventoryTotalCost;

    // نواقص المنتجات (الرصيد <= الحد الأدنى)
    $lowStockProducts = Product::with('category')
      ->whereColumn('quantity', '<=', 'min_quantity')
      ->orderBy('quantity')
      ->get();

    // إجمالي الوحدات المباعة
    $totalUnitsSold = (int) (clone $invoiceItemsQuery)->where('item_type', 'product')->sum('qty');

    // أفضل 8 منتجات مبيعاً
    $topSellingProducts = (clone $invoiceItemsQuery)
      ->where('item_type', 'product')
      ->selectRaw('name, product_id, SUM(qty) as total_qty, SUM(total) as revenue, SUM(cost * qty) as total_cost, SUM(total - cost * qty) as profit')
      ->groupBy('name', 'product_id')
      ->orderByDesc('total_qty')
      ->take(8)
      ->get();

    // أكثر 8 منتجات تحقيقاً للأرباح
    $topProfitableProducts = (clone $invoiceItemsQuery)
      ->where('item_type', 'product')
      ->selectRaw('name, product_id, SUM(qty) as total_qty, SUM(total) as revenue, SUM(cost * qty) as total_cost, SUM(total - cost * qty) as profit')
      ->groupBy('name', 'product_id')
      ->orderByDesc('profit')
      ->take(8)
      ->get();

    // توزيع المبيعات حسب الأقسام
    $categoryBreakdown = (clone $invoiceItemsQuery)
      ->where('item_type', 'product')
      ->join('products', 'invoice_items.product_id', '=', 'products.id')
      ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
      ->selectRaw('COALESCE(categories.name, "بدون قسم") as category_name, SUM(invoice_items.qty) as qty, SUM(invoice_items.total) as revenue, SUM(invoice_items.total - invoice_items.cost * invoice_items.qty) as profit')
      ->groupBy('category_name')
      ->orderByDesc('revenue')
      ->get();

    // ==========================================
    // 4️⃣ تحليلات الخامات ومستودع التشغيل (Ingredients & Raw Materials)
    // ==========================================
    $totalIngredientsCount = Ingredient::count();

    // خامات تحت حد الأمان (Alert Stock)
    $lowStockIngredients = Ingredient::with('unit')
      ->whereColumn('stock', '<=', 'alert_stock')
      ->orderBy('stock')
      ->get();

    // جميع الخامات لعرض جدول شامل
    $allIngredients = Ingredient::with('unit')
      ->orderBy('stock')
      ->get();

    // حركات صرف واستهلاك الخامات
    $ingredientStockQuery = IngredientStock::query();
    if ($fromDateTime && $toDateTime) {
      $ingredientStockQuery->whereBetween('ingredient_stocks.created_at', [$fromDateTime, $toDateTime]);
    }
    $totalOutMovements = (clone $ingredientStockQuery)->where('type', 'out')->count();
    $totalInMovements = (clone $ingredientStockQuery)->where('type', 'in')->count();
    $totalOutAmount = (float) (clone $ingredientStockQuery)->where('type', 'out')->sum('amount');

    // أكثر الخامات استهلاكاً خلال الفترة
    $topConsumedIngredients = (clone $ingredientStockQuery)
      ->where('type', 'out')
      ->join('ingredients', 'ingredient_stocks.ingredient_id', '=', 'ingredients.id')
      ->leftJoin('units', 'ingredient_stocks.unit_id', '=', 'units.id')
      ->selectRaw('ingredients.name, COALESCE(units.name, "") as unit_name, SUM(ingredient_stocks.amount) as consumed_qty, COUNT(ingredient_stocks.id) as op_count')
      ->groupBy('ingredients.id', 'ingredients.name', 'units.name')
      ->orderByDesc('consumed_qty')
      ->take(8)
      ->get();

    // ==========================================
    // 5️⃣ الاشتراكات وحجوزات القاعات (Subscriptions & Bookings)
    // ==========================================
    $subscriptionsQuery = Subscription::query();
    if ($fromDateTime && $toDateTime) {
      $subscriptionsQuery->whereBetween('subscriptions.created_at', [$fromDateTime, $toDateTime]);
    }
    $totalSubscriptionsInPeriod = (clone $subscriptionsQuery)->count();
    $activeSubscriptions = Subscription::where('is_active', true)->count();
    $planDistribution = (clone $subscriptionsQuery)
      ->join('subscription_plans', 'subscriptions.plan_id', '=', 'subscription_plans.id')
      ->selectRaw('subscription_plans.name, COUNT(*) as count')
      ->groupBy('subscription_plans.name')
      ->get();

    $bookingsQuery = Booking::query();
    if ($fromDateTime && $toDateTime) {
      $bookingsQuery->whereBetween('bookings.start_at', [$fromDateTime, $toDateTime]);
    }
    $totalBookings = (clone $bookingsQuery)->count();
    $totalBookingAttendees = (int) (clone $bookingsQuery)->sum('attendees');
    $topHall = (clone $bookingsQuery)
      ->join('halls', 'bookings.hall_id', '=', 'halls.id')
      ->selectRaw('halls.name, COUNT(*) as count, SUM(COALESCE(bookings.real_total, bookings.estimated_total)) as revenue')
      ->groupBy('halls.name')
      ->orderByDesc('count')
      ->first();

    // ==========================================
    // 6️⃣ بيانات الرسم البياني الزمني (Timeline Chart Data)
    // ==========================================
    $dailyRows = (clone $invoiceItemsQuery)
      ->selectRaw('DATE(invoice_items.created_at) as date, SUM(total) as revenue, SUM(cost * qty) as cost')
      ->groupBy('date')
      ->orderBy('date')
      ->get()
      ->keyBy('date');

    $dailyExpenses = (clone $expensesQuery)
      ->selectRaw('DATE(created_at) as date, SUM(amount) as expense')
      ->groupBy('date')
      ->get()
      ->keyBy('date');

    $timelineLabels = [];
    $timelineRevenue = [];
    $timelineProfit = [];

    if ($from && $to) {
      $startDay = Carbon::parse($from);
      $endDay = Carbon::parse($to);
      $daysCount = $startDay->diffInDays($endDay);

      if ($daysCount <= 62) {
        for ($d = $startDay->copy(); $d->lte($endDay); $d->addDay()) {
          $dateStr = $d->toDateString();
          $timelineLabels[] = $d->format('m/d');
          $rev = (float) ($dailyRows[$dateStr]->revenue ?? 0);
          $cost = (float) ($dailyRows[$dateStr]->cost ?? 0);
          $exp = (float) ($dailyExpenses[$dateStr]->expense ?? 0);
          $timelineRevenue[] = round($rev, 2);
          $timelineProfit[] = round($rev - ($cost + $exp), 2);
        }
      } else {
        foreach ($dailyRows as $dateStr => $row) {
          $timelineLabels[] = $dateStr;
          $rev = (float) $row->revenue;
          $cost = (float) $row->cost;
          $exp = (float) ($dailyExpenses[$dateStr]->expense ?? 0);
          $timelineRevenue[] = round($rev, 2);
          $timelineProfit[] = round($rev - ($cost + $exp), 2);
        }
      }
    } else {
      // حالة All time
      foreach ($dailyRows as $dateStr => $row) {
        $timelineLabels[] = $dateStr;
        $rev = (float) $row->revenue;
        $cost = (float) $row->cost;
        $exp = (float) ($dailyExpenses[$dateStr]->expense ?? 0);
        $timelineRevenue[] = round($rev, 2);
        $timelineProfit[] = round($rev - ($cost + $exp), 2);
      }
    }

    return view('analytics.comprehensive', compact(
      'period',
      'from',
      'to',
      'totalRevenue',
      'sessionHoursRevenue',
      'productRevenue',
      'subscriptionRevenue',
      'bookingRevenue',
      'invoicesCount',
      'totalDiscounts',
      'avgInvoiceValue',
      'cogs',
      'totalExpenses',
      'grossProfit',
      'netProfit',
      'grossProfitMargin',
      'netProfitMargin',
      'totalSessions',
      'activeSessionsNow',
      'closedSessions',
      'totalPersons',
      'avgPersonsPerSession',
      'avgDurationMinutes',
      'avgDurationFormatted',
      'sessionBeverageRevenue',
      'standaloneSalesRevenue',
      'sessionsWithPurchasesCount',
      'beverageAttachmentRate',
      'totalSessionCombinedRevenue',
      'avgSpendPerSession',
      'avgSpendPerPerson',
      'peakHoursData',
      'totalProductCatalog',
      'producedProductsCount',
      'packagedProductsCount',
      'inventoryTotalCost',
      'inventoryTotalRetail',
      'inventoryPotentialProfit',
      'lowStockProducts',
      'totalUnitsSold',
      'topSellingProducts',
      'topProfitableProducts',
      'categoryBreakdown',
      'totalIngredientsCount',
      'lowStockIngredients',
      'allIngredients',
      'totalOutMovements',
      'totalInMovements',
      'totalOutAmount',
      'topConsumedIngredients',
      'totalSubscriptionsInPeriod',
      'activeSubscriptions',
      'planDistribution',
      'totalBookings',
      'totalBookingAttendees',
      'topHall',
      'timelineLabels',
      'timelineRevenue',
      'timelineProfit'
    ));
  }
}
