<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBankAccountRequest extends FormRequest
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
            'account_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:50', 'unique:bank_accounts,account_number'],
            'bank_name' => ['required', 'string', 'max:255'],
            'branch' => ['nullable', 'string', 'max:255'],
            'currency' => ['required', 'string', 'size:3'],
            'current_balance' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'account_name.required' => 'Account name is required',
            'account_number.required' => 'Account number is required',
            'account_number.unique' => 'This account number already exists',
            'bank_name.required' => 'Bank name is required',
            'currency.required' => 'Currency is required',
            'currency.size' => 'Currency must be a 3-letter code (e.g., USD, ZAR)',
            'current_balance.required' => 'Current balance is required',
            'current_balance.min' => 'Balance cannot be negative',
        ];
    }
}
