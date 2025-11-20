<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateOrganizationSettingsRequest extends FormRequest
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
            'org_name' => ['required', 'string', 'max:255'],
            'org_short_name' => ['required', 'string', 'max:100'],
            'org_address' => ['nullable', 'string', 'max:500'],
            'org_phone' => ['nullable', 'string', 'max:50'],
            'org_email' => ['nullable', 'email', 'max:255'],
            'org_website' => ['nullable', 'url', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'org_name.required' => 'Organization name is required',
            'org_short_name.required' => 'Organization short name is required',
            'org_email.email' => 'Please provide a valid email address',
            'org_website.url' => 'Please provide a valid website URL',
        ];
    }
}
