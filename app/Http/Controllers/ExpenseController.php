<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseDraft;
use App\Models\ExpenseType;
use App\Models\Shift;
use App\Models\ShiftAction;
use App\Services\AnalyticsService;
use Auth;
use DB;
use Exception;
use Illuminate\Http\Request;
use Carbon\Carbon;

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

      if ($openShift) {
        $expenseAmount = $expense->amount ?? $request->input('amount') ?? 0;
        ShiftAction::create([
          'shift_id' => $openShift->id,
          'action_type' => 'expense_note',
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

  public function convertFromDraft(Request $request, ExpenseDraft $draft)
  {
    $request->validate([
      'expense_type_id' => 'required|exists:expense_types,id',
      'amount' => 'required|numeric|min:0',
    ]);

    // إنشاء مصروف رسمي
    try {
      // أنشئ المصروف بدون استخدام create (لكي نتحكم بالتواريخ)
      $expense = new Expense();
      $expense->expense_type_id = $request->expense_type_id;
      $expense->amount = $request->amount;
      $expense->note = $draft->note;
      $expense->added_by = auth()->id();

      // اضبط created_at و updated_at بنفس تاريخ الدرافت
      $expense->created_at = $draft->created_at;
      $expense->updated_at = $draft->created_at;

      // منع Laravel من إعادة كتابة timestamps
      $expense->timestamps = false;
      $expense->save();

      // بعد التحويل نحذف الدرافت
      $draft->delete();

      return redirect()->back()->with('success', 'تم تحويل الملاحظة إلى مصروف رسمي ✅');

    } catch (Exception $e) {
      return redirect()->back()->with('error', 'حدث خطأ ما ❌');

    }
  }
}
