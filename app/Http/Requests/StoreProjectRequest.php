<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'total_budget' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:planning,active,on_hold,completed,cancelled'],
            'office_location' => ['nullable', 'string', 'max:255'],
            'donors' => ['nullable', 'array'],
            'donors.*.donor_id' => ['required', 'exists:donors,id'],
            'donors.*.funding_amount' => ['required', 'numeric', 'min:0'],
            'donors.*.funding_period_start' => ['nullable', 'date'],
            'donors.*.funding_period_end' => ['nullable', 'date', 'after:donors.*.funding_period_start'],
            'donors.*.is_restricted' => ['nullable', 'boolean'],
            'team_members' => ['nullable', 'array'],
            'team_members.*.user_id' => ['required', 'exists:users,id'],
            'team_members.*.role' => ['nullable', 'string', 'in:team_member,project_lead'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Project name is required',
            'start_date.required' => 'Start date is required',
            'end_date.after' => 'End date must be after start date',
            'donors.*.donor_id.exists' => 'Selected donor does not exist',
            'team_members.*.user_id.exists' => 'Selected team member does not exist',
        ];
    }
}
