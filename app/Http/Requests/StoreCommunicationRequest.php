<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommunicationRequest extends FormRequest
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
            'communicable_id' => ['required', 'integer'],
            'communicable_type' => ['required', 'string'],
            'type' => ['required', 'in:email,phone_call,meeting,letter,other,Email,Phone Call,Meeting,Letter,Other'],
            'communication_date' => ['required', 'date'],
            'subject' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,txt,jpg,jpeg,png', 'max:5120'], // 5MB max
            'next_action_date' => ['nullable', 'date', 'after_or_equal:communication_date'],
            'next_action_notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Communication type is required',
            'type.in' => 'Invalid communication type selected',
            'communication_date.required' => 'Communication date is required',
            'subject.required' => 'Subject is required',
            'attachment.mimes' => 'Attachment must be a PDF, DOC, DOCX, TXT, JPG, JPEG, or PNG file',
            'attachment.max' => 'Attachment must not exceed 5MB',
            'next_action_date.after_or_equal' => 'Next action date must be after or equal to communication date',
        ];
    }
}
