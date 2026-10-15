<?php
// app/Http/Requests/StoreInvoiceRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_type' => ['required', 'in:product,subscription,booking,session,deposit'],
            'items.*.qty' => ['nullable', 'integer', 'min:1'],

            // حقول التحقق الخاصة بالمنتجات والخدمات الأخرى...
            'items.*.product_id' => ['required_if:items.*.item_type,product', 'integer', 'exists:products,id'],
            'items.*.subscription_id' => ['required_if:items.*.item_type,subscription', 'integer', 'exists:subscriptions,id'],
            'items.*.booking_id' => ['required_if:items.*.item_type,booking', 'integer', 'exists:bookings,id'],
            'items.*.session_id' => ['required_if:items.*.item_type,session', 'integer', 'exists:sessions,id'],
            'items.*.amount' => ['required_if:items.*.item_type,deposit', 'numeric', 'min:0.01'],
            'items.*.description' => ['nullable', 'string', 'max:255'],

            // 👇 إضافة حقول الخصم الجديدة هنا
            'discount_type' => ['nullable', 'string', 'in:fixed,percentage'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'discount_reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string']
        ];
    }
}
