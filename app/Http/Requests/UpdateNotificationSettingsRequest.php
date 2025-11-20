<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateNotificationSettingsRequest extends FormRequest
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
            'notifications_expense_approvals' => ['required', 'boolean'],
            'notifications_budget_alerts' => ['required', 'boolean'],
            'notifications_project_milestones' => ['required', 'boolean'],
            'notifications_comment_mentions' => ['required', 'boolean'],
            'notifications_report_generation' => ['required', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'notifications_expense_approvals.boolean' => 'Invalid value for expense approvals notification',
            'notifications_budget_alerts.boolean' => 'Invalid value for budget alerts notification',
            'notifications_project_milestones.boolean' => 'Invalid value for project milestones notification',
            'notifications_comment_mentions.boolean' => 'Invalid value for comment mentions notification',
            'notifications_report_generation.boolean' => 'Invalid value for report generation notification',
        ];
    }
}
