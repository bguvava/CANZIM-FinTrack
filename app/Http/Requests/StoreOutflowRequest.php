<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOutflowRequest extends FormRequest
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
            'bank_account_id' => ['required', 'exists:bank_accounts,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'expense_id' => ['required', 'exists:expenses,id'],
            'transaction_date' => ['required', 'date', 'before_or_equal:today'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:1000'],
            'reference' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'bank_account_id.required' => 'Bank account is required',
            'bank_account_id.exists' => 'Selected bank account does not exist',
            'project_id.exists' => 'Selected project does not exist',
            'expense_id.required' => 'Expense is required',
            'expense_id.exists' => 'Selected expense does not exist',
            'transaction_date.required' => 'Transaction date is required',
            'transaction_date.before_or_equal' => 'Transaction date cannot be in the future',
            'amount.required' => 'Amount is required',
            'amount.min' => 'Amount must be greater than 0',
        ];
    }
}
