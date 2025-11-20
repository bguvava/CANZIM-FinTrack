<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('generate', \App\Models\Report::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date', 'before_or_equal:end_date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date', 'before_or_equal:today'],
            'project_ids' => ['nullable', 'array', 'max:5'],
            'project_ids.*' => ['exists:projects,id'],
            'category_ids' => ['nullable', 'array', 'max:5'],
            'category_ids.*' => ['exists:expense_categories,id'],
            'donor_ids' => ['nullable', 'array', 'max:5'],
            'donor_ids.*' => ['exists:donors,id'],
            'grouping' => ['nullable', 'string', Rule::in(['month', 'quarter', 'year'])],
            'group_by' => ['nullable', 'string', Rule::in(['category', 'project', 'month'])],
            'project_id' => [
                Rule::requiredIf(function () {
                    return str_contains(request()->path(), 'project-status');
                }),
                'exists:projects,id',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'start_date.required' => 'Start date is required.',
            'start_date.before_or_equal' => 'Start date must be before or equal to end date.',
            'end_date.required' => 'End date is required.',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
            'end_date.before_or_equal' => 'End date cannot be in the future.',
            'project_ids.max' => 'You can select a maximum of 5 projects.',
            'category_ids.max' => 'You can select a maximum of 5 categories.',
            'donor_ids.max' => 'You can select a maximum of 5 donors.',
            'project_ids.*.exists' => 'Selected project does not exist.',
            'category_ids.*.exists' => 'Selected category does not exist.',
            'donor_ids.*.exists' => 'Selected donor does not exist.',
            'project_id.required' => 'Project ID is required for project status report.',
            'project_id.exists' => 'Selected project does not exist.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'start_date' => 'start date',
            'end_date' => 'end date',
            'project_ids' => 'projects',
            'category_ids' => 'categories',
            'donor_ids' => 'donors',
            'grouping' => 'grouping period',
            'group_by' => 'group by field',
            'project_id' => 'project',
        ];
    }
}
