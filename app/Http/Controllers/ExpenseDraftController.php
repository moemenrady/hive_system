<?php

namespace App\Http\Controllers;

use App\Models\ExpenseDraft;

use App\Models\ExpenseType;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;

use App\Models\Shift;

use App\Models\ShiftAction;

class ExpenseDraftController extends Controller
{
  public function create()
  {
    $drafts = ExpenseDraft::with('user')->orderBy('created_at', 'desc')->get(); // جلب الكل

    $types = ExpenseType::where('user_appearance', 1)->get();

    return view('expense.admin.draft_index', compact('drafts', 'types'));
  }

  public function index()
  {
    $drafts = ExpenseDraft::with('expenseType')
      ->where('created_by', auth()->id())
      ->latest()
      ->paginate(10);
    $types = ExpenseType::where('user_appearance', 1)->get();

    return view('expense.user.index', compact('drafts', "types"));
  }

  public function store(Request $request)
  {

    $request->validate([
      'note' => 'nullable|string|max:255',
      'estimated_amount' => 'required|numeric|min:0',
      'expense_type_id' => 'required|exists:expense_types,id',
    ]);

    $user = Auth::user();
    if ($user->hasRole('admin')) {
      DB::rollBack();
      return redirect()->back()->with('error', '⚠️ الادارة لا تضيف المصروف كملاحظات');
    }
    $openShift = Shift::where('user_id', $user->id)->whereNull('end_time')->first();
    if (!$openShift && !$user->hasRole('admin')) {
      DB::rollBack();
      session()->flash('shift_required', true);
      return redirect()->back()->with('error', '⚠️ لا يوجد شيفت مفتوح، ابدأ شيفت أولاً.');
    }
    // نستخدم transaction عشان إذا حصل خطأ نتراجع
    DB::beginTransaction();

    try {
      // 1) إنشاء الـ ExpenseDraft
      $draft = ExpenseDraft::create([
        'note' => $request->note,
        'estimated_amount' => $request->estimated_amount,
        'expense_type_id' => $request->expense_type_id,
        'created_by' => $user->id,
      ]);

      // 2) تحقق من وجود شيفت مفتوح للموظف (ما عدا الادمن ممكن يسمح)

      if (!$openShift && !$user->hasRole('admin')) {
        DB::rollBack();
        session()->flash('shift_required', true);

        return redirect()->back()->with('error', '⚠️ لا يوجد شيفت مفتوح، ابدأ شيفت أولاً.');
      }

      // 3) لو فيه شيفت مفتوح نحفظ الـ ShiftAction المرتبط بالـ expense draft
      if ($openShift) {
        // نستخدم قيمة expense_amount إن وُجدت وإلا نستخدم estimated_amount
        $expenseAmount = $request->input('expense_amount') ?? $draft->estimated_amount ?? 0;


        ShiftAction::create([
          'shift_id' => $openShift->id,
          'action_type' => 'expense_note',
          'invoice_id' => null, // ضمان عدم ربط فاتورة
          'expense_draft_id' => $draft->id,
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

      DB::commit();

      return redirect()->back()->with('success', 'تم حفظ الملاحظة كـ Draft ✅ وتم إضافتها للشيفت (إن وُجد).');
    } catch (\Throwable $e) {
      DB::rollBack();
      // لو تحب تطبع $e->getMessage() في اللوج أو للـ debug فقط
      \Log::error('ExpenseDraft store error: ' . $e->getMessage());
      return redirect()->back()->with('error', 'حدث خطأ أثناء الحفظ، حاول مرة أخرى.');
    }
  }


  public function bulkDelete(Request $request)
  {
    $request->validate([
      'ids' => 'required|array',
      'ids.*' => 'exists:expense_drafts,id',
    ]);

    try {
      DB::beginTransaction();

      // حذف الـ ShiftActions المرتبطة أولاً لتجنب مشاكل الـ Foreign Key
      ShiftAction::whereIn('expense_draft_id', $request->ids)->delete();

      // حذف الـ Drafts
      ExpenseDraft::whereIn('id', $request->ids)->delete();

      DB::commit();
      return response()->json(['success' => 'تم حذف المصروفات المحددة بنجاح.']);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(['error' => 'حدث خطأ أثناء الحذف.'], 500);
    }
  }
}
