<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBudgetReallocationRequest extends FormRequest
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
            'budget_id' => ['required', 'exists:budgets,id'],
            'from_budget_item_id' => ['required', 'exists:budget_items,id'],
            'to_budget_item_id' => ['required', 'exists:budget_items,id', 'different:from_budget_item_id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'justification' => ['required', 'string', 'min:10'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'from_budget_item_id.required' => 'Source budget item is required',
            'to_budget_item_id.required' => 'Destination budget item is required',
            'to_budget_item_id.different' => 'Source and destination must be different',
            'amount.required' => 'Reallocation amount is required',
            'justification.required' => 'Justification is required',
            'justification.min' => 'Justification must be at least 10 characters',
        ];
    }
}
