<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MarkReceivedRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:purchase_order_items,id'],
            'items.*.quantity_received' => ['required', 'numeric', 'min:0.01'],
            'items.*.received_date' => ['nullable', 'date', 'before_or_equal:today'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'items.required' => 'At least one item must be marked as received',
            'items.min' => 'At least one item must be marked as received',
            'items.*.item_id.required' => 'Item ID is required',
            'items.*.item_id.exists' => 'Selected item does not exist',
            'items.*.quantity_received.required' => 'Received quantity is required',
            'items.*.quantity_received.min' => 'Received quantity must be greater than 0',
            'items.*.received_date.before_or_equal' => 'Received date cannot be in the future',
        ];
    }
}
