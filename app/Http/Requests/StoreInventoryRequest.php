<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'company' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:50'],
            'min_quantity' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],

            'keywords' => ['nullable', 'array'],
            'keywords.*' => ['string', 'max:100'],

            // الدفعة الأولى (تُنشأ دائماً عند إضافة دواء جديد)
            'batch_number' => ['required', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:0'],
            'expiry_date' => ['required', 'date'],

            // بيانات العملية (اختيارية)
            'actor_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم الدواء مطلوب',
            'category.required' => 'تصنيف الدواء مطلوب',
            'company.required' => 'الشركة المصنعة مطلوبة',
            'unit.required' => 'وحدة القياس مطلوبة',
            'min_quantity.required' => 'الحد الأدنى للكمية مطلوب',
            'price.required' => 'السعر مطلوب',
            'batch_number.required' => 'رقم الدفعة الأولى مطلوب',
            'quantity.required' => 'الكمية مطلوبة',
            'expiry_date.required' => 'تاريخ انتهاء الصلاحية مطلوب',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $quantity = $this->input('quantity');
            $minQuantity = $this->input('min_quantity');

            if ($quantity !== null && $minQuantity !== null
                && (int) $quantity > 0
                && (int) $minQuantity > (int) $quantity) {
                $validator->errors()->add(
                    'min_quantity',
                    'الحد الأدنى لا يمكن أن يكون أكبر من الكمية الحالية'
                );
            }
        });
    }
}