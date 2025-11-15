<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['sometimes', 'required', 'exists:projects,id'],
            'budget_item_id' => ['nullable', 'exists:budget_items,id'],
            'purchase_order_id' => ['nullable', 'exists:purchase_orders,id'],
            'expense_category_id' => ['sometimes', 'required', 'exists:expense_categories,id'],
            'expense_date' => ['sometimes', 'required', 'date', 'before_or_equal:today'],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0.01'],
            'currency' => ['nullable', 'string', 'size:3'],
            'description' => ['sometimes', 'required', 'string', 'max:1000'],
            'receipt' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'project_id.exists' => 'Selected project does not exist',
            'expense_category_id.exists' => 'Selected expense category does not exist',
            'expense_date.before_or_equal' => 'Expense date cannot be in the future',
            'amount.min' => 'Amount must be greater than 0',
            'receipt.mimes' => 'Receipt must be a PDF, JPG, JPEG, or PNG file',
            'receipt.max' => 'Receipt file size must not exceed 5MB',
        ];
    }
}
