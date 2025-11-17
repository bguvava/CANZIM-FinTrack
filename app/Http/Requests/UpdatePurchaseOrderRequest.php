<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UpdatePurchaseOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $purchaseOrder = $this->route('purchaseOrder');

        if ($purchaseOrder && ! in_array($purchaseOrder->status, ['Draft', 'Rejected'])) {
            throw ValidationException::withMessages([
                'status' => 'Cannot update purchase order that is not in draft status',
            ]);
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'vendor_id' => ['sometimes', 'required', 'exists:vendors,id'],
            'project_id' => ['sometimes', 'required', 'exists:projects,id'],
            'order_date' => ['sometimes', 'required', 'date', 'before_or_equal:today'],
            'expected_delivery_date' => ['nullable', 'date', 'after:order_date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'terms_conditions' => ['nullable', 'string', 'max:2000'],
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit' => ['required', 'string', 'max:50'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'vendor_id.exists' => 'Selected vendor does not exist',
            'project_id.exists' => 'Selected project does not exist',
            'expected_delivery_date.after' => 'Expected delivery date must be after order date',
            'items.min' => 'At least one item is required',
            'items.*.description.required' => 'Item description is required',
            'items.*.quantity.required' => 'Item quantity is required',
            'items.*.quantity.min' => 'Quantity must be greater than 0',
            'items.*.unit.required' => 'Item unit is required',
            'items.*.unit_price.required' => 'Unit price is required',
            'items.*.unit_price.min' => 'Unit price must be greater than 0',
        ];
    }
}
