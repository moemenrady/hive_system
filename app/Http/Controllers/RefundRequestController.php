<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\RefundRequest;
use App\Models\Shift;
use App\Models\ShiftAction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RefundRequestController extends Controller
{
    /**
     * صفحة طلبات الارتجاع الخاصة بالموظف
     */
    public function index()
    {
        $user = Auth::user();

        $refundRequests = RefundRequest::with(['shift', 'approvedBy', 'invoice'])
            ->where('created_by', $user->id)
            ->latest()
            ->paginate(15);

        $openShift = Shift::where('user_id', $user->id)
            ->whereNull('end_time')
            ->first();

        // جلب الفواتير الصادرة حصراً في هذا الشيفت المفتوح للموظف
        $shiftInvoices = collect();
        if ($openShift) {
            $shiftActionInvoices = ShiftAction::where('shift_id', $openShift->id)
                ->whereNotNull('invoice_id')
                ->pluck('payment_type', 'invoice_id');

            $shiftInvoices = Invoice::with(['client', 'items'])
                ->whereIn('id', $shiftActionInvoices->keys())
                ->latest()
                ->get()
                ->map(function ($inv) use ($shiftActionInvoices) {
                    $inv->shift_payment_type = $shiftActionInvoices[$inv->id] ?? 'cash';
                    $itemsSummary = $inv->items->map(function ($item) {
                        return $item->name . ($item->qty > 1 ? " × {$item->qty}" : "");
                    })->implode('، ');
                    $inv->items_summary = $itemsSummary ?: 'فاتورة عامة';
                    return $inv;
                });
        }

        $stats = [
            'pending' => RefundRequest::where('created_by', $user->id)->where('status', 'pending')->count(),
            'approved' => RefundRequest::where('created_by', $user->id)->where('status', 'approved')->count(),
            'rejected' => RefundRequest::where('created_by', $user->id)->where('status', 'rejected')->count(),
            'total_amount' => RefundRequest::where('created_by', $user->id)->where('status', 'approved')->sum('amount'),
        ];

        return view('refunds.user.index', compact('refundRequests', 'openShift', 'shiftInvoices', 'stats'));
    }

    /**
     * إرسال طلب ارتجاع جديد من الموظف وخصمه فورياً من الشيفت
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'required|string|min:3|max:500',
            'payment_type' => 'nullable|in:cash,digital',
            'invoice_id' => 'nullable|integer',
            'invoice_number' => 'nullable|string|max:100',
        ], [
            'amount.required' => 'يرجى إدخال مبلغ الارتجاع',
            'amount.min' => 'مبلغ الارتجاع يجب أن يكون أكبر من صفر',
            'notes.required' => 'يرجى تسجيل ملاحظة توضح سبب الارتجاع',
            'notes.min' => 'ملاحظة سبب الارتجاع يجب أن تكون 3 أحرف على الأقل',
        ]);

        $user = Auth::user();
        $openShift = Shift::where('user_id', $user->id)->whereNull('end_time')->first();

        // الموظف لازم يكون فاتح شيفت عشان يخصم منه
        if (!$openShift && !$user->hasRole('admin')) {
            session()->flash('shift_required', true);
            return redirect()->back()
                ->withInput()
                ->with('error', '⚠️ لا يوجد شيفت مفتوح. يجب فتح شيفت أولاً لتسجيل طلب الارتجاع وخصمه منه.');
        }

        // محاولة ربط الفاتورة إن اختار من الشيفت أو أدخل رقمها يدوياً
        $invoice = null;
        if ($request->filled('invoice_id')) {
            $invId = (int) $request->invoice_id;
            if ($openShift) {
                // التأكد من أن الفاتورة تابعة للشيفت المفتوح حصراً
                $isShiftInvoice = ShiftAction::where('shift_id', $openShift->id)
                    ->where('invoice_id', $invId)
                    ->exists();

                if ($isShiftInvoice) {
                    $invoice = Invoice::find($invId);
                }
            } else {
                $invoice = Invoice::find($invId);
            }
        } elseif ($request->filled('invoice_number')) {
            $invNum = trim($request->invoice_number);
            $invoice = Invoice::where('invoice_number', $invNum)
                ->orWhere('id', $invNum)
                ->first();
        }

        DB::beginTransaction();
        try {
            $amount = floatval($request->amount);
            $paymentType = $request->payment_type ?: 'cash';

            // 1) إنشاء طلب الارتجاع في الحالة المعلقة
            $refundRequest = RefundRequest::create([
                'created_by' => $user->id,
                'shift_id' => $openShift?->id,
                'amount' => $amount,
                'notes' => $request->notes,
                'payment_type' => $paymentType,
                'invoice_id' => $invoice?->id,
                'client_id' => $invoice?->client_id,
                'status' => 'pending',
            ]);

            // 2) الخصم الفوري من الشيفت وتسجيل الـ ShiftAction
            if ($openShift) {
                ShiftAction::create([
                    'shift_id' => $openShift->id,
                    'action_type' => 'refund_request',
                    'refund_request_id' => $refundRequest->id,
                    'invoice_id' => $invoice?->id,
                    'amount' => 0, // ليس إيراد
                    'expense_amount' => $amount, // يُخصم كمصروف/خروج نقدية من الشيفت
                    'payment_type' => $paymentType,
                    'notes' => 'طلب ارتجاع (معلق): ' . $request->notes,
                ]);

                // تحديث إجمالي مصروفات/خصومات الشيفت
                $openShift->total_expense += $amount;
                $openShift->save();
            }

            DB::commit();

            return redirect()->back()->with('success', 'تم حفظ طلب الارتجاع وخصم المبلغ من الشيفت بنجاح ✅ — الطلب الآن معلق في انتظار تأكيد الإدارة.');
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('RefundRequest store error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'حدث خطأ أثناء حفظ طلب الارتجاع: ' . $e->getMessage());
        }
    }

    /**
     * صفحة إدارة طلبات الارتجاع للمدير
     */
    public function adminIndex(Request $request)
    {
        $query = RefundRequest::with(['user', 'shift', 'approvedBy', 'invoice', 'client']);

        // فلترة بالحالة
        if ($request->filled('status') && in_array($request->status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $request->status);
        }

        // فلترة بالبحث (اسم الموظف أو الملاحظة أو رقم الفاتورة)
        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('notes', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($u) use ($q) {
                        $u->where('name', 'like', "%{$q}%");
                    })
                    ->orWhereHas('invoice', function ($inv) use ($q) {
                        $inv->where('invoice_number', 'like', "%{$q}%");
                    });
            });
        }

        // فلترة بالتاريخ
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $refundRequests = $query->latest()->paginate(20)->withQueryString();

        $counts = [
            'all' => RefundRequest::count(),
            'pending' => RefundRequest::where('status', 'pending')->count(),
            'approved' => RefundRequest::where('status', 'approved')->count(),
            'rejected' => RefundRequest::where('status', 'rejected')->count(),
            'pending_amount' => RefundRequest::where('status', 'pending')->sum('amount'),
            'approved_amount' => RefundRequest::where('status', 'approved')->sum('amount'),
        ];

        return view('refunds.admin.index', compact('refundRequests', 'counts'));
    }

    /**
     * تأكيد واعتماد طلب الارتجاع من قبل المدير
     */
    public function approve(RefundRequest $refundRequest)
    {
        if (!$refundRequest->isPending()) {
            return redirect()->back()->with('error', 'هذا الطلب تم اتخاذ إجراء عليه مسبقاً.');
        }

        DB::beginTransaction();
        try {
            $refundRequest->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            // تحديث نص ShiftAction ليعكس الاعتماد
            ShiftAction::where('refund_request_id', $refundRequest->id)->each(function ($action) use ($refundRequest) {
                $action->notes = 'طلب ارتجاع (مؤكد ومعتمد من الإدارة): ' . $refundRequest->notes;
                $action->save();
            });

            DB::commit();

            return redirect()->back()->with('success', 'تم تأكيد واعتماد طلب الارتجاع بنجاح ✅');
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('RefundRequest approve error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء اعتماد الطلب: ' . $e->getMessage());
        }
    }

    /**
     * رفض طلب الارتجاع وإلغاء الخصم من الشيفت
     */
    public function reject(Request $request, RefundRequest $refundRequest)
    {
        if (!$refundRequest->isPending()) {
            return redirect()->back()->with('error', 'هذا الطلب تم اتخاذ إجراء عليه مسبقاً.');
        }

        $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $refundRequest->update([
                'status' => 'rejected',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejection_reason' => $request->rejection_reason,
            ]);

            // إلغاء الخصم من الشيفت الأصلي
            if ($refundRequest->shift_id) {
                // 1) تصفير قيمة المصروف من الحركة الأصلية لطلب الارتجاع حتى لا يُحسب في sum('expense_amount')
                $originalAction = ShiftAction::where('refund_request_id', $refundRequest->id)
                    ->where('action_type', 'refund_request')
                    ->first();
                if ($originalAction) {
                    $originalAction->expense_amount = 0;
                    $originalAction->notes = trim(($originalAction->notes ?: '') . ' [تم الرفض من الإدارة: ' . ($request->rejection_reason ?: 'بدون إبداء أسباب') . ']');
                    $originalAction->save();
                }

                // 2) تحديث إجمالي المصروفات في جدول الشيفت إذا كان الشيفت مفتوحاً
                $shift = Shift::find($refundRequest->shift_id);
                if ($shift && is_null($shift->end_time)) {
                    $shift->total_expense = max(0, (float)$shift->total_expense - (float)$refundRequest->amount);
                    $shift->save();
                }

                // 3) تسجيل حركة تصحيحية لإلغاء الخصم للتدقيق المالي
                ShiftAction::create([
                    'shift_id' => $refundRequest->shift_id,
                    'action_type' => 'refund_rejected',
                    'refund_request_id' => $refundRequest->id,
                    'invoice_id' => $refundRequest->invoice_id,
                    'amount' => 0,
                    'expense_amount' => 0,
                    'payment_type' => $refundRequest->payment_type,
                    'notes' => 'إلغاء خصم الارتجاع (مرفوض من الإدارة): ' . ($request->rejection_reason ?: $refundRequest->notes),
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'تم رفض طلب الارتجاع وإلغاء الخصم من الشيفت بنجاح ❌');
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('RefundRequest reject error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء رفض الطلب: ' . $e->getMessage());
        }
    }
}
