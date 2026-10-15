<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Product;
use App\Models\User;
use App\Models\VenuePricing;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    public function discountPercent()
    {
        return view('employees.discountPercent');
    }

    public function indexDiscountPercent()
    {
        // جلب الخصومات الخاصة بالموظفين فقط
        $discounts = VenuePricing::where('is_employee_discount', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('employees.discountPercent', compact('discounts'));
    }

    public function storeDiscountPercent(Request $request)
    {
        $request->validate([
            'base_hour_price' => 'required|numeric|min:0|max:100', // نسبة مئوية
        ]);

        VenuePricing::create([
            'base_hour_price' => $request->base_hour_price,
            'setter_name' => Auth::user()->name, // اسم المستخدم أوتوماتيك
            'is_active' => true,                 // مفعل أوتوماتيك
            'is_employee_discount' => true,      // مخصص للموظفين أوتوماتيك
        ]);

        return redirect()->route('employees.discountPercent')
            ->with('success', 'تم إضافة نسبة الخصم بنجاح');
    }

    public function updateDiscountPercent(Request $request, $id)
    {
        $request->validate([
            'base_hour_price' => 'required|numeric|min:0|max:100',
        ]);

        $discount = VenuePricing::where('is_employee_discount', true)->findOrFail($id);

        $discount->update([
            'base_hour_price' => $request->base_hour_price,
            'setter_name' => Auth::user()->name, // تحديث اسم المعدل
        ]);

        return redirect()->route('employees.discountPercent')
            ->with('success', 'تم تعديل نسبة الخصم بنجاح');
    }

    public function destroyDiscountPercent($id)
    {
        $discount = VenuePricing::where('is_employee_discount', true)->findOrFail($id);
        $discount->delete();

        return redirect()->route('employees.discountPercent')
            ->with('success', 'تم حذف نسبة الخصم بنجاح');
    }
    public function index()
    {
        $employees = Employee::with('user')->latest()->paginate(20);

        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        $users = User::all();
        return view('employees.create', compact('users'));
    }

    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required',
            'phone' => 'nullable',
            'salary' => 'required|numeric',
            'user_id' => 'nullable|exists:users,id'
        ]);

        Employee::create($request->all());

        return redirect()
            ->route('employees.index')
            ->with('success', 'تم إضافة الموظف');
    }

    // داخل EmployeeController.php
    public function show($id)
    {
        $employee = Employee::with(['transactions' => function ($q) {
            $q->orderBy('created_at', 'desc');
        }, 'user'])->findOrFail($id);

        // تجميع المسحوبات حسب الشهر للسجل التاريخي
        $history = $employee->transactions->groupBy(function ($date) {
            return \Carbon\Carbon::parse($date->created_at)->format('Y-m');
        });

        // بيانات الشهر الحالي فقط
        $currentMonth = now()->format('Y-m');
        $currentTransactions = $employee->transactions->filter(function ($t) use ($currentMonth) {
            return $t->created_at->format('Y-m') === $currentMonth;
        });

        // حساب المجاميع للشهر الحالي
        $totalAdvances = $currentTransactions->where('type', 'advance')->sum('amount');
        $totalPurchases = $currentTransactions->where('type', 'purchase')->sum('amount');
        $totalDeductions = $currentTransactions->where('type', 'deduction')->sum('amount');
        $totalBonuses = $currentTransactions->where('type', 'bonus')->sum('amount'); // الإضافة الجديدة

        // إجمالي الخصومات كلها
        $totalDiscounts = $totalAdvances + $totalPurchases + $totalDeductions;

        // صافي المرتب = (الأساسي + المكافآت) - الخصومات
        $netSalary = ($employee->salary + $totalBonuses) - $totalDiscounts;

        return view('employees.show', compact(
            'employee',
            'history',
            'totalAdvances',
            'totalPurchases',
            'totalDeductions',
            'totalBonuses',
            'totalDiscounts',
            'netSalary',
            'currentTransactions'
        ));
    }

    public function edit($id)
    {
        $employee = Employee::with('transactions.product')->findOrFail($id);

        // جلب المنتجات لربطها بالمشتريات عند إضافة عملية جديدة
        $products = Product::all();

        // حسابات الشهر الحالي
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $currentTransactions = $employee->transactions()
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalAdvances = $currentTransactions->where('type', 'advance')->sum('amount');
        $totalPurchases = $currentTransactions->where('type', '!=', 'advance')->sum('amount');
        $netSalary = $employee->salary - ($totalAdvances + $totalPurchases);

        // السجل التاريخي مقسم ومجمع حسب الشهر (باستثناء الشهر الحالي)
        $history = $employee->transactions()
            ->where(function ($query) use ($currentMonth, $currentYear) {
                $query->whereMonth('created_at', '!=', $currentMonth)
                    ->orWhereYear('created_at', '!=', $currentYear);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('Y-m');
            });

        return view('employees.edit', compact(
            'employee',
            'products',
            'currentTransactions',
            'totalAdvances',
            'totalPurchases',
            'netSalary',
            'history'
        ));
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string',
            'salary' => 'required|numeric|min:0',
        ]);

        $employee->update($request->only(['name', 'phone', 'salary']));

        return redirect()->back()->with('success', 'تم تحديث بيانات الموظف بنجاح');
    }



    public function destroy(Employee $employee)
    {

        $employee->delete();

        return redirect()
            ->route('employees.index')
            ->with('success', 'تم حذف الموظف');
    }
}
