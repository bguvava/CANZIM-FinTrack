<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInKindContributionRequest extends FormRequest
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
            'donor_id' => ['required', 'exists:donors,id'],
            'project_id' => ['required', 'exists:projects,id'],
            'description' => ['required', 'string'],
            'estimated_value' => ['required', 'numeric', 'min:0'],
            'contribution_date' => ['required', 'date'],
            'category' => ['required', 'in:equipment,supplies,services,training,other'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'donor_id.required' => 'Please select a donor',
            'project_id.required' => 'Please select a project',
            'description.required' => 'Description is required',
            'estimated_value.required' => 'Estimated value is required',
            'contribution_date.required' => 'Contribution date is required',
            'category.required' => 'Category is required',
        ];
    }
}
