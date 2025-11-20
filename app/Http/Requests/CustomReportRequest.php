<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomReportRequest extends FormRequest
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
            'report_type' => ['required', 'string', Rule::in([
                'budget-vs-actual',
                'cash-flow',
                'expense-summary',
                'project-status',
                'donor-contributions',
            ])],
            'title' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date', 'before_or_equal:end_date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date', 'before_or_equal:today'],
            'filters' => ['nullable', 'array', 'max:5'],
            'filters.project_ids' => ['nullable', 'array', 'max:5'],
            'filters.project_ids.*' => ['exists:projects,id'],
            'filters.category_ids' => ['nullable', 'array', 'max:5'],
            'filters.category_ids.*' => ['exists:expense_categories,id'],
            'filters.donor_ids' => ['nullable', 'array', 'max:5'],
            'filters.donor_ids.*' => ['exists:donors,id'],
            'filters.grouping' => ['nullable', 'string', Rule::in(['month', 'quarter', 'year'])],
            'filters.group_by' => ['nullable', 'string', Rule::in(['category', 'project', 'month'])],
            'filters.project_id' => ['nullable', 'exists:projects,id'],
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
            'report_type.required' => 'Report type is required.',
            'report_type.in' => 'Invalid report type selected.',
            'title.required' => 'Report title is required.',
            'title.max' => 'Report title cannot exceed 255 characters.',
            'start_date.required' => 'Start date is required.',
            'start_date.before_or_equal' => 'Start date must be before or equal to end date.',
            'end_date.required' => 'End date is required.',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
            'end_date.before_or_equal' => 'End date cannot be in the future.',
            'filters.max' => 'You can apply a maximum of 5 filters.',
            'filters.project_ids.max' => 'You can select a maximum of 5 projects.',
            'filters.category_ids.max' => 'You can select a maximum of 5 categories.',
            'filters.donor_ids.max' => 'You can select a maximum of 5 donors.',
            'filters.project_ids.*.exists' => 'Selected project does not exist.',
            'filters.category_ids.*.exists' => 'Selected category does not exist.',
            'filters.donor_ids.*.exists' => 'Selected donor does not exist.',
            'filters.project_id.exists' => 'Selected project does not exist.',
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
            'report_type' => 'report type',
            'title' => 'report title',
            'start_date' => 'start date',
            'end_date' => 'end date',
            'filters' => 'filters',
            'filters.project_ids' => 'projects',
            'filters.category_ids' => 'categories',
            'filters.donor_ids' => 'donors',
            'filters.grouping' => 'grouping period',
            'filters.group_by' => 'group by field',
            'filters.project_id' => 'project',
        ];
    }
}
