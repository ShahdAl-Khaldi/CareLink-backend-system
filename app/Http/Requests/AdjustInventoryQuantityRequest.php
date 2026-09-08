<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdjustInventoryQuantityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'delta' => ['required', 'integer', 'not_in:0'],
            'type' => ['nullable', 'string', 'in:restock,adjust'],
            'actor_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'delta.required' => 'قيمة التعديل مطلوبة',
            'delta.not_in' => 'قيمة التعديل يجب ألا تساوي صفراً',
        ];
    }
}