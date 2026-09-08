<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateInventoryRequest extends FormRequest
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

            // وضع الدفعة الواحدة: تُرسل الكمية والصلاحية مباشرة
            'quantity' => ['required_without:batches', 'integer', 'min:0'],
            'expiry_date' => ['required_without:batches', 'date'],

            // وضع الدفعات المتعددة: تُرسل قائمة الدفعات كاملة (استبدال شامل)
            'batches' => ['required_without:quantity', 'array', 'min:1'],
            'batches.*.id' => ['nullable', 'integer'],
            'batches.*.batch_number' => ['required_with:batches', 'string', 'max:100'],
            'batches.*.quantity' => ['required_with:batches', 'integer', 'min:0'],
            'batches.*.expiry_date' => ['required_with:batches', 'date'],

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
            'quantity.required_without' => 'الكمية يجب أن تكون رقماً صالحاً',
            'expiry_date.required_without' => 'تاريخ انتهاء الصلاحية مطلوب',
            'batches.min' => 'يجب الإبقاء على دفعة واحدة على الأقل',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->has('batches')) {
                return;
            }

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