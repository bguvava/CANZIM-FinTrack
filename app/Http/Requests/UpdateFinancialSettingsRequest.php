<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateFinancialSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('manage-settings');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'base_currency' => ['required', 'string', 'in:USD,EUR,GBP,ZWL,ZAR'],
            'fiscal_year_start_month' => ['required', 'integer', 'min:1', 'max:12'],
            'date_format' => ['required', 'string', 'in:d/m/Y,m/d/Y,Y-m-d'],
            'datetime_format' => ['required', 'string', 'in:d/m/Y H:i,m/d/Y H:i,Y-m-d H:i'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'base_currency.required' => 'Base currency is required',
            'base_currency.in' => 'Invalid currency selected',
            'fiscal_year_start_month.required' => 'Fiscal year start month is required',
            'fiscal_year_start_month.min' => 'Month must be between 1 and 12',
            'fiscal_year_start_month.max' => 'Month must be between 1 and 12',
        ];
    }
}
