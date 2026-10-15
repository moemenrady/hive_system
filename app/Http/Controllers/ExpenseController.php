<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseDraft;
use App\Models\ExpenseType;
use App\Models\Shift;
use App\Models\ShiftAction;
use App\Services\AnalyticsService;
use Auth;
use Exception;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{

  protected $analytics;
  public function __construct(AnalyticsService $analytics)
  {
    $this->analytics = $analytics;
  }
  public function ajaxSearch(Request $request)
  {
    $query = Expense::query()->with(['type', 'admin']);

    // فلتر نوع المصروف
    if ($request->has('expense_type_id') && $request->expense_type_id != '') {
      $query->where('expense_type_id', $request->expense_type_id);
    }

    // فلتر التاريخ - من
    if ($request->has('from') && $request->from != '') {
      $query->whereDate('created_at', '>=', $request->from);
    }

    // فلتر التاريخ - إلى
    if ($request->has('to') && $request->to != '') {
      $query->whereDate('created_at', '<=', $request->to);
    }

    // جلب البيانات
    $expenses = $query->orderBy('created_at', 'DESC')->get();

    // رجّع JSON جاهز للشغل
    return response()->json(
      $expenses->map(function ($exp) {
        return [
          'id' => $exp->id,
          'amount' => number_format($exp->amount, 2),
          'note' => $exp->note,
          'expense_type_name' => $exp->type?->name,
          'added_by' => $exp->admin?->name,
          'created_at' => $exp->created_at,
        ];
      })
    );
  }

  // صفحة إضافة مصروف
  public function create()
  {
    $types = ExpenseType::all(); // كل أنواع المصاريف
    return view('expense.admin.create', compact('types'));
  }


  public function store(Request $request)
  {
    $request->validate([
      'expense_type_id' => 'required|exists:expense_types,id',
      'amount' => 'required|numeric|min:0',
      'note' => 'nullable|string|max:500',
      'expense_time' => 'nullable|date'
    ]);

    try {
      $user = Auth::user();

      $openShift = Shift::where('user_id', $user->id)->whereNull('end_time')->first();

      $expenseTime = $request->expense_time
        ? Carbon::createFromFormat('Y-m-d\TH:i', $request->expense_time)
        : now();

      $expense = Expense::create([
        'expense_type_id' => $request->expense_type_id,
        'amount' => $request->amount,
        'note' => $request->note,
        'added_by' => auth()->id(),
        'created_at' => $expenseTime,
      ]);

      if ($openShift &&
    (
        !$request->expense_time ||
        Carbon::parse($request->expense_time)->isToday()
    )) {
        $expenseAmount = $expense->amount ?? $request->input('amount') ?? 0;
        ShiftAction::create([
          'shift_id' => $openShift->id,
          'action_type' => 'expense_note',
           'expense_type_id' => $request->expense_type_id, // 👈 الجديد
          'invoice_id' => null, // ضمان عدم ربط فاتورة
          'amount' => 0, // هذه عملية مصروف لذا الإيراد = 0
          'expense_amount' => $expenseAmount ?: null,
          'notes' => $request->note,
        ]);

        // 4) تحديث إجمالي المصروف في الشيفت
        if ($expenseAmount && $expenseAmount > 0) {
          $openShift->total_expense = $openShift->total_expense + $expenseAmount;
          $openShift->save();
        }
      }

      return redirect()->route("main.create")->with('success', 'تم إضافة المصروف بنجاح ✅');
    } catch (Exception $e) {
      return redirect()->back()->with('error', 'حدث خطأ أثناء إضافة المصروف ❌');
    }
  }


  public function index(Request $request)
  {
    $expenseTypes = ExpenseType::all();
    return view('expense.index', compact('expenseTypes'));
  }

 public function convertFromDraft(Request $request, $id = null)
{
    // 1. تحديد العناصر المطلوب تحويلها (سواء كانت من URL أو من مصفوفة IDs في Request)
    $ids = $id ? [$id] : ($request->ids ? (is_array($request->ids) ? $request->ids : explode(',', $request->ids)) : []);

    if (empty($ids)) {
        return redirect()->back()->with('error', 'يرجى تحديد مصروف واحد على الأقل ❌');
    }

    // 2. التحقق من البيانات (النوع والمبلغ مطلوبين فقط في الحالة الفردية)
    // في الحالة الجماعية سنستخدم البيانات الافتراضية لكل درافت
    if ($id) {
        $request->validate([
            'expense_type_id' => 'required|exists:expense_types,id',
            'amount' => 'required|numeric|min:0',
        ]);
    }

    try {
        DB::beginTransaction();

        $drafts = ExpenseDraft::whereIn('id', $ids)->get();

        foreach ($drafts as $draft) {
            $expense = new Expense();
            
            // إذا كان تحويل فردي نأخذ القيمة من Input، وإذا جماعي نأخذ النوع المختار في الدرافت (أو قيمة افتراضية)
            $expense->expense_type_id = $id ? $request->expense_type_id : ($draft->expense_type_id ?? $request->expense_type_id);
            $expense->amount = $id ? $request->amount : $draft->estimated_amount;
            
            $expense->note = $draft->note;
            $expense->added_by = auth()->id();
            $expense->created_at = $draft->created_at;
            $expense->updated_at = $draft->created_at;
            $expense->timestamps = false; // لمنع لارافل من تحديث الوقت للوقت الحالي
            $expense->save();

            // حذف الدرافت بعد التحويل
            $draft->delete();
        }

        DB::commit();
        
        $message = count($ids) > 1 ? "تم تحويل " . count($ids) . " مصاريف بنجاح ✅" : "تم تحويل الملاحظة إلى مصروف رسمي ✅";
        return redirect()->back()->with('success', $message);

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'حدث خطأ ما أثناء التحويل ❌');
    }
}  public function edit(Expense $expense)
{
    $types = ExpenseType::all(); 
    return view('expense.edit', compact('expense', 'types'));
}

public function update(Request $request, Expense $expense)
{
    $request->validate([
        'expense_type_id' => 'required|exists:expense_types,id',
        'amount' => 'required|numeric|min:0',
        'note' => 'nullable|string|max:500',
        'expense_time' => 'nullable|date',
    ]);

    try {

        $expenseTime = $request->expense_time
            ? Carbon::createFromFormat('Y-m-d\TH:i', $request->expense_time)
            : $expense->created_at;

        $expense->update([
            'expense_type_id' => $request->expense_type_id,
            'amount' => $request->amount,
            'note' => $request->note,
            'created_at' => $expenseTime,
        ]);

        return redirect()
            ->route('expenses.index')
            ->with('success', 'تم تعديل المصروف بنجاح ✅');

    } catch (Exception $e) {

        return redirect()
            ->back()
            ->with('error', 'حدث خطأ أثناء التعديل ❌');
    }
}


}
