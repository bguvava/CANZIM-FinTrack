<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateSecuritySettingsRequest extends FormRequest
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
            'session_timeout' => ['required', 'integer', 'min:5', 'max:120'],
            'password_min_length' => ['required', 'integer', 'min:6', 'max:32'],
            'max_login_attempts' => ['required', 'integer', 'min:3', 'max:10'],
            'lockout_duration' => ['required', 'integer', 'min:5', 'max:60'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'session_timeout.required' => 'Session timeout is required',
            'session_timeout.min' => 'Session timeout must be at least 5 minutes',
            'session_timeout.max' => 'Session timeout cannot exceed 120 minutes',
            'password_min_length.min' => 'Minimum password length must be at least 6 characters',
            'max_login_attempts.min' => 'Maximum login attempts must be at least 3',
            'lockout_duration.min' => 'Lockout duration must be at least 5 minutes',
        ];
    }
}
