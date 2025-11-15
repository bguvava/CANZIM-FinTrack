<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBudgetRequest extends FormRequest
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
            'project_id' => ['required', 'exists:projects,id'],
            'fiscal_year' => ['required', 'string', 'regex:/^\d{4}$/'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.category' => ['required', 'string', 'in:Travel,Staff Salaries,Procurement/Supplies,Consultants,Other'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.cost_code' => ['nullable', 'string', 'max:50'],
            'items.*.allocated_amount' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'project_id.required' => 'Project is required',
            'project_id.exists' => 'Selected project does not exist',
            'fiscal_year.required' => 'Fiscal year is required',
            'items.required' => 'At least one budget item is required',
            'items.*.category.required' => 'Category is required for all budget items',
            'items.*.allocated_amount.required' => 'Allocated amount is required for all budget items',
        ];
    }
}
