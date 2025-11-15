<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'required', 'date', 'after:start_date'],
            'total_budget' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:planning,active,on_hold,completed,cancelled'],
            'office_location' => ['nullable', 'string', 'max:255'],
            'donors' => ['nullable', 'array'],
            'donors.*.donor_id' => ['required', 'exists:donors,id'],
            'donors.*.funding_amount' => ['required', 'numeric', 'min:0'],
            'donors.*.funding_period_start' => ['nullable', 'date'],
            'donors.*.funding_period_end' => ['nullable', 'date'],
            'donors.*.is_restricted' => ['nullable', 'boolean'],
            'team_members' => ['nullable', 'array'],
            'team_members.*.user_id' => ['required', 'exists:users,id'],
            'team_members.*.role' => ['nullable', 'string', 'in:team_member,project_lead'],
        ];
    }
}
