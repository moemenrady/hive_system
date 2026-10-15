<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeTransaction;
use App\Models\VenuePricing;
use Carbon\Carbon;

class EmployeeFreeDrinkService
{
    /**
     * هل الموظف استخدم مجاني النهارده؟
     */
    public function hasUsedFreeToday(Employee $employee): bool
    {
        return EmployeeTransaction::where('employee_id', $employee->id)
            ->whereDate('created_at', Carbon::today())
            ->where('type', 'purchase')
            ->where('amount', 0)
            ->exists();
    }

    /**
     * عدد المشروبات المجانية المستخدمة اليوم
     */
    public function freeUsedToday(Employee $employee): int
    {
        return EmployeeTransaction::where('employee_id', $employee->id)
            ->whereDate('created_at', Carbon::today())
            ->where('type', 'purchase')
            ->where('amount', 0)
            ->count();
    }

    /**
     * هل يحق له مجاني؟
     */
    public function canTakeFree(Employee $employee): bool
    {
        return !$this->hasUsedFreeToday($employee);
    }

    /**
     * تطبيق التسعير على items
     */
    public function calculateItems(Employee $employee, array $items): array
    {
        $result = [];

        // 👇 هل استخدم المجاني؟
        $hasFreeLeft = !$this->hasUsedFreeToday($employee);

        // 👇 نجيب إعدادات الخصم
        $employeeDiscount = VenuePricing::where('is_active', true)
            ->where('is_employee_discount', true)
            ->latest()
            ->first();

        // 👇 نسبة الخصم
        $discountPercent = $employeeDiscount?->base_hour_price ?? 0;

        foreach ($items as $item) {

            $type = $item['type'];

            // =========================
            // 💰 السلف
            // =========================
            if ($type === 'advance') {

                $result[] = [
                    'product_id' => null,
                    'quantity'   => 0,
                    'amount'     => $item['amount'],
                    'is_free'    => false,
                    'note'       => $item['note'] ?? null,
                    'type'       => 'advance',
                ];

                continue;
            }
            // =========================
            // 🎁 BONUS
            // =========================
            if ($type === 'bonus') {

                $result[] = [
                    'product_id' => null,
                    'quantity'   => 0,
                    'amount'     => $item['amount'],
                    'is_free'    => false,
                    'note'       => $item['note'] ?? null,
                    'type'       => 'bonus',
                ];

                continue;
            }
            if ($type === 'deduction') {

                $result[] = [
                    'product_id'      => null,
                    'quantity'        => 0,
                    'amount'          => $item['amount'],
                    'is_free'         => false,
                    'deduction_type'  => $item['deduction_type'] ?? null,
                    'note'            => $item['note'] ?? null,
                    'type'            => 'deduction',
                ];

                continue;
            }
            // =========================
            // 🛒 المشتريات
            // =========================
            if ($type === 'purchase') {

                $price = $item['price'] ?? 0;
                $quantity = $item['quantity'] ?? 1;

                $isFree = false;

                // السعر الأصلي
                $originalAmount = $price * $quantity;

                $amount = $originalAmount;

                // ===================================
                // 🎁 أول مشروب مجاني
                // ===================================
                if ($hasFreeLeft) {

                    $amount = 0;
                    $isFree = true;
                    $hasFreeLeft = false;
                } else {

                    // ===================================
                    // 👨‍💼 خصم الموظفين
                    // ===================================
                    if ($discountPercent > 0) {

                        $discountValue = ($originalAmount * $discountPercent) / 100;

                        $amount = $originalAmount - $discountValue;
                    }
                }

                $result[] = [
                    'product_id' => $item['product_id'],
                    'quantity'   => $quantity,
                    'amount'     => $amount,
                    'is_free'    => $isFree,
                    'note' => $isFree
                        ? "مشروب مجاني لليوم - {$item['product']->name}"
                        : ($discountPercent > 0
                            ? "تم تطبيق خصم موظف {$discountPercent}% على {$item['product']->name}"
                            : ($item['note'] ?? null)),
                    'type'       => 'purchase',
                ];
            }
        }

        return $result;
    }
}
