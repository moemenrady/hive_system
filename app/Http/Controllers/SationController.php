<?php

namespace App\Http\Controllers;

use App\Enums\SystemActionType;
use App\Models\Booking;
use App\Models\Category;
use App\Models\FullDayHour;
use App\Models\Hall;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\SessionPurchase;
use App\Models\Shift;
use App\Models\ShiftAction;
use App\Models\SystemAction;
use App\Models\VenuePricing;
use App\Models\Visit;
use App\Models\Subscription;
use App\Services\ShiftService;
use App\Support\InvoiceNumber;
use Auth;
use Carbon\Carbon;
use App\Models\Client;
use App\Models\Sation;
use App\Models\Product;
use DB;
use Illuminate\Http\Request;
use App\Models\ImportantProduct;



class SationController extends Controller
{
  public function split(Request $request)
  {
    // ✅ تحقق من وجود شيفت مفتوح
    $user = Auth::user();
    $openShift = Shift::where('user_id', $user->id)->whereNull('end_time')->first();
    $isAdmin = $user->hasRole('admin');
    if (!$openShift && !$isAdmin) {
      session()->flash('shift_required', true);
      return redirect()->back()
        ->with('error', '⚠️ لا يوجد شيفت مفتوح، ابدأ شيفت أولاً.');
    }

    // 1) قواعد الفاليديشن المحسنة
    $request->validate([
      'session_id' => 'required|exists:sations,id',
      'split_persons' => 'required|integer|min:1',
      'hours' => 'required|numeric|min:0',
      'items' => 'nullable|array',
      'items.*' => 'nullable|integer|min:0', // القيم (الكميات)
    ]);
    // ✅ تحقق إضافي: لازم يكون فيه ساعات أو مشتريات
    $submittedItems = $request->input('items', []);
    $hasItems = collect($submittedItems)->filter(function ($qty) {
      return intval($qty) > 0;
    })->isNotEmpty();
    if (floatval($request->hours) <= 0 && !$hasItems) {
      return redirect()->back()
        ->withInput()
        ->with('error', '⚠️ لا يمكن عمل حساب منفصل من جلسة لا يوجد بها مشتريات وعدد الساعات = 0.');
    }

    DB::beginTransaction();
    try {
      // lock session row to avoid concurrent modifications
      $parentSession = Sation::where('id', $request->session_id)
        ->lockForUpdate()
        ->with(['purchases.product']) // جلب مشتريات الجلسة مع علاقة المنتج إن وُجدت
        ->first();

      if (!$parentSession || $parentSession->status !== 'active') {
        DB::rollBack();
        return redirect()->back()->with('error', '⚠️ الجلسة غير موجودة أو منتهية بالفعل.');
      }

      // تحقق أن عدد الأشخاص المطلوب فصلهم لا يتجاوز الموجود بالفعل
      if ($request->split_persons > $parentSession->persons) {
        DB::rollBack();
        return redirect()->back()->with('error', '⚠️ عدد الأفراد المختارين أكبر من الموجود في الجلسة.');
      }

      // إذا المستخدم لم يرسل أي items لكن الجلسة الأصلية لديها مشتريات --> نمنع العملية ونعطي تحذير واضح
      if (!$hasItems && $parentSession->purchases->isNotEmpty()) {
        DB::rollBack();
        return redirect()->back()
          ->withInput()
          ->with('error', '⚠️ الجلسة الأصلية تحتوي على مشتريات. اختر المنتجات/الكميات التي تريد نقلها للحساب المنفصل أو أكد أنك تريد فصل الحساب بدون نقل مشتريات.');
      }

      // 3) تحقق تفصيلي: أن كل منتج مرسل موجود فعلاً في مشتريات الجلسة وبكمية كافية
      $insufficient = []; // سنجمع هنا أي منتجات بكميات ناقصة
      foreach ($submittedItems as $prodIdStr => $qty) {
        $prodId = intval($prodIdStr);
        $qty = intval($qty);
        if ($qty <= 0)
          continue;

        // حاول إيجاد السطر المقابل في مشتريات الجلسة الأصلية
        $parentPurchase = $parentSession->purchases->firstWhere('product_id', $prodId);
        if (!$parentPurchase) {
          // المنتج غير موجود أساساً في مشتريات الجلسة
          $insufficient[] = [
            'product_id' => $prodId,
            'needed' => $qty,
            'available' => 0,
            'name' => optional($parentPurchase)->product->name ?? "ID: {$prodId}"
          ];
        } else {
          // تحقق الكمية
          $availableQty = intval($parentPurchase->quantity ?? 0);
          if ($availableQty < $qty) {
            $insufficient[] = [
              'product_id' => $prodId,
              'needed' => $qty,
              'available' => $availableQty,
              'name' => optional($parentPurchase->product)->name ?? "ID: {$prodId}"
            ];
          }
        }
      }

      if (!empty($insufficient)) {
        // جهّز رسالة خطأ مفصّلة
        $messages = ['⚠️ لا يمكن إتمام الفصل بسبب اختلاف في كميات المنتجات المرسلة:'];
        foreach ($insufficient as $it) {
          $messages[] = "- {$it['name']}: مطلوب {$it['needed']}، متاح في الجلسة الأصلية {$it['available']}.";
        }
        DB::rollBack();
        return redirect()->back()->withInput()->with('error', implode("\n", $messages));
      }

      // 4) إنشاء الجلسة الجديدة (لا تضع end_time هنا — اترك checkout يتولّى ذلك)
      $newSession = Sation::create([
        'client_id' => $parentSession->client_id,
        'persons' => $request->split_persons,
        'status' => 'active',
        'start_time' => $parentSession->start_time,
        'end_time' => null,
      ]);

      // 5) تجهيز المشتريات: قفل صفوف المنتجات ثم إنشاء سجلات session_purchases للجلسة الجديدة
      $purchasesForInvoice = [];
      $now = now();
      foreach ($submittedItems as $prodIdStr => $qty) {
        $prodId = intval($prodIdStr);
        $qty = intval($qty);
        if ($qty <= 0)
          continue;

        // lock product row to avoid concurrent stock changes
        $product = Product::where('id', $prodId)->lockForUpdate()->first();
        if (!$product) {
          DB::rollBack();
          return redirect()->back()->with('error', "⚠️ المنتج (ID: {$prodId}) غير موجود.");
        }

        // prepare invoice item snapshot
        $purchasesForInvoice[] = [
          'product_id' => $product->id,
          'name' => $product->name,
          'qty' => $qty,
          'price' => $product->price,
          'cost' => $product->cost,
        ];

        // deduct from parent session purchase (lock that row too)
        $parentPurchaseRow = $parentSession->purchases()->where('product_id', $prodId)->lockForUpdate()->first();
        if ($parentPurchaseRow) {
          $parentPurchaseRow->quantity = max(0, $parentPurchaseRow->quantity - $qty);
          if ($parentPurchaseRow->quantity == 0) {
            $parentPurchaseRow->delete();
          } else {
            $parentPurchaseRow->save();
          }
        }

        // أنشئ سجل session_purchases للجلسة الجديدة (سجل تاريخي)
        DB::table('session_purchases')->insert([
          'sation_id' => $newSession->id,
          'product_id' => $product->id,
          'quantity' => $qty,
          'created_at' => $now,
          'updated_at' => $now,
        ]);
      }

      // 6) تقليل الأشخاص في الجلسة الأصلية
      $parentSession->persons = max(0, $parentSession->persons - $request->split_persons);
      $parentSession->save();

      // استخرج سعر الساعة الحالي
      $hourly_rate = DB::table('venue_pricing')
        ->where('is_active', true)
        ->orderByDesc('id')
        ->value('base_hour_price');

      // ===== هنا التعديل: حول الساعات المُدخلة إلى ساعات قابلة للفوترة =====
      $originalHours = floatval($request->hours);
      $splitPersons = intval($request->split_persons);
      $billableHours = $originalHours * max(1, $splitPersons);
      // 7) جهز الـ request للـ checkout (نمرّر billableHours حتى لا نلمس checkout)
      $checkoutRequest = new Request([
        'hours' => $billableHours,
        'hourly_rate' => $hourly_rate,
        'purchases' => json_encode($purchasesForInvoice),
        'payment_type' => $request->input('payment_type'), // 👈 ده المهم
      ]);
      // ===================================================================

      // 8) سجل system_action للـ session الجديدة (START_SESSION)
      SystemAction::create([
        'user_id' => Auth::id(),
        'action' => SystemActionType::START_SESSION->value,
        'actionable_type' => Sation::class,
        'actionable_id' => $newSession->id,
        'note' => "إنشاء جلسة منفصله (new session) من الجلسة رقم {$parentSession->id}",
        'meta' => json_encode([
          'from_session_id' => $parentSession->id,
          'split_persons' => $request->split_persons,
        ]),
        'shift_id' => $openShift?->id,
        'ip' => request()->ip(),
        'source' => 'web',
      ]);

      // 9) سجل system_action لفعل "تقسيم الجلسة" (SPLIT_SESSION) مربوط بالجلسة الأصلية
      SystemAction::create([
        'user_id' => Auth::id(),
        'action' => SystemActionType::SPLIT_SESSION->value,
        'actionable_type' => Sation::class,
        'actionable_id' => $parentSession->id,
        'note' => "فصل حساب منفصل (split) - تم إنشاء جلسة جديدة رقم {$newSession->id} من الجلسة {$parentSession->id}",
        'meta' => json_encode([
          'new_session_id' => $newSession->id,
          'split_persons' => $splitPersons,
          'original_hours' => $originalHours,
          'billable_hours' => $billableHours,
          'purchases' => $purchasesForInvoice,
        ]),
        'shift_id' => $openShift?->id,
        'ip' => request()->ip(),
        'source' => 'web',
      ]);

      // استدعاء checkout على الجلسة الجديدة (سيستخدم hours التي مررناها = billableHours)
      $response = app()->call([$this, 'checkout'], ['request' => $checkoutRequest, 'session' => $newSession]);

      DB::commit();

      return redirect()->route('session.show', $parentSession->id)
        ->with('success', '✅ تم إنشاء حساب منفصل وإنهاءه بنجاح.');
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()->with('error', '❌ حدث خطأ أثناء تقسيم الجلسة: ' . $e->getMessage());
    }
  }
  public function checkout(Request $request, Sation $session, ShiftService $shiftService)
  {
    // ✅ تحقق أولًا هل الجلسة ما زالت active
    if ($session->status !== 'active') {
      return redirect()->back()
        ->with('error', '⚠️ هذه الجلسة تم إنهاؤها مسبقًا.');
    }

    // ✅ تحقق من وجود شيفت مفتوح
    $user = Auth::user();
    $openShift = Shift::where('user_id', $user->id)->whereNull('end_time')->first();
    $isAdmin = $user->hasRole('admin');
    if (!$openShift && !$isAdmin) {
      session()->flash('shift_required', true);

      return redirect()->back()
        ->with('error', '⚠️ لا يوجد شيفت مفتوح، ابدأ شيفت أولاً.');
    }

    $request->validate([
      'hours' => 'required|numeric|min:0',
      'hourly_rate' => 'required|numeric|min:0',
      'purchases' => 'nullable|string',
      'payment_type' => 'nullable|string',
    ]);

    $hours = (float) $request->input('hours', 0);
    $hourlyRate = (float) $request->input('hourly_rate', 0);
    $purchases = json_decode($request->input('purchases', '[]'), true);
    $payment_type = $request->input('payment_type') ?: 'cash';
    $isPayed = (bool) $request->input('isPayed');

    // إذا لم تُرسل المشتريات من الواجهة، نجلبها مباشرة من مشتريات الجلسة المسجلة بقاعدة البيانات
    if (empty($purchases) && $session->purchases()->exists()) {
      $purchases = $session->purchases()->with('product')->get()->map(function ($p) {
        return [
          'product_id' => $p->product_id,
          'name' => $p->product->name ?? "منتج #{$p->product_id}",
          'qty' => $p->quantity,
          'price' => (float) ($p->product->price ?? 0),
          'cost' => (float) ($p->product->cost ?? 0),
        ];
      })->toArray();
    }

    // تحقق إضافي: إذا الساعات صفر والمشتريات فارغة → منع العملية
    if ($hours == 0 && empty($purchases)) {
      return redirect()->back()
        ->with('error', 'لا يمكن إنهاء الجلسة: لم يتم قضاء أي ساعة ولم توجد مشتريات.');
    }
    if ($hours != 0 && $hourlyRate == 0 && empty($purchases)) {
      return redirect()->back()
        ->with('error', 'سعر الساعة للنظام صفر');
    }

    $invoice = DB::transaction(function () use ($session, $hours, $hourlyRate, $purchases, $openShift, $isPayed, $isAdmin, $shiftService, $payment_type) {

      // 2. إضافة ساعات الجلسة ان لم يكن دافع مسبقا
      if (!$isPayed) {
        // 1. إنشاء الفاتورة
        $invoice = Invoice::create([
          'invoice_number' => InvoiceNumber::next(),
          'client_id' => $session->client_id,
          'type' => 'session',
          'total' => 0,
        ]);

        $total = 0;
        $sessionItemTotal = $hours * $hourlyRate;
        $total += $sessionItemTotal;

        if ($hours > 0) {
          InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_type' => 'session',
            'session_id' => $session->id,
            'name' => "جلسة رقم {$session->id}",
            'qty' => $hours,
            'price' => $hourlyRate,
            'cost' => 0,
            'total' => $sessionItemTotal,
            'description' => null,
          ]);
        }

        // 3. إضافة المشتريات للفاتورة
        foreach ($purchases as $item) {
          $qty = (int) ($item['qty'] ?? 1);
          $price = (float) ($item['price'] ?? 0);
          $itemTotal = $price * $qty;
          $total += $itemTotal;

          InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_type' => 'product',
            'product_id' => $item['product_id'] ?? null,
            'name' => $item['name'] ?? 'منتج',
            'qty' => $qty,
            'price' => $price,
            'cost' => $item['cost'] ?? 0,
            'total' => $itemTotal,
            'description' => null,
          ]);
        }

        // 4. تحديث الفاتورة بالمجموع النهائي
        $invoice->update(['total' => $total]);
      }

      if ($isPayed) {
        // 1. جلب الفاتورة الأصلية التي تحتوي على جلسة مسبقة الدفع، وإن لم توجد أنشئ واحدة
        $invoice = Invoice::where('client_id', $session->client_id)
          ->where('type', 'session')
          ->latest()
          ->first();

        if (!$invoice) {
          $invoice = Invoice::create([
            'invoice_number' => InvoiceNumber::next(),
            'client_id' => $session->client_id,
            'type' => 'session',
            'total' => 0,
          ]);
        }

        $total = 0;

        // 2. إضافة المشتريات
        foreach ($purchases as $item) {
          $qty = (int) ($item['qty'] ?? 1);
          $price = (float) ($item['price'] ?? 0);
          $itemTotal = $price * $qty;
          $total += $itemTotal;

          InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_type' => 'product',
            'product_id' => $item['product_id'] ?? null,
            'name' => $item['name'] ?? 'منتج',
            'qty' => $qty,
            'price' => $price,
            'cost' => $item['cost'] ?? 0,
            'total' => $itemTotal,
            'description' => null,
          ]);
        }

        // 3. تحديث مجموع الفاتورة بطريقة صحيحة
        $invoice->update(['total' => $invoice->total + $total]);

        if ($invoice && $openShift && $total > 0) {
          try {
            $shiftService->logAction(
              'separate_sale',
              $invoice->id,
              $total,
              $payment_type,
              null,
              "إنهاء جلسة رقم {$session->id}"
            );
          } catch (\Throwable $e) {
            \Log::warning('Shift separate_sale log skipped: ' . $e->getMessage());
          }
        }
      }

      // 5. تحديث حالة الجلسة إلى مغلقة
      $session->update([
        'status' => 'closed',
        'end_time' => now(),
      ]);

      // 6. تسجيل زيارة العميل
      Visit::create([
        'client_id' => $session->client_id,
      ]);

      SystemAction::create([
        'user_id' => Auth::id(),
        'action' => SystemActionType::SESSION_CHECKOUT->value,
        'actionable_type' => Sation::class,
        'actionable_id' => $session->id,
        'invoice_id' => $invoice->id,
        'shift_id' => $openShift?->id,
        'amount' => $invoice->total,
        'note' => "إنهاء جلسة رقم : {$session->id} - فاتورة : {$invoice->invoice_number} : للعميل : {$session->client->name}",
        'meta' => json_encode([
          'hours' => $hours,
          'hourly_rate' => $hourlyRate,
          'purchases' => $purchases,
          'old_status' => 'active',
          'new_status' => 'closed',
        ]),
        'ip' => request()->ip(),
        'source' => 'web',
      ]);

      return $invoice;
    });

    if (!$isPayed) {
      if ($invoice && $openShift) {
        try {
          $shiftService->logAction(
            'end_session',
            $invoice->id,
            $invoice->total,
            $payment_type,
            null,
            "إنهاء جلسة رقم {$session->id}"
          );
        } catch (\Throwable $e) {
          \Log::warning('Shift end_session log skipped: ' . $e->getMessage());
        }
      }

      $clientName = $session->client->name ?? '';

      return redirect()->route('session.index-manager')
        ->with('success', "✅ تم إنهاء الجلسة وإغلاق الفاتورة للعميل {$clientName} بنجاح.");
    }

    $clientName = $session->client->name ?? '';

    return redirect()->route('session.index-manager')
      ->with('success', "✅ تم إنهاء الجلسة وإغلاق الفاتورة للعميل {$clientName} بنجاح.");
  }

  private function canProduceProduct(Product $product, int $qty): bool
  {
    foreach ($product->recipe as $recipeItem) {
      $ingredient = $recipeItem->ingredient;

      if (!$ingredient) return false;

      $requiredAmount = $recipeItem->amount * $qty;

      if ($ingredient->stock < $requiredAmount) {
        return false;
      }
    }

    return true;
  }
  public function show(Sation $session)
  {
    try {
      // تأكد من وجود وقت بداية
      if (empty($session->start_time)) {
        return redirect()->route('admin-error.create')
          ->with('message', 'غير ممكن حساب مدة الجلسة: تاريخ البداية غير موجود.');
      }

      $start = Carbon::parse($session->start_time);
      $end = $session->end_time ? Carbon::parse($session->end_time) : Carbon::now();

      // الفرق بالدقائق كـ integer
      $minutes = (int) $start->diffInMinutes($end);

      // حساب عدد الساعات (منطقك: أقل من أو يساوي 15 دقيقة => 0 ساعة، وإلا نقسم ونقرب حسب >15 دقيقة)
      if ($minutes <= 15) {
        $hours = 0;
      } else {
        $fullHours = intdiv($minutes, 60);     // عدد الساعات الكاملة
        $remainingMinutes = $minutes % 60;

        if ($remainingMinutes > 15) {
          $fullHours += 1;
        }

        $hours = max(0, (int) $fullHours);
      }
      $categories = Category::with('products')->get();
      // جلب آخر FullDayHour مفعل (أحدث واحد)
      $fullDayHoursModel = FullDayHour::where('is_active', 1)
        ->orderByDesc('created_at')
        ->first();

      if (!$fullDayHoursModel) {
        // رجع رسالة واضحة لو مفيش record مفعل
        return redirect()->route('admin-error.create')
          ->with('message', 'عدد ساعات اليوم الكامل لم تضاف');
      }

      // نحول القيمة لعدد (int) — هنا نتجنّب وضع الموديل داخل $hours
      $fullDayHoursCount = (int) $fullDayHoursModel->hours_count;
      if ($fullDayHoursCount < 0)
        $fullDayHoursCount = 0; // حماية

      // لو وصل أو تجاوز عدد الساعات قيمة Full Day -> نعتبره full day
      $isFullDay = ($hours >= $fullDayHoursCount);
      if ($isFullDay) {
        $hours = $fullDayHoursCount;
      }

      // جلب سعر الساعة بأمان (float)
      $hourly_rate = (float) (DB::table('venue_pricing')
        ->where('is_active', true)
        ->orderByDesc('id')
        ->value('base_hour_price') ?? 0);

      // تأكد من عدد الأشخاص (fallback إلى 1 لو مفيش)
      $persons = max(1, (int) ($session->persons ?? 1));

      // حساب سعر الساعات
      $hours_price = round($hours * $persons * $hourly_rate, 2);
      // eager load للعلاقات المهمة (لو مش محملة)
      $session->loadMissing('purchases.product');

      // حساب سعر المنتجات المرتبطة (حماية من عدم وجود المنتج أو السعر)
      $products_price = $session->purchases->sum(function ($purchase) {
        $price = (float) optional($purchase->product)->price ?? 0.0;
        $qty = (int) ($purchase->quantity ?? 0);
        return $price * $qty;
      });

      $total = $hours_price + $products_price;
      $finalTotal = $session->isPayed
        ? ($total - $hours_price)
        : $total;
      $hourlyRate = $hourly_rate;
      $fullDayHours = FullDayHour::where('is_active', 1)->orderByDesc('created_at')->first()->hours_count;
      return view('session.show', compact(
        'session',
        'categories',
        'hours',
        'hours_price',
        'products_price',
        'finalTotal',
        'isFullDay',
        'hourly_rate',
        'hourlyRate',
        'fullDayHours'
      ) + [
        'purchases' => $session->purchases,
        'importantProducts' => ImportantProduct::all(),
      ]);
    } catch (\Throwable $e) {
      \Log::error('Error in session.show: ' . $e->getMessage(), [
        'session_id' => $session->id ?? null,
        'trace' => $e->getTraceAsString()
      ]);
      return redirect()->route('admin-error.create')->with('message', 'حدث خطأ أثناء حساب تكلفة الجلسة، يرجى المحاولة أو مراجعة السجلات.');
    }
  }

  public function indexManager(Request $request)
  {
    $query = $request->get('search');

    $sessions = Sation::with('client')
      ->when($query, function ($q) use ($query) {
        $q->whereHas('client', function ($sub) use ($query) {
          $sub->where('phone', 'like', "%{$query}%")
            ->orWhere('name', 'like', "%{$query}%");
        });
      })
      ->where('status', 'active') // فلتر للجلسات النشطة فقط
      ->latest()
      ->get();
    $halls = Hall::all();
    $sessions_count = Sation::where('status', 'active')->sum('persons');

    // عدد الحجوزات الجارية
    $private_sessions_count = Booking::where('status', 'in_progress')->count();
    $hourlyRate = VenuePricing::where('is_active', 1)->first()?->base_hour_price ?? 10;
    $fullDay = FullDayHour::where('is_active', 1)->first()?->hours_count ?? 8;
    $fullDayPrice = $hourlyRate * $fullDay;

    return view('session.index-manager', compact('sessions', 'query', "halls", "private_sessions_count", "sessions_count", "fullDayPrice"));
  }
  // public function create()
  // {
  //   return view("session.create");
  // }
  public function store(Request $request)
  {

    // input validated phone
    $request->validate([
      'name' => [
        'required',
        'string',
        'regex:/^[\pL\s]+$/u', // يقبل الحروف (عربي/انجليزي) والمسافات فقط
        'min:3',
        'max:50',
      ],
      'phone' => [
        'required',
        'regex:/^(010|011|012|015)[0-9]{8}$/'
      ],
      "persons" => [
        "required",
      ],
      "isPayed" => []
    ], [
      // رسائل الاسم
      'name.required' => 'من فضلك أدخل اسم العميل',
      'name.string' => 'الاسم يجب أن يكون نصًا',
      'name.regex' => 'الاسم يجب أن يحتوي على حروف فقط',
      'name.min' => 'الاسم يجب أن لا يقل عن 3 أحرف',
      'name.max' => 'الاسم يجب أن لا يزيد عن 50 حرفًا',

      // رسائل الهاتف
      'phone.required' => 'من فضلك أدخل رقم الهاتف',
      'phone.regex' => ' ادخل رقم مصري صحيح (11 رقم ويبدأ بـ 010 أو 011 أو 012 أو 015)',

      //persons
      'persons.required' => '',
    ]);

    $phone = $request->input('phone');
    $name = $request->input('name');
    $persons = $request->persons;
    // is client with this phone exists 
    $exists = Client::where('phone', $phone)->exists();
    // if exists, start session with this client_id
    if ($exists) {
      if (
        Sation::where('client_id', Client::where('phone', $phone)->first()->id)
        ->where('status', 'active')
        ->exists()
      ) {
        return back()->with('error', 'هذا العميل (' . (Client::where('phone', $phone)->first()->name) . ') لديه جلسة نشطة بالفعل');
      } else {
        try {
          $client = Client::where("phone", $phone)->first();

          $session = Sation::create([
            'client_id' => $client->id,
            'start_time' => now(),
            'persons' => $persons,
            'status' => 'active',
          ]);
          SystemAction::create([
            'user_id' => Auth::id(), // المستخدم الحالي (admin/employee)
            'action' => SystemActionType::START_SESSION->value,
            'actionable_type' => Sation::class,   // polymorphic relation
            'actionable_id' => $session->id,
            'note' => "بدء جلسة جديدة للعميل: {$client->name} - هاتف: {$client->phone}",
            'meta' => json_encode([
              'persons' => $persons,
              'client_id' => $client->id,
              'client_phone' => $client->phone,
            ]),
            'ip' => request()->ip(),
            'source' => 'web',
          ]);
        } catch (\Exception $e) {
          return to_route("error.create");
        }
        return redirect()->route('session.index-manager')->with('success', 'تم إضافة الجلسة بنجاح');

        //=============================================== go to session index
      }
    }
    // if not exists, create new client and start session
    else {
      try {
        // 1. إنشاء العميل
        $client = Client::create([
          'phone' => $phone,
          'name' => $name,
        ]);

        // Log → إضافة عميل جديد
        SystemAction::create([
          'user_id' => Auth::id(),
          'action' => SystemActionType::ADD_NEW_CLIENT->value,
          'actionable_type' => Client::class,
          'actionable_id' => $client->id,
          'note' => "تم إضافة عميل جديد: {$client->name} - هاتف: {$client->phone}",
          'meta' => json_encode([
            'client_id' => $client->id,
            'phone' => $client->phone,
            'name' => $client->name,
          ]),
          'ip' => request()->ip(),
          'source' => 'web',
        ]);

        // 2. إنشاء جلسة
        $session = Sation::create([
          'client_id' => $client->id,
          'persons' => $persons,
          'start_time' => now(),
          'status' => 'active',
        ]);

        // Log → بدء جلسة جديدة
        SystemAction::create([
          'user_id' => Auth::id(),
          'action' => SystemActionType::START_SESSION->value,
          'actionable_type' => Sation::class,
          'actionable_id' => $session->id,
          'note' => "بدء جلسة جديدة للعميل: {$client->name}",
          'meta' => json_encode([
            'persons' => $persons,
            'client_id' => $client->id,
          ]),
          'ip' => request()->ip(),
          'source' => 'web',
        ]);
      } catch (\Exception $e) {
        return to_route("error.create");
      }
      return redirect()->route('session.index-manager')->with('success', 'تم إضافة الجلسة بنجاح');

      //=============================================== go to session index

    }
    //check if the client_phone is provided
    //if provided s
    //if not, create a new client with request values
    //fetch the client_id by phone
    //save client_id in the in variable 
    //start session by this client_id
    //go route to session.index-manager with success message



    //   $client = Sation::create([
    //   'client_id' => $request->client_id,
    //   'hours' => 0,
    //   'rate_per_hour' => 0,
    // ]);
  }

  public function storeFromManager(Request $request)
  {
    // input validated phone
    $request->validate(
      [
        'name' => [
          'required',
          'string',
          'regex:/^[\pL\s]+$/u',
          'min:3',
          'max:50',
        ],
        'phone' => [
          'required',
          'regex:/^(010|011|012|015)[0-9]{8}$/'
        ],
        'persons' => [
          'required',
        ],

      ],
      [
        'name.required' => 'من فضلك أدخل اسم العميل',
        'name.string' => 'الاسم يجب أن يكون نصًا',
        'name.regex' => 'الاسم يجب أن يحتوي على حروف فقط',
        'name.min' => 'الاسم يجب أن لا يقل عن 3 أحرف',
        'name.max' => 'الاسم يجب أن لا يزيد عن 50 حرفًا',

        'phone.required' => 'من فضلك أدخل رقم الهاتف',
        'phone.regex' => 'ادخل رقم مصري صحيح (11 رقم ويبدأ بـ 010 أو 011 أو 012 أو 015)',

        'persons.required' => 'من فضلك أدخل عدد الأشخاص',

      ]
    );


    $phone = $request->input('phone');
    $name = $request->input('name');
    $persons = $request->input('persons');
    $isPayed = $request->input('isPayed') ?? 0;
    $totalPayed = $request->input('totalPayed');
    $payment_type = $request->input('payment_type');
    $fullDayHours = FullDayHour::where('is_active', 1)->value('hours_count');
    $hourlyRate = VenuePricing::where('is_active', 1)->value('base_hour_price');
    // helper to check if request expects JSON (AJAX)
    $isJson = $request->wantsJson() || $request->ajax();
    try {
      // حاول الحصول على العميل مرة واحدة
      $client = Client::where('phone', $phone)->first();

      if ($client) {

        // تحقق لو لدى العميل جلسة نشطة
        $hasActive = Sation::where('client_id', $client->id)
          ->where('status', 'active')
          ->exists();

        if ($hasActive) {

          $msg = 'هذا العميل (' . $client->name . ') لديه جلسة نشطة بالفعل';

          if ($isJson) {
            return response()->json([
              'success' => false,
              'error' => $msg,
            ], 409); // Conflict
          }

          return back()->with('error', $msg);
        }

        $forceStart = $request->boolean('session_for_subscriber');
        // لو مفيش force start نتحقق من الاشتراك
        if (!$forceStart) {

          $activeSub = Subscription::where("client_id", $client->id)
            ->where("is_active", true)
            ->first();

          if ($activeSub) {
            if ($isJson) {
              return response()->json([
                'success' => false,
                'has_active_subscription' => true,
                'message' => 'العميل لديه اشتراك نشط بالفعل'
              ], 409);
            }

            return back()
              ->with('client_has_active_sub', 'العميل لديه اشتراك نشط بالفعل.')->with('active_subscription_id', $activeSub->id)
              ->with('return_phone', $phone)
              ->with('return_name', $name)
              ->with('return_persons', $persons);
          }
        }

        // لا توجد جلسة نشطة -> إنشاء جلسة جديدة للعميل الموجود
        $session = Sation::create([
          'client_id' => $client->id,
          'start_time' => now(),
          'persons' => $persons,
          'isPayed' => $isPayed ?? '0',
          'status' => 'active',
        ]);

        // سجل العملية
        SystemAction::create([
          'user_id' => Auth::id(),
          'action' => SystemActionType::START_SESSION->value,
          'actionable_type' => Sation::class,
          'actionable_id' => $session->id,
          'note' => "بدء جلسة جديدة للعميل: {$client->name} - هاتف: {$client->phone}",
          'meta' => json_encode([
            'persons' => $persons,
            'client_id' => $client->id,
            'client_phone' => $client->phone,
          ]),
          'ip' => request()->ip(),
          'source' => 'web',
        ]);
        if ($isPayed && $payment_type != null) {
          $user = Auth::user();

          $shift = Shift::where('user_id', $user->id)->whereNull('end_time')->first();
          if (!$shift) {
            session()->flash('shift_required', true);

            throw new \Exception("⚠️ لازم تفتح شيفت الأول قبل تسجيل أي عملية");
          }
          // 1. إنشاء الفاتورة
          $invoice = Invoice::create([
            'invoice_number' => InvoiceNumber::next(),
            'client_id' => $session->client_id,
            'type' => 'session',
            'total' => $totalPayed,
          ]);
          $allHours = $fullDayHours * $persons;
          InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_type' => 'session',
            'session_id' => $session->id,
            'name' => "جلسة رقم {$session->id}",
            'qty' => $allHours,
            'price' => $hourlyRate,
            'cost' => 0,
            'total' => $totalPayed,
            'description' => null,
          ]);

          $shiftAction = ShiftAction::create([
            'shift_id' => $shift->id,
            'action_type' => 'session_prepaid',
            'invoice_id' => null,
            'amount' => $totalPayed,
            'payment_type' => $payment_type,
            'expense_amount' => null,
            'notes' => "دفع يوم كامل قبل الدخول",
          ]);

          if ($isJson) {
            return response()->json([
              'success' => true,
              'message' => 'تم بدء الجلسة بنجاح',
              'session' => $session,
            ], 201);
          } else {
            return redirect()->route('session.index-manager')
              ->with('success', "✅ تم تسجيل الجلسة للعميل دافع الساعات مسبقا ");
          }
        }
      }

      if (!$client) {

        // العميل غير موجود -> إنشاؤه ثم إنشاء الجلسة
        $client = Client::create([
          'phone' => $phone,
          'name' => $name,
        ]);
        // Log → إضافة عميل جديد
        SystemAction::create([
          'user_id' => Auth::id(),
          'action' => SystemActionType::ADD_NEW_CLIENT->value,
          'actionable_type' => Client::class,
          'actionable_id' => $client->id,
          'note' => "تم إضافة عميل جديد: {$client->name} - هاتف: {$client->phone}",
          'meta' => json_encode([
            'client_id' => $client->id,
            'phone' => $client->phone,
            'name' => $client->name,
          ]),
          'ip' => request()->ip(),
          'source' => 'web',
        ]);

        // 2. إنشاء جلسة
        $session = Sation::create([
          'client_id' => $client->id,
          'persons' => $persons,
          'start_time' => now(),
          'isPayed' => $isPayed ?? 0,
          'status' => 'active',
        ]);

        // Log → بدء جلسة جديدة
        SystemAction::create([
          'user_id' => Auth::id(),
          'action' => SystemActionType::START_SESSION->value,
          'actionable_type' => Sation::class,
          'actionable_id' => $session->id,
          'note' => "بدء جلسة جديدة للعميل: {$client->name}",
          'meta' => json_encode([
            'persons' => $persons,
            'client_id' => $client->id,
          ]),
          'ip' => request()->ip(),
          'source' => 'web',
        ]);


        if ($isPayed && $payment_type != null) {
          $user = Auth::user();

          $shift = Shift::where('user_id', $user->id)->whereNull('end_time')->first();
          if (!$shift) {
            session()->flash('shift_required', true);

            throw new \Exception("⚠️ لازم تفتح شيفت الأول قبل تسجيل أي عملية");
          }
          // 1. إنشاء الفاتورة
          $invoice = Invoice::create([
            'invoice_number' => InvoiceNumber::next(),
            'client_id' => $session->client_id,
            'type' => 'session',
            'total' => $totalPayed,
          ]);
          $allHours = $fullDayHours * $persons;
          InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_type' => 'session',
            'session_id' => $session->id,
            'name' => "جلسة رقم {$session->id}",
            'qty' => $allHours,
            'price' => $hourlyRate,
            'cost' => 0,
            'total' => $totalPayed,
            'description' => null,
          ]);
          ShiftAction::create([
            'shift_id' => $shift->id,
            'action_type' => 'session_prepaid',
            'invoice_id' => null,
            'amount' => $totalPayed,
            'payment_type' => $payment_type,
            'expense_amount' => null,
            'notes' => "دفع يوم كامل قبل الدخول",
          ]);


          if ($isJson) {
            return response()->json([
              'success' => true,
              'message' => 'تم بدء الجلسة بنجاح',
              'session' => $session,
            ], 201);
          } else {
            return redirect()->route('session.index-manager')
              ->with('success', "✅ تم تسجيل الجلسة للعميل");
          }
        }
      }
      return redirect()->route('session.index-manager')->with('success', 'تم إضافة الجلسة بنجاح');
    } catch (\Exception $e) {
      // لو حدث استثناء، نرجع JSON مع كود 500 لو AJAX، وإلا نذهب لصفحة الخطأ كما سابقًا
      \Log::error('Session store error: ' . $e->getMessage(), ['exception' => $e]);

      if ($isJson) {
        return response()->json([
          'success' => false,
          'error' => 'حدث خطأ أثناء معالجة الطلب، الرجاء المحاولة لاحقًا'
        ], 500);
      }

      return to_route("error.create");
    }
  }







  public function deleteEmpty(Sation $session, Request $request)
  {
    $user = auth()->user();

    // تحقق أولًا إذا الجلسة فارغة
    if ($session->hours > 0 || $session->purchases()->count() > 0) {
      return redirect()->back()->with('error', '⚠️ الجلسة ليست فارغة، لا يمكن حذفها.');
    }

    DB::beginTransaction();
    try {
      // جهّز بيانات الـ meta (مثلاً القيم القديمة قبل الحذف)
      $meta = [
        'old_values' => [
          'id' => $session->id,
          'hours' => $session->hours,
          'purchases_count' => $session->purchases()->count(),
          'created_at' => optional($session->created_at)?->toDateTimeString(),
          'extra' => $session->toArray(), // لو حابب تحفظ نسخة كاملة
        ],
        'user_agent' => $request->header('User-Agent'),
      ];

      // ابحث عن شيفت مفتوح للمستخدم (لو عندكم لوجيك مختلف غيّره)
      $openShift = Shift::where('user_id', $user->id)->whereNull('end_time')->first();

      // تسجيل الـ system action
      $action = SystemAction::create([
        'user_id' => $user->id,
        'action' => SystemActionType::DELETE_SESSION->value, // أو (string)SystemActionType::DELETE_SESSION
        'actionable_type' => Sation::class,
        'actionable_id' => $session->id,
        'invoice_id' => null,
        'shift_id' => $openShift?->id ?? null,
        'amount' => null,
        'note' => 'حذف جلسة فارغة للعميل : ' . $session->client->name,
        'meta' => json_encode($meta),
        'ip' => $request->ip(),
        'source' => 'web',
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
      ]);

      // الآن احذف الجلسة
      $session->delete();

      DB::commit();

      return redirect()->route('session.index-manager')
        ->with('success', '✅ تم حذف الجلسة الفارغة بنجاح.');
    } catch (\Throwable $e) {
      DB::rollBack();
      // لو حابب تحفظ خطأ في اللوج أو ترجع رسالة للمستخدم
      \Log::error('deleteEmpty error: ' . $e->getMessage(), [
        'user_id' => $user?->id,
        'session_id' => $session->id ?? null,
      ]);

      return redirect()->back()
        ->with('error', '⚠️ حدث خطأ أثناء محاولة حذف الجلسة. المرجو المحاولة لاحقًا.');
    }
  }


  public function search(Request $request)
  {
    $query = $request->get('query');

    $sessions = Sation::with('client')
      ->where('status', 'active') // ✅ يرجع بس الـ active
      ->when($query, function ($q) use ($query) {
        $q->where(function ($q2) use ($query) {
          if (is_numeric($query)) {
            // لو المستخدم دخل رقم → اعتبره client_id
            $q2->orWhere('client_id', $query);
          }

          // بحث بالاسم أو التليفون للعميل
          $q2->orWhereHas('client', function ($c) use ($query) {
            $c->where('name', 'LIKE', "%{$query}%")
              ->orWhere('phone', 'LIKE', "%{$query}%");
          });
        });
      })

      ->get();

    return response()->json($sessions);
  }
  public function updateStartTime(Request $request, Sation $session)
  {
    $request->validate([
      'start_time' => 'required|date',
    ]);

    // تحقق إن الجلسة مفتوحة — اعتبارات متعددة لحالة الإغلاق
    if (
      $session->end_time !== null
      || ($session->status ?? '') === 'finished'
      || ($session->is_finished ?? false)
    ) {
      return response()->json([
        'status' => 'error',
        'message' => '⚠️ هذه الجلسة تم إنهاؤها من قبل، لا يمكن التعديل عليها.',
      ], 400);
    }

    $oldStart = $session->start_time ? Carbon::parse($session->start_time)->toIso8601String() : null;
    $newStart = Carbon::parse($request->start_time)->toIso8601String();

    DB::beginTransaction();

    try {
      $session->update(['start_time' => $request->start_time]);

      $user = Auth::user();
      $openShift = Shift::where('user_id', $user->id)
        ->whereNull('end_time')
        ->first();

      SystemAction::create([
        'user_id' => $user->id,
        'action' => SystemActionType::EDIT_SESSION_TIME->value,
        'actionable_type' => Sation::class,
        'actionable_id' => $session->id,
        'note' => "تعديل موعد الجلسة رقم {$session->id}",
        'meta' => json_encode([
          'old_start_time' => $oldStart,
          'new_start_time' => $newStart,
        ]),
        'shift_id' => $openShift?->id,
        'ip' => $request->ip(),
        'source' => 'web',
      ]);

      DB::commit();

      return response()->json([
        'status' => 'success',
        'message' => '✅ تم تعديل موعد الجلسة بنجاح',
        'start_time' => $newStart,
      ]);
    } catch (\Throwable $e) {
      DB::rollBack();
      \Log::error('updateStartTime error: ' . $e->getMessage());

      return response()->json([
        'status' => 'error',
        'message' => '⚠️ حدث خطأ أثناء تعديل موعد الجلسة، حاول لاحقًا.',
      ], 500);
    }
  }

  public function adjustStartTime(Request $request, Sation $session)
  {
    $request->validate([
      'amount' => 'required|integer|min:1',
      'unit' => 'required|in:minutes,hours',
      'direction' => 'required|in:forward,backward',
    ]);

    // تحقق إن الجلسة مفتوحة — نفس الشروط كـ updateStartTime
    if ($session->end_time !== null || $session->status === 'closed') {
      return response()->json([
        'status' => 'error',
        'message' => '⚠️ هذه الجلسة تم إنهاؤها من قبل، لا يمكن التعديل عليها.',
      ], 400);
    }

    // تأكد أن هناك start_time صالح
    if (empty($session->start_time)) {
      return response()->json([
        'status' => 'error',
        'message' => '⚠️ لا يوجد وقت بداية مسجل لهذه الجلسة، لا يمكن التعديل.',
      ], 400);
    }

    $oldStart = Carbon::parse($session->start_time);
    $newStart = $oldStart->copy();

    // تعديل الوقت حسب الاتجاه
    if ($request->direction === 'forward') {
      $newStart->{$request->unit === 'minutes' ? 'addMinutes' : 'addHours'}($request->amount);
    } else {
      $newStart->{$request->unit === 'minutes' ? 'subMinutes' : 'subHours'}($request->amount);
    }

    DB::beginTransaction();
    try {
      $session->update([
        'start_time' => $newStart->toDateTimeString(),
      ]);

      $user = Auth::user();
      $openShift = Shift::where('user_id', $user->id)
        ->whereNull('end_time')
        ->first();

      SystemAction::create([
        'user_id' => $user->id,
        'action' => SystemActionType::EDIT_SESSION_TIME->value,
        'actionable_type' => Sation::class,
        'actionable_id' => $session->id,
        'note' => "تعديل موعد الجلسة رقم {$session->id} بـ {$request->amount} {$request->unit} ({$request->direction})",
        'meta' => json_encode([
          'old_start_time' => $oldStart->toIso8601String(),
          'new_start_time' => $newStart->toIso8601String(),
          'direction' => $request->direction,
        ]),
        'shift_id' => $openShift?->id,
        'ip' => request()->ip(),
        'source' => 'web',
      ]);

      DB::commit();

      return response()->json([
        'status' => 'success',
        'message' => '✅ تم تعديل الموعد بنجاح، سيتم تحديث الصفحة...',
        'start_time' => $newStart->toIso8601String(),
      ]);
    } catch (\Throwable $e) {
      DB::rollBack();
      \Log::error('adjustStartTime error: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => '❌ حدث خطأ أثناء تعديل الموعد، حاول مجددًا.',
      ], 500);
    }
  }


  public function justStart(Request $request)
  {
    $request->validate([
      'name' => [
        'required',
        'string',
        'regex:/^[\pL\s]+$/u',
        'min:3',
        'max:50',
      ],
      'phone' => [
        'required',
        'regex:/^(010|011|012|015)[0-9]{8}$/'
      ],
      "persons" => ["required"],
    ], [
      'name.required' => 'من فضلك أدخل اسم العميل',
      'name.string' => 'الاسم يجب أن يكون نصًا',
      'name.regex' => 'الاسم يجب أن يحتوي على حروف فقط',
      'name.min' => 'الاسم يجب أن لا يقل عن 3 أحرف',
      'name.max' => 'الاسم يجب أن لا يزيد عن 50 حرفًا',
      'phone.required' => 'من فضلك أدخل رقم الهاتف',
      'phone.regex' => 'ادخل رقم مصري صحيح (11 رقم ويبدأ بـ 010 أو 011 أو 012 أو 015)',
      'persons.required' => '',
    ]);

    $phone = $request->input('phone');
    $name = $request->input('name');
    $persons = $request->input('persons');

    // helper to check if request expects JSON (AJAX)
    $isJson = $request->wantsJson() || $request->ajax();

    try {
      // تحقق لو العميل موجود
      $client = Client::firstOrCreate(
        ['phone' => $phone],
        [
          'name' => $name,
          'age' => $request->input('age'),
          'specialization_id' => $request->input('specialization_id'),
          'education_stage_id' => $request->input('education_stage_id'),
        ]
      );

      // تحقق من الجلسة النشطة
      $hasActive = Sation::where('client_id', $client->id)
        ->where('status', 'active')
        ->exists();

      if ($hasActive) {
        $msg = "هذا العميل ({$client->name}) لديه جلسة نشطة بالفعل";

        if ($isJson) {
          return response()->json([
            'success' => false,
            'error' => $msg,
          ], 409);
        }

        return back()->with('error', $msg);
      }

      // إنشاء الجلسة الجديدة
      $session = Sation::create([
        'client_id' => $client->id,
        'persons' => $persons,
        'start_time' => now(),
        'status' => 'active',
      ]);

      // ✅ حذف الـ SetionNotAdded المرتبطة
      if ($request->filled('session_id')) {
        SetionNotAdded::where('id', $request->session_id)->delete();
      }

      // سجل العملية
      SystemAction::create([
        'user_id' => Auth::id(),
        'action' => SystemActionType::START_SESSION->value,
        'actionable_type' => Sation::class,
        'actionable_id' => $session->id,
        'note' => "بدء جلسة جديدة للعميل: {$client->name}",
        'meta' => json_encode([
          'persons' => $persons,
          'client_id' => $client->id,
          'client_phone' => $client->phone,
        ]),
        'ip' => $request->ip(),
        'source' => 'web',
      ]);

      // ✅ لو request جاي من AJAX نرجع JSON
      if ($isJson) {
        return response()->json([
          'success' => true,
          'message' => 'تم بدء الجلسة بنجاح',
          'session' => $session,
        ], 201);
      }

      // ✅ redirect عادي لو request عادي
      return redirect()
        ->route('session.index-manager')
        ->with('success', 'تم إضافة الجلسة بنجاح');
    } catch (\Exception $e) {
      \Log::error('Session store error: ' . $e->getMessage(), ['exception' => $e]);

      if ($isJson) {
        return response()->json([
          'success' => false,
          'error' => 'حدث خطأ أثناء معالجة الطلب، الرجاء المحاولة لاحقًا'
        ], 500);
      }

      return to_route("error.create");
    }
  }

  public function fullDayActivation(Request $request)
  {
    try {

      $request->validate([
        'session_id' => 'required|exists:sations,id',
        'payment_type' => 'required|in:cash,digital',
        'total' => 'required|numeric|min:0',
      ]);

      $session = Sation::findOrFail($request->session_id);

      $payment_type = $request->payment_type;
      $totalPayed = $request->total;

      $user = Auth::user();

      $shift = Shift::where('user_id', $user->id)
        ->whereNull('end_time')
        ->first();

      if (!$shift) {
        return response()->json([
          'success' => false,
          'message' => '⚠️ لازم تفتح شيفت الأول قبل تسجيل أي عملية',
        ], 422);
      }

      $persons = $session->persons ?? 1;
      $hourlyRate = $request->hourly_rate ?? 0;
      $fullDayHours = $request->full_day_hours ?? 12;

      $allHours = $fullDayHours * $persons;

      $invoice = Invoice::create([
        'invoice_number' => InvoiceNumber::next(),
        'client_id' => $session->client_id,
        'type' => 'session',
        'total' => $totalPayed,
      ]);

      InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'item_type' => 'session',
        'session_id' => $session->id,
        'name' => "Full Day Session #{$session->id}",
        'qty' => $allHours,
        'price' => $hourlyRate,
        'cost' => 0,
        'total' => $totalPayed,
        'description' => 'Full Day Activation',
      ]);

      $shiftAction = ShiftAction::create([
        'shift_id' => $shift->id,
        'action_type' => 'session_prepaid',
        'invoice_id' => $invoice->id,
        'amount' => $totalPayed,
        'payment_type' => $payment_type,
        'expense_amount' => null,
        'notes' => "Full Day Activation - Session {$session->id}",
      ]);

      $session->update([
        'isPayed' => 1,
      ]);

      return response()->json([
        'success' => true,
        'message' => 'تم تفعيل اليوم الكامل بنجاح',
        'invoice_id' => $invoice->id,
        'shift_action_id' => $shiftAction->id,
      ]);
    } catch (\Exception $e) {

      return response()->json([
        'success' => false,
        'message' => 'حصل خطأ في العملية',
      ], 500);
    }
  }
}
