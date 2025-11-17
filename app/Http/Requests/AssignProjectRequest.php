<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignProjectRequest extends FormRequest
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
            'funding_amount' => ['required', 'numeric', 'min:0.01'],
            'funding_period_start' => ['nullable', 'date'],
            'funding_period_end' => ['nullable', 'date', 'after:funding_period_start'],
            'is_restricted' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'project_id.required' => 'Please select a project',
            'project_id.exists' => 'Selected project does not exist',
            'funding_amount.required' => 'Funding amount is required',
            'funding_amount.min' => 'Funding amount must be greater than zero',
            'funding_period_end.after' => 'End date must be after start date',
        ];
    }
}
